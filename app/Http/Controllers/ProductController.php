<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use App\Models\Category;
use App\Helpers\Helper;
use App\Models\ProcurementCost;
use App\Models\Product;

class ProductController extends Controller
{
    protected $moduleName = 'Products';

    public function index(Request $request) {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            $categories = Category::all();

            return view('products.index', compact('moduleName', 'categories'));
        }
        
        $products = Product::query();

        if (isset($request->filterStatus)) {
            if ($request->filterStatus != '') {
                $products->where('status', $request->filterStatus);
            }
        }

        if (isset($request->filterCategory)) {
            if ($request->filterCategory != '') {
                $products->where('category_id', $request->filterCategory);
            }
        }

        return dataTables()->eloquent($products)
            ->editColumn('addedby.name', function($category) {
                return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($category->created_at))."'>".$category->addedby->name."</span>";
            })
            ->editColumn('updatedby.name', function($category) {
                if ($category->updatedby->name != '-') {
                    return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($category->updated_at))."'>".$category->updatedby->name."</span>";
                } else {
                    return $category->updatedby->name;
                }
            })
            ->addColumn("category", function($product) {
                return $product->category->name ?? '';
            })
            ->editColumn("unique_number", function ($product) {
                return !empty(trim($product->unique_number)) ? $product->unique_number : '-';
            })
            ->addColumn('action', function ($product) {
                $variable = $product;

                $action = "";
                $action .= '<div class="whiteSpace">';
                if (auth()->user()->hasPermission("products.edit")) {
                    $url = route("products.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("products.edit")) {
                    $eId = encrypt($variable->id);
                    $url = route("products.image", $eId);
                    $action .= "<div class='tableCards d-inline-block me-1 pb-0'><div class='editDlbtn' ><a data-bs-toggle='tooltip' style='background:#ffc107 !important;' title='Alter Images' href='{$url}' class='editBtn'> <i class='fa fa-image text-dark' aria-hidden='true'></i> </a></div></div>"; 
                }
                if (auth()->user()->hasPermission("products.view")) {
                    $url = route("products.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("products.activeinactive")) {
                    $url = route("products.activeinactive", encrypt($variable->id));
                    $action .= view('buttons.status', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("products.delete")) {
                    $url = route("products.delete", encrypt($variable->id));
                    $action .= view('buttons.delete', compact('variable', 'url')); 
                }
                $action .= '</div>';

                return $action;
            })
            ->editColumn("status",function($users) {
                if ($users->status == 1) {
                    return "<span class='badge bg-success'>Active</span>";
                } else {
                    return "<span class='badge bg-danger'>InActive</span>";
                }
            })
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function checkProduct(Request $request) {
        $product = Product::whereNotNull('unique_number')->where('unique_number', trim($request->name));

        if ($request->has('id') && !empty(trim($request->id))) {
            $product = $product->where('id', '!=', decrypt($request->id));
        }

        return response()->json($product->doesntExist());
    }

    public function saveProductImage(Request $request, $id) {
        if (!file_exists(storage_path('app/public/product-images'))) {
            mkdir(storage_path('app/public/product-images'), 0777, true);
        }

        $id = decrypt($id);

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $name = 'IMAGE-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(storage_path('app/public/product-images'), $name);

                if (file_exists(storage_path("app/public/product-images/{$name}"))) {
                    ProductImage::create(['product_id' => $id,'name' => $name]);
                }
            }
        }

        $images = ProductImage::select('id', 'name')->where('product_id', $id)->get();

        return response()->json(view('products.image', compact('images'))->render());
    }

    public function deleteImage(Request $request)
    {
        $response = false;
        if ($image = ProductImage::find($request->id)) {
            if (storage_path("app/public/product-images/{$image->name}")) {
                @unlink(storage_path("app/public/product-images/{$image->name}"));
                $response = true;
            }
            $image->delete();
        }

        return response()->json($response);
    }

    public function create(Request $request) {
        $moduleName = 'Product';
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();

        return view('products.create', compact('moduleName', 'categories'));
    }

    public function store(ProductRequest $request)
    {

        $product = new Product();
        $product->unique_number = $request->unique_number;
        $product->name = $request->name;
        $product->category_id = $request->category;
        $product->purchase_price = $request->pprice;
        $product->sales_price = $request->sprice;
        $product->added_by = auth()->user()->id;
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    public function edit($id)
    {
        $did = decrypt($id);

        $moduleName = 'Product';
        $product = Product::with(['images'])->where('id', $did)->first();
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $images = ProductImage::select('id', 'name')->where('product_id', $did)->get();

        return view('products.edit', compact('moduleName', 'id', 'product', 'categories', 'images'));
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::find(decrypt($id));
        $product->unique_number = $request->unique_number;
        $product->name = $request->name;
        $product->category_id = $request->category;
        $product->purchase_price = $request->pprice;
        $product->sales_price = $request->sprice;
        $product->updated_by = auth()->user()->id;
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product Updated successfully.');
    }

    public function show($id)
    {
        $did = decrypt($id);

        $moduleName = 'Product';
        $product = Product::with(['images'])->where('id', $did)->first();

        return view('products.view', compact('moduleName', 'product'));
    }

    public function destroy($id)
    {
        $product = Product::where('id', decrypt($id));

        if ($product->exists()) {

            $product->delete();

            $images = ProductImage::where('product_id', decrypt($id))->select('name')->pluck('name')->toArray();

            foreach ($images as $image) {
                if (file_exists(storage_path("app/public/product-images/{$image}"))) {
                    unlink(storage_path("app/public/product-images/{$image}"));
                }
            }

            ProductImage::where('product_id', decrypt($id))->delete();
            ProcurementCost::where('product_id', decrypt($id))->delete();

            return response()->json(['success' => 'Product deleted Successfully.', 'status' => 200]);            
        } else {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function status($id)
    {
        try {
            $user = Product::find(decrypt($id));
            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();

            if ($user->status == 1) {
                return response()->json(['success' => 'Product activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Product deactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }        
    }

    public function images(Request $request, $id) {

        $images = ProductImage::select('id', 'name')->where('product_id', decrypt($id))->get();
        $moduleName = 'Product Images';

        return view('products.images', compact('images', 'moduleName', 'id'));
    }
}

