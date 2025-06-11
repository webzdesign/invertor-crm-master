<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoldcellWebhookController extends Controller
{
    public function handleMoldcellWebhook(Request $request)
    {
        $data = $request->all();

        Log::info(['Call Details => '],$data);

        return response()->json(['status' => 'ok']);
    }

}
