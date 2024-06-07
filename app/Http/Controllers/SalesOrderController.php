<?php

namespace App\Http\Controllers;

use App\Models\{ProcurementCost, SalesOrderStatus, SalesOrderItem, SalesOrder, Product, Stock};
use App\Models\{Category, User, Wallet, Bonus, DistributionItem, Setting, AddressLog, Deliver};
use App\Models\{AddTaskToOrderTrigger, ChangeOrderStatusTrigger, Trigger};
use App\Helpers\{Helper, Distance};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    protected $moduleName = 'Sales Orders';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            $sellers = User::whereHas('role', fn ($builder) => ($builder->where('roles.id', '2')))->select('name', 'id')->pluck('name', 'id')->toArray();

            return view('so.index', compact('moduleName', 'sellers'));
        }

        $po = SalesOrder::with(['items', 'addedby', 'updatedby']);
        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();

        if (!in_array(1, $thisUserRoles)) {
            if (in_array(2, $thisUserRoles)) { //seller orders
                $po = $po->where('seller_id', auth()->user()->id);
            } else {
                $po = $po->where('id', '0');
            }
        }

        if ($request->has('filterSeller') && !empty(trim($request->filterSeller))) {
            $po = $po->where('seller_id', $request->filterSeller);
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
                return Helper::currencyFormatter($product->total());
            })
            ->addColumn('action', function ($users) {

                $variable = $users;

                $action = "";
                $action .= '<div class="whiteSpace">';
                // if (auth()->user()->hasPermission("sales-orders.edit")) {
                //     $url = route("sales-orders.edit", encrypt($variable->id));
                //     $action .= view('buttons.edit', compact('variable', 'url'));
                // }
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
            $commissionPrice = ProcurementCost::select('base_price', 'default_commission_price', 'min_sales_price')->active()->whereIn('role_id', User::getUserRoles())->where('product_id', $product->id);
            $pricesAttr = '';

            if ($commissionPrice->exists()) {
                $commissionPrice = $commissionPrice->first();

                $pricesAttr .= ' data-baseprice="' . $commissionPrice->base_price . '" ';
                $pricesAttr .= ' data-minsalesprice="' . $commissionPrice->min_sales_price . '" ';
                $pricesAttr .= ' data-defcomprice="' . $commissionPrice->default_commission_price . '" ';
            }


            $html .= "<option value='{$product->id}' {$pricesAttr} data-price='{$product->price}' data-availablestock='" . (($product->stockin->sum('qty') ?? 0) - ($product->stockout->sum('qty') ?? 0)) . "' > {$product->name} </option>";
        }

        return response()->json($html);
    }

    public function create()
    {
        $moduleName = 'Sales Order';
        $moduleLink = route('sales-orders.index');
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $statuses = SalesOrderStatus::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $orderNo = Helper::generateSalesOrderNumber();

        $items = [];
        $products = Product::with(['stockin', 'stockout'])->active()->selectRaw("id, name, sales_price as price")->get();

        foreach ($products as $product) {
            $commissionPrice = ProcurementCost::select('base_price', 'default_commission_price', 'min_sales_price')->active()->whereIn('role_id', User::getUserRoles())->where('product_id', $product->id);

            $temp = [
                'id' => $product->id,
                'name' => $product->name
            ];

            if ($commissionPrice->exists()) {
                $commissionPrice = $commissionPrice->first();
                $temp['price'] = $commissionPrice->min_sales_price;
            }

            $items[] = $temp;
        }

        return view('so.create-2', compact('moduleName', 'categories', 'orderNo', 'statuses', 'items','moduleLink'));
    }

    public function getAvailableItem(Request $request) {

        $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'product' => 'required',
            'price' => 'required|numeric|min:0',
            'postal_code' => 'required|max:8',
            'address_line_1' => 'required'
        ], [
            'product.required' => 'Select a product.',
            'price.required' => 'Enter price.',
            'price.numeric' => 'Enter valid format.',
            'price.min' => 'Price can\'t be less than 0.',
            'postal_code.required' => 'Postal code is required.',
            'postal_code.max' => 'Maximum 8 characters allowed for postal code.',
            'address_line_1' => 'Address line is required.'
        ]);

        if($validated->fails()){
            return response()->json([
                "status" => false,
                "messages" => $validated->messages()->toArray() ?? []
            ]);
        }

        $errorWhileSavingLatLong = true;
        $latFrom = $longFrom = $toLat = $toLong = $range = '';

        $users = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 3);
        })->whereNotNull('lat')->whereNotNull('long')
        ->select('id', 'lat', 'long')->get()->toArray();

        try {

            $key = trim(Setting::first()?->geocode_key);

            $address = trim("{$request->address_line_1} {$request->postal_code}");
            $address = str_replace(' ', '+', $address);
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$key}";

            $data = json_decode(file_get_contents($url), true);

            if ($data['status'] == "OK") {
                $lat = $data['results'][0]['geometry']['location']['lat'];
                $long = $data['results'][0]['geometry']['location']['lng'];

                if (!empty($lat)) {
                    $latFrom = $lat;
                    $longFrom = $long;

                    $errorWhileSavingLatLong = false;
                }

                AddressLog::create([
                    'postal_code' => $request->postal_code,
                    'address' => $request->address_line_1,
                    'lat' => $lat,
                    'long' => $long,
                    'added_by' => auth()->user()->id,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Please provide accurate address.']);
        }

        $thisProduct = $request->product;
        $neededStock = $request->stock;

        if (is_numeric($neededStock) && (intval($neededStock) > 0)) {
            $neededStock = intval($neededStock);
        } else {
            $neededStock = null;
        }

        $users = collect($users)->map(function ($ele) use ($thisProduct, $neededStock) {
            $inStock = DistributionItem::where('to_driver', $ele['id'])
            ->where('product_id', $thisProduct)
            ->select('qty')
            ->sum('qty');

            $outStock = DistributionItem::where('from_driver', $ele['id'])
            ->where('product_id', $thisProduct)
            ->select('qty')
            ->sum('qty');

            $availStock = intval($inStock) - intval($outStock);

            if ($availStock > 0) {
                return $ele;
            }

        })->filter()->values()->toArray();

        if ($errorWhileSavingLatLong === false) {

            if (!empty($latFrom) && !empty($longFrom)) {

                if (!empty($users)) {
                    $getAllDriversDistance = [];

                    foreach ($users as $row) {
                        $getAllDriversDistance[$row['id']] = Distance::measure($latFrom, $longFrom, $row['lat'], $row['long']);
                    }

                    $getNearbyDriver = array_search(min($getAllDriversDistance), $getAllDriversDistance);
                    $range = min($getAllDriversDistance);

                    $category = Product::where('id', $request->product)->first()->category_id;
                    $category = Category::where('id', $category)->first();
                    $minSalesPrice = Product::msp($request->product);
                    $product = Product::where('id', $request->product)->first();
                    $orderNo = Helper::generateSalesOrderNumber();
                    $driverDetail = User::findOrFail($getNearbyDriver);
                    $postalcode = $request->postal_code;
                    $addressline = $request->address_line_1;

                    return response()->json(['status' => true , 'message' => 'Available', 'html' => view('so.single-product', compact('product', 'minSalesPrice', 'orderNo', 'category', 'longFrom', 'latFrom', 'driverDetail', 'range', 'postalcode', 'addressline'))->render()]);

                } else {
                    return response()->json(['status' => false, 'message' => 'No driver is available nearby to deliver.']);
                }

            } else {
                return response()->json(['status' => false, 'message' => 'Please provide accurate address.']);
            }

        } else {
            return response()->json(['status' => false, 'message' => 'Please provide accurate address.']);
        }
    }

    public function saveSo(Request $request) {
        $this->validate($request, [
            'order_del_date' => 'required',
            'customername' => 'required',
            'customerphone' => 'required',
            'postal_code' => 'required|max:8',
            'address_line_1' => 'required',
            'category.*' => 'required',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0'
        ], [
            'order_del_date.required' => 'Select order delivery date .',
            'customername.required' => 'Enter customer name.',
            'customerphone.required' => 'Enter customer phone number.',
            'postal_code.required' => 'Enter a postal code.',
            'postal_code.max' => 'Maximum 8 characters allowed for postal code.',
            'address_line_1.required' => 'Enter address line 1.',
            'category.*' => 'Select a category.',
            'product.*' => 'Select a product.',
            'quantity.*.required' => 'Enter quantity.',
            'quantity.*.numeric' => 'Enter valid format.',
            'quantity.*.min' => 'Quantity can\'t be less than 1.',
            'price.*.required' => 'Enter Price.',
            'price.*.numeric' => 'Enter valid format.',
            'price.*.min' => 'Price can\'t be less than 0.'
        ]);

        $salesPriceErrors = [];
        $orderNo = Helper::generateSalesOrderNumber();
        $userId = auth()->user()->id;
        $isSeller = null;

        DB::beginTransaction();

        try {

            if (is_array($request->product) && count($request->product) > 0) {

                $so = new SalesOrder();
                $so->order_no = $orderNo;
                $so->date = now();
                $so->delivery_date = date('Y-m-d H:i:s', strtotime($request->order_del_date));
                $so->customer_name = $request->customername;
                $so->customer_address_line_1 = $request->address_line_1;
                $so->customer_phone = $request->customerphone;
                $so->country_dial_code = $request->country_dial_code;
                $so->country_iso_code = $request->country_iso_code;
                $so->customer_postal_code = $request->postal_code;
                $so->lat = $request->lat;
                $so->long = $request->long;

                if (in_array(2, auth()->user()->roles->pluck('id')->toArray())) {
                    $so->seller_id = $userId;
                    $isSeller = $userId;
                }

                $so->customer_facebook = $request->customerfb;
                $so->status = 1;
                $so->added_by = $userId;
                $so->save();

                $soId = $so->id;
                $soItems = $wallet = [];

                foreach ($request->product as $key => $product) {

                    $qty = intval($request->quantity[$key]) ?? 0;
                    $itemBaseAmt = floatval($request->price[$key]) ?? 0;
                    $itemAmt = floatval($request->amount[$key]) ?? 0;

                    $tempArr = [
                        'so_id' => $soId,
                        'category_id' => $request->category[$key] ?? '',
                        'product_id' => $product,
                        'price' => $itemBaseAmt,
                        'qty' => $qty,
                        'amount' => $itemAmt,
                        'remarks' => $request->remarks[$key] ?? '',
                        'added_by' => $userId,
                        'created_at' => now()
                    ];

                    $salesPriceSet = ProcurementCost::with('product')->active()->whereIn('role_id', User::getUserRoles())->where('product_id', $product);

                    if ($salesPriceSet->exists()) {
                        $salesPriceSet = $salesPriceSet->first();
                        if (floatval($itemBaseAmt) < $salesPriceSet->min_sales_price) {
                            $salesPriceErrors[] = isset($salesPriceSet->product->name) ? "{$salesPriceSet->product->name} : Sales price must be atleast {$salesPriceSet->min_sales_price} and you gave {$itemBaseAmt}." : '';
                        } else {

                            if ($itemBaseAmt > $salesPriceSet->base_price) {
                                $comPrice = $itemBaseAmt - $salesPriceSet->base_price;
                            } else {
                                $comPrice = $salesPriceSet->default_commission_price;
                            }

                            $wallet[] = [
                                'seller_id' => $isSeller,
                                'added_by' => $userId,
                                'form' => 1,
                                'form_record_id' => $soId,
                                'item_id' => $product,
                                'commission_amount' => $comPrice * $qty,
                                'item_amount' => $itemBaseAmt,
                                'commission_actual_amount' => $comPrice,
                                'item_qty' => $qty,
                                'created_at' => now()
                            ];

                            $soItems[] = $tempArr;
                        }
                    } else {
                        $soItems[] = $tempArr;
                    }
                }

                if (count($soItems) > 0) {
                    if (count($salesPriceErrors) > 0) {
                        DB::rollBack();
                        return redirect()->back()->with('error', implode(' <br/> ', $salesPriceErrors));
                    } else {
                        SalesOrderItem::insert($soItems);

                        if (count($wallet) > 0) {
                            Wallet::insert($wallet);
                        }

                        $getFirstItemId = SalesOrderItem::where('so_id', $soId)->first();

                        Deliver::create([
                            'user_id' => $request->driver_id,
                            'so_id' => $soId,
                            'soi_id' => $getFirstItemId->id ?? 0,
                            'added_by' => auth()->user()->id,
                            'driver_lat' => $request->driver_lat,
                            'driver_long' => $request->driver_long,
                            'delivery_location_lat' => $request->lat,
                            'delivery_location_long' => $request->long,
                            'range' => $request->range
                        ]);

                        $oldStatus = SalesOrder::where('id', $soId)->select('status')->first()->status;

                        $newStatus = Trigger::where('status_id', 1)->where('type', 2)
                        ->whereIn('action_type', [1, 3])->first()->next_status_id ?? 0;
            
                        /** TASKS **/
                        $currentTime1 = date('Y-m-d H:i:s');
                        $y = [];
            
                        try {
            
                            $triggers = Trigger::where('type', 1)->where('status_id', 1)->whereIn('action_type', [1, 3]);
                            if ($triggers->count() > 0) {
            
                                foreach ($triggers->get() as $t) {
            
                                    $currentTime1 = date('Y-m-d H:i:s', strtotime("{$currentTime1} {$t->time}"));
                                    
                                    $record = AddTaskToOrderTrigger::create([
                                        'order_id' => $soId,
                                        'status_id' => 1,
                                        'added_by' => auth()->user()->id,
                                        'time' => $t->time,
                                        'type' => $t->time_type,
                                        'main_type' => 2,
                                        'description' => $t->task_description,
                                        'current_status_id' => $oldStatus,
                                        'executed_at' => $currentTime1,
                                        'trigger_id' => $t->id
                                    ]);
            
                                    if ($t->time_type == 1) {
                                        $y[] = $record->id;
                                    }
                                }
                            }
            
                            (new \App\Console\Commands\TaskTrigger)->handle($y);
            
                        } catch (\Exception $e) {
                            Helper::logger($e->getMessage());
                        }
            
                        /** TASKS **/
            
            
                        /** Change order status **/
                        $currentTime = date('Y-m-d H:i:s');
                        $x = [];
            
                        try {
            
                            $triggers = Trigger::where('type', 2)->where('status_id', 1)->whereIn('action_type', [1, 3]);
                            if ($triggers->count() > 0) {
                                foreach ($triggers->get() as $t) {
            
                                    $currentTime = date('Y-m-d H:i:s', strtotime("{$currentTime} {$t->time}"));
            
                                    $record = ChangeOrderStatusTrigger::create([
                                        'order_id' => $soId,
                                        'status_id' => $newStatus,
                                        'added_by' => auth()->user()->id,
                                        'time' => $t->time,
                                        'type' => $t->time_type,
                                        'current_status_id' => 1,
                                        'executed_at' => $currentTime,
                                        'trigger_id' => $t->id
                                    ]);
                                    
                                    if ($t->time_type == 1) {
                                        $x[] = $record->id;
                                    }
                                }
                            }
            
                            (new \App\Console\Commands\StatusTrigger)->handle($x);
            
                        } catch (\Exception $e) {
                            Helper::logger($e->getMessage());
                        }
                        /** Change order status **/

                        DB::commit();
                        return redirect()->route('sales-orders.index')->with('success', "Sales order added successfully.");
                    }
                } else {
                    DB::rollBack();

                    if (count($salesPriceErrors) > 0) {
                        return redirect()->back()->with('error', implode(' <br/> ', $salesPriceErrors));
                    }

                    return redirect()->back()->with('error', Helper::$errorMessage);
                }

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

    public function store(Request $request)
    {
        $this->validate($request, [
            'order_del_date' => 'required',
            'customername' => 'required',
            'customerphone' => 'required',
            'postal_code' => 'required|max:8',
            'address_line_1' => 'required',
            'category.*' => 'required',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0'
        ], [
            'order_del_date.required' => 'Select order delivery date .',
            'customername.required' => 'Enter customer name.',
            'customerphone.required' => 'Enter customer phone number.',
            'postal_code.required' => 'Enter a postal code.',
            'postal_code.max' => 'Maximum 8 characters allowed for postal code.',
            'address_line_1.required' => 'Enter address line 1.',
            'category.*' => 'Select a category.',
            'product.*' => 'Select a product.',
            'quantity.*.required' => 'Enter quantity.',
            'quantity.*.numeric' => 'Enter valid format.',
            'quantity.*.min' => 'Quantity can\'t be less than 1.',
            'price.*.required' => 'Enter Price.',
            'price.*.numeric' => 'Enter valid format.',
            'price.*.min' => 'Price can\'t be less than 0.'
        ]);

        $salesPriceErrors = [];
        $orderNo = Helper::generateSalesOrderNumber();
        $userId = auth()->user()->id;
        $isSeller = null;

        DB::beginTransaction();

        try {

            if (is_array($request->product) && count($request->product) > 0) {

                $so = new SalesOrder();
                $so->order_no = $orderNo;
                $so->date = now();
                $so->delivery_date = date('Y-m-d H:i:s', strtotime($request->order_del_date));
                $so->customer_name = $request->customername;
                $so->customer_address_line_1 = $request->address_line_1;
                $so->customer_phone = $request->customerphone;
                $so->country_dial_code = $request->country_dial_code;
                $so->country_iso_code = $request->country_iso_code;
                $so->customer_postal_code = $request->postal_code;

                if (in_array(2, auth()->user()->roles->pluck('id')->toArray())) {
                    $so->seller_id = $userId;
                    $isSeller = $userId;
                }

                $so->customer_facebook = $request->customerfb;
                $so->status = 1;
                $so->added_by = $userId;
                $so->save();

                $soId = $so->id;
                $soItems = $wallet = [];

                foreach ($request->product as $key => $product) {

                    $qty = intval($request->quantity[$key]) ?? 0;
                    $itemBaseAmt = floatval($request->price[$key]) ?? 0;
                    $itemAmt = floatval($request->amount[$key]) ?? 0;

                    $tempArr = [
                        'so_id' => $soId,
                        'category_id' => $request->category[$key] ?? '',
                        'product_id' => $product,
                        'price' => $itemBaseAmt,
                        'qty' => $qty,
                        'amount' => $itemAmt,
                        'remarks' => $request->remarks[$key] ?? '',
                        'added_by' => $userId,
                        'created_at' => now()
                    ];

                    $salesPriceSet = ProcurementCost::with('product')->active()->whereIn('role_id', User::getUserRoles())->where('product_id', $product);

                    if ($salesPriceSet->exists()) {
                        $salesPriceSet = $salesPriceSet->first();
                        if (floatval($itemBaseAmt) < $salesPriceSet->min_sales_price) {
                            $salesPriceErrors[] = isset($salesPriceSet->product->name) ? "{$salesPriceSet->product->name} : Sales price must be atleast {$salesPriceSet->min_sales_price} and you gave {$itemBaseAmt}." : '';
                        } else {

                            if ($itemBaseAmt > $salesPriceSet->base_price) {
                                $comPrice = $itemBaseAmt - $salesPriceSet->base_price;
                            } else {
                                $comPrice = $salesPriceSet->default_commission_price;
                            }

                            $wallet[] = [
                                'seller_id' => $isSeller,
                                'added_by' => $userId,
                                'form' => 1,
                                'form_record_id' => $soId,
                                'item_id' => $product,
                                'commission_amount' => $comPrice * $qty,
                                'item_amount' => $itemBaseAmt,
                                'commission_actual_amount' => $comPrice,
                                'item_qty' => $qty,
                                'created_at' => now()
                            ];

                            $soItems[] = $tempArr;
                        }
                    } else {
                        $soItems[] = $tempArr;
                    }
                }

                if (count($soItems) > 0) {
                    SalesOrderItem::insert($soItems);

                    if (count($wallet) > 0) {
                        Wallet::insert($wallet);
                    }

                    DB::commit();

                    if (count($salesPriceErrors) > 0) {
                        return redirect()->route('sales-orders.edit', encrypt($soId))->with('error', implode(' <br/> ', $salesPriceErrors));
                    }

                    return redirect()->route('sales-orders.index')->with('success', "Sales order added successfully.");
                } else {
                    DB::rollBack();

                    if (count($salesPriceErrors) > 0) {
                        return redirect()->back()->with('error', implode(' <br/> ', $salesPriceErrors));
                    }

                    return redirect()->back()->with('error', Helper::$errorMessage);
                }

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
        $moduleLink = route('sales-orders.index');
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $so = SalesOrder::find(decrypt($id));

        $htmlAttributes = [];

        foreach ($so->items as $item) {
            $commissionPrice = ProcurementCost::select('base_price', 'default_commission_price', 'min_sales_price')->active()->whereIn('role_id', User::getUserRoles())->where('product_id', $item->product_id);

            if ($commissionPrice->exists()) {
                $commissionPrice = $commissionPrice->first();

                $htmlAttributes[$item->product_id] = [
                    'baseprice' => $commissionPrice->base_price,
                    'minsalesprice' => $commissionPrice->min_sales_price,
                    'defcomprice' => $commissionPrice->default_commission_price
                ];
            }
        }

        return view('so.edit', compact('moduleName', 'categories', 'id', 'so', 'htmlAttributes','moduleLink'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'order_del_date' => 'required',
            'customername' => 'required',
            'customerphone' => 'required',
            'postal_code' => 'required|max:8',
            'address_line_1' => 'required',
            'category.*' => 'required',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0'
        ], [
            'order_del_date.required' => 'Select order delivery date .',
            'customername.required' => 'Enter customer name.',
            'customerphone.required' => 'Enter customer phone number.',
            'postal_code.required' => 'Enter a postal code.',
            'postal_code.max' => 'Maximum 8 characters allowed for postal code.',
            'address_line_1.required' => 'Enter address line 1.',
            'category.*' => 'Select a category.',
            'product.*' => 'Select a product.',
            'quantity.*.required' => 'Enter quantity.',
            'quantity.*.numeric' => 'Enter valid format.',
            'quantity.*.min' => 'Quantity can\'t be less than 1.',
            'price.*.required' => 'Enter Price.',
            'price.*.numeric' => 'Enter valid format.',
            'price.*.min' => 'Price can\'t be less than 0.'
        ]);

        $salesPriceErrors = [];
        $userId = auth()->user()->id;
        $id = decrypt($id);
        $isSeller = null;

        if (in_array(2, auth()->user()->roles->pluck('id')->toArray())) {
            $isSeller = $userId;
        }

        DB::beginTransaction();

        try {

            if (is_array($request->product) && count($request->product) > 0) {

                $so = SalesOrder::find($id);
                $so->date = now();
                $so->delivery_date = date('Y-m-d H:i:s', strtotime($request->order_del_date));
                $so->customer_name = $request->customername;
                $so->customer_address_line_1 = $request->address_line_1;
                $so->customer_phone = $request->customerphone;
                $so->country_dial_code = $request->country_dial_code;
                $so->country_iso_code = $request->country_iso_code;
                $so->customer_postal_code = $request->postal_code;
                $so->customer_facebook = $request->customerfb;
                $so->updated_by = $userId;
                $so->save();

                $soItems = $wallet = [];

                foreach ($request->product as $key => $product) {

                    $qty = intval($request->quantity[$key]) ?? 0;
                    $itemBaseAmt = floatval($request->price[$key]) ?? 0;
                    $itemAmt = floatval($request->amount[$key]) ?? 0;

                    $tempArr = [
                        'so_id' => $id,
                        'category_id' => $request->category[$key] ?? '',
                        'product_id' => $product,
                        'price' => $itemBaseAmt,
                        'qty' => $qty,
                        'amount' => $itemAmt,
                        'remarks' => $request->remarks[$key] ?? '',
                        'added_by' => $userId,
                        'created_at' => now()
                    ];

                    $salesPriceSet = ProcurementCost::with('product')->active()->whereIn('role_id', User::getUserRoles())->where('product_id', $product);

                    if ($salesPriceSet->exists()) {
                        $salesPriceSet = $salesPriceSet->first();
                        if (floatval($itemBaseAmt) < $salesPriceSet->min_sales_price) {
                            $salesPriceErrors[] = isset($salesPriceSet->product->name) ? "{$salesPriceSet->product->name} : Sales price must be atleast {$salesPriceSet->min_sales_price} and you gave {$itemBaseAmt}." : '';
                        } else {

                            if ($itemBaseAmt > $salesPriceSet->base_price) {
                                $comPrice = $itemBaseAmt - $salesPriceSet->base_price;
                            } else {
                                $comPrice = $salesPriceSet->default_commission_price;
                            }

                            $wallet[] = [
                                'seller_id' => $isSeller,
                                'added_by' => $userId,
                                'form' => 1,
                                'form_record_id' => $id,
                                'item_id' => $product,
                                'commission_amount' => $comPrice * $qty,
                                'item_amount' => $itemBaseAmt,
                                'commission_actual_amount' => $comPrice,
                                'item_qty' => $qty,
                                'created_at' => now()
                            ];

                            $soItems[] = $tempArr;
                        }

                    } else {
                        $soItems[] = $tempArr;
                    }

                }

                if (count($soItems) > 0) {

                    SalesOrderItem::where('so_id', $id)->delete();
                    Wallet::where('form', 1)->where('form_record_id', $id)->delete();

                    if (count($wallet) > 0) {
                        Wallet::insert($wallet);
                    }

                    SalesOrderItem::insert($soItems);

                    DB::commit();
                    if (count($salesPriceErrors) > 0) {
                        return redirect()->route('sales-orders.edit', encrypt($id))->with('error', implode(' <br/> ', $salesPriceErrors));
                    }

                    return redirect()->route('sales-orders.index')->with('success', 'Sales order updated successfully.');

                } else {
                    DB::rollBack();

                    if (count($salesPriceErrors) > 0) {
                        return redirect()->back()->with('error', implode(' <br/> ', $salesPriceErrors));
                    }

                    return redirect()->back()->with('error', Helper::$errorMessage);
                }

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
        $moduleLink = route('sales-orders.index');
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $so = SalesOrder::find(decrypt($id));
        $driver = isset($so->items->first()->driver->user->name) ? ($so->items->first()->driver->user->name . ' - (' . $so->items->first()->driver->user->email . ')') : '-';

        return view('so.view', compact('moduleName', 'categories', 'so', 'driver','moduleLink'));
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $soId = decrypt($id);

            SalesOrder::where('id', $soId)->delete();
            SalesOrderItem::where('so_id', $soId)->delete();
            Stock::where('type', '1')->where('form', '2')->where('form_record_id', $soId)->delete();
            Wallet::where('form', 1)->where('form_record_id', $soId)->delete();
            Bonus::where('form', 1)->where('form_record_id', $soId)->delete();

            DB::commit();
            return response()->json(['success' => 'Sales order deleted successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function ordersToBeDeliverd(Request $request) {
        if (!in_array('3', User::getUserRoles())) {
            abort(403);
        }

        if (!$request->ajax()) {
            $moduleName = 'Orders to Deliver';

            return view('so.delivery-list', compact('moduleName'));
        }

        $d = Deliver::with('item.order');
        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();

        if (!in_array('1', $thisUserRoles)) {
            if (in_array('3', $thisUserRoles)) {
                $d = $d->where('user_id', auth()->user()->id);
            } else {
                $d = $d->where('id', '0');
            }
        }

        return dataTables()->eloquent($d)
            ->addColumn('quantity', function ($row) {
                return $row?->item?->qty ?? '-';
            })
            ->addColumn('order_no', function ($row) {
                return $row?->item?->order?->order_no ?? '-';
            })
            ->addColumn('item', function ($row) {
                return $row?->item?->product?->name ?? '-';
            })
            ->addColumn('distance', function ($row) {
                if ($row->range < 1) {
                    return '<span title="' . number_format($row->range, 2) . ' meter">' . number_format($row->range, 2) . ' m </span>';
                } else {
                    return '<span title="' . number_format($row->range, 2) . ' kilometer">' . number_format($row->range, 2) . ' km </span>';
                }
            })
            ->addColumn('location', function ($row) {
                return ($row?->item?->order?->customer_address_line_1 ?? '-') . ' ' . ($row?->item?->order?->customer_postal_code ?? '');
            })
            ->rawColumns(['distance'])
            ->addIndexColumn()
            ->make(true);
    }
}
