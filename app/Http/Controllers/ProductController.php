<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use App\Models\Category;
use App\Helpers\Helper;
use App\Models\ProcurementCost;
use App\Models\DistributionItem;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrderItem;
use App\Models\Stock;
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
           ->addColumn("hot_product", function($product) {
                $checked = $product->is_hot ? 'checked' : '';
                $disabled = $product->status == 0 ? 'disabled' : '';
                $input = '<input type="checkbox" class="form-check-input is-hot-product" data-id="'.encrypt($product->id).'" '.$checked. ' ' . $disabled .'>';
                return $input;
            })
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
                $action .= '<div class="d-flex align-items-center justify-content-center">';
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
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name','hot_product'])
            // ->addIndexColumn()
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
        $moduleLink = route('products.index');
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $prodNo = Helper::generateProductNumber();

        return view('products.create', compact('moduleName', 'categories', 'prodNo','moduleLink'));
    }

    public function store(ProductRequest $request)
    {
        $product = new Product();
        $product->unique_number = Helper::generateProductNumber();
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->slider_title = $request->slider_title;
        $product->slider_content = $request->slider_content;
        $product->category_id = $request->category;
        $product->description = $request->description;
        $product->shipping_and_payment = $request->shipping_and_payment ?? '';
        $product->web_sales_price = $request->web_sales_price ?? 0;
        $product->web_sales_old_price = $request->web_sales_old_price ?? 0;
        $product->sku = $request->sku ?? '';
        $product->brand = $request->brand ?? '';
        $product->gtin = $request->gtin ?? '';
        $product->mpn = $request->mpn ?? '';
        $product->youtube_video_url = $request->youtube_video_url ?? '';
        $product->air_conditioner_capacity = $request->air_conditioner_capacity ?? '';
        $product->available_power_capacity = (is_array($request->available_power_capacity) && !empty($request->available_power_capacity) ? implode(',',array_filter($request->available_power_capacity)) : '');
        $product->added_by = auth()->user()->id;
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    public function edit($id)
    {
        $did = decrypt($id);
        $moduleLink = route('products.index');
        $moduleName = 'Product';
        $product = Product::with(['images'])->where('id', $did)->first();
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $images = ProductImage::select('id', 'name')->where('product_id', $did)->get();

        return view('products.edit', compact('moduleName', 'id', 'product', 'categories', 'images','moduleLink'));
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::find(decrypt($id));
        $product->name = $request->name;
        $product->slug = $request->slug;
        $product->slider_title = $request->slider_title;
        $product->slider_content = $request->slider_content;
        $product->category_id = $request->category;
        $product->description = $request->description;
        $product->shipping_and_payment = $request->shipping_and_payment ?? '';
        $product->web_sales_price = $request->web_sales_price ?? 0;
        $product->web_sales_old_price = $request->web_sales_old_price ?? 0;
        $product->sku = $request->sku ?? '';
        $product->brand = $request->brand ?? '';
        $product->gtin = $request->gtin ?? '';
        $product->mpn = $request->mpn ?? '';
        $product->youtube_video_url = $request->youtube_video_url ?? '';
        $product->air_conditioner_capacity = $request->air_conditioner_capacity ?? '';
        $product->available_power_capacity = (is_array($request->available_power_capacity) && !empty($request->available_power_capacity) ? implode(',',array_filter($request->available_power_capacity)) : '');
        $product->updated_by = auth()->user()->id;
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function show($id)
    {
        $did = decrypt($id);
        $moduleLink = route('products.index');
        $moduleName = 'Product';
        $product = Product::with(['images'])->where('id', $did)->first();

        return view('products.view', compact('moduleName', 'product','moduleLink'));
    }

    public function destroy($id)
    {
        $product = Product::where('id', decrypt($id));


        if ($product->exists()) {
            $ProcurementCost = ProcurementCost::where('product_id',decrypt($id))->count();
            $DistributionItem = DistributionItem::where('product_id',decrypt($id))->count();
            $PurchaseOrderItem = PurchaseOrderItem::where('product_id',decrypt($id))->count();
            $SalesOrderItem = SalesOrderItem::where('product_id',decrypt($id))->count();
            $Stock = Stock::where('product_id',decrypt($id))->count();

            if($ProcurementCost > 0 || $DistributionItem > 0 || $PurchaseOrderItem > 0 || $SalesOrderItem > 0 || $Stock > 0) {
                return response()->json(['error' => 'The product has been assigned to a different module, therefore it will not be removed.', 'status' => 500]);
            } else {
                $product->delete();

                $images = ProductImage::where('product_id', decrypt($id))->select('name')->pluck('name')->toArray();

                foreach ($images as $image) {
                    if (file_exists(storage_path("app/public/product-images/{$image}"))) {
                        unlink(storage_path("app/public/product-images/{$image}"));
                    }
                }

                ProductImage::where('product_id', decrypt($id))->delete();
                ProcurementCost::where('product_id', decrypt($id))->delete();

                return response()->json(['success' => 'Product deleted successfully.', 'status' => 200]);
            }
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
                return response()->json(['success' => 'Product inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function images(Request $request, $id) {

        $images = ProductImage::select('id', 'name')->where('product_id', decrypt($id))->get();
        $moduleName = 'Product';
        $moduleLink = route('products.index');
        return view('products.images', compact('images', 'moduleName', 'id','moduleLink'));
    }

    public function checkProductSlug(Request $request)
    {
        $product = Product::where('slug', trim($request->slug));

        if ($request->has('id') && !empty(trim($request->id))) {
            $product = $product->where('id', '!=', decrypt($request->id));
        }

        return response()->json($product->doesntExist());
    }

    public function isHotProduct(Request $request) {
       
        if(isset($request->id) && !empty($request->id) && isset($request->is_hot)) {

            $isHotProducts = Product::select(['id'])->where('is_hot',1)->where('status',1)->whereNull('deleted_at')->count();

            if($isHotProducts < 15) {
                $product = Product::find(decrypt($request->id));

                if(!empty($product)) {
                    $product->is_hot = $request->is_hot ? 1 : 0;
                    $product->update();
                    
                    return response()->json(['success' => true, 'is_hot' => $product->is_hot]);
                } else {
                    return response()->json(['success' => false]);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Maximum 15 products allowed as hot offres.']);
            }
        } else {
            return response()->json(['success' => false]);
        }
    }
}

