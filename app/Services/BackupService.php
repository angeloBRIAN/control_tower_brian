<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use App\Models\BackupLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupService
{
    protected $disk = 'local';
    protected $backupFolder = 'backups';

    public function create($remark = null)
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $zipFilename = 'backup-' . $timestamp . '.zip';
        $zipPath = storage_path('app/' . $this->backupFolder . '/' . $zipFilename);
        
        // Ensure backup directory exists
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $tempDir = storage_path('app/temp_backup_' . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $sqlPath = $tempDir . '/database.sql';

        try {
            // 1. Generate SQL Dump
            $this->generateSqlDump($sqlPath);

            // 2. Create ZIP
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Cannot open zip file: $zipPath");
            }

            // Add SQL file
            $zip->addFile($sqlPath, 'database.sql');

            // 3. Add Storage Files (Remarks)
            $remarksPath = storage_path('app/public/remarks');
            if (File::exists($remarksPath)) {
                $files = File::allFiles($remarksPath);
                foreach ($files as $file) {
                    // Add to zip relative to storage root: storage/remarks/filename.jpg
                    $relativePath = 'storage/remarks/' . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relativePath);
                }
            }

            $zip->close();

            // Get accurate file size
            clearstatcache(true, $zipPath);
            $fileSize = file_exists($zipPath) ? filesize($zipPath) : 0;
            
            if ($fileSize < 100) {
                throw new \Exception('Backup file is too small (' . $fileSize . ' bytes), backup may have failed');
            }

            // Create BackupLog record
            BackupLog::create([
                'filename' => $zipFilename,
                'path' => $this->backupFolder . '/' . $zipFilename,
                'disk' => $this->disk,
                'size' => $fileSize,
                'remark' => $remark,
                'created_by' => Auth::check() ? Auth::user()->name : 'System/Scheduler',
            ]);

            return $zipFilename;

        } finally {
            // Cleanup temp SQL
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    protected function generateSqlDump($outputPath)
    {
        $config = config('database.connections.mysql');
        
        $command = sprintf(
            'mysqldump --skip-ssl --no-tablespaces --user=%s --password=%s --host=%s --port=%s %s 2>/dev/null',
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['database'])
        );

        $sqlContent = shell_exec($command);
        
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
            throw new \Exception('Database dump failed: ' . ($errorOutput ?: 'Empty output'));
        }

        file_put_contents($outputPath, $sqlContent);
    }

    public function list()
    {
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

    public function restoreFromFile(UploadedFile $file)
    {
        // Save uploaded file temporarily
        $tempPath = storage_path('app/temp_restore_upload_' . time() . '.' . $file->getClientOriginalExtension());
        $file->move(dirname($tempPath), basename($tempPath));

        try {
            $this->restoreFromPath($tempPath, $file->getClientOriginalName());
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return true;
    }

    protected function restoreFromPath($path, $filename)
    {
        $isZip = str_ends_with(strtolower($path), '.zip') || str_ends_with(strtolower($filename), '.zip');
        $isGzip = str_ends_with(strtolower($filename), '.gz');

        if ($isZip) {
            $this->restoreFromZip($path);
        } else {
            // Legacy handling for .sql or .sql.gz
            $this->restoreFromSql($path, $isGzip);
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

    protected function restoreFromZip($zipPath)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \Exception('Could not open ZIP file');
        }

        $extractPath = storage_path('app/temp_restore_zip_' . time());
        if (!file_exists($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        try {
            $zip->extractTo($extractPath);
            $zip->close();

            // 1. Restore Database
            $sqlFile = $extractPath . '/database.sql';
            if (file_exists($sqlFile)) {
                $this->restoreFromSql($sqlFile, false);
            } else {
                throw new \Exception('database.sql not found in ZIP archive');
            }

            // 2. Restore Remarks Images
            // Expecting storage/remarks/ in zip
            $sourceRemarks = $extractPath . '/storage/remarks';
            $targetRemarks = storage_path('app/public/remarks');
            
            if (File::exists($sourceRemarks)) {
                // Ensure target exists
                if (!File::exists($targetRemarks)) {
                    File::makeDirectory($targetRemarks, 0755, true);
                }
                
                // Copy files
                File::copyDirectory($sourceRemarks, $targetRemarks);
            }

        } finally {
            // Cleanup extracted files
            if (file_exists($extractPath)) {
                File::deleteDirectory($extractPath);
            }
        }
    }

    protected function restoreFromSql($path, $isGzipped)
    {
        $config = config('database.connections.mysql');
        
        // Build command
        $cmdArgs = sprintf(
            '--skip-ssl --user=%s --password=%s --host=%s --port=%s %s',
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['database'])
        );

        if ($isGzipped) {
            $command = "gunzip < " . escapeshellarg($path) . " | mysql $cmdArgs 2>&1";
        } else {
            $command = "mysql $cmdArgs < " . escapeshellarg($path) . " 2>&1";
        }

        // Execute
        $output = null;
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMsg = implode("\n", $output);
            throw new \Exception("Database restore failed (Exit Code $returnVar): $errorMsg");
        }
    }

    public function delete($filename)
    {
        $path = $this->backupFolder . '/' . $filename;
        
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
        
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

    public function deleteBatch(array $filenames): int
    {
        $deleted = 0;
        foreach ($filenames as $filename) {
            try {
                $this->delete($filename);
                $deleted++;
            } catch (\Exception $e) {
                // Continue
            }
        }
        return $deleted;
    }

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

            // Keep weekly
            if (!isset($weeklyCounts[$weekKey])) {
                $weeklyCounts[$weekKey] = 0;
            }
            if ($weeklyCounts[$weekKey] < 1 && count($weeklyCounts) <= $keepWeekly) {
                $keep = true;
                $weeklyCounts[$weekKey]++;
            }

            // Keep monthly
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

        $deleted = [];
        foreach ($backups as $backup) {
            if (!isset($keepSet[$backup->filename])) {
                try {
                    $this->delete($backup->filename);
                    $deleted[] = $backup->filename;
                } catch (\Exception $e) {
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
