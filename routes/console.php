<?php

use App\Jobs\RegenerateHearts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new RegenerateHearts)->everyThirtyMinutes();
Schedule::command('league:close-week')->weeklyOn(0, '23:00');
