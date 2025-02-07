<?php

namespace App\Listeners;

use App\Events\MessGroupUpdated;
use App\Http\Controllers\MessGroupController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecalculateBalances
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessGroupUpdated $event): void
    {
        // Log event trigger
        Log::info("MessGroupUpdated event triggered for MessGroup ID: {$event->messGroup->id}");

        $messGroup = $event->messGroup;
        $controller = new MessGroupController();
        $updatedMessGroup = $controller->calculateBalances($messGroup->id);

        // Log after balance recalculation
        Log::info("Balances recalculated for MessGroup ID: {$updatedMessGroup}");
    }
}
