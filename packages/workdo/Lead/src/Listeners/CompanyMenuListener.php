<?php

namespace Workdo\Lead\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        // All Lead/CRM related menu items removed as per user request.
    }
}
