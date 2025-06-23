<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\CallTaskStatus;
use App\Models\CallHistory;
use Helper;

class MoldcellWebhookController extends Controller
{
    public function handleMoldcellWebhook(Request $request)
    {
        $data = $request->all();
        
        /* add log */
        Log::info(['Call Details => '], $data);

        $phoneDetail = Helper::parsePhoneNumber($data['phone']);
        $phoneNo = $phoneDetail->phone;
        $dialCode = $phoneDetail->dial_code;
        $isoCode = $phoneDetail->iso_code;

        DB::beginTransaction();

        try {
            $customerRole = Role::where('slug', 'customer')->first();

            /* if customer not exist then create and connect to manager when call status is success and type is incomming */
            if (isset($data['cmd']) && $data['cmd'] == 'history' && $data['type'] == 'in') {

                $checkUser = User::where('phone', $phoneNo)->whereHas('role', function ($q) use ($customerRole) {
                    $q->where('roles.id', $customerRole->id);
                })->first();
                
                $isNewCustomer = 0;
                if ($data['status'] == 'Success') {
                    $getManager = User::select('id')->where('ext', $data['ext'])->first();
                    if ($checkUser && $getManager) {
                        if ($checkUser->connected_user_id == '' || $checkUser->connected_user_id == null) {
                            $checkUser->connected_user_id = $getManager->id;
                            $checkUser->save();
                        }
                    } else {
                        if ($getManager && $customerRole) {
                            $user = new User();
                            $user->name = $phoneNo;
                            $user->phone = $phoneNo;
                            $user->password = bcrypt($phoneNo);
                            $user->country_dial_code = $dialCode;
                            $user->country_iso_code = $isoCode;
                            $user->connected_user_id = $getManager->id;
                            $user->added_by = 1;
                            $user->save();
                            $user->roles()->attach($customerRole);

                            $isNewCustomer = 1;
                        }
                    }
                } else if ($data['status'] == 'missed') {
                    if (!$checkUser) {
                        $user = new User();
                        $user->name = $phoneNo;
                        $user->phone = $phoneNo;
                        $user->password = bcrypt($phoneNo);
                        $user->country_dial_code = $dialCode;
                        $user->country_iso_code = $isoCode;
                        $user->added_by = 1;
                        $user->save();
                        $user->roles()->attach($customerRole);

                        $isNewCustomer = 1;
                    }
                }

                /* add call histroy */
                $statusId = ($isNewCustomer == 1 && $data['status'] == 'missed') ? CallTaskStatus::status1 : NULL;
                $fromUserId = ($isNewCustomer == 1) ? $user->id : $checkUser->id;
                $start = (isset($data['start'])) ? Carbon::parse($data['start'])->toDateTimeString() : NULL;
                $startTz = (isset($data['start'])) ? Carbon::parse($data['start'])->getTimezone() : NULL;

                CallHistory::updateOrCreate([
                    'uid' => $data['callid']
                ], [
                    'from_user_id' => $fromUserId,
                    'to_user_id' => $getManager->id ?? NULL,
                    'uid' => $data['callid'],
                    'status_id' => $statusId,
                    'type' => $data['type'],
                    'status' => $data['status'],
                    'diversion' => $data['diversion'],
                    'start' => $start,
                    'start_timezone' => $startTz,
                    'duration' => $data['duration'] ?? '',
                    'record' => $data['link'] ?? ''
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Moldcell Webhook Transaction Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }

        /* check customer exist or not and redirect call */
        if (isset($data['cmd']) && $data['cmd'] == 'contact') {
            $user = User::where('phone', $phoneNo)->whereNotNull('connected_user_id')->whereHas('role', function ($q) use ($customerRole) {
                $q->where('roles.id', $customerRole->id);
            })->first();
            if ($user && isset($user->connectedUser) && isset($user->connectedUser->ext) && $user->connectedUser->ext != '') {
                return response()->json([
                    'contact_name' => $user->connectedUser->name,
                    'responsible' => $user->connectedUser->ext
                ]);
            }
        }
    }
}
