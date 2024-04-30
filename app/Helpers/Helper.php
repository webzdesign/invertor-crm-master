<?php

namespace App\Helpers;

use App\Models\Setting;

class Helper {

    public static $appLogo = 'assets/images/logo.png';
    public static $favIcon = 'assets/images/favicon.ico';

    public static function getAppTitle()
    {
        $setting = Setting::select('title')->first();
        if ($setting) {
            return $setting->title;
        } else {
            return config('app.name');
        }
    }

    public static function getAppFavicon()
    {
        $setting = Setting::select('favicon')->first();
        if ($setting) {
            if ($setting->favicon && @file_exists(storage_path("public/settings/favicon/" . $setting->favicon))) {
                return "storage/app/public/settings/favicon/" . $setting->favicon;
            }
            return self::$favIcon . '?time=' . time();
        } else {
            return self::$favIcon . '?time=' . time();
        }
    }

    //get app favicon
    public static function getAppLogo()
    {
        $setting = Setting::select('logo')->first();
        if ($setting) {
            if ($setting->logo && @file_exists(storage_path("public/settings/logo/" . $setting->logo))) {
                return "storage/app/public/settings/logo/" . $setting->logo;
            }
            return self::$appLogo . '?time=' . time();
        } else {
            return self::$appLogo . '?time=' . time();
        }
    }
}