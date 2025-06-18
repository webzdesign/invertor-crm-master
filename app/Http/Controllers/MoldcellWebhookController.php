<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoldcellWebhookController extends Controller
{
    public function handleMoldcellWebhook(Request $request)
    {
        $data = $request->all();
        
        /* add log */
        Log::info(['Call Details => '], $data);

        /* call redirect */
        if (isset($data['phone']) && $data['phone'] == 919313845942) {

            Log::info(['Call Redirect => '], ['919313845942']);

            return response()->json(['contact_name' => 'Gasper Mariana', 'responsible' => '703']);
        }

        return response()->json(['status' => 'ok']);
    }

}
