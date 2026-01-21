<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Import;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // One-time data fix: Convert user names to user IDs in 'imported_by' column
        // We use chunking to avoid memory issues with large datasets
        Import::chunk(100, function ($imports) {
            foreach ($imports as $import) {
                // Check if the value is a string and not numeric (indicating it's likely a name)
                if (!empty($import->imported_by) && !is_numeric($import->imported_by)) {
                    
                    // Try to find the user by name
                    $user = User::where('name', $import->imported_by)->first();
                    
                    if ($user) {
                        // Update with the ID
                        $import->update(['imported_by' => $user->id]);
                    } else {
                        // If user not found, we might want to log it or set to null?
                        // For now, let's leave it as is to preserve the string name at least,
                        // or set to null if strict integrity is desired.
                        // Leaving as-is is safer so we don't lose data (the name).
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration logic needed for a one-time data fix.
        // Reverting this would require knowing which records were changed, which is complex.
    }
};
