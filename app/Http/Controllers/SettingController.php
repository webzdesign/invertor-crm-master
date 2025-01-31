<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Setting;
use App\Models\User;

class SettingController extends Controller
{
    public function index(Request $request) {
        if (!User::isAdmin()) {
            abort(403);
        }

        $moduleName = 'Settings';
        $settings = Setting::first();

        return view('settings.index', compact('moduleName', 'settings'));
    }

    public function update(Request $request) {

        $this->validate($request, [
            'name' => 'required',
            'logo' => 'file|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon' => 'file|mimes:ico|max:1024',
            'geocode' => 'required',
            'gsheetid' => 'required',
            'twilioAccountSid' => 'required',
            'twilioAuthToken' => 'required',
            'twilioUrl' => 'required',
            'twilloTemplateUrl' => 'required'
        ],[
            'name.required' => 'Name is required',
            'logo.file' => 'Upload a valid image file.',
            'logo.mimes' => 'Logo must be .png, .jpg, .jpeg or .webp',
            'logo.max' => 'Logo size is exceeded. Maximum 2MB',

            'favicon.file' => 'Upload a valid image file',
            'favicon.mimes' => 'Favicon must be .ico',
            'favicon.max' => 'Favicon size is exceeded. Maximum 1MB',

            'geocode.required' => 'Enter geocode API key',
            'gsheetid.required' => 'Enter google sheet id',
            'twilioAccountSid' => 'Twilio account id is required',
            'twilioAuthToken' => 'Twilio auth token is required',
            'twilioUrl' => 'Twilio url is required',
            'twilloTemplateUrl' => 'Twilio template url is required'
        ]);

        if (!file_exists(public_path('assets/images'))) {
            mkdir(public_path('assets/images'), 0777, true);
        }

        $deletable = [];

        DB::beginTransaction();

        try {

            $settings = Setting::find(1);

            if ($request->hasFile('logo')) {
                $name = 'LOGO-' . date('YmdHis') . uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();
                $request->file('logo')->move(public_path('assets/images'), $name);

                if (!empty($settings->logo)) {
                    if(file_exists(public_path("assets/images/{$settings->logo}"))) {
                        unlink(public_path("assets/images/{$settings->logo}"));
                    }
                }

                $settings->logo = $name;
                $deletable[] = $name;
            }

            if ($request->hasFile('favicon')) {
                $name = 'FAVICON-' . date('YmdHis') . uniqid() . '.' . $request->file('favicon')->getClientOriginalExtension();
                $request->file('favicon')->move(public_path('assets/images'), $name);

                if (!empty($settings->favicon)) {
                    if(file_exists(public_path("assets/images/{$settings->favicon}"))) {
                        unlink(public_path("assets/images/{$settings->favicon}"));
                    }
                }

                $settings->favicon = $name;
                $deletable[] = $name;
            }

            $settings->title = $request->name;
            $settings->geocode_key = $request->geocode;
            $settings->google_sheet_id = $request->gsheetid;
            $settings->twilioAccountSid = $request->twilioAccountSid;
            $settings->twilioAuthToken = $request->twilioAuthToken;
            $settings->twilioUrl = $request->twilioUrl;
            $settings->twilloTemplateUrl = $request->twilloTemplateUrl;
            $settings->twilioFromNumber = $request->twilioFromNumber;

            $settings->facebookUrl = $request->facebookUrl;
            $settings->linkdinUrl = $request->linkdinUrl;
            $settings->instgramUrl = $request->instgramUrl;
            $settings->tiktokUrl = $request->tiktokUrl;
            $settings->youtubeUrl = $request->youtubeUrl;

            $settings->save();

            DB::commit();
            return redirect()->back()->with('success', 'Settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($deletable)) {
                foreach ($deletable as $image) {
                    if(file_exists(public_path("assets/images/{$image}"))) {
                        unlink(public_path("assets/images/{$image}"));
                    }
                }
            }

            return redirect()->back()->with('error', Helper::$errorMessage);
        }

    }
}
