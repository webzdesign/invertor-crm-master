<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('status:trigger')->everyMinute();
Schedule::command('task:trigger')->everyMinute();
Schedule::command('change_user:trigger')->everyMinute();