<?php

namespace App\Console\Commands;

use App\Models\Staff;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncStaffFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:sync {--dry : Show what would change without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync active users from external API into Staff table (deactivate missing)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
            $apiUrl = config('app.apiurl');
            $apiKey = config('app.appkey');

            $this->info('Fetching staff from API: ' . $apiUrl . '/users/active');

            $response = Http::withHeaders([
                'Accept'    => 'application/json',
                'X-API-KEY' => $apiKey,
            ])->get($apiUrl . '/users/active');

            if (! $response->ok()) {
                $this->error('API request failed: ' . $response->status());
                $this->line($response->body());
                return self::FAILURE;
            }

            $users = $response->json();
            if (! is_array($users)) {
                $this->error('Unexpected API response format.');
                return self::FAILURE;
            }

            $dry = $this->option('dry');
            $created = 0;
            $updated = 0;

            $apiIds = collect($users)->pluck('id')->filter()->unique(); // IDs returned by API

            $this->info('Records received: ' . count($users));
            $bar = $this->output->createProgressBar(count($users));
            $bar->start();

            foreach ($users as $staffData) {
                $payload = [
                    'name'                 => $staffData['name'] ?? null,
                    'api_id'               => $staffData['id'] ?? null,
                    'email'                => $staffData['email'] ?? null,
                    'email_verified_at'    => $staffData['email_verified_at'] ?? null,
                    'emp_no'               => $staffData['emp_no'] ?? null,
                    'gender'               => $staffData['gender'] ?? null,
                    'designation'          => $staffData['designation'] ?? null,
                    'mobile'               => $staffData['mobile'] ?? null,
                    'phone'                => $staffData['phone'] ?? null,
                    'department_id'        => $staffData['department_id'] ?? null,
                    'active'               => $staffData['active'] ?? true,
                    'location_id'          => $staffData['location_id'] ?? null,
                    'nid'                  => $staffData['nid'] ?? null,
                    'supervisor_id'        => $staffData['supervisor_id'] ?? null,
                    'joined_date'          => $staffData['joined_date'] ?? null,
                    'is_annual_applicable' => $staffData['is_annual_applicable'] ?? false,
                    'profile_photo_path'   => $staffData['profile_photo_path'] ?? null,
                    'profile_photo_url'    => $staffData['profile_photo_url'] ?? null,
                    'external_id'          => $staffData['external_id'] ?? null,
                ];

                if ($dry) {
                    $exists = Staff::where('emp_no', $payload['emp_no'])->exists();
                    $exists ? $updated++ : $created++;
                } else {
                    $model = Staff::updateOrCreate(
                        ['emp_no' => $payload['emp_no']],
                        $payload
                    );
                    $model->wasRecentlyCreated ? $created++ : $updated++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Deactivate local staff missing from API
            $missingQuery = Staff::whereNotNull('api_id')
                ->where('active', true)
                ->whereNotIn('api_id', $apiIds);

            $toDeactivate = (clone $missingQuery)->count();

            if ($dry) {
                $this->comment("[DRY RUN] Would deactivate: $toDeactivate staff not in API.");
            } else {
                if ($toDeactivate > 0) {
                    $missingQuery->update(['active' => false]);
                }
                $this->info("Deactivated missing staff: $toDeactivate");
            }

            if ($dry) {
                $this->comment("[DRY RUN] Would create: $created, would update: $updated");
            } else {
                $this->info("Sync complete. Created: $created, Updated: $updated");
            }

        return self::SUCCESS;
    }
}
