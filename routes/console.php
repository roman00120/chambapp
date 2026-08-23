<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payments:reconcile --limit=100 --days=30')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->onOneServer();

Schedule::command('payments:reconcile --settled --limit=50 --days=180')
    ->hourly()
    ->withoutOverlapping(55)
    ->onOneServer();

Schedule::command('mercadopago:refresh-tokens --limit=100')
    ->dailyAt('02:30')
    ->withoutOverlapping(30)
    ->onOneServer();
