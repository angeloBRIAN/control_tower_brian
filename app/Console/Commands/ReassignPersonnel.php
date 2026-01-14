<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\ServiceAdvisor;
use App\Models\Foreman;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReassignPersonnel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:reassign-personnel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassign Resigned Personnel (Aditya -> Ekky, Fatma -> Rustamadji)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting personnel reassignment...');

        DB::transaction(function () {
            // 1. Service Advisor: ADITYA -> EKKY
            $this->reassignServiceAdvisor('ADITYA', 'EKKY');
            $this->reassignServiceAdvisor('Aditya', 'EKKY'); // Case variant

            // 2. Foreman: FATMA -> RUSTAMADJI
            $this->reassignForeman('FATMA', 'RUSTAMADJI');
            $this->reassignForeman('Fatma', 'RUSTAMADJI'); // Case variant
        });

        $this->info('Reassignment complete!');
    }

    private function reassignServiceAdvisor($oldName, $newName)
    {
        // Update Jobs
        $count = Job::where('service_advisor', $oldName)->update(['service_advisor' => $newName]);
        if ($count > 0) {
            $this->info("Updated {$count} jobs from SA '{$oldName}' to '{$newName}'");
        }

        // Update Master Data
        $oldMaster = ServiceAdvisor::where('name', $oldName)->first();
        if ($oldMaster) {
            $newMaster = ServiceAdvisor::where('name', $newName)->first();
            if ($newMaster) {
                // New master exists, delete old one
                $oldMaster->delete();
                $this->info("Deleted old SA master record '{$oldName}' (Merged into existing '{$newName}')");
            } else {
                // Rename old master to new
                $oldMaster->update(['name' => $newName]);
                $this->info("Renamed SA master record from '{$oldName}' to '{$newName}'");
            }
        }
    }

    private function reassignForeman($oldName, $newName)
    {
        // Update Jobs
        $count = Job::where('foreman', $oldName)->update(['foreman' => $newName]);
        if ($count > 0) {
            $this->info("Updated {$count} jobs from Foreman '{$oldName}' to '{$newName}'");
        }

        // Update Master Data
        $oldMaster = Foreman::where('name', $oldName)->first();
        if ($oldMaster) {
            $newMaster = Foreman::where('name', $newName)->first();
            if ($newMaster) {
                // New master exists, delete old one
                $oldMaster->delete();
                $this->info("Deleted old Foreman master record '{$oldName}' (Merged into existing '{$newName}')");
            } else {
                // Rename old master to new
                $oldMaster->update(['name' => $newName]);
                $this->info("Renamed Foreman master record from '{$oldName}' to '{$newName}'");
            }
        }
    }
}
