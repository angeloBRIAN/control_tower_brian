<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Task Scheduling
|--------------------------------------------------------------------------
|
| Schedule recurring tasks for the application.
|
*/

// Weekly Report - Sends every Monday at 8:00 AM
Schedule::command('report:weekly')->weeklyOn(1, '08:00')
    ->timezone('Asia/Jakarta')
    ->description('Send weekly workshop report to admins and managers');

// Daily Customer Duplicate Scan at 7:00 AM
Schedule::command('customers:find-duplicates')->dailyAt('07:00')
    ->timezone('Asia/Jakarta')
    ->description('Scan for duplicate customer names');

// Scheduled Email Reports - Check every minute
Schedule::command('reports:send')->everyMinute()
    ->timezone('Asia/Jakarta')
    ->description('Send scheduled email reports');
