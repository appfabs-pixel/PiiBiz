<?php

namespace Workdo\Hrm\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        // All HRM related menu items removed as per user request.
    }
}

