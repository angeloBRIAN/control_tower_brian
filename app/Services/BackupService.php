<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use App\Models\BackupLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class BackupService
{
    protected $disk = 'local';
    protected $backupFolder = 'backups';

    public function create($remark = null)
    {
        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql.gz';
        $path = storage_path('app/' . $this->backupFolder . '/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $config = config('database.connections.mysql');
        
        // Create temporary SQL file first, then gzip (more reliable than piping)
        $tempSqlPath = $path . '.tmp.sql';
        
        // Build mysqldump command - capture stderr, disable SSL for internal Docker network
        // --no-tablespaces avoids PROCESS privilege requirement
        $command = sprintf(
            'mysqldump --skip-ssl --no-tablespaces --user=%s --password=%s --host=%s --port=%s %s 2>/dev/null',
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['database'])
        );

        // Execute and capture output
        $sqlContent = shell_exec($command);
        
        // Check if mysqldump returned valid SQL (should start with comments or SET)
        if (empty($sqlContent) || strlen($sqlContent) < 100) {
            // Try again with stderr to get actual error
            $errorCommand = sprintf(
                'mysqldump --skip-ssl --no-tablespaces --user=%s --password=%s --host=%s --port=%s %s 2>&1',
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['database'])
            );
            $errorOutput = shell_exec($errorCommand);
            throw new \Exception('Backup failed: ' . ($errorOutput ?: 'mysqldump returned empty output'));
        }

        // Write SQL to temp file
        file_put_contents($tempSqlPath, $sqlContent);
        
        // Gzip the file
        $gzHandle = gzopen($path, 'wb9');
        if (!$gzHandle) {
            unlink($tempSqlPath);
            throw new \Exception('Failed to create gzip file');
        }
        gzwrite($gzHandle, $sqlContent);
        gzclose($gzHandle);
        
        // Clean up temp file
        if (file_exists($tempSqlPath)) {
            unlink($tempSqlPath);
        }

        // Get accurate file size
        clearstatcache(true, $path);
        $fileSize = file_exists($path) ? filesize($path) : 0;
        
        if ($fileSize < 100) {
            throw new \Exception('Backup file is too small (' . $fileSize . ' bytes), backup may have failed');
        }

        // Create BackupLog record
        BackupLog::create([
            'filename' => $filename,
            'path' => $this->backupFolder . '/' . $filename,
            'disk' => $this->disk,
            'size' => $fileSize,
            'remark' => $remark,
            'created_by' => Auth::check() ? Auth::user()->name : 'System/Scheduler',
        ]);

        return $filename;
    }

    public function list()
    {
        // Return BackupLogs ordered by latest
        return BackupLog::latest()->get();
    }

    public function restore($filename)
    {
        $path = storage_path('app/' . $this->backupFolder . '/' . $filename);
        
        if (!file_exists($path)) {
            throw new \Exception('Backup file not found.');
        }

        $this->restoreFromPath($path, $filename);
        return true;
    }

    /**
     * Restore from uploaded file
     */
    public function restoreFromFile(UploadedFile $file)
    {
        // Save uploaded file temporarily
        $tempPath = storage_path('app/temp_restore_' . time() . '.sql.gz');
        $file->move(dirname($tempPath), basename($tempPath));

        try {
            $this->restoreFromPath($tempPath, $file->getClientOriginalName());
        } finally {
            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return true;
    }

    /**
     * Common restore logic
     */
    protected function restoreFromPath($path, $filename)
    {
        $config = config('database.connections.mysql');
        
        // Detect if file is gzipped
        $isGzipped = str_ends_with(strtolower($filename), '.gz');
        
        if ($isGzipped) {
            $command = sprintf(
                'gunzip < %s | mysql --skip-ssl --user=%s --password=%s --host=%s --port=%s %s',
                escapeshellarg($path),
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['database'])
            );
        } else {
            $command = sprintf(
                'mysql --skip-ssl --user=%s --password=%s --host=%s --port=%s %s < %s',
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['database']),
                escapeshellarg($path)
            );
        }

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Restore failed with exit code ' . $returnVar);
        }
        
        // Log the restoration action
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'RESTORE',
            'model_type' => 'Database',
            'model_id' => 0,
            'details' => json_encode([
                'file' => $filename,
                'restored_by' => Auth::check() ? Auth::user()->name : 'System',
                'timestamp' => now()->toDateTimeString()
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public function delete($filename)
    {
        $path = $this->backupFolder . '/' . $filename;
        
        // Delete file
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
        
        // Delete log record
        BackupLog::where('filename', $filename)->delete();
        
        return true;
    }
    
    public function download($filename)
    {
         $path = $this->backupFolder . '/' . $filename;
         if (Storage::disk($this->disk)->exists($path)) {
             return Storage::disk($this->disk)->download($path);
         }
         return null;
    }

    /**
     * Delete multiple backups
     */
    public function deleteBatch(array $filenames): int
    {
        $deleted = 0;
        foreach ($filenames as $filename) {
            try {
                $this->delete($filename);
                $deleted++;
            } catch (\Exception $e) {
                // Continue with other files
            }
        }
        return $deleted;
    }

    /**
     * Prune old backups based on retention policy (like borgbackup)
     */
    public function prune(int $keepDaily = 7, int $keepWeekly = 4, int $keepMonthly = 6): array
    {
        $backups = BackupLog::orderByDesc('created_at')->get();
        
        $keepSet = [];
        $dailyCounts = [];
        $weeklyCounts = [];
        $monthlyCounts = [];

        foreach ($backups as $backup) {
            $date = $backup->created_at;
            $dayKey = $date->format('Y-m-d');
            $weekKey = $date->format('Y-W');
            $monthKey = $date->format('Y-m');

            $keep = false;

            // Keep daily
            if (!isset($dailyCounts[$dayKey])) {
                $dailyCounts[$dayKey] = 0;
            }
            if ($dailyCounts[$dayKey] < 1 && count($dailyCounts) <= $keepDaily) {
                $keep = true;
                $dailyCounts[$dayKey]++;
            }

            // Keep weekly (first of each week)
            if (!isset($weeklyCounts[$weekKey])) {
                $weeklyCounts[$weekKey] = 0;
            }
            if ($weeklyCounts[$weekKey] < 1 && count($weeklyCounts) <= $keepWeekly) {
                $keep = true;
                $weeklyCounts[$weekKey]++;
            }

            // Keep monthly (first of each month)
            if (!isset($monthlyCounts[$monthKey])) {
                $monthlyCounts[$monthKey] = 0;
            }
            if ($monthlyCounts[$monthKey] < 1 && count($monthlyCounts) <= $keepMonthly) {
                $keep = true;
                $monthlyCounts[$monthKey]++;
            }

            if ($keep) {
                $keepSet[$backup->filename] = true;
            }
        }

        // Delete backups not in keep set
        $deleted = [];
        foreach ($backups as $backup) {
            if (!isset($keepSet[$backup->filename])) {
                try {
                    $this->delete($backup->filename);
                    $deleted[] = $backup->filename;
                } catch (\Exception $e) {
                    // Continue
                }
            }
        }

        return [
            'kept' => count($keepSet),
            'deleted' => count($deleted),
            'deleted_files' => $deleted,
        ];
    }
}
