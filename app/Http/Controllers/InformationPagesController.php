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
       ->editColumn("page_banner", function ($info) {
            $langs = Helper::getMultiLang();
            $imgJson = json_decode($info->page_banner);
            $allImages = [];

            foreach ($langs as $value) {
                if (!empty($imgJson->$value->image)) {
                    $imagePath = storage_path('app/public/information-images/' . $imgJson->$value->image);
                    if (file_exists($imagePath)) {
                        $url = asset('storage/information-images/' . $imgJson->$value->image);
                        $allImages[] = '<img src="' . $url . '" alt="page banner" class="p-1" style="height:100px; width:300px;" />';
                    }
                }
            }

            return count($allImages) > 0 ? implode('', $allImages) : '-';
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
        ->rawColumns(['action', 'status', 'added_by', 'updated_by', 'page_description', 'page_title','page_banner'])
        ->addIndexColumn()
        ->make(true);
    }
    public function create() {
        $moduleName = $this->moduleName;
        $langs = Helper::getMultiLang();
        return view('information.create',compact('moduleName','langs'));
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

        $infoImg = [];
        
        if ($request->hasFile('page_banner') || $request->hasFile('page_banner_mob')) {
            $files = $request->file('page_banner');
            $mobfiles = $request->file('page_banner_mob');
            
            $langs = Helper::getMultiLang();

            foreach ($langs as $lang) {
                $name = '';
                if(isset($files[$lang]) && !empty($files[$lang])){
                    $name = 'IMAGE-' . date('YmdHis') . uniqid() . '.' . $files[$lang]->getClientOriginalExtension();
                    $files[$lang]->move(storage_path('app/public/information-images'), $name);
                }
                
                $mob_name = '';
                if(isset($mobfiles[$lang]) && !empty($mobfiles[$lang])) {
                    $mob_name = 'MOB-IMAGE-' . date('YmdHis') . uniqid() . '.' . $mobfiles[$lang]->getClientOriginalExtension();
                    $mobfiles[$lang]->move(storage_path('app/public/information-images'), $mob_name);
                }
                
                $infoImg[$lang] = ['image' => $name, 'mob_image' => $mob_name];
            }
            $infoImg = json_encode($infoImg); 
        }

        $information = new InformationPages();
        $information->page_title = $request->page_title;
        $information->slug = $request->slug;
        $information->page_description = $request->page_description;
        $information->page_banner = !empty($infoImg) ? $infoImg : '';
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
        $langs = Helper::getMultiLang();
        return view('information.view',compact('moduleName','info','langs'));
    }
    public function edit($id) {
        $moduleName = $this->moduleName;
        $id = decrypt($id);
        $info = InformationPages::where('id',$id)->first();
        $langs = Helper::getMultiLang();
        return view('information.edit',compact('moduleName','info','langs'));
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

        $infoImg = $info->page_banner;
        $updatedImages = json_decode($infoImg, true) ?? []; 
        
        if (!empty($request->old_banner)) {
            foreach ($request->old_banner as $lang => $val) {
                if (!empty($val) && isset($updatedImages[$lang]['image']) && !empty($updatedImages[$lang]['image'])) {
                    $imagePath = storage_path("app/public/information-images/{$updatedImages[$lang]['image']}");

                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    unset($updatedImages[$lang]['image']);
                }
            }
        }

        if (!empty($request->old_banner_mob)) {
            foreach ($request->old_banner_mob as $lang => $val) {
                if(!empty($val) && isset($updatedImages[$lang]['mob_image']) && !empty($updatedImages[$lang]['mob_image'])) {
                    $mob_imagePath = storage_path("app/public/information-images/{$updatedImages[$lang]['mob_image']}");
               
                    if (file_exists($mob_imagePath)) {
                        unlink($mob_imagePath);
                    }
                    unset($updatedImages[$lang]['mob_image']);    
                }
            }
        }
       
        if ($request->hasFile('page_banner')) {
            $files = $request->file('page_banner');
            $mobfiles = $request->file('page_banner_mob');
            $langs = Helper::getMultiLang();

            foreach ($langs as $lang) {
                $name = '';
                if(isset($files[$lang])) {
                    $name = 'IMAGE-' . date('YmdHis') . uniqid() . '.' . $files[$lang]->getClientOriginalExtension();
                    $files[$lang]->move(storage_path('app/public/information-images'), $name);
                } else {
                    $name = isset($updatedImages[$lang]['image']) ? $updatedImages[$lang]['image'] : '';
                }

                $updatedImages[$lang] = array_merge($updatedImages[$lang] ?? [], ['image' => $name]);
            }
        }
        
        if($request->hasFile('page_banner_mob')){
            $mobfiles = $request->file('page_banner_mob');
            $langs = Helper::getMultiLang();

            foreach ($langs as $lang) {
                $mob_name = '';
                if(isset($mobfiles[$lang]) && !empty($mobfiles[$lang])) {
                    $mob_name = 'MOB-IMAGE-' . date('YmdHis') . uniqid() . '.' . $mobfiles[$lang]->getClientOriginalExtension();
                    $mobfiles[$lang]->move(storage_path('app/public/information-images'), $mob_name);
                } else {
                    $mob_name = isset($updatedImages[$lang]['mob_image']) ? $updatedImages[$lang]['mob_image'] : '';
                }

                $updatedImages[$lang] = array_merge($updatedImages[$lang] ?? [], ['mob_image' => $mob_name]);
            }
        }

        $info->page_title = $request->page_title;
        $info->slug = $request->slug;
        $info->page_description = $request->page_description;
        $info->page_banner = json_encode($updatedImages);
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