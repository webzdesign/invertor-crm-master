<?php

namespace App\Http\Controllers;

use App\Models\CallHistory;
use App\Models\CallTaskStatus;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MoldcellController extends Controller
{

    public static function createMoldcellEmployee($data)
    {
        if (config('services.moldcell.api_status')) {
            try {
                $setting = Setting::select(['moldcell_url', 'moldcell_auth_pbx_key', 'moldcell_auth_crm_key'])->first();

                $params = [];

                if (!empty($data->username)) {
                    $params['login'] = $data->username;
                }

                if (!empty($data->name)) {
                    $params['name'] = $data->name;
                }

                if (!empty($data->password)) {
                    $params['password'] = $data->password;
                }

                if (!empty($data->email)) {
                    $params['email'] = $data->email;
                }

                if (!empty($data->mobile)) {
                    $params['mobile'] = $data->mobile;
                }

                $queryString = http_build_query($params);

                $url = $setting->moldcell_url . '/crmapi/v1/users';
                if (!empty($queryString)) {
                    $url .= '?' . $queryString;
                }

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_HTTPHEADER => array(
                        'X-API-KEY: '.$setting->moldcell_auth_pbx_key
                    ),
                ));

                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                return (object) ['status' => $httpCode, 'response' => json_decode($response)];
            } catch (\Exception $e) {
                return (object) ['status' => 500, 'response' => $e->getMessage()];
            }
        }

        return (object) [
            'status' => 500,
            'response' => "Something went wrong, Please try again."
        ];
    }

    public static function updateMoldcellEmployee($data)
    {
        if (config('services.moldcell.api_status')) {
            try {
                $setting = Setting::select(['moldcell_url', 'moldcell_auth_pbx_key', 'moldcell_auth_crm_key'])->first();

                $params = [];

                if (!empty($data->name)) {
                    $params['name'] = $data->name;
                }

                if (!empty($data->password)) {
                    $params['password'] = $data->password;
                }

                if (!empty($data->email)) {
                    $params['email'] = $data->email;
                }

                if (!empty($data->mobile)) {
                    $params['mobile'] = $data->mobile;
                }

                $queryString = http_build_query($params);

                $url = $setting->moldcell_url . '/crmapi/v1/users/' . $data->username;
                if (!empty($queryString)) {
                    $url .= '?' . $queryString;
                }

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_HTTPHEADER => array(
                        'X-API-KEY: '.$setting->moldcell_auth_pbx_key
                    ),
                ));

                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                return (object) ['status' => $httpCode, 'response' => json_decode($response)];
            } catch (\Exception $e) {
                return (object) ['status' => 500, 'response' => $e->getMessage()];
            }
        }

        return (object) [
            'status' => 500,
            'response' => "Something went wrong, Please try again."
        ];
    }

    public static function deleteMoldcellEmployee($username)
    {
        if (config('services.moldcell.api_status')) {
            try {
                $setting = Setting::select(['moldcell_url', 'moldcell_auth_pbx_key', 'moldcell_auth_crm_key'])->first();

                $url = $setting->moldcell_url . '/crmapi/v1/users/' . $username;

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'DELETE',
                    CURLOPT_HTTPHEADER => array(
                        'X-API-KEY: '.$setting->moldcell_auth_pbx_key
                    ),
                ));

                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                return (object) ['status' => $httpCode, 'response' => json_decode($response)];
            } catch (\Exception $e) {
                return (object) ['status' => 500, 'response' => $e->getMessage()];
            }
        }

        return (object) [
            'status' => 500,
            'response' => "Something went wrong, Please try again."
        ];
    }

    public static function getMoldcellCallHistory($cmd = 0, $period = "today", $type = "in")
    {
        if (config('services.moldcell.api_status')) {
            try {
                $setting = Setting::select(['moldcell_url', 'moldcell_auth_pbx_key', 'moldcell_auth_crm_key'])->first();

                $role = Role::where('slug', 'customer')->first();

                $users = User::query()
                    ->select(['id',DB::raw('REPLACE(REPLACE(phone,"+","")," ","") as phone')])
                    ->whereHas('role', function ($builder) use($role) {
                        $builder->where('roles.id', $role->id);
                    })
                    ->pluck("id","phone")
                    ->toArray();

                $sellerManagerRole = Role::where('slug', 'seller-manager')->first();
                $sellerManagers = User::query()
                    ->select(['id',DB::raw('REPLACE(REPLACE(phone,"+","")," ","") as phone')])
                    ->whereHas('role', function ($builder) use($sellerManagerRole) {
                        $builder->where('roles.id', $sellerManagerRole->id);
                    })
                    ->pluck("id","phone")
                    ->toArray();

                 /*
                  * period
                  * - today
                  * - yesterday
                  * - this_week
                  * - last_week
                  * - this_month
                  * - last_month
                  * */
                /*
                 * type
                 * - all
                 * - in
                 * - out
                 * - missed
                 * */
                $queryParameters = [
                    "period" => $period,
                    "type" => $type,
                ];

                $url = $setting->moldcell_url . '/crmapi/v1/history/json?'. http_build_query($queryParameters);

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'X-API-KEY: '.$setting->moldcell_auth_pbx_key
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                $res = json_decode($response);

                if (!empty($response) && !empty($res) && !isset($res->message)) {
                    $histories = [];
                    $newUser = [];
                    $i = 1;

                    foreach ($res as $call) {
                        if ($cmd) {
                            echo "{$i}) {$call->uid} | TYPE - {$call->type} | STATUS - {$call->status}\n";
                            $i++;
                        }

                        $fromUser = null;
                        $toUser = null;
                        $assignUser = null;

                        $phone = str_replace(["+"," "], "", $call->client);
                        if (isset($users[$phone])) {
                            $fromUser = $users[$phone];
                        }
                        else {
                            $code = Str::substr($phone,0,3);

                            $user = new User();
                            $user->name = $phone;
                            $user->phone = $phone;
                            $user->password = bcrypt($phone);
                            $user->country_dial_code = $code == 373 ? "373" : null;
                            $user->country_iso_code = $code == 373 ? "md" : null;
                            $user->added_by = 1;
                            $user->save();
                            $user->roles()->attach($role);
                            $fromUser = $user->id;
                            $users[$phone] = $fromUser;
                        }

                        $diversion = str_replace(["+"," "], "", $call->diversion);
                        if (!isset($sellerManagers[$diversion])) {
                            $res1 = self::getTelNumsNumberAndCreateManager($call->diversion);
                            if (!empty($res1)) {
                                $sellerManagers[$diversion] = $res1->id;
                            }
                        }

                        if (isset($sellerManagers[$diversion])) {
                            $toUser = $sellerManagers[$diversion];
                        }

                        if (!empty($toUser) && $call->status == "success") {
                            $assignUser = $toUser;
                        }

                        $start = !empty($call->start ?? null) ? Carbon::parse($call->start)->toDateTimeString() : null;
                        $startTz = !empty($call->start ?? null) ? Carbon::parse($call->start)->getTimezone() : null;

                        $histories[] = [
                            "from_user_id" => $fromUser,
                            "to_user_id" => $toUser,
                            "assigned_user_id" => $assignUser,
                            "uid" => $call->uid,
                            "status_id" => ($toUser == $assignUser) ? CallTaskStatus::status2 : CallTaskStatus::status1,
                            "type" => $call->type ?? null,
                            "status" => $call->status ?? null,
                            "client" => $call->client ?? null,
                            "diversion" => $call->diversion ?? null,
                            "telnum_name" => $call->telnum_name ?? null,
                            "destination" => $call->destination ?? null,
                            "user" => $call->user ?? null,
                            "user_name" => $call->user_name ?? null,
                            "group_name" => $call->group_name ?? null,
                            "start" => $start,
                            "start_timezone" => $startTz,
                            "wait" => $call->wait ?? null,
                            "duration" => $call->duration ?? null,
                            "record" => $call->record ?? null,
                            "rating" => $call->rating ?? null,
                            "note" => $call->note ?? null,
                            "missedstatus" => $call->missedstatus ?? null,
                        ];
                    }

                    if (!empty($histories)) {
                        foreach (array_chunk($histories, 100) as $items) {
                            CallHistory::upsert($items, ['uid']);
                        }
                    }
                } else {
                    if ($cmd) {
                        echo "Error :- {$response}\n";
                    }
                }
            } catch (\Exception $e) {
                if ($cmd) {
                    echo "Error :- {$e->getMessage()}\n";
                }
            }
        }
    }

    public static function getTelNumsNumberAndCreateManager($number)
    {
        $role = Role::where('slug', 'seller-manager')->first();
        $userExist = User::query()
            ->whereRaw('REPLACE(REPLACE(phone, "+", ""), " ", "") = ?', [$number])
            ->whereHas('role', function ($builder) use($role) {
                $builder->where('roles.id', $role->id);
            })
            ->exists();

        if (!$userExist) {

            $setting = Setting::select(['moldcell_url', 'moldcell_auth_pbx_key', 'moldcell_auth_crm_key'])->first();

            $url = $setting->moldcell_url . "/crmapi/v1/telnums/{$number}";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'X-API-KEY: '.$setting->moldcell_auth_pbx_key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $res = json_decode($response);

            if (!isset($res->message) && !empty($res->telnum)) {

                $code = Str::substr($res->telnum,0,3);

                $user = new User();
                $user->name = $res->group_name;
                $user->phone = $res->telnum;
                $user->password = bcrypt($res->telnum);
                $user->country_dial_code = $code == 373 ? "373" : null;
                $user->country_iso_code = $code == 373 ? "md" : null;
                $user->added_by = 1;
                $user->save();
                $user->roles()->attach($role);

                return $user;
            }
        }

        return null;
    }
}
