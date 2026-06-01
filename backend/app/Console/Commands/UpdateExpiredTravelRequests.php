<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TravelRequestStatus;
use App\Models\ActivityLog;
use App\Models\TravelRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class UpdateExpiredTravelRequests extends Command
{
    protected $signature = 'travel-requests:expire';

    protected $description = 'Mark travel requests as expired when their departure date has passed and they are still in requested status';

    public function handle(): int
    {
        $this->info('Starting to update expired travel requests...');

        $expiredRequests = TravelRequest::where('status', TravelRequestStatus::Requested->value)
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
            $request->status = TravelRequestStatus::Expired->value;
            $request->save();

            ActivityLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'status_change',
                'model_type' => get_class($request),
                'model_id' => $request->id,
                'description' => 'Pedido de viagem expirado automaticamente (data de partida passou)',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => TravelRequestStatus::Expired->value],
            ]);

            $this->line("  - Request #{$request->id} ({$request->destination}) marked as expired.");
            $count++;
        }

        $this->info("Successfully marked {$count} travel requests as expired.");

        return Command::SUCCESS;
    }
}
