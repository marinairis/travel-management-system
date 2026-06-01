<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\TravelRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateExpiredTravelRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'travel-requests:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark travel requests as expired when their departure date has passed and they are still in requested status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update expired travel requests...');

        // Find all requests that:
        // 1. Have status = 'requested'
        // 2. Departure date is before today
        $expiredRequests = TravelRequest::where('status', 'requested')
            ->where('departure_date', '<', now()->startOfDay())
            ->get();

        if ($expiredRequests->isEmpty()) {
            $this->info('No expired travel requests found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredRequests->count()} expired travel requests.");

        $count = 0;
        foreach ($expiredRequests as $request) {
            $oldStatus = $request->status;
            $request->status = 'expired';
            $request->save();

            // Log the status change directly without requiring a Request object
            ActivityLog::create([
                'user_id' => Auth::id() ?? 1, // Use system user ID if no auth
                'action' => 'status_change',
                'model_type' => get_class($request),
                'model_id' => $request->id,
                'description' => 'Pedido de viagem expirado automaticamente (data de partida passou)',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'expired'],
            ]);

            $this->line("  - Request #{$request->id} ({$request->destination}) marked as expired.");
            $count++;
        }

        $this->info("Successfully marked {$count} travel requests as expired.");

        return Command::SUCCESS;
    }
}
