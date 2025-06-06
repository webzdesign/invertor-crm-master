<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;
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
            ->editColumn('phone', function($contactus) {
                return !empty($contactus->country_dial_code) ? '+'.$contactus->country_dial_code.' '.$contactus->phone : $contactus->phone;
            })
            ->editColumn('action', function($contactus) {
                $view = '<div class="tableCards d-inline-block me-1 pb-0">
                            <div class="editDlbtn">
                                <a data-bs-toggle="tooltip" title="View" href="javascript:void(0);"  style="background: #4BA64F !important;" class="editBtn modal-view-btn" data-uniqueid="'.encrypt($contactus->id).'"> <i class="text-white fa fa-eye" aria-hidden="true"></i></a>
                            </div>
                        </div>';

                return $view;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }

    public function detail(Request $request) {

        try {
            $id = decrypt($request->id);
            if($id) {
                $contactus = ContactUs::select(['name','email','phone','message','country_dial_code'])->where('id', $id)->first();
                
                if(!empty($contactus)) {
                    $data = $contactus->toArray();

                    array_walk_recursive($data, function (&$item) {
                        if (is_string($item)) {
                            $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                            $item = preg_replace('/[^\P{C}\n]+/u', '', $item);
                        }
                    });

                    return response()->json([
                        'success' => 1,
                        'data' => $contactus 
                    ]);
                } else {
                    return response()->json([
                        'success' => 0,
                        'message' => 'something went wrong!!'  
                    ]);
                }
            } else {
                return response()->json([
                    'success' => 0,
                    'message' => 'something went wrong!!'  
                ]);
            }
        } catch (\Throwable $e) {
            Log::info('Contact Us Detail Popup Error -> '. $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => $e->getMessage()  
            ]);
        }
    }
}
