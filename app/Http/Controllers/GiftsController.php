<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Gifts;
use Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class GiftsController extends Controller
{
     protected $moduleName = 'Gifts';

    public function index(Request $request) {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            return view('gifts.index', compact('moduleName'));
        }

        $gifts = Gifts::query();

        return dataTables()->eloquent($gifts)
            ->editColumn('gift_title', function($gift) {
                return $gift->gift_title ?? '-';
            })
            ->editColumn('category', function($gift) {
                return $gift->category->name ?? '-';
            })
            ->editColumn('gift_images', function($gift) {
                    $imagePath = storage_path('app/public/gifts-images/' . $gift->gift_images);
                    if (file_exists($imagePath) && !empty($gift->gift_images)) {
                        $url = asset('storage/gifts-images/' . $gift->gift_images);
                        return '<img src="' . $url . '" alt="'.$gift->name.'" class="p-1" style="height:100px; width:200px;" />';
                    }
                    return '-';
            })
            ->editColumn('addedby.name', function($brand) {
                return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($brand->created_at))."'>".$brand->addedby->name."</span>";
            })
            ->editColumn('updatedby.name', function($brand) {
                if ($brand->updatedby->name != '-') {
                    return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($brand->updated_at))."'>".$brand->updatedby->name."</span>";
                } else {
                    return $brand->updatedby->name;
                }
            })
            ->addColumn('action', function ($brand) {
                $variable = $brand;

                $action = "";
                $action .= '<div class="d-flex align-items-center justify-content-center">';
                if (auth()->user()->hasPermission("gifts.edit")) {
                    $url = route("gifts.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("gifts.view")) {
                    $url = route("gifts.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("gifts.activeinactive")) {
                    $url = route("gifts.activeinactive", encrypt($variable->id));
                    $action .= view('buttons.status', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("gifts.delete")) {
                    $url = route("gifts.delete", encrypt($variable->id));
                    $action .= view('buttons.delete', compact('variable', 'url'));
                }
                $action .= '</div>';

                return $action;
            })
            ->editColumn("status",function($brand) {
                if ($brand->status == 1) {
                    return "<span class='badge bg-success'>Active</span>";
                } else {
                    return "<span class='badge bg-danger'>InActive</span>";
                }
            })
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name','gift_title','category','gift_images'])
            ->addIndexColumn()
            ->make(true);
    }
    public function create(Request $request) {
        $moduleName = $this->moduleName;
        $moduleLink = route('brands.index');
        $categorys = Category::where('status',1)->get();
        return view('gifts.create', compact('moduleName','moduleLink','categorys'));
    }
    public function store(Request $request)
    {   
        
        Validator::make($request->all(),[
            'gift_title' => 'required',
            'category_id' => 'required',
            'gift_images' => 'required',
        ],[
            'gift_title.required' => 'Gift name is require!',
            'category_id.required' => 'Catgeory is require!',
            'gift_images.required' => 'Gift image is required!',
        ]);

        if (!file_exists(storage_path('app/public/gifts-images'))) {
            mkdir(storage_path('app/public/gifts-images'), 0777, true);
        }

        $LogoImg = '';
        if ($request->hasFile('gift_images')) {
            $main_file = $request->file('gift_images');
            $main_name = 'GIFT-IMAGE-' . date('YmdHis') . uniqid() . '.' . $main_file->getClientOriginalExtension();
            $main_file->move(storage_path('app/public/gifts-images'), $main_name);
            $LogoImg = $main_name;
        }

        $gift = new Gifts();
        $gift->gift_title = $request->gift_title;
        $gift->category_id = $request->category_id;
        $gift->gift_images = $LogoImg;
        $gift->status = 1;
        $gift->added_by = auth()->user()->id;
        $gift->save();

        return redirect()->route('gifts.index')->with('success', 'Gift added successfully.');
    }

    public function edit($id)
    {
        $moduleName = $this->moduleName;
        $moduleLink = route('gifts.index');
        $gift = Gifts::where('id', decrypt($id))->first();
        $categorys = Category::where('status',1)->get();
        return view('gifts.edit', compact('moduleName', 'id', 'gift','moduleLink','categorys'));
    }

    public function update(Request $request, $id)
    {
        Validator::make($request->all(),[
            'gift_title' => 'required',
            'category_id' => 'required',
            'gift_images' => 'required',
        ],[
            'gift_title.required' => 'Gift name is require!',
            'category_id.required' => 'Catgeory is require!',
            'gift_images.required' => 'Gift image is required!',
        ]);

        $gift = Gifts::find(decrypt($id));

        
        if (!empty($request->old_image)) {
            unlink(storage_path('app/public/gifts-images/'.$gift->gift_images));
        }

        $LogoImg = $gift->gift_images;
        if ($request->hasFile('gift_images')) {
            $main_file = $request->file('gift_images');
            $main_name = 'GIFT-IMAGE-' . date('YmdHis') . uniqid() . '.' . $main_file->getClientOriginalExtension();
            $main_file->move(storage_path('app/public/gifts-images'), $main_name);
            $LogoImg = $main_name;
        }

        $gift->gift_title = $request->gift_title;
        $gift->category_id = $request->category_id;
        $gift->gift_images = $LogoImg;
        $gift->status = 1;
        $gift->updated_by = auth()->user()->id;
        $gift->update();

        return redirect()->route('gifts.index')->with('success', 'Gift updated successfully.');
    }

    public function show($id)
    {
        $moduleName = $this->moduleName;
        $moduleLink = route('gifts.index');
        $gift = Gifts::where('id', decrypt($id))->first();
        $categorys = Category::where('status',1)->get();
        return view('gifts.view', compact('moduleName', 'id', 'gift','moduleLink','categorys'));
    }

    public function destroy($id)
    {
        $gifts = Gifts::find(decrypt($id));
        
        if ($gifts->delete()) {
            return response()->json(['success' => 'Gift deleted successfully.', 'status' => 200]);
        } else {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function status($id)
    {
        try {
            $gift = Gifts::find(decrypt($id));
            $gift->status = $gift->status == 1 ? 0 : 1;
            $gift->save();

            if ($gift->status == 1) {
                return response()->json(['success' => 'Gift activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Gift inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
