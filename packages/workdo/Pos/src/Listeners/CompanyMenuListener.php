<?php

namespace Workdo\Pos\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        // All POS related menu items removed as per user request.
    }
}
