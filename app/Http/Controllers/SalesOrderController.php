<?php

namespace App\Http\Controllers;

use App\Models\{Category, User, Wallet, Bonus, Setting, AddressLog, Deliver, ChangeOrderUser, AddTaskToOrderTrigger, ManageStatus, DriverWallet};
use App\Models\{ProcurementCost, SalesOrderStatus, SalesOrderItem, SalesOrder, Product, Stock, ChangeOrderStatusTrigger, SalesOrderProofImages};
use App\Models\{AdminWallet, PaymentForDelivery, Transaction};
use App\Helpers\{Helper, Distance};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SalesOrderController extends Controller
{
    protected $moduleName = 'Sales Orders';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            $sellers = User::whereHas('role', fn ($builder) => ($builder->whereIn('roles.id', [2, 6])))->select('name', 'id')->pluck('name', 'id')->toArray();
            $drivers = User::whereHas('role', fn ($builder) => ($builder->where('roles.id', [3])))->selectRaw("CONCAT(name, ' - (', email, ')') as name, users.id, users.lat, users.long")->get()->toArray();
            $statuses = DB::table('sales_order_statuses')->select('name', 'id')->pluck('name', 'id')->toArray();
            $products = Product::select('name', 'id')->pluck('name', 'id')->toArray();

            return view('so.index', compact('moduleName', 'sellers', 'drivers', 'statuses', 'products'));
        }

        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();
        $orderClosedWinStatus = SalesOrderStatus::where('slug', 'closed-win')->first()->id ?? 0;

        $po = SalesOrder::with(['items.product', 'addedby', 'updatedby', 'ostatus'])->where(function ($builder) use ($thisUserRoles) {
            if (!in_array(1, $thisUserRoles)) {
                $builder->where('added_by', auth()->user()->id)
                ->orWhereHas('driver', fn ($innerBuilder) => $innerBuilder->where('user_id', auth()->user()->id)->whereIn('status', [0, 1]))
                ->orWhere('responsible_user', auth()->user()->id);
            }
        });


        if ($request->has('filterSeller') && !empty(trim($request->filterSeller))) {
            $po = $po->where('seller_id', $request->filterSeller);
        }

        if ($request->has('filterStatus') && !empty(trim($request->filterStatus))) {
            $po = $po->where('status', $request->filterStatus);
        }

        if ($request->has('filterFrom') && !empty(trim($request->filterFrom))) {
            $po = $po->where('delivery_date', '>=', date('Y-m-d H:i:s', strtotime($request->filterFrom)));
        }

        if ($request->has('filterTo') && !empty(trim($request->filterTo))) {
            $po = $po->where('delivery_date', '<=', date('Y-m-d H:i:s', strtotime($request->filterTo)));
        }

        if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
            $po = $po->orderBy('id', 'desc');
        }

        if ($request->has('filterDriver') && !empty(trim($request->filterDriver))) {
            $dri = $request->filterDriver;
            $po = $po->whereHas('driver', function ($builder) use ($dri) {
                $builder->where('user_id', $dri)->whereIn('status', [0, 1, 3]);
            });
        }

        if ($request->has('filterProduct') && !empty(trim($request->filterProduct))) {
            $pro = $request->filterProduct;
            $po = $po->whereHas('items', function ($builder) use ($pro) {
                $builder->where('product_id', $pro);
            });
        }

        $orderClosedWinStatus = SalesOrderStatus::where('slug', 'closed-win')->first()->id ?? 0;
        $allStatuses = SalesOrderStatus::custom()->active()->select('id', 'name', 'color')->get();

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

                //if status is 1 and user is admin or user is seller or seller manager who added order can delete order
                if ($users->status == '1' && (User::isAdmin() || (auth()->user()->id == $users->added_by && (User::isSeller() || User::isSellerManager())))) {
                    if (auth()->user()->hasPermission("sales-orders.delete")) {
                        $url = route("sales-orders.delete", encrypt($variable->id));
                        $action .= view('buttons.delete', compact('variable', 'url'));
                    }
                }

                if (User::isAdmin() || (auth()->user()->id == $users->added_by && (User::isSeller() || User::isSellerManager())) || Deliver::where('so_id', $users->id)->where('user_id', auth()->user()->id)->whereIn('status', [0, 1])->exists()) {
                    $action .= '
                        <div class="tableCards d-inline-block pb-0">
                            <div class="editDlbtn">
                                <button style="margin-left:0px!important;" data-bs-toggle="tooltip" title="Order History" class="btn btn-sm btn-success show-order-details" data-oid="' . $users->id . '" data-title="' . $users->order_no . '"> <i class="fa fa-history"></i> </button>
                            </div>
                        </div>
                        ';
                }
                
                $delvieryPartner = Deliver::where('so_id', $users->id)->where('status', 1)->first();

                if ($users->status == $orderClosedWinStatus && !$users->price_matched && isset($delvieryPartner) && $delvieryPartner->user_id == auth()->user()->id) {
                    $action .= '
                    <div class="tableCards d-inline-block me-1 pb-0">
                        <div class="editDlbtn">
                            <button class="btn btn-sm btn-warning close-order" style="width: 30px;margin-left: 2px;" data-bs-toggle="tooltip" title="Final sale price" data-oid="' . $users->id . '" data-title="' . $users->order_no . '"> <i class="fa fa-gbp"> </i> </button>
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
            ->addColumn('note', function ($row) {
                $note = \App\Models\TriggerLog::where('order_id', $row->id)->where('type', 2)->whereNotNull('description')->where('description', '!=', '')->orderBy('id', 'DESC')->first()->description ?? '-';
                $shortNote = Str::of(strip_tags($note))->limit(20);

                if (strlen($note) > 20) {
                    return '<a data-bs-toggle="tooltip" data-bs-placement="top" style="margin-right:10px;" title="' . $note . '"> ' . $shortNote . ' <a>';
                } else {
                    return '<a data-bs-toggle="tooltip" data-bs-placement="right" style="margin-right:10px;"> ' . $shortNote . ' <a>';
                }

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
            ->addColumn('postalcode', function ($row) {
                return '<a target="_blank" href="https://www.google.com/maps/place/' . ($row->customer_postal_code) . '"> ' . $row->customer_postal_code . ' </a>';
            })
            ->addColumn('option', function ($row) use ($allStatuses) {
                $html = "";

                if ($row->status != '1') {

                    $manageSt = ManageStatus::where('status_id', $row->status)->first()->ps ?? [];
                    $allStatuses = SalesOrderStatus::custom()->active()->whereIn('id', $manageSt)->select('id', 'name', 'color')->get();

                    if (User::isAdmin() || (!empty($row->responsible_user) && is_numeric($row->responsible_user) && $row->responsible_user == auth()->user()->id)) {
                        if (count($allStatuses) > 0) {
    
                            $html = 
                            '<div class="status-main button-dropdown position-relative">
                                <label class="status-label" style="background:' . ($row->ostatus->color ?? '') . ';color:' . (Helper::generateTextColor($row->ostatus->color ?? '')) . ';"> ' . ($row->ostatus->name ?? '') . ' </label>
                                <button type="button" class="dropdown-toggle status-opener ms-2 d-inline-flex align-items-center justify-content-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 20 19" fill="none">
                                    <path d="M0.998047 14.613V18.456H4.84105L16.175 7.12403L12.332 3.28103L0.998047 14.613ZM19.147 4.15203C19.242 4.05721 19.3174 3.94458 19.3688 3.82061C19.4202 3.69664 19.4466 3.56374 19.4466 3.42953C19.4466 3.29533 19.4202 3.16243 19.3688 3.03846C19.3174 2.91449 19.242 2.80186 19.147 2.70703L16.747 0.307035C16.6522 0.212063 16.5396 0.136719 16.4156 0.0853128C16.2916 0.0339065 16.1588 0.00744629 16.0245 0.00744629C15.8903 0.00744629 15.7574 0.0339065 15.6335 0.0853128C15.5095 0.136719 15.3969 0.212063 15.302 0.307035L13.428 2.18403L17.271 6.02703L19.147 4.15203Z" fill="#3C3E42"/>
                                    </svg>
                                </button>
                                <div class="dropdown-menu status-modal">
                                    <label class="c-gr f-500 f-14 w-100 mb-2"> STATUS : <span class="text-danger">*</span></label>
                                    <div class="status-dropdown">';
    
                                    foreach ($allStatuses as $k => $status) {
                                        if ($k == 0) {
                                        $html .= '<button type="button" data-sid="' . $status->id . '" data-oid="' . $row->id . '" style="background:' . $status->color . ';color:' . Helper::generateTextColor($status->color) . ';" class="status-dropdown-toggle d-flex align-items-center justify-content-between f-14">
                                            <span>' . $status->name . '</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                                                <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                                            </svg>
                                        </button>';
                                        }
                                    }
        
                                        $html .= '<div class="status-dropdown-menu">';
        
                                        foreach ($allStatuses as $status) {
                                            $html .= '<div class="f-14 cursor-pointer" data-isajax="true" style="background: '. $status->color .';color:' . Helper::generateTextColor($status->color) . ';" data-sid="' . $status->id . '" data-oid="' . $row->id . '" > '. $status->name .' </div>';
                                        }
        
                                        $html .= '</div>
                                    </div>
    
                                    <label class="c-gr f-500 f-14 w-100 mb-2 mt-2"> COMMENT : <span class="text-danger">*</span></label>
                                    <textarea placeholder="Add a comment" class="form-control" style="height:60px;"> </textarea>
                                    <label class="cmnt-er-lbl f-12 d-none text-danger"> Add comment to change status </label>
    
                                    <div class="status-action-btn mt-2 position-relative -z-1">
                                        <button class="status-save-btn btn-primary f-500 f-14 d-inline-block" disabled type="button"> Save </button>
                                        <button class="refresh-dt hide-dropdown btn-default f-500 f-14 d-inline-block ms-1" type="button"> Cancel </button>
                                    </div>
                                </div>
                            </div>';

                        } else {
                            $html = "<strong> " . strtoupper($row->ostatus->name ?? '-') . " </strong>";
                        }
                    } else  {
                        if (count($allStatuses) > 0) {
                            $html .= '<label class="status-lbl trigger-box-label-task-ns" style="background:' . ($row->ostatus->color ?? '#000') . ';color:' . Helper::generateTextColor($row->ostatus->color ?? '#fff') . ';"> ' . ($row->ostatus->name ?? '-') .' </label> ';
                        } else {
                            $html = "<strong> " . strtoupper($row->ostatus->name ?? '-') . " </strong>";
                        }
                    }

                    $lastChangedDate = \App\Models\TriggerLog::where('order_id', $row->id)->where('type', 2)->orderBy('id', 'DESC')->first();
                    if (isset($lastChangedDate->created_at)) {
                        $html .= " <div class='f-12'> Last changed on : <strong> " . date('d-m-Y H:i', strtotime($lastChangedDate->created_at)) . " </strong> </div>" ;
                    } else {
                        $html .= "-";
                    }

                } else {
                    if (Deliver::where('so_id', $row->id)->where('status', 0)->doesntExist() && Deliver::where('so_id', $row->id)->where('status', 2)->exists()) {
                        if (in_array(auth()->user()->roles->first()->id, [1,2,6])) {
                            $isRejected = Deliver::with('user')->where('so_id', $row->id)->where('status', 2)->first();

                            if ($isRejected != null) {
                                if (Deliver::with('user')->where('so_id', $row->id)->whereIn('status', [0,1,3])->doesntExist()) {
    
                                    $deliveryUser = Deliver::where('so_id', $row->id)->whereIn('status', [0,1])->first()->user_id ?? null;
    
                                    $html =  '
                                    <i class="fa fa-warning" aria-hidden="true" style="color: #dd2d20;font-size:16px;"></i>
                                    <strong class="text-danger f-12"> Order was rejected by ' . (isset($isRejected->user->name) ? $isRejected->user->name : 'driver') . ' </strong>
                                    <div class="text-primary cursor-pointer f-12 driver-change-modal-opener" data-deliveryboy="' . $deliveryUser . '" data-oid="' . $row->id . '" data-title="' . $row->order_no . '" > click here to change driver </div>
                                    ';
                                }
                            }
                        } else {
                            $html = "<strong> " . strtoupper($row->ostatus->name ?? '-') . " </strong>";
                        }
                    } else {
                        if (User::isDriver() && Deliver::where('so_id', $row->id)->where('user_id', auth()->user()->id)->where('status', 0)->exists()) {
                            $html .= '<button id="driver-approve-the-order" class="btn-primary f-500 f-14 btn-sm bg-success" data-oid="' . $row->id . '"> ACCEPT </button>
                            <button id="driver-reject-the-order" class="btn-primary f-500 f-14 btn-sm bg-error" data-oid="' . $row->id . '"> REJECT </button>';
                        } else if (User::isSeller() || User::isSellerManager() || User::isAdmin()) {
                            $driver = Deliver::with('user')->where('status', 0)->where('so_id', $row->id);
                            if ($driver->exists()) {
                                $deliveryUser = Deliver::where('so_id', $row->id)->whereIn('status', [0,1])->first()->user_id ?? null;

                                return '<strong> Order assigned to : ' . ($driver->first()->user->name ?? '-') . ' </strong>
                                <div class="text-primary cursor-pointer f-12 driver-change-modal-opener" data-deliveryboy="' . $deliveryUser . '" data-oid="' . $row->id . '" data-title="' . $row->order_no . '" > click here to change driver </div>
                                ';
                            } else {
                                $html = "<strong> " . strtoupper($row->ostatus->name ?? '-') . " </strong>";
                            }

                        } else {
                            $html = "<strong> " . strtoupper($row->ostatus->name ?? '-') . " </strong>";
                        }
                    }
                }

                return $html;

            })
            ->rawColumns(['action', 'postalcode', 'addedby.name', 'updatedby.name', 'option', 'order_no', 'note'])
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
                $latFrom = ['22.3011558', '50.383458', '54.495736', '50.953966', '51.043485'];
                $longFrom = ['70.7602854', '-3.585609', '-2.202220', '-3.755581', '-2.389790'];

                $latFrom = $latFrom[array_rand($latFrom)];
                $longFrom = $longFrom[array_rand($longFrom)];

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
            'address_line_1.required' => 'House number is required.',
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
                if (User::isSeller() || User::isSellerManager()) {
                    $so->seller_id = $userId;
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
                $soItems = [];

                foreach ($request->product as $key => $product) {

                    $qty = intval($request->quantity[$key]) ?? 0;
                    $itemBaseAmt = floatval($request->price[$key]) ?? 0;
                    $itemAmt = floatval($request->amount[$key]) ?? 0;

                    $soItems[] = [
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

                }

                if (count($soItems) > 0) {
                    if (count($salesPriceErrors) > 0) {
                        DB::rollBack();
                        return redirect()->back()->with('error', implode(' <br/> ', $salesPriceErrors));
                    } else {
                        SalesOrderItem::insert($soItems);

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
                $soItems = [];

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

                            $soItems[] = $tempArr;
                        }
                    } else {
                        $soItems[] = $tempArr;
                    }
                }

                if (count($soItems) > 0) {
                    SalesOrderItem::insert($soItems);

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

                $soItems = [];

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

                            $soItems[] = $tempArr;
                        }

                    } else {
                        $soItems[] = $tempArr;
                    }

                }

                if (count($soItems) > 0) {

                    SalesOrderItem::where('so_id', $id)->delete();
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
        $driverDetails = Deliver::with('user')->where('so_id', decrypt($id))->whereIn('status', [0,1,3])->first();

        return view('so.view', compact('moduleName', 'categories', 'so', 'moduleLink', 'driverDetails'));
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

            if (User::isDriver()) {
                $d = $d->where('user_id', auth()->user()->id);
            } else if (User::isAdmin()) {
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
                return '<a target="_blank" href="' . route('sales-orders.view', isset($row?->order?->items?->first()?->order?->id) ? encrypt($row?->order?->items?->first()?->order?->id) : '1') . '"> ' . (isset($row?->order?->items?->first()?->order?->order_no) ? $row?->order?->items?->first()?->order?->order_no : '-') . '</a>';
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
            ->addColumn('added_by', function ($row) {
                return $row?->order?->addedby->name ?? '-';
            })
            ->rawColumns(['distance', 'action', 'order_no'])
            ->addIndexColumn()
            ->make(true);
    }

    public function checkPrice (Request $request) {
        if (!empty($request->order_id) && !empty($request->amount) && is_numeric($request->amount)) {
            $order = SalesOrder::with(['items', 'seller.roles'])->where('id', $request->order_id);
            if ($order->exists()) {
                $order = $order->first();
                if (round($order->items->sum('amount')) == round($request->amount)) {

                    $comPrice = $prodQty = $driverRecevies = 0;

                    if ($order->exists()) {
                        $order = $order->first();
                        $thisProductId = $order->items->first()->product_id ?? 0;
                        $thisDriverId = auth()->user()->id;
        
                        if (empty(Helper::getAvailableStockFromDriver($thisDriverId))) {
                            return response()->json(['status' => false, 'messages' => 'You don\'t have stock for this product.']);
                        }
        
                        DB::beginTransaction();
        
                        try {
            
                            $procurementCost = ProcurementCost::where('role_id', $order->seller->roles->first()->id ?? 2)->where('product_id', $thisProductId);
                            $newTotal = is_numeric($request->amount) ? round($request->amount) : round(floatval($request->amount));
                            $orderTotal = round($order->items->sum('amount'));
                            $prodQty = $order->items->first()->qty ?? 1;
            
                            //driver amount
                            $p4dDriver = PaymentForDelivery::where('driver_id', $thisDriverId);
                            $assignedDriver = Deliver::where('user_id', $thisDriverId)->where('status', 1)->first();
        
                            if ($p4dDriver->exists() && $assignedDriver != null) {
                                $thisRange = sprintf('%.2f', $assignedDriver->range);
                                $p4dDriver = $p4dDriver->where('distance', '>=', $thisRange)->orderBy('distance', 'ASC')->first();
        
                                if ($p4dDriver != null) {
                                    $driverRecevies = $p4dDriver->payment;
                                }
        
                            } else if (PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first() != null) {
                                $driverRecevies = PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first()->payment;
                            }
        
                            $orderAmountAfterDriverAmountDeduction = $newTotal - $driverRecevies;
        
                            DriverWallet::create([
                                'so_id' => $order->id,
                                'driver_id' => $thisDriverId,
                                'amount' => $orderAmountAfterDriverAmountDeduction,
                                'driver_receives' => $driverRecevies
                            ]);
        
                            Transaction::create([
                                'form_id' => 1, //Sales Order
                                'form_record_id' => $order->id,
                                'transaction_id' => Helper::hash(),
                                'user_id' => auth()->user()->id,
                                'ledger_type' => 0,
                                'voucher' => $order->order_no,
                                'amount' => $orderAmountAfterDriverAmountDeduction,
                                'year' => '2024-25',
                                'added_by' => auth()->user()->id
                            ]);
                            //driver amount
        
                            if ($procurementCost->exists()) {
                                $procurementCost = $procurementCost->first();
                                if ($newTotal != $orderTotal) {
            
                                    $newProductTotal = $orderAmountAfterDriverAmountDeduction / $prodQty;
        
                                    if ($newProductTotal > $procurementCost->base_price) {
                                        $comPrice = $newProductTotal - $procurementCost->base_price;
                                    } else {
                                        $comPrice = $procurementCost->default_commission_price;
                                    }
        
                                    Wallet::create([
                                        'seller_id' => $order->seller_id,
                                        'added_by' => $thisDriverId,
                                        'form' => 1,
                                        'form_record_id' => $order->id,
                                        'item_id' => $order->items->first()->id ?? null,
                                        'commission_amount' => $comPrice * $prodQty,
                                        'item_amount' => $newProductTotal,
                                        'commission_actual_amount' => $comPrice,
                                        'item_qty' => $prodQty
                                    ]);
                                }
                            }
        
                            $si = Stock::where('product_id', $thisProductId)->where('type', 1)->whereIn('form', [1, 3])->sum('qty');
                            $so = Stock::where('product_id', $thisProductId)->where('form', 3)->where('type', 0)->where('driver_id', $thisDriverId)->sum('qty');
                            $stotal = ($si - $so) - $prodQty;
        
                            if ($stotal > 0) {
                                Stock::create([
                                    'product_id' => $thisProductId,
                                    'driver_id' => $thisDriverId,
                                    'type' => 1,
                                    'date' => now(),
                                    'qty' => $prodQty,
                                    'added_by' => $thisDriverId,
                                    'form' => 2,
                                    'form_record_id' => $order->id
                                ]);
                            }
        
                            $newestTotal = $orderAmountAfterDriverAmountDeduction;
        
                            if ($newestTotal == 0) {
                                $newestTotalQty = 0;
                            } else {
                                $newestTotalQty = $newestTotal / $prodQty;
                            }
            
                            SalesOrder::where('id', $request->order_id)->update(['price_matched' => 1, 'sold_amount' => $newestTotal, 'driver_amount' => $driverRecevies]);
                            SalesOrderItem::where('so_id', $request->order_id)->update(['sold_item_amount' => $newestTotalQty]);
        
                            DB::commit();
                            return response()->json(['status' => true, 'next' => false]);
        
                        } catch (\Exception $e) {
                            Helper::logger($e->getMessage());
                            DB::rollBack();
                
                            return response()->json(['status' => false, 'next' => false]);
                        }
                    } else {
                        return response()->json(['status' => false, 'next' => false]);
                    }
                    
                } else {
                    return response()->json(['status' => true, 'next' => true]);
                }
            }
        }

        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }

    public function priceUnmatched(Request $request) {

        if (!$request->hasFile('file')) {
            return response()->json(['status' => false, 'messages' => 'Upload atleast a file.']);
        }

        $toBeDeleted = [];
        $comPrice = $prodQty = $driverRecevies = 0;

        if (!file_exists(storage_path('app/public/so-price-change-agreement'))) {
            mkdir(storage_path('app/public/so-price-change-agreement'), 0777, true);
        }

        if (!empty($request->order_id) && !empty($request->amount) && is_numeric($request->amount)) {
            $order = SalesOrder::with(['items', 'seller.roles'])->where('id', $request->order_id);
            if ($order->exists()) {
                $order = $order->first();
                $thisProductId = $order->items->first()->product_id ?? 0;
                $thisDriverId = auth()->user()->id;

                if (empty(Helper::getAvailableStockFromDriver($thisDriverId))) {
                    return response()->json(['status' => false, 'messages' => 'You don\'t have stock for this product.']);
                }

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
    
                    $procurementCost = ProcurementCost::where('role_id', $order->seller->roles->first()->id ?? 2)->where('product_id', $thisProductId);
                    $newTotal = is_numeric($request->amount) ? round($request->amount) : round(floatval($request->amount));
                    $orderTotal = round($order->items->sum('amount'));
                    $prodQty = $order->items->first()->qty ?? 1;
    
                    //driver amount
                    $p4dDriver = PaymentForDelivery::where('driver_id', $thisDriverId);
                    $assignedDriver = Deliver::where('user_id', $thisDriverId)->where('status', 1)->first();

                    if ($p4dDriver->exists() && $assignedDriver != null) {
                        $thisRange = sprintf('%.2f', $assignedDriver->range);
                        $p4dDriver = $p4dDriver->where('distance', '>=', $thisRange)->orderBy('distance', 'ASC')->first();

                        if ($p4dDriver != null) {
                            $driverRecevies = $p4dDriver->payment;
                        }

                    } else if (PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first() != null) {
                        $driverRecevies = PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first()->payment;
                    }

                    $orderAmountAfterDriverAmountDeduction = $newTotal - $driverRecevies;

                    DriverWallet::create([
                        'so_id' => $order->id,
                        'driver_id' => $thisDriverId,
                        'amount' => $orderAmountAfterDriverAmountDeduction,
                        'driver_receives' => $driverRecevies
                    ]);

                    Transaction::create([
                        'form_id' => 1, //Sales Order
                        'form_record_id' => $order->id,
                        'transaction_id' => Helper::hash(),
                        'user_id' => auth()->user()->id,
                        'ledger_type' => 0,
                        'voucher' => $order->order_no,
                        'amount' => $orderAmountAfterDriverAmountDeduction,
                        'year' => '2024-25',
                        'added_by' => auth()->user()->id
                    ]);
                    //driver amount

                    if ($procurementCost->exists()) {
                        $procurementCost = $procurementCost->first();
                        if ($newTotal != $orderTotal) {
    
                            $newProductTotal = $orderAmountAfterDriverAmountDeduction / $prodQty;

                            if ($newProductTotal > $procurementCost->base_price) {
                                $comPrice = $newProductTotal - $procurementCost->base_price;
                            } else {
                                $comPrice = $procurementCost->default_commission_price;
                            }

                            Wallet::create([
                                'seller_id' => $order->seller_id,
                                'added_by' => $thisDriverId,
                                'form' => 1,
                                'form_record_id' => $order->id,
                                'item_id' => $order->items->first()->id ?? null,
                                'commission_amount' => $comPrice * $prodQty,
                                'item_amount' => $newProductTotal,
                                'commission_actual_amount' => $comPrice,
                                'item_qty' => $prodQty
                            ]);
                        }
                    }

                    $si = Stock::where('product_id', $thisProductId)->where('type', 1)->whereIn('form', [1, 3])->sum('qty');
                    $so = Stock::where('product_id', $thisProductId)->where('form', 3)->where('type', 0)->where('driver_id', $thisDriverId)->sum('qty');
                    $stotal = ($si - $so) - $prodQty;

                    if ($stotal > 0) {
                        Stock::create([
                            'product_id' => $thisProductId,
                            'driver_id' => $thisDriverId,
                            'type' => 1,
                            'date' => now(),
                            'qty' => $prodQty,
                            'added_by' => $thisDriverId,
                            'form' => 2,
                            'form_record_id' => $order->id
                        ]);
                    }

                    $newestTotal = $orderAmountAfterDriverAmountDeduction;

                    if ($newestTotal == 0) {
                        $newestTotalQty = 0;
                    } else {
                        $newestTotalQty = $newestTotal / $prodQty;
                    }
    
                    SalesOrder::where('id', $request->order_id)->update(['price_matched' => 1, 'sold_amount' => $newestTotal, 'driver_amount' => $driverRecevies]);
                    SalesOrderItem::where('so_id', $request->order_id)->update(['sold_item_amount' => $newestTotalQty]);

                    DB::commit();
                    return response()->json(['status' => true, 'messages' => 'Sales price changes proof uploaded successfully.']);

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

                    return response()->json(['status' => false, 'messages' => Helper::$errorMessage]);
                }
            }
        }

        return response()->json(['status' => false, 'messages' => Helper::$notFound]);
    }

    public function changeDriver(Request $request) {
        if (!empty($request->driver_id) && !empty($request->order_id)) {

            $thisUser = User::where('id', $request->driver_id)->first();

            if ($thisUser == null) {
                return response()->json(['status' => false, 'message' => 'Driver not found.']);
            }

            if (Deliver::where('so_id', $request->order_id)->whereIn('status', [0, 1])->exists()) {
                $driver = Deliver::where('so_id', $request->order_id)->whereIn('status', [0, 1])->first();
                Deliver::create([
                    'user_id' => $request->driver_id,
                    'so_id' => $request->order_id,
                    'added_by' => auth()->user()->id,
                    'driver_lat' => $driver->driver_lat,
                    'driver_long' => $driver->driver_long,
                    'delivery_location_lat' => $driver->delivery_location_lat,
                    'delivery_location_long' => $driver->delivery_location_long,
                    'range' => Distance::measure($thisUser->lat, $thisUser->long, $driver->delivery_location_lat, $driver->delivery_location_long),
                    'status' => 0
                ]);
    
                Deliver::where('id', $driver->id)->update(['status' => 4]);
                SalesOrder::where('id', $request->order_id)->update(['responsible_user' => $request->driver_id]);

                return response()->json(['status' => true, 'message' => 'Driver added successfully.']);
            } else if (Deliver::where('so_id', $request->order_id)->where('status', 2)->exists()) {
                $driver = Deliver::where('so_id', $request->order_id)->where('status', 2)->first();
                Deliver::create([
                    'user_id' => $request->driver_id,
                    'so_id' => $request->order_id,
                    'added_by' => auth()->user()->id,
                    'driver_lat' => $driver->driver_lat,
                    'driver_long' => $driver->driver_long,
                    'delivery_location_lat' => $driver->delivery_location_lat,
                    'delivery_location_long' => $driver->delivery_location_long,
                    'range' => Distance::measure($thisUser->lat, $thisUser->long, $driver->delivery_location_lat, $driver->delivery_location_long),
                    'status' => 0
                ]);

                Deliver::where('id', $driver->id)->update(['status' => 4]);
                SalesOrder::where('id', $request->order_id)->update(['responsible_user' => $request->driver_id]);

                return response()->json(['status' => true, 'message' => 'Driver added successfully.']);
            }

        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function getRealTimeCommission(Request $request) {
        
        $total = 0;
        $salesPriceSet = ProcurementCost::with('product')->active()->whereIn('role_id', User::getUserRoles())->where('product_id', $request->product);

        if ($salesPriceSet->exists()) {
            $salesPriceSet = $salesPriceSet->first();
            if (floatval($request->price) >= $salesPriceSet->min_sales_price) {

                if (floatval($request->price) > $salesPriceSet->base_price) {
                    $comPrice = floatval($request->price) - $salesPriceSet->base_price;
                } else {
                    $comPrice = $salesPriceSet->default_commission_price;
                }

                $total = $comPrice * $request->qty;
            }
        }


        return response()->json(['com' => $total]);
    }
}
