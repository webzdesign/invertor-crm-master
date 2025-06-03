<?php

namespace App\Http\Controllers;

use App\Models\InformationPages;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class InformationPagesController extends Controller
{
    protected $moduleName = 'Information Pages';
    public function index() {
        $moduleName = $this->moduleName;
        return view('information.index',compact('moduleName'));
    }

    public function informationList(Request $request){

    $informations = InformationPages::query();

    return dataTables()->eloquent($informations)
        ->editColumn('added_by', function ($info) {
            $user = User::find($info->added_by);
            return $user->name;
        })
        ->editColumn('updated_by', function ($info) {
            if (!empty($info->updated_by)) {
                $user = User::find($info->updated_by);
                return $user->name;
            } else {
                return '-';
            }
        })
        ->editColumn("page_title", function ($info) {
            return $info->page_title ?? '-';
        })
        ->editColumn("page_description", function ($info) {
            return !empty(trim($info->page_description)) ? $info->page_description : '-';
        })
        ->addColumn('action', function ($info) {
            $variable = $info;
            $action = '<div class="d-flex align-items-center justify-content-center">';
            
            if (auth()->user()->hasPermission("information.edit")) {
                $url = route("information.edit", encrypt($info->id));
                $action .= view('buttons.edit', compact('variable', 'url'));
            }

            if (auth()->user()->hasPermission("information.view")) {
                $url = route("information.view", encrypt($info->id));
                $action .= view('buttons.view', compact('variable', 'url'));
            }

            if (auth()->user()->hasPermission("information.activeinactive")) {
                $url = route("information.activeinactive", encrypt($info->id));
                $action .= view('buttons.status', compact('variable', 'url'));
            }

            if (auth()->user()->hasPermission("information.delete")) {
                $url = route("information.delete", encrypt($info->id));
                $action .= view('buttons.delete', compact('variable', 'url'));
            }

            $action .= '</div>';
            return $action;
        })
        ->editColumn("status", function ($info) {
            return $info->status == 1
                ? "<span class='badge bg-success'>Active</span>"
                : "<span class='badge bg-danger'>Inactive</span>";
        })
        ->rawColumns(['action', 'status', 'added_by', 'updated_by', 'page_description', 'page_title'])
        ->addIndexColumn()
        ->make(true);
    }
    public function create() {
        $moduleName = $this->moduleName;
        return view('information.create',compact('moduleName'));
    }

    public function store(Request $request) {
            
        Validator::make($request->all(),[
            'page_title' => 'required',
            'slug' => 'required',
            'page_description' => 'required'
        ],[
            'page_title.required' => 'Page title is require!',
            'slug.required' => 'Page title is require!',
            'page_description.required' => 'Page title is require!'
        ]);

        if (!file_exists(storage_path('app/public/information-images'))) {
            mkdir(storage_path('app/public/information-images'), 0777, true);
        }

        $infoImg = '';
        if ($request->hasFile('page_banner')) {
            $file = $request->file('page_banner');
            $name = 'IMAGE-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(storage_path('app/public/information-images'), $name);
            $infoImg = $name;
        }

        $information = new InformationPages();
        $information->page_title = $request->page_title;
        $information->slug = $request->slug;
        $information->page_description = $request->page_description;
        $information->page_banner = $infoImg;
        $information->added_by = auth()->user()->id;
        $information->status = 1;

        if($information->save()) {
            return redirect()->route('information.index')->with('success', 'Information page added successfully.');
        } else {
            return redirect()->route('information.index')->with('error', 'Something went wrong!');
        }
    }
    public function view($id) {
        $moduleName = $this->moduleName;
        $id = decrypt($id);
        $info = InformationPages::where('id',$id)->first();
        return view('information.view',compact('moduleName','info'));
    }
    public function edit($id) {
        $moduleName = $this->moduleName;
        $id = decrypt($id);
        $info = InformationPages::where('id',$id)->first();
        return view('information.edit',compact('moduleName','info'));
    }
    public function update(Request $request, $id) {
        
        $id = decrypt($id);
        $info = InformationPages::find($id);
        Validator::make($request->all(),[
            'page_title' => 'required',
            'slug' => 'required',
            'page_description' => 'required'
        ],[
            'page_title.required' => 'Page title is require!',
            'slug.required' => 'Page title is require!',
            'page_description.required' => 'Page title is require!'
        ]);

        if (!file_exists(storage_path('app/public/information-images'))) {
            mkdir(storage_path('app/public/information-images'), 0777, true);
        }

        $infoImg = '';
        if(!empty($request->old_banner)){
            if (file_exists(storage_path("app/public/information-images/{$info->page_banner}"))) {
                unlink(storage_path("app/public/information-images/{$info->page_banner}"));
            }
        } else {
            $infoImg = $info->page_banner;
        }

        if ($request->hasFile('page_banner')) {
            $file = $request->file('page_banner');
            $name = 'IMAGE-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(storage_path('app/public/information-images'), $name);
            $infoImg = $name;
        }

        $info->page_title = $request->page_title;
        $info->slug = $request->slug;
        $info->page_description = $request->page_description;
        $info->page_banner = $infoImg;
        $info->updated_by = auth()->user()->id;
        $info->status = 1;

        if($info->update()) {
            return redirect()->route('information.index')->with('success', 'Information page updated successfully.');
        } else {
            return redirect()->route('information.index')->with('error', 'Something went wrong!');
        }
    }

    public function destroy($id)
    {
        $info = InformationPages::where('id', decrypt($id));

        if ($info->exists()) {
            if(!empty($info->page_banner)) {
                if (file_exists(storage_path("app/public/information-images/{$info->page_banner}"))) {
                    unlink(storage_path("app/public/information-images/{$info->page_banner}"));
                }
            }
                
            $info->delete();
            return response()->json(['success' => 'Informaion page deleted successfully.', 'status' => 200]);
        } else {
            return response()->json(['error' => 'Something went wrong!', 'status' => 500]);
        }
    }
    public function status($id)
    {
        try {
            $info = InformationPages::find(decrypt($id));
            $info->status = $info->status == 1 ? 0 : 1;
            $info->save();

            if ($info->status == 1) {
                return response()->json(['success' => 'Information page activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Information page inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

}