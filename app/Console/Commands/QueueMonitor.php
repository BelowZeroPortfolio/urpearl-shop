<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor {--clear : Clear failed jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue status and optionally clear failed jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clear')) {
            $this->clearFailedJobs();
            return;
        }

        $this->displayQueueStatus();
    }

    /**
     * Display current queue status
     */
    private function displayQueueStatus()
    {
        $this->info('Queue Status Monitor');
        $this->line('==================');

        // Pending jobs
        $pendingJobs = DB::table('jobs')->count();
        $this->line("Pending Jobs: {$pendingJobs}");

        // Failed jobs
        $failedJobs = DB::table('failed_jobs')->count();
        $this->line("Failed Jobs: {$failedJobs}");

        if ($failedJobs > 0) {
            $this->warn("You have {$failedJobs} failed jobs. Run with --clear to remove them.");
            
            // Show recent failed jobs
            $recentFailed = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get(['id', 'queue', 'exception', 'failed_at']);

            if ($recentFailed->count() > 0) {
                $this->line("\nRecent Failed Jobs:");
                $this->table(
                    ['ID', 'Queue', 'Exception', 'Failed At'],
                    $recentFailed->map(function ($job) {
                        return [
                            $job->id,
                            $job->queue,
                            substr($job->exception, 0, 50) . '...',
                            $job->failed_at
                        ];
                    })->toArray()
                );
            }
        }

        // Queue configuration
        $this->line("\nQueue Configuration:");
        $this->line("Connection: " . config('queue.default'));
        $this->line("Mail Queue: " . (config('queue.default') !== 'sync' ? 'Enabled' : 'Disabled'));
    }

    /**
     * Clear failed jobs
     */
    private function clearFailedJobs()
    {
        $count = DB::table('failed_jobs')->count();
        
        if ($count === 0) {
            $this->info('No failed jobs to clear.');
            return;
        }

        if ($this->confirm("Are you sure you want to clear {$count} failed jobs?")) {
            DB::table('failed_jobs')->truncate();
            $this->info("Cleared {$count} failed jobs.");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
