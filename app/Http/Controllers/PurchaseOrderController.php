<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use App\Models\User;

class PurchaseOrderController extends Controller
{
    protected $moduleName = 'Purchase Orders';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            $suppliers = User::whereHas('role', function ($builder) {
                $builder->where('roles.id', 4);
            })->select('users.id as id', 'users.name as name')->pluck('name', 'id')->toArray();
    
            return view('po.index', compact('moduleName', 'suppliers'));
        }

        $po = PurchaseOrder::with(['items', 'addedby', 'updatedby']);

        if ($request->has('filterSupplier') && !empty(trim($request->filterSupplier))) {
            $po = $po->where('supplier_id', $request->filterSupplier);
        }

        if ($request->has('filterFrom') && !empty(trim($request->filterFrom))) {
            $po = $po->where('date', '>=', date('Y-m-d H:i:s', strtotime($request->filterFrom)));
        }

        if ($request->has('filterTo') && !empty(trim($request->filterTo))) {
            $po = $po->where('date', '<=', date('Y-m-d H:i:s', strtotime($request->filterTo)));
        }

        if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
            $po = $po->orderBy('id', 'desc');
        }

        return dataTables()->eloquent($po)
            ->editColumn('addedby.name', function($user) {
                return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($user->created_at))."'>".($user->addedby->name ?? '-')."</span>";
            })
            ->editColumn('updatedby.name', function($user) {
                if (($user->updatedby->name ?? '') != '-') {
                    return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($user->updated_at))."'>".($user->updatedby->name ?? '-')."</span>";
                } else {
                    return ($user->updatedby->name ?? '-');
                }
            })
            ->addColumn('total', function ($product) {
                return number_format(round($product->total() ?? 0), 00);
            })
            ->addColumn('action', function ($users) {

                $variable = $users;

                $action = "";
                $action .= '<div class="whiteSpace">';
                if (auth()->user()->hasPermission("purchase-orders.edit")) {
                    $url = route("purchase-orders.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("purchase-orders.view")) {
                    $url = route("purchase-orders.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("purchase-orders.delete")) {
                    $url = route("purchase-orders.delete", encrypt($variable->id));
                    $action .= view('buttons.delete', compact('variable', 'url')); 
                }
                $action .= '</div>';

                return $action;
            })
            ->rawColumns(['action', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        $moduleName = 'Purchase Order';
        $suppliers = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 4);
        })->select('users.id as id', 'users.name as name')->pluck('name', 'id')->toArray();
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $orderNo = Helper::generatePurchaseOrderNumber();

        return view('po.create', compact('moduleName', 'suppliers', 'categories', 'orderNo'));
    }

    public function productsOnCategory(Request $request)
    {
        $html = "<option value='' selected> Select a Product </option>";
        $products = Product::active()->where('category_id', $request->id)->selectRaw("id, name, purchase_price as price")->get();

        foreach ($products as $product) {
            $html .= "<option value='{$product->id}' data-price='{$product->price}'> {$product->name} </option>";
        }

        return response()->json($html);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'order_date' => 'required',
            'supplier' => 'required',
            'category.*' => 'required',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'expense.*' => 'required|numeric|min:0'
        ], [
            'order_date.required' => 'Select order date.',
            'supplier.required' => 'Select a supplier.',
            'category.*' => 'Select a category.',
            'product.*' => 'Select a product.',
            'quantity.*.required' => 'Enter quantity.',
            'quantity.*.numeric' => 'Enter valid format.',
            'quantity.*.min' => 'Quantity can\'t be less than 1.',
            'price.*.required' => 'Enter Price.',
            'price.*.numeric' => 'Enter valid format.',
            'price.*.min' => 'Quantity can\'t be less than 0.',
            'expense.*.required' => 'Enter expense.',
            'expense.*.numeric' => 'Enter valid format.',
            'expense.*.min' => 'Quantity can\'t be less than 0.'
        ]);

        $orderNo = Helper::generatePurchaseOrderNumber();
        $userId = auth()->user()->id;

        DB::beginTransaction();

        try {

            if (is_array($request->product) && count($request->product) > 0) {

                $po = new PurchaseOrder();
                $po->order_no = $orderNo;
                $po->supplier_id = $request->supplier;
                $po->added_by = $userId;
                $po->date = date('Y-m-d H:i:s', strtotime($request->order_date));
                $po->save();

                $poId = $po->id;
                $poItems = [];
                $poItemForStock = [];

                foreach ($request->product as $key => $product) {
                    $poItems[] = [
                        'po_id' => $poId,
                        'category_id' => $request->category[$key] ?? '',
                        'product_id' => $product,
                        'price' => floatval($request->price[$key]) ?? 0,
                        'expense' => floatval($request->expense[$key]) ?? 0,
                        'qty' => intval($request->quantity[$key]) ?? 0,
                        'amount' => floatval($request->amount[$key]) ?? 0,
                        'remarks' => $request->remarks[$key] ?? '',
                        'added_by' => $userId,
                    ];

                    $poItemForStock[] = [
                        'product_id' => $product,
                        'type' => 0,
                        'date' => now(),
                        'qty' => intval($request->quantity[$key]) ?? 0,
                        'added_by' => $userId,
                        'form' => 1,
                        'form_record_id' => $poId
                    ];
                }

                PurchaseOrderItem::insert($poItems);
                Stock::insert($poItemForStock);

                DB::commit();
                return redirect()->route('purchase-orders.index')->with('success', 'Purchase order added successfully.');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Select at least a product to add purchase order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logger($e->getMessage(), 'error');
            return redirect()->back()->with('error', Helper::$errorMessage);
        }

    }

    public function edit(Request $request, $id)
    {
        $moduleName = 'Purchase Order';
        $suppliers = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 4);
        })->select('users.id as id', 'users.name as name')->pluck('name', 'id')->toArray();
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $po = PurchaseOrder::find(decrypt($id));
        $items = PurchaseOrderItem::with('category')->where('po_id', decrypt($id))->get();

        return view('po.edit', compact('moduleName', 'suppliers', 'categories', 'id', 'po', 'items'));
    }

    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'order_date' => 'required',
            'supplier' => 'required',
            'category.*' => 'required',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'expense.*' => 'required|numeric|min:0'
        ], [
            'order_date.required' => 'Select order date.',
            'supplier.required' => 'Select a supplier.',
            'category.*' => 'Select a category.',
            'product.*' => 'Select a product.',
            'quantity.*.required' => 'Enter quantity.',
            'quantity.*.numeric' => 'Enter valid format.',
            'quantity.*.min' => 'Quantity can\'t be less than 1.',
            'price.*.required' => 'Enter Price.',
            'price.*.numeric' => 'Enter valid format.',
            'price.*.min' => 'Quantity can\'t be less than 0.',
            'expense.*.required' => 'Enter expense.',
            'expense.*.numeric' => 'Enter valid format.',
            'expense.*.min' => 'Quantity can\'t be less than 0.'
        ]);

        $userId = auth()->user()->id;
        $id = decrypt($id);

        DB::beginTransaction();

        try {

            if (is_array($request->product) && count($request->product) > 0) {

                $po = PurchaseOrder::find($id);
                $po->supplier_id = $request->supplier;
                $po->updated_by = $userId;
                $po->date = date('Y-m-d H:i:s', strtotime($request->order_date));
                $po->save();

                $poItems = [];
                $poItemForStock = [];

                PurchaseOrderItem::where('po_id', $id)->delete();
                Stock::where('type', '0')->where('form', '1')->where('form_record_id', $id)->delete();

                foreach ($request->product as $key => $product) {
                    $poItems[] = [
                        'po_id' => $id,
                        'category_id' => $request->category[$key] ?? '',
                        'product_id' => $product,
                        'price' => floatval($request->price[$key]) ?? 0,
                        'expense' => floatval($request->expense[$key]) ?? 0,
                        'qty' => intval($request->quantity[$key]) ?? 0,
                        'amount' => floatval($request->amount[$key]) ?? 0,
                        'remarks' => $request->remarks[$key] ?? '',
                        'added_by' => $userId,
                    ];

                    $poItemForStock[] = [
                        'product_id' => $product,
                        'type' => 0,
                        'date' => now(),
                        'qty' => intval($request->quantity[$key]) ?? 0,
                        'added_by' => $userId,
                        'form' => 1,
                        'form_record_id' => $id
                    ];
                }

                PurchaseOrderItem::insert($poItems);
                Stock::insert($poItemForStock);

                DB::commit();
                return redirect()->route('purchase-orders.index')->with('success', 'Purchase order updated successfully.');

            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Select at least a product to update purchase order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logger($e->getMessage(), 'error');
            return redirect()->back()->with('error', Helper::$errorMessage);
        }

    }

    public function show(Request $request, $id)
    {
        $moduleName = 'Purchase Order';
        $suppliers = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 4);
        })->select('users.id as id', 'users.name as name')->pluck('name', 'id')->toArray();
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $po = PurchaseOrder::find(decrypt($id));
        $items = PurchaseOrderItem::with('category')->where('po_id', decrypt($id))->get();

        return view('po.view', compact('moduleName', 'suppliers', 'categories', 'po', 'items'));
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $poId = decrypt($id);

            PurchaseOrder::where('id', $poId)->delete();
            PurchaseOrderItem::where('po_id', $poId)->delete();
            Stock::where('type', '0')->where('form', '1')->where('form_record_id', $poId)->delete();

            DB::commit();
            return response()->json(['success' => $this->moduleName.' Deleted Successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
