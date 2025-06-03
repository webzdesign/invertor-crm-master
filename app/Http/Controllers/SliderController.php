<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Product;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    protected $moduleName = 'Home Page Sliders';
    public function index() {
        $moduleName = $this->moduleName;
        return view('sliders.index',compact('moduleName'));
    }

    public function sliderList(Request $request){

    $sliders = Slider::query();

    return dataTables()->eloquent($sliders)
        ->editColumn('added_by', function ($slider) {
            $user = User::find($slider->added_by);
            return $user->name;
        })
        ->editColumn('updated_by', function ($slider) {
            if (!empty($slider->updated_by)) {
                $user = User::find($slider->updated_by);
                return $user->name;
            } else {
                return '-';
            }
        })
        ->editColumn("title", function ($slider) {
            return $slider->title ?? '-';
        })
        ->editColumn("short_description", function ($slider) {
            return !empty(trim($slider->short_description)) ? $slider->short_description : '-';
        })
        ->editColumn("main_image", function ($slider) {
            $imagePath = storage_path('app/public/sliders-images/' . $slider->main_image);
            if (file_exists($imagePath)) {
                $url = asset('storage/sliders-images/' . $slider->main_image);
                return '<img src="' . $url . '" alt="main image" class="p-1" style="height:100px; width:400px;" />';
            }
            return '-';
        })
       ->editColumn("gift_images", function ($slider) {
            $imagesHtml = '';
            if(!empty($slider->gift_images)) {
                foreach (explode(',',$slider->gift_images) as $value) {
                    $imagePath = storage_path('app/public/sliders-images/' . $value);
                    if (file_exists($imagePath)) {
                        $url = asset('storage/sliders-images/' . $value);
                        $imagesHtml .= '<img src="' . $url . '" alt="gift image" style="height:100px; width:100px; padding:10px;" />';
                    }
                }
            } else {
                $imagesHtml = '-';
            }

            return $imagesHtml;
        })
        ->addColumn('action', function ($slider) {
            $variable = $slider;
            $action = '<div class="d-flex align-items-center justify-content-center">';
            
            if (auth()->user()->hasPermission("sliders.edit")) {
                $url = route("sliders.edit", encrypt($slider->id));
                $action .= view('buttons.edit', compact('variable', 'url'));
            }

            if (auth()->user()->hasPermission("sliders.view")) {
                $url = route("sliders.view", encrypt($slider->id));
                $action .= view('buttons.view', compact('variable', 'url'));
            }

            if (auth()->user()->hasPermission("sliders.activeinactive")) {
                $url = route("sliders.activeinactive", encrypt($slider->id));
                $action .= view('buttons.status', compact('variable', 'url'));
            }

            if (auth()->user()->hasPermission("sliders.delete")) {
                $url = route("sliders.delete", encrypt($slider->id));
                $action .= view('buttons.delete', compact('variable', 'url'));
            }

            $action .= '</div>';
            return $action;
        })
        ->editColumn("status", function ($slider) {
            return $slider->status == 1
                ? "<span class='badge bg-success'>Active</span>"
                : "<span class='badge bg-danger'>Inactive</span>";
        })
        ->rawColumns(['action', 'status', 'added_by', 'updated_by', 'short_description', 'title', 'main_image','gift_images'])
        ->addIndexColumn()
        ->make(true);
    }
    public function create() {
        $moduleName = $this->moduleName;
        
        $products = Product::where('status',1)->pluck('name','id');

        return view('sliders.create',compact('moduleName','products'));
    }

    public function store(Request $request) {

        Validator::make($request->all(),[
            'title' => 'required',
            'product_id' => 'required',
            // 'short_description' => 'required',
            'main_image' => 'required',
        ],[
            'title.required' => 'Slider title is require!',
            'product_id.required' => 'Product is require!',
            // 'short_description.required' => 'Short Description is require!',
            'main_image.required' => 'Main banner image is required!',
        ]);

        if (!file_exists(storage_path('app/public/sliders-images'))) {
            mkdir(storage_path('app/public/sliders-images'), 0777, true);
        }

        $mainImg = '';
        if ($request->hasFile('main_image')) {
            $main_file = $request->file('main_image');
            $main_name = 'MAIN-IMAGE-' . date('YmdHis') . uniqid() . '.' . $main_file->getClientOriginalExtension();
            $main_file->move(storage_path('app/public/sliders-images'), $main_name);
            $mainImg = $main_name;
        }

        $giftImg = '';
        if ($request->hasFile('gift_images')) {
            $gift_files = $request->file('gift_images');
            $gName = [];
            foreach (array_filter($gift_files) as $gift_file) {   
                $gift_name = 'GIFT-IMAGE-' . date('YmdHis') . uniqid() . '.' . $gift_file->getClientOriginalExtension();
                $gift_file->move(storage_path('app/public/sliders-images'), $gift_name);
                $gName[] = $gift_name; 
            }
            $giftImg = implode(',',$gName);
        }

        $slider = new Slider();
        $slider->title = $request->title;
        $slider->product_id = $request->product_id;
        // $slider->sort_description = $request->sort_description;
        $slider->main_image = $mainImg;
        $slider->gift_images = $giftImg;
        $slider->added_by = auth()->user()->id;
        $slider->status = 1;

        if($slider->save()) {
            return redirect()->route('sliders.index')->with('success', 'Slider added successfully.');
        } else {
            return redirect()->route('sliders.index')->with('error', 'Something went wrong!');
        }
    }
    public function view($id) {
        $moduleName = $this->moduleName;
        $id = decrypt($id);
        $slider = Slider::where('id',$id)->first();
         $products = Product::where('status',1)->pluck('name','id');
        return view('sliders.view',compact('moduleName','slider','products'));
    }
    public function edit($id) {
        $moduleName = $this->moduleName;
        $id = decrypt($id);
        $slider = Slider::where('id',$id)->first();
        $products = Product::where('status',1)->pluck('name','id');
        return view('sliders.edit',compact('moduleName','slider','products'));
    }
    public function update(Request $request, $id) {

        $id = decrypt($id);
        $slider = Slider::find($id);
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

        if(!empty($request->remove_images)) {
            $oldImagesRemove = explode(',',$request->remove_images);
            foreach ($oldImagesRemove as $key => $value) {
                $imagePath = storage_path('app/public/sliders-images/' . $value);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        $mainImg = '';
        if ($request->hasFile('main_image')) {
            $main_file = $request->file('main_image');
            $main_name = 'MAIN-IMAGE-' . date('YmdHis') . uniqid() . '.' . $main_file->getClientOriginalExtension();
            $main_file->move(storage_path('app/public/sliders-images'), $main_name);
            $mainImg = $main_name;
        } else {
            $mainImg = $slider->main_image;
        }

        $giftImg = '';
        if ($request->hasFile('gift_images')) {
            $gift_files = $request->file('gift_images');
            $gName = [];
            foreach (array_filter($gift_files) as $gift_file) {   
                $gift_name = 'GIFT-IMAGE-' . date('YmdHis') . uniqid() . '.' . $gift_file->getClientOriginalExtension();
                $gift_file->move(storage_path('app/public/sliders-images'), $gift_name);
                $gName[] = $gift_name; 
            }
            $oldImages = explode(',', $slider->gift_images);
            $giftImg = implode(',',array_merge($oldImages,$gName));
        } else {
            if (!empty($request->remove_images)) {
                $oldImagesRemove = explode(',', $request->remove_images);
                $oldImages = explode(',', $slider->gift_images);

                $updatedImages = array_diff($oldImages, $oldImagesRemove);

                $giftImg = implode(',', $updatedImages);
            } else {
                $giftImg = $slider->gift_images;
            }
        }

        $slider->title = $request->title;
        $slider->product_id = $request->product_id;
        // $slider->short_description = $request->short_description;
        $slider->main_image = $mainImg;
        $slider->gift_images = $giftImg;
        $slider->updated_by = auth()->user()->id;
        $slider->status = 1;

        if($slider->update()) {
            return redirect()->route('sliders.index')->with('success', 'Slider updated successfully.');
        } else {
            return redirect()->route('sliders.index')->with('error', 'Something went wrong!');
        }
    }

    public function destroy($id)
    {
        $slider = Slider::where('id', decrypt($id));

        if ($slider->exists()) {
            if(!empty($slider->main_images)) {
                if (file_exists(storage_path("app/public/sliders-images/{$slider->main_images}"))) {
                    unlink(storage_path("app/public/sliders-images/{$slider->main_images}"));
                }
            }

            if(!empty($slider->main_images)) {
                if (file_exists(storage_path("app/public/sliders-images/{$slider->main_images}"))) {
                    unlink(storage_path("app/public/sliders-images/{$slider->main_images}"));
                }
            }

            if(!empty($slider->gift_images)) {
                $oldImagesRemove = explode(',',$slider->remove_images);
                foreach ($oldImagesRemove as $key => $value) {
                    $imagePath = storage_path('app/public/sliders-images/' . $value);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
                
            $slider->delete();
            return response()->json(['success' => 'Slider deleted successfully.', 'status' => 200]);
        } else {
            return response()->json(['error' => 'Something went wrong!', 'status' => 500]);
        }
    }
    public function status($id)
    {
        try {
            $slider = Slider::find(decrypt($id));
            $slider->status = $slider->status == 1 ? 0 : 1;
            $slider->save();

            if ($slider->status == 1) {
                return response()->json(['success' => 'Slider activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Slider inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
