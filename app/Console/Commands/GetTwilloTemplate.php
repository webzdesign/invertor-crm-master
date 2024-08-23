<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{TwilloTemplate,Setting,TwilloMessageNotification,Trigger};
use App\Helpers\Helper;

class GetTwilloTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twillo-template:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twillo template get from twillo account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $setting=Setting::select('twilioAuthToken','twilloTemplateUrl','twilioAccountSid')->first();
            $newTemplate = [];
            $notification = TwilloTemplate::select('id','contentsid')->pluck('id','contentsid')->toArray();
            // Twilio credentials
            if($setting) {

                $twilioAccountSid = $setting->twilioAccountSid;
                $twilioAuthToken = $setting->twilioAuthToken;
                $url = "{$setting->twilloTemplateUrl}/ContentAndApprovals?PageSize=2&&page=0";

                reTry:
                $ch = curl_init($url);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERPWD, $twilioAccountSid . ':' . $twilioAuthToken);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (curl_errno($ch)) {
                    Helper::logger(curl_error($ch));
                } else {
                    if($httpCode == 201 || $httpCode == 200) {
                        $result = json_decode($response);

                        if(isset($result->contents) && !empty($result->contents)) {
                            foreach($result->contents as $templateData) {
                                echo $templateData->sid."\n";
                                $newTemplate[] = $templateData->sid;
                                TwilloTemplate::updateOrCreate([
                                    'contentsid'=>$templateData->sid
                                ],[
                                    'templatename'=>$templateData->approval_requests->name??null,
                                    'templatestatus'=>$templateData->approval_requests->status??null,
                                    'response'=>json_encode($templateData)
                                ]);
                            }
                        }
                        if(isset($result->meta->next_page_url) && $result->meta->next_page_url !="") {
                            $url = $result->meta->next_page_url;
                            goto reTry;
                        }
                        if(!empty($newTemplate)) {
                            $twillonotificationid = TwilloMessageNotification::select('id')->whereNotIn('template_id',$newTemplate)->pluck('id')->toArray();
                            Trigger::whereIn('twillo_notification_id',$twillonotificationid)->delete();
                            TwilloMessageNotification::whereIn('id',$twillonotificationid)->delete();
                            TwilloTemplate::whereNotIn('contentsid',$newTemplate)->delete();
                        }
                    } else {
                        Helper::logger('Twillo template not found.');
                    }

                }
                curl_close($ch);
            }

        } catch (\Exception $e) {
            Helper::logger($e->getMessage());
        }
    }
}
