<?php

namespace App\Http\Controllers;

use App\Models\{ProcurementCost, SalesOrderStatus, SalesOrderItem, SalesOrder, Product, Stock, ChangeOrderStatusTrigger, SalesOrderProofImages};
use App\Models\{Category, User, Wallet, Bonus, DistributionItem, Setting, AddressLog, Deliver, ChangeOrderUser, AddTaskToOrderTrigger};
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

        $users = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 3);
        })->whereNotNull('lat')->whereNotNull('long')
        ->select('id', 'lat', 'long')->get()->toArray();

        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();
        $orderClosedWinStatus = SalesOrderStatus::where('slug', 'closed-win')->first()->id ?? 0;

        $po = SalesOrder::with(['items.product', 'addedby', 'updatedby', 'ostatus'])->where(function ($builder) use ($thisUserRoles, $orderClosedWinStatus) {
            if (!in_array(1, $thisUserRoles)) {
                $builder->where('added_by', auth()->user()->id)
                ->orWhere(function ($innerBuilder) use ($orderClosedWinStatus) {
                    $innerBuilder->where(function ($innerBuilder2) use ($orderClosedWinStatus) {
                        $innerBuilder2->where('status', $orderClosedWinStatus)
                        ->where('price_matched', 0)
                        ->whereHas('driver', fn ($innerBuilder3) => $innerBuilder3
                        ->where('user_id', auth()->user()->id)->where('status', 1));
                    });
                })
                ->orWhereHas('driver', fn ($innerBuilder) => $innerBuilder->where('user_id', auth()->user()->id)->where('status', 0));
            }
        });


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

        $orderClosedWinStatus = SalesOrderStatus::where('slug', 'closed-win')->first()->id ?? 0;

        return dataTables()->eloquent($po)
            ->addColumn('total', function ($product) {
                return Helper::currency($product->total());
            })
            ->addColumn('action', function ($users) use ($orderClosedWinStatus) {

                $variable = $users;

                $action = "";
                $action .= '<div class="whiteSpace">';

                if (auth()->user()->hasPermission("sales-orders.view")) {
                    $url = route("sales-orders.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }
                if ($users->status == '1' && (in_array(1, User::getUserRoles()) || auth()->user()->id == $users->added_by)) {
                    if (auth()->user()->hasPermission("sales-orders.delete")) {
                        $url = route("sales-orders.delete", encrypt($variable->id));
                        $action .= view('buttons.delete', compact('variable', 'url'));
                    }
                }

                $delvieryPartner = Deliver::where('so_id', $users->id)->where('status', 1)->first();

                if ($users->status == $orderClosedWinStatus && !$users->price_matched && isset($delvieryPartner) && $delvieryPartner->user_id == auth()->user()->id) {
                    $action .= '
                    <div class="tableCards d-inline-block me-1 pb-0">
                        <div class="editDlbtn">
                            <button class="btn btn-sm btn-success close-order" data-oid="' . $users->id . '" data-title="' . $users->order_no . '"> FINAL SALES PRICE </button>
                        </div>
                    </div>
                    ';
                }

                $action .= '</div>';

                return $action;
            })
            ->addColumn('product', function ($row) {
                    return $row->items->first()->product->name;
            })
            ->addColumn('quantity', function ($row) {
                    return $row->items->sum('qty');
            })
            ->editColumn('addedby.name', function($user) {
                return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($user->created_at))."'>".($user->addedby->name ?? '-')."</span>";
            })
            ->editColumn('order_no', function($row) {
                return '<a target="_blank" href="' . route('sales-orders.view', encrypt($row->id)) . '"> ' . ($row->order_no) . '</a>';
            })
            ->addColumn('option', function ($row) use ($users) {
                $html = "";

                if ($row->status != '1') {
                    return '<span class="status-lbl f-12" style="background: ' . (($row->ostatus->color ?? '#000')) . ';color:' . (Helper::generateTextColor(($row->ostatus->color ?? '#000'))) . ';text-transform:uppercase;"> ' . ($row->ostatus->name ?? '-') . ' </span>';
                } else {
                    if (in_array(1, User::getUserRoles())) {
                        return '<span class="status-lbl f-12" style="background: ' . (($row->ostatus->color ?? '#000')) . ';color:' . (Helper::generateTextColor(($row->ostatus->color ?? '#000'))) . ';text-transform:uppercase;"> ' . ($row->ostatus->name ?? '-') . ' </span>';
                    } else if (in_array(2, User::getUserRoles())) {
                        $driver = Deliver::with('user')->where('status', 0)->where('so_id', $row->id);
                        if ($driver->exists()) {
                            return "<strong> Order assigned to : " . ($driver->first()->user->name ?? '-') . " </strong>";
                        } else {

                            $html .= '<form id="validateDriver" method="POST" class="" action="'. route('assign-new-driver', encrypt($row->id)) .'">';
                            $html .= csrf_field();

                            $isRejected = Deliver::with('user')->where('so_id', $row->id)->where('status', 2)->first();

                            if ($isRejected != null) {
                                if (Deliver::with('user')->where('so_id', $row->id)->whereIn('status', [0,1,3])->doesntExist()) {
                                    $html .=  '<a data-toggle="tooltip" class="deleteBtn" title=" Order was rejected by ' . (($isRejected->user->name ?? 'Driver')) . '">
                                        <i class="fa fa-warning" aria-hidden="true" style="color: #dd2d20;font-size:16px;"></i>
                                    </a>';
                                }
                            }

                            $html .= '<select class="driver-selection" name="driver"><option value="" selected> --- Select a driver --- </option>';
                            
                            $thisProduct = $row->items->first()->product_id;

                            $users = collect($users)->map(function ($ele) use ($thisProduct) {
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

                            if (!empty($users)) {
                                foreach ($users as $u) {
                                    $thisUser = User::findOrFail($u['id']);
                                    $dist = Distance::measure($thisUser->lat, $thisUser->long, $row->lat, $row->long);
                                    $html .= '<option data-distance="'. $dist .'" value="' . $u['id'] . '"> ' . ($thisUser->name ?? '') . ' - (' . ($thisUser->email ?? '') . ') ' . (number_format($dist, 2)) . ' miles </option>';
                                }
            
                            }

                            $html .= "</select><button type='submit' class='btn-primary btn-sm' style='margin-left:10px;'> ASSIGN </button></form>";
                        }
                    } else if (in_array(3, User::getUserRoles())) {
                        $html .= '<button id="driver-approve-the-order" class="btn-primary f-500 f-14 btn-sm bg-success" data-oid="' . $row->id . '"> ACCEPT </button>
                        <button id="driver-reject-the-order" class="btn-primary f-500 f-14 btn-sm bg-error" data-oid="' . $row->id . '"> REJECT </button>';
                    }
                }

                return $html;

            })
            ->rawColumns(['action', 'addedby.name', 'updatedby.name', 'option', 'order_no'])
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
        $statuses = SalesOrderStatus::custom()->active()->select('id', 'name')->pluck('name', 'id')->toArray();
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
            if (env('GEOLOCATION_API') == 'true') {
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
            } else {
                $latFrom = '22.3011558';
                $longFrom = '70.7602854';

                $errorWhileSavingLatLong = false;
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
                    $enteredPrice = $request->price;

                    return response()->json(['status' => true , 'message' => 'Available', 'html' => view('so.single-product', compact('product', 'minSalesPrice', 'orderNo', 'category', 'longFrom', 'latFrom', 'driverDetail', 'range', 'postalcode', 'addressline', 'enteredPrice'))->render()]);

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

                //seller or seller manager
                if (in_array(2, auth()->user()->roles->pluck('id')->toArray()) || in_array(6, auth()->user()->roles->pluck('id')->toArray())) {
                    $so->seller_id = $userId;
                    $isSeller = $userId;
                }

                $so->customer_facebook = $request->customerfb;
                $so->status = 1;
                $so->added_by = $userId;
                $so->save();

                \App\Models\TriggerLog::create([
                    'trigger_id' => 0,
                    'order_id' => $so->id,
                    'watcher_id' => $userId,
                    'next_status_id' => 1,
                    'current_status_id' => 1,
                    'type' => 2,
                    'time_type' => 1,
                    'main_type' => 2,
                    'hour' => 0,
                    'minute' => 0,
                    'time' => '+0 seconds',
                    'executed_at' => date('Y-m-d H:i:s'),
                    'executed' => 1,
                    'from_status' => null,
                    'to_status' => [
                        'name' => 'NEW',
                        'color' => '#a9ebfc'
                     ]
                ]);


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
                        'amount' => round($itemAmt),
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

                        Deliver::create([
                            'user_id' => $request->driver_id,
                            'so_id' => $soId,
                            'added_by' => auth()->user()->id,
                            'driver_lat' => $request->driver_lat,
                            'driver_long' => $request->driver_long,
                            'delivery_location_lat' => $request->lat,
                            'delivery_location_long' => $request->long,
                            'range' => $request->range
                        ]);

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
                        'amount' => round($itemAmt),
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

        return view('so.view', compact('moduleName', 'categories', 'so', 'moduleLink'));
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
            Deliver::where('so_id', $soId)->delete();
            SalesOrderProofImages::where('so_id', $soId)->delete();
            AddTaskToOrderTrigger::where('order_id', $soId)->delete();
            ChangeOrderUser::where('order_id', $soId)->delete();
            ChangeOrderStatusTrigger::where('order_id', $soId)->delete();

            DB::commit();
            return response()->json(['success' => 'Sales order deleted successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function ordersToBeDeliverd(Request $request) {
        
        $statusToBeShown = [1,2];
        $tempStatus = SalesOrderStatus::whereIn(DB::raw("LOWER(slug)"), ['no-answered-1', 'no-answered-2', 'confirmed-order'])->select('id');

        if ($tempStatus->exists()) {
            $statusToBeShown = [1, 2, ...$tempStatus->pluck('id')->toArray()];
        }

        if (!$request->ajax()) {
            $moduleName = 'Orders to Deliver';
            $drivers = Deliver::with('order')
            ->whereHas('order', fn ($builder) => $builder->whereIn('status', $statusToBeShown))
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->get();

            return view('so.delivery-list', compact('moduleName', 'drivers'));
        }

        $d = Deliver::with('order')
                ->whereHas('order', fn ($builder) => $builder->whereIn('status', $statusToBeShown))
                ->where('status', 1)
                ->orderBy('id', 'DESC');

            if (in_array(3, User::getUserRoles())) {
                $d = $d->where('user_id', auth()->user()->id);
            } else if (in_array(1, User::getUserRoles())) {
                $d = $d->where('id', '>', 0);
            } else {
                $soids = SalesOrder::where('added_by', auth()->user()->id)->select('id')->pluck('id')->toArray();
                $d = $d->whereIn('so_id', $soids);
            }

            if ($request->has('driver') && !empty($request->driver)) {
                $d = $d->where('user_id', $request->driver);                
            }

        return dataTables()->eloquent($d)
            ->addColumn('quantity', function ($row) {
                return $row?->order?->items?->first()?->qty ?? '-';
            })
            ->addColumn('order_no', function ($row) {
                return $row?->order?->items?->first()?->order?->order_no ?? '-';
            })
            ->addColumn('item', function ($row) {
                return $row?->order?->items?->first()?->product?->name ?? '-';
            })
            ->addColumn('distance', function ($row) {
                return '<span title="' . number_format($row->range, 2, '.', "") . ' miles">' . number_format($row->range, 2, '.', "") . ' miles </span>';
            })
            ->addColumn('location', function ($row) {
                return ($row?->order?->customer_address_line_1 ?? '-') . ' ' . ($row?->order?->customer_postal_code ?? '');
            })
            ->rawColumns(['distance', 'action'])
            ->addIndexColumn()
            ->make(true);
    }

    public function checkPrice (Request $request) {
        if (!empty($request->order_id) && !empty($request->amount) && is_numeric($request->amount)) {
            $order = SalesOrder::with('items')->where('id', $request->order_id);
            if ($order->exists()) {
                $order = $order->first();
                if (round($order->items->sum('amount')) == round($request->amount)) {
                    SalesOrder::where('id', $request->order_id)->update(['price_matched' => 1, 'sold_amount' => round($request->amount)]);
                    return response()->json(['status' => true, 'next' => false]);
                } else {
                    return response()->json(['status' => true, 'next' => true]);
                }
            }
        }

        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }

    public function priceUnmatched(Request $request) {

        if (!$request->hasFile('file')) {
            return response()->json(['status' => false, 'message' => 'Upload atleast a file.']);
        }

        $toBeDeleted = [];

        if (!file_exists(storage_path('app/public/so-price-change-agreement'))) {
            mkdir(storage_path('app/public/so-price-change-agreement'), 0777, true);
        }

        if (!empty($request->order_id) && !empty($request->amount) && is_numeric($request->amount)) {
            $order = SalesOrder::with('items')->where('id', $request->order_id);
            if ($order->exists()) {
                $order = $order->first();

                DB::beginTransaction();

                try {

                    if($request->hasFile('file')) {
                        foreach ($request->file('file') as $file) {
                            $name = 'SO-PRICE-PROOF-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->move(storage_path('app/public/so-price-change-agreement'), $name);
    
                            if (file_exists(storage_path("app/public/so-price-change-agreement/{$name}"))) {
                                $toBeDeleted[] = storage_path("app/public/so-price-change-agreement/{$name}");
                                SalesOrderProofImages::create(['so_id' => $order->id,'name' => $name]);
                            }
                        }                    
                    }
    
                    $procurementCost = ProcurementCost::where('role_id', 2)->where('product_id', $order->items->first()->id ?? 0);
                    $newTotal = is_numeric($request->amount) ? round($request->amount) : round(floatval($request->amount));
                    $orderTotal = round($order->items->sum('amount'));
    
                    $prodQty = $order->items->first()->qty ?? 1;
                    $newProductTotal = $newTotal / $prodQty;
    

                    if ($procurementCost->exists()) {
                        $procurementCost = $procurementCost->first();
                        if ($newTotal != $orderTotal) {
    
                            if (Wallet::where('form_record_id', $request->order_id)->where('form', 1)->exists()) {
                                Wallet::where('form_record_id', $request->order_id)->where('form', 1)->delete();
                            }

                            Helper::logger("SINGLE : $newProductTotal AND MIN SALE : $procurementCost->min_sales_price AND BASE : $procurementCost->base_price");
                            
                            if ($newProductTotal > $procurementCost->base_price) {
                                $comPrice = $newProductTotal - $procurementCost->base_price;
                            } else {
                                $comPrice = $procurementCost->default_commission_price;
                            }
                            Helper::logger("COMM:" . ($comPrice * $prodQty));
                            Wallet::create([
                                'seller_id' => $order->seller_id,
                                'added_by' => auth()->user()->id,
                                'form' => 1,
                                'form_record_id' => $order->id,
                                'item_id' => $order->items->first()->id ?? null,
                                'commission_amount' => $comPrice * $prodQty,
                                'item_amount' => $newProductTotal,
                                'commission_actual_amount' => $comPrice,
                                'item_qty' => $prodQty,
                                'created_at' => now()
                            ]);
    
                        }
                    }
    
                    SalesOrder::where('id', $request->order_id)->update(['price_matched' => 1, 'sold_amount' => round($request->amount)]);

                    DB::commit();
                    return response()->json(['status' => true, 'message' => 'Sales price changes proof uploaded successfully.']);

                } catch (\Exception $e) {
                    Helper::logger($e->getMessage());
                    DB::rollBack();

                    if (!empty($toBeDeleted)) {
                        foreach ($toBeDeleted as $eachImage) {
                            if (file_exists($eachImage)) {
                                unlink($eachImage);
                            }
                        }
                    }

                    return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
                }
            }
        }

        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }
}
