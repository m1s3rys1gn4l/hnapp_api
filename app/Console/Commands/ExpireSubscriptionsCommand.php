<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ExpireSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and expire subscriptions that have passed their expiry date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        $expiredUsers = User::query()
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<=', now())
            ->where('subscription_plan', '!=', 'free')
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($expiredUsers as $user) {
            $this->line("Expiring subscription for user #{$user->id} ({$user->email})");
            $user->applyPlan('free');
            $user->save();
            $count++;
        }

        $this->info("Successfully expired {$count} subscription(s).");
        
        return Command::SUCCESS;
    }
}
