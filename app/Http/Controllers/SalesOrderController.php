<?php

namespace App\Http\Controllers;

use App\Models\{SalesOrderStatus, SalesOrderItem, SalesOrder, Product, Stock, Category};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class SalesOrderController extends Controller
{
    protected $moduleName = 'Sales Orders';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
    
            return view('so.index', compact('moduleName'));
        }

        $po = SalesOrder::with(['items', 'addedby', 'updatedby']);
        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();

        if (!in_array(1, $thisUserRoles)) {
            if (in_array(2, $thisUserRoles)) { //seller orders
                $thisUserRoles = $thisUserRoles->where('added_by', auth()->user()->id);
            } else {
                $thisUserRoles = $thisUserRoles->where('id', '0');
            }
        }

        if ($request->has('filterStatus') && !empty(trim($request->filterStatus))) {
            $po = $po->where('supplier_id', $request->filterStatus);
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
                if (auth()->user()->hasPermission("sales-orders.edit")) {
                    $url = route("sales-orders.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("sales-orders.view")) {
                    $url = route("sales-orders.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("sales-orders.delete")) {
                    $url = route("sales-orders.delete", encrypt($variable->id));
                    $action .= view('buttons.delete', compact('variable', 'url')); 
                }
                $action .= '</div>';

                return $action;
            })
            ->rawColumns(['action', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function productsOnCategory(Request $request)
    {
        $html = "<option value='' selected> Select a Product </option>";
        $products = Product::with(['stockin', 'stockout'])->active()->where('category_id', $request->id)->selectRaw("id, name, sales_price as price")->get();

        foreach ($products as $product) {
            $html .= "<option value='{$product->id}' data-price='{$product->price}' data-availablestock='" . (($product->stockin->sum('qty') ?? 0) - ($product->stockout->sum('qty') ?? 0)) . "' > {$product->name} </option>";
        }

        return response()->json($html);
    }

    public function create()
    {
        $moduleName = 'Sales Order';

        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $statuses = SalesOrderStatus::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $orderNo = Helper::generateSalesOrderNumber();

        return view('so.create', compact('moduleName', 'categories', 'orderNo', 'statuses'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'order_del_date' => 'required',
            'customername' => 'required',
            'customerphone' => 'required',
            'status' => 'required',
            'postal_code' => 'required',
            'address_line_1' => 'required',
            'category.*' => 'required',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'expense.*' => 'required|numeric|min:0'
        ], [
            'order_del_date.required' => 'Select order felivery date .',
            'customername.required' => 'Enter customer name.',
            'customerphone.required' => 'Enter customer phone number.',
            'status.required' => 'Select a status.',
            'postal_code.required' => 'Enter a postal code.',
            'address_line_1.required' => 'Enter address line 1.',
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

        $orderNo = Helper::generateSalesOrderNumber();
        $userId = auth()->user()->id;

        DB::beginTransaction();

        try {

            if (is_array($request->product) && count($request->product) > 0) {

                $so = new SalesOrder();
                $so->order_no = $orderNo;
                $so->date = now();
                $so->delivery_date = date('Y-m-d H:i:s', strtotime($request->order_del_date));
                $so->customer_name = $request->customername;
                $so->customer_address_line_1 = $request->address_line_1;
                $so->customer_phone = preg_replace('/[^0-9]/', '', $request->customerphone);
                $so->country_dial_code = $request->country_dial_code;
                $so->country_iso_code = $request->country_iso_code;
                $so->customer_postal_code = $request->postal_code;
                $so->customer_facebook = $request->customerfb;
                $so->status = $request->status;
                $so->added_by = $userId;
                $so->save();

                $soId = $so->id;
                $soItems = [];
                $soItemForStock = [];

                foreach ($request->product as $key => $product) {

                    $qty = intval($request->quantity[$key]) ?? 0;

                    $soItems[] = [
                        'so_id' => $soId,
                        'category_id' => $request->category[$key] ?? '',
                        'product_id' => $product,
                        'price' => floatval($request->price[$key]) ?? 0,
                        'qty' => $qty,
                        'amount' => floatval($request->amount[$key]) ?? 0,
                        'remarks' => $request->remarks[$key] ?? '',
                        'added_by' => $userId,
                    ];

                    $soItemForStock[] = [
                        'product_id' => $product,
                        'type' => 1,
                        'date' => now(),
                        'qty' => $qty,
                        'added_by' => $userId,
                        'form' => 2,
                        'form_record_id' => $soId
                    ];
                }

                SalesOrderItem::insert($soItems);
                Stock::insert($soItemForStock);

                DB::commit();
                return redirect()->route('sales-orders.index')->with('success', 'Sales order added successfully.');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Select at least a product to add sales order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logger($e->getMessage(), 'error');
            return redirect()->back()->with('error', Helper::$errorMessage);
        }

    }

    public function edit(Request $request, $id)
    {
        $moduleName = 'Sales Order';
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $so = SalesOrder::find(decrypt($id));
        $statuses = SalesOrderStatus::active()->select('id', 'name')->pluck('name', 'id')->toArray();

        return view('so.edit', compact('moduleName', 'categories', 'id', 'so', 'statuses'));
    }
    
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'order_del_date' => 'required',
            'customername' => 'required',
            'customerphone' => 'required',
            'status' => 'required',
            'postal_code' => 'required',
            'address_line_1' => 'required',
            'category.*' => 'required',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'expense.*' => 'required|numeric|min:0'
        ], [
            'order_del_date.required' => 'Select order felivery date .',
            'customername.required' => 'Enter customer name.',
            'customerphone.required' => 'Enter customer phone number.',
            'status.required' => 'Select a status.',
            'postal_code.required' => 'Enter a postal code.',
            'address_line_1.required' => 'Enter address line 1.',
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

                $so = SalesOrder::find($id);
                $so->date = now();
                $so->delivery_date = date('Y-m-d H:i:s', strtotime($request->order_del_date));
                $so->customer_name = $request->customername;
                $so->customer_address_line_1 = $request->address_line_1;
                $so->customer_phone = preg_replace('/[^0-9]/', '', $request->customerphone);
                $so->country_dial_code = $request->country_dial_code;
                $so->country_iso_code = $request->country_iso_code;
                $so->customer_postal_code = $request->postal_code;
                $so->customer_facebook = $request->customerfb;
                $so->status = $request->status;
                $so->updated_by = $userId;
                $so->save();

                $soItems = [];
                $soItemForStock = [];

                SalesOrderItem::where('so_id', $id)->delete();
                Stock::where('type', '1')->where('form', '2')->where('form_record_id', $id)->delete();

                foreach ($request->product as $key => $product) {

                    $qty = intval($request->quantity[$key]) ?? 0;

                    $soItems[] = [
                        'so_id' => $id,
                        'category_id' => $request->category[$key] ?? '',
                        'product_id' => $product,
                        'price' => floatval($request->price[$key]) ?? 0,
                        'qty' => $qty,
                        'amount' => floatval($request->amount[$key]) ?? 0,
                        'remarks' => $request->remarks[$key] ?? '',
                        'added_by' => $userId,
                    ];

                    $soItemForStock[] = [
                        'product_id' => $product,
                        'type' => 1,
                        'date' => now(),
                        'qty' => $qty,
                        'added_by' => $userId,
                        'form' => 2,
                        'form_record_id' => $id
                    ];
                }

                SalesOrderItem::insert($soItems);
                Stock::insert($soItemForStock);

                DB::commit();
                return redirect()->route('sales-orders.index')->with('success', 'Sales order updated successfully.');
            } else {
                DB::rollBack();
                return redirect()->back()->with('error', 'Select at least a product to add sales order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logger($e->getMessage(), 'error');
            return redirect()->back()->with('error', Helper::$errorMessage);
        }

    }

    public function show(Request $request, $id)
    {
        $moduleName = 'Sales Order';
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $so = SalesOrder::find(decrypt($id));

        return view('so.view', compact('moduleName', 'categories', 'so'));
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $soId = decrypt($id);

            SalesOrder::where('id', $soId)->delete();
            SalesOrderItem::where('so_id', $soId)->delete();
            Stock::where('type', '1')->where('form', '2')->where('form_record_id', $soId)->delete();

            DB::commit();
            return response()->json(['success' => 'Sales order deleted successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
