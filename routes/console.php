<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('status:trigger')->everyMinute();
Schedule::command('task:trigger')->everyMinute();
Schedule::command('change_user:trigger')->everyMinute();
Schedule::command('add:data-to-sheet')->everyFiveMinutes();
// Schedule::command('twillo-template:get')->hourly();
