<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $moduleName = 'Dashboard';
        return view('dashboard', compact('moduleName'));
    }

    public function readNotification(Request $request, $id, $url) {
        Notification::where('id', $id)->update(['read' => true]);
        return redirect($url);
    }

    public function readAllNotification(Request $request) {
        Notification::where('user_id', auth()->user()->id)->update(['read' => true]);
        return redirect()->back();
    }

    public function getNotification(Request $request) {
        if (isset($request->id)) {
            $notifications = Notification::where('user_id', auth()->user()->id)->unseen()->get()->toArray();

            return response()->json([
                'status' => true,
                'html' => view('notification', compact('notifications'))->render(),
                'count' => count($notifications)
            ]);
        }

        return response()->json(['status' => false]);
    }
}
