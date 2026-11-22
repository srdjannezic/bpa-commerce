<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', /** Write an inspirational quote to the console. */ function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
