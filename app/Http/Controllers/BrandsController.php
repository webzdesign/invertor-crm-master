<?php

namespace App\Http\Controllers;

use App\Models\Brands;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Helpers\Helper;
use App\Models\ProcurementCost;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class BrandsController extends Controller
{
   protected $moduleName = 'Brands';

    public function index(Request $request) {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            return view('brands.index', compact('moduleName'));
        }

        $brands = Brands::query();

        // if (isset($request->filterStatus)) {
        //     if ($request->filterStatus != '') {
        //         $categories->where('status', $request->filterStatus);
        //     }
        // }

        return dataTables()->eloquent($brands)
            ->editColumn('name', function($brand) {
                return $brand->name ?? '-';
            })
            ->editColumn('category', function($brand) {
                return $brand->category->name ?? '-';
            })
            ->editColumn('brand_logo', function($brand) {
                    $imagePath = storage_path('app/public/brands-images/' . $brand->brand_logo);
                    if (file_exists($imagePath) && !empty($brand->brand_logo)) {
                        $url = asset('storage/brands-images/' . $brand->brand_logo);
                        return '<img src="' . $url . '" alt="'.$brand->name.'" class="p-1" style="height:100px; width:200px;" />';
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
                if (auth()->user()->hasPermission("brands.edit")) {
                    $url = route("brands.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("brands.view")) {
                    $url = route("brands.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("brands.activeinactive")) {
                    $url = route("brands.activeinactive", encrypt($variable->id));
                    $action .= view('buttons.status', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("brands.delete")) {
                    $url = route("brands.delete", encrypt($variable->id));
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
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name','name','category','brand_logo'])
            ->addIndexColumn()
            ->make(true);
    }

    public function checkBrands(Request $request) {
        $Brands = Brands::where('name', trim($request->name));

        if ($request->has('id') && !empty(trim($request->id))) {
            $Brands = $Brands->where('id', '!=', decrypt($request->id));
        }

        return response()->json($Brands->doesntExist());
    }

    public function create(Request $request) {
        $moduleName = $this->moduleName;
        $moduleLink = route('brands.index');
        $categorys = Category::where('status',1)->get();
        return view('brands.create', compact('moduleName','moduleLink','categorys'));
    }

    public function store(Request $request)
    {
        Validator::make($request->all(),[
            'name' => 'required',
            'category_id' => 'required',
            'brand_logo' => 'required',
        ],[
            'name.required' => 'Brnad name is require!',
            'category_id.required' => 'Catgeory is require!',
            'brand_logo.required' => 'Brand logo is required!',
        ]);

        if (!file_exists(storage_path('app/public/brands-images'))) {
            mkdir(storage_path('app/public/brands-images'), 0777, true);
        }

        $LogoImg = '';
        if ($request->hasFile('brand_logo')) {
            $main_file = $request->file('brand_logo');
            $main_name = 'BRAND-LOGO-IMAGE-' . date('YmdHis') . uniqid() . '.' . $main_file->getClientOriginalExtension();
            $main_file->move(storage_path('app/public/brands-images'), $main_name);
            $LogoImg = $main_name;
        }

        $brand = new Brands();
        $brand->name = $request->name;
        $brand->category_id = $request->category_id;
        $brand->brand_logo = $LogoImg;
        $brand->status = 1;
        $brand->added_by = auth()->user()->id;
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Brand added successfully.');
    }

    public function edit($id)
    {
        $moduleName = $this->moduleName;
        $moduleLink = route('brands.index');
        $brand = Brands::where('id', decrypt($id))->first();
        $categorys = Category::where('status',1)->get();
        return view('brands.edit', compact('moduleName', 'id', 'brand','moduleLink','categorys'));
    }

    public function update(CategoryRequest $request, $id)
    {
        Validator::make($request->all(),[
            'name' => 'required',
            'category_id' => 'required',
            'brand_logo' => 'required',
        ],[
            'name.required' => 'Brnad name is require!',
            'category_id.required' => 'Catgeory is require!',
            'brand_logo.required' => 'Brand logo is required!',
        ]);

        $brand = Brands::find(decrypt($id));

        
        $LogoImg = $brand->brand_logo;
        if ($request->hasFile('brand_logo')) {
            if (!empty($request->old_image)) {
                unlink(storage_path('app/public/brands-images/'.$brand->brand_logo));
            }
            $main_file = $request->file('brand_logo');
            $main_name = 'BRAND-LOGO-IMAGE-' . date('YmdHis') . uniqid() . '.' . $main_file->getClientOriginalExtension();
            $main_file->move(storage_path('app/public/brands-images'), $main_name);
            $LogoImg = $main_name;
        }

        $brand->name = $request->name;
        $brand->category_id = $request->category_id;
        $brand->brand_logo = $LogoImg;
        $brand->status = 1;
        $brand->updated_by = auth()->user()->id;
        $brand->update();

        return redirect()->route('brands.index')->with('success', 'Category updated successfully.');
    }

    public function show($id)
    {
        $moduleName = $this->moduleName;
        $moduleLink = route('brands.index');
        $brand = Brands::where('id', decrypt($id))->first();
        $categorys = Category::where('status',1)->get();
        return view('brands.view', compact('moduleName', 'id', 'brand','moduleLink','categorys'));
    }

    public function destroy($id)
    {
        $brand = Brands::find(decrypt($id));
        
        if ($brand->delete()) {
            return response()->json(['success' => 'Brand deleted successfully.', 'status' => 200]);
        } else {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function status($id)
    {
        try {
            $brand = Brands::find(decrypt($id));
            $brand->status = $brand->status == 1 ? 0 : 1;
            $brand->save();

            if ($brand->status == 1) {
                return response()->json(['success' => 'Brand activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Brand inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
