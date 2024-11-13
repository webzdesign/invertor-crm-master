<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Helpers\Helper;

class ContactUsController extends Controller
{
    protected $moduleName = 'Contact Us';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            return view('contactUs.index', compact('moduleName'));
        }

        $contactus = ContactUs::query();

        if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
            $contactus = $contactus->orderBy('id', 'desc');
        }

        return dataTables()->eloquent($contactus)
            ->editColumn('created_at', function($contactus) {
                return date('d-m-Y h:i:s A', strtotime($contactus->created_at));
            })
            ->rawColumns([])
            ->addIndexColumn()
            ->make(true);
    }
}
