<?php

namespace App\Http\Controllers;

use App\Models\{Category, User, Wallet, Bonus, Setting, AddressLog, Deliver, ChangeOrderUser, AddTaskToOrderTrigger, ManageStatus, DriverWallet};
use App\Models\{ProcurementCost, SalesOrderStatus, SalesOrderItem, SalesOrder, Product, Stock, ChangeOrderStatusTrigger, SalesOrderProofImages};
use App\Models\{PaymentForDelivery, Transaction, TriggerLog, SalesOrderUserFilter, Notification};
use App\Helpers\{Helper, Distance};
use App\Models\ScammerContact;
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
            $filterSelectedData = (isset(SalesOrderUserFilter::where('user_id',auth()->user()->id)->first()?->filters)) ? json_decode(SalesOrderUserFilter::where('user_id',auth()->user()->id)->first()?->filters) : [];

            $sellers = User::whereHas('role', fn ($builder) => ($builder->whereIn('roles.id', [2, 6])))->selectRaw("CONCAT(name, ' - (', email, ')') as name, users.id as id")->pluck('name', 'id')->toArray();
            $drivers = User::whereHas('role', fn ($builder) => ($builder->where('roles.id', [3])))->selectRaw("CONCAT(name, ' - (', email, ')') as name, users.id, users.lat, users.long")->get()->toArray();
            $statuses = SalesOrderStatus::select('name', 'id')->pluck('name', 'id')->toArray();
            $products = Product::select('name', 'id')->pluck('name', 'id')->toArray();

            return view('so.index', compact('moduleName', 'sellers', 'drivers', 'statuses', 'products','filterSelectedData'));
        }

        $filterData['filterSeller'] = $request->filterSeller ?? null;
        $filterData['filterProduct'] = $request->filterProduct ?? null;
        $filterData['filterDriver'] = $request->filterDriver ?? null;
        $filterData['filterStatus'] = isset($request->filterStatus) ? str_replace(',','-',$request->filterStatus): null;
        $filterData['filterFrom'] = $request->filterFrom ?? null;
        $filterData['filterTo'] = $request->filterTo ?? null;

        SalesOrderUserFilter::updateOrCreate(['user_id'=>auth()->user()->id],['filters'=>json_encode($filterData)]);
        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();
        $orderClosedWinStatus = SalesOrderStatus::where('slug', 'closed-win')->first()->id ?? 0;

        $po = SalesOrder::with(['items.product', 'addedby', 'updatedby', 'ostatus', 'assigneddriver'])->where(function ($builder) use ($thisUserRoles) {
            if (!in_array(1, $thisUserRoles)) {
                $builder->where('added_by', auth()->user()->id)->orWhereIn('added_by',User::where('added_by',auth()->user()->id)->pluck('id')->toArray())
                ->orWhereHas('driver', fn ($innerBuilder) => $innerBuilder->where('user_id', auth()->user()->id)->whereIn('status', [0, 1]))
                ->orWhere('responsible_user', auth()->user()->id);
            }
        });
        if(in_array(auth()->user()->roles->first()->id, [2,3])) {
           $po->whereNotIn('status',[9]);
        }
        if(in_array(auth()->user()->roles->first()->id, [3])) {

            $po->where(function ($query) {
                $query->whereNull('responsible_user')->orWhereHas('responsible', function($q1)
                {
                    $q1->where('id','!=', User::whereHas('role', function ($builder) {
                        $builder->where('roles.id', 2);
                    })->get()->pluck('id')->toArray());
                });

            });

        }
        if ($request->has('filterSeller') && !empty(trim($request->filterSeller))) {
            $po = $po->where('seller_id', $request->filterSeller);
        }

        if ($request->has('filterStatus') && !empty(trim($request->filterStatus))) {
            $statusfilter = explode(',',$request->filterStatus);
            $po = $po->whereIn('status',$statusfilter);
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
        $allStatuses = SalesOrderStatus::active()->select('id', 'name', 'color')->get();

        return dataTables()->eloquent($po)
            ->addColumn('total', fn ($row) => $row->price_matched ? ('£' . ($row->sold_amount + $row->driver_amount)) : ('£' . ($row->total())))
            ->addColumn('action', function ($users) use ($orderClosedWinStatus) {

                $variable = $users;

                $action = "";
                $action .= '<div class="d-flex align-items-center justify-content-center">';

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

                $action .= '</div>';

                return $action;
            })
            ->addColumn('product', function ($row) {
                    return $row->items->first()->product->name;
            })
            ->addColumn('note', function ($row) {
                $note = TriggerLog::where('order_id', $row->id)->where('type', 2)->whereNotNull('description')->where('description', '!=', '')->orderBy('id', 'DESC')->first()->description ?? '-';
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

                $cwStatus = SalesOrderStatus::select('id')->where('slug', 'closed-win')->first()->id ?? 0;

                if ($row->status != '1') {

                    $manageSt = ManageStatus::where('status_id', $row->status)->first()->ps ?? [];
                    $allStatuses = SalesOrderStatus::active()->whereIn('id', $manageSt)->select('id', 'name', 'color')->get();

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
                                            $html .= '<div class="f-14 cursor-pointer" data-cwstatus="' . $cwStatus . '" data-onumber="' . $row->order_no . '" data-isajax="true" style="background: '. $status->color .';color:' . Helper::generateTextColor($status->color) . ';" data-sid="' . $status->id . '" data-oid="' . $row->id . '" > '. $status->name .' </div>';
                                        }

                                        $html .= '</div>
                                    </div>

                                    <label class="c-gr f-500 f-14 w-100 mb-2 mt-2"> COMMENT : <span class="text-danger">*</span></label>
                                    <textarea id="cs-txtar" placeholder="Add a comment" class="form-control" style="height:60px;"> </textarea>
                                    <label class="cmnt-er-lbl f-12 d-none text-danger"> Add comment to change status </label>

                                    <div class="form-group closedwin-statusupdate">
                                        <label class="c-gr f-500 f-14 w-100 mb-2 mt-2"> FINAL SALES PRICE : <span class="text-danger">*</span></label>
                                        <input type="text" id="cs-fsp" class="form-control" />
                                        <label class="fsp-er-lbl f-12 d-none text-danger"> </label>

                                        <label class="c-gr f-500 f-14 w-100 mb-2 mt-2"> PRICE CHANGE PROOF : </label>
                                        <input type="file" multiple id="cs-pcp" class="form-control" />
                                        <label class="pcp-er-lbl f-12 d-none text-danger"> </label>
                                    </div>

                                    <div class="status-action-btn mt-2 position-relative -z-1">
                                        <button data-cwstatus="' . $cwStatus . '" class="status-save-btn btn-primary f-500 f-14 d-inline-block" disabled type="button"> Save </button>
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

                    $lastChangedDate = TriggerLog::where('order_id', $row->id)->where('type', 2)->orderBy('id', 'DESC')->first();
                    if (isset($lastChangedDate->created_at)) {
                        $html .= " <div class='f-12'> Last changed on : <strong> " . date('d-m-Y H:i', strtotime($lastChangedDate->created_at)) . " </strong> </div>" ;
                    } else {
                        $html .= "-";
                    }

                } else {
                    $totalDeliver = Deliver::where('so_id', $row->id)->count();
                    $rejectedDriver = Deliver::where('so_id', $row->id)->where('status', 2)->count();

                    if (Deliver::where('so_id', $row->id)->where('status', 0)->doesntExist() && $rejectedDriver == $totalDeliver) {
                        if (in_array(auth()->user()->roles->first()->id, [1,2,6])) {
                            $isRejected = Deliver::with('user')->where('so_id', $row->id)->where('status', 2)->get();

                            if ($isRejected != null) {
                                if (Deliver::with('user')->where('so_id', $row->id)->whereIn('status', [0,1,3])->doesntExist()) {
                                    $alldriver = Deliver::with('user')->where('so_id', $row->id)->get()->pluck('user.name')->toArray();
                                    $alldriverName = '';
                                    if(!empty($alldriver)) {
                                        $alldriverName = implode(',',$alldriver);
                                    }
                                    $deliveryUser = Deliver::where('so_id', $row->id)->whereIn('status', [0,1])->first()->user_id ?? null;
                                    //. (isset($isRejected->user->name) ? $isRejected->user->name : 'driver') .

                                    if (empty($alldriverName)) {
                                        $html =  '
                                        <strong>  Order Placed  </strong>
                                        <div class="text-primary cursor-pointer f-12 driver-change-modal-opener" data-deliveryboy="' . $deliveryUser . '" data-oid="' . $row->id . '" data-title="' . $row->order_no . '" > click here to change driver </div>
                                        ';
                                    } else {
                                        $html =  '
                                        <i class="fa fa-warning" aria-hidden="true" style="color: #dd2d20;font-size:16px;"></i>
                                        <strong class="text-danger f-12"> Order was rejected by <span title="'.$alldriverName.'" class="drivertitle"> All drivers</span> </strong>
                                        <div class="text-primary cursor-pointer f-12 driver-change-modal-opener" data-deliveryboy="' . $deliveryUser . '" data-oid="' . $row->id . '" data-title="' . $row->order_no . '" > click here to change driver </div>
                                        ';
                                    }
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
                                // $deliveryUser = Deliver::where('so_id', $row->id)->whereIn('status', [0,1])->first()->user_id ?? null;

                                return '<strong> Order Placed </strong><div class="f-12">Waiting for driver response</div>';
                                // <div class="text-primary cursor-pointer f-12 driver-change-modal-opener" data-deliveryboy="' . $deliveryUser . '" data-oid="' . $row->id . '" data-title="' . $row->order_no . '" > click here to change driver </div>
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
            ->addColumn('allocated_to', function ($row) {

                $assigneOrderdriver = Deliver::query()
                ->with(['user' => function ($query) {
                    $query->selectRaw("id,CONCAT(name, ' - ', city_id) AS driverinfo")->withTrashed();
                }]);
                if(Deliver::where('so_id', $row->id)->where('status',1)->count() > 0){
                    $assigneOrderdriver->where('status',1);
                }
                $assigneOrderdriver = $assigneOrderdriver->where('so_id', $row->id)
                ->get()->pluck('user.driverinfo')->toArray();

                if ($row->status == 11) {
                    return '-';
                }

                return (!empty($assigneOrderdriver) ? implode(', ',$assigneOrderdriver) : '<i class="fa fa-warning" aria-hidden="true" style="color: #dd2d20;font-size:16px;"></i><strong class="text-danger f-12"> No driver found nearby when order was placed </strong>');
            })
            ->editColumn('assigneddriver.range', function($user) {
                if(isset($user->assigneddriver->range) && $user->assigneddriver->range !="") {
                    return '<span title="'.number_format($user->assigneddriver->range,2,'.','').' miles">'.number_format($user->assigneddriver->range,2,'.','').'</span>';
                } else {
                    return '-';
                }
            })
            ->rawColumns(['action', 'postalcode', 'addedby.name', 'updatedby.name', 'option', 'order_no', 'note','assigneddriver.range', 'allocated_to'])
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
        ->active()
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

        if ($errorWhileSavingLatLong === false) {

            if (!empty($latFrom) && !empty($longFrom)) {

                if (!empty($users)) {
                    $getAllDriversDistance = [];

                    foreach ($users as $row) {
                        $getAllDriversDistance[$row['id']] = Distance::measure($latFrom, $longFrom, $row['lat'], $row['long']);
                    }


                    asort($getAllDriversDistance);

                    $result = self::getDriver($getAllDriversDistance);

                    if ($result['exists']) {
                        $getNearbyDriver = $result['drivers'];
                        $driverids = array_keys($getNearbyDriver);
                    } else {
                        $isNotAvail = true;
                        $successdrivers = [];
                        foreach ($getAllDriversDistance as $tmpDriver => $tmpRange) {
                            if (PaymentForDelivery::where(fn ($b) => $b->whereNull('driver_id')->orWhere('driver_id', ''))->where('distance', '>=', $tmpRange)->exists()) {
                                $successdrivers[$tmpDriver] = $tmpRange;
                                // $getNearbyDriver = $tmpDriver;
                                // $range = $tmpRange;
                                $isNotAvail = false;
                                // break;
                            }
                        }
                        if(!empty($successdrivers)) {
                            $getNearbyDriver = $successdrivers;
                            $driverids = array_keys($getNearbyDriver);
                            $isNotAvail = false;
                        }
                        if ($isNotAvail) {
                            return response()->json(['status' => false, 'message' => 'We cannot accept your order because delivery location falls outside from the driver\'s delivery zone.']);
                        }
                    }

                    $category = Product::where('id', $request->product)->first()->category_id;
                    $category = Category::where('id', $category)->first();
                    $minSalesPrice = Product::msp($request->product);
                    $product = Product::where('id', $request->product)->first();
                    $orderNo = Helper::generateSalesOrderNumber();
                    $driverDetail = User::active()->whereIn('id',$driverids)->get();
                    $postalcode = $request->postal_code;
                    $addressline = $request->address_line_1;
                    $enteredPrice = $request->price;

                    return response()->json(['status' => true , 'message' => 'Available', 'html' => view('so.single-product', compact('product', 'minSalesPrice', 'orderNo', 'category', 'longFrom', 'latFrom', 'driverDetail', 'getNearbyDriver', 'postalcode', 'addressline', 'enteredPrice'))->render()]);

                } else {
                    return response()->json(['status' => false, 'message' => 'We cannot accept your order because delivery location falls outside from the driver\'s delivery zone.']);
                }

            } else {
                return response()->json(['status' => false, 'message' => 'Please provide accurate address.']);
            }

        } else {
            return response()->json(['status' => false, 'message' => 'Please provide accurate address.']);
        }
    }

    private static function getDriver($drivers) {
        if (empty($drivers)) {
            return ['exists' => false];
        } else {
            $successDrivers = [];
            foreach($drivers as $driverid=>$driverdetials) {
                $paymentForDelivery = PaymentForDelivery::where('driver_id', $driverid)->where('distance', '>=', $driverdetials);
                if($paymentForDelivery->exists()) {
                    $successDrivers[$driverid] = $driverdetials;
                }
            }
        }
        if(!empty($successDrivers)) {
            return ['exists' => true, 'drivers' => $successDrivers];
        } else {
            return ['exists' => false];
        }
        // $nearbyDriverId = array_search(min($drivers), $drivers);
        // $range = 0;

        // if (isset($drivers[$nearbyDriverId])) {
        //     $range = $drivers[$nearbyDriverId];
        //     unset($drivers[$nearbyDriverId]);
        // }

        // $paymentForDelivery = PaymentForDelivery::where('driver_id', $nearbyDriverId)->where('distance', '>=', $range);

        // if ($paymentForDelivery->exists()) {

        //     return ['exists' => true, 'driver' => $nearbyDriverId, 'range' => $range];
        // } else {
        //     return self::getDriver($drivers);
        // }
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

                TriggerLog::create([
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
                        $driverids = [];
                        if($request->range !="") {
                            $driverrangeData = json_decode($request->range);
                            if(!empty($driverrangeData)) {
                                $driverphonenumber = [];
                                foreach($driverrangeData as $driverid=>$range) {
                                    $driverDetail = User::active()->find($driverid);
                                    if(!empty($driverDetail)) {
                                        $driverids[] = $driverid;
                                        Deliver::create([
                                            'user_id' => $driverid,
                                            'so_id' => $soId,
                                            'added_by' => auth()->user()->id,
                                            'driver_lat' => $driverDetail->lat,
                                            'driver_long' => $driverDetail->long,
                                            'delivery_location_lat' => $request->lat,
                                            'delivery_location_long' => $request->long,
                                            'range' => $range
                                        ]);

                                        Notification::create([
                                            'user_id' => $driverid,
                                            'so_id' => $soId,
                                            'title' => 'New Order',
                                            'description' => 'Order <strong>' . $orderNo . '</strong> is allocated to you please check the order.',
                                            'link' => 'sales-orders'
                                        ]);
                                        $driverphonenumber[$driverDetail->id] = $driverDetail->country_dial_code.$driverDetail->phone;

                                        event(new \App\Events\OrderStatusEvent('order-allocation-info', ['driver' => $driverid, 'content' => "Order {$orderNo} is allocated to you please check the order.", 'link' => url('sales-orders')]));
                                    }
                                }

                                if(!empty($driverphonenumber)) {
                                    Helper::sendTwilioMsg($driverphonenumber,1,1,$soId);
                                }
                            }
                        }
                        if(!empty($driverids)) {
                            /*Allocated driver history*/
                            TriggerLog::create([
                                'trigger_id' => 0,
                                'order_id' => $soId,
                                'type' => 4,
                                'allocated_driver_id' => implode(',',$driverids),
                            ]);
                        }

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
        $driverDetails = Deliver::with(['user' => fn ($builder) => ($builder->withTrashed())])->where('so_id', decrypt($id))->whereIn('status', [1,3])->first();//0,
        $logs = TriggerLog::with([
            'watcher' => fn ($builder) => ($builder->withTrashed()),
            'user' => fn ($builder) => ($builder->withTrashed()),
            'assigneddriver' => fn ($builder) => ($builder->withTrashed())
        ])->where('order_id', $so->id)->whereIn('type', [2, 4])->orderBy('id', 'ASC')->get();

        return view('so.view', compact('moduleName', 'categories', 'so', 'moduleLink', 'driverDetails', 'logs'));
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
                return '<span title="' . number_format($row->range, 2, '.', "") . ' miles">' . number_format($row->range, 2, '.', "") . '</span>';
            })
            ->addColumn('location', function ($row) {
                return ($row?->order?->customer_address_line_1 ?? '-') . ' ' . ('<a target="_blank" href="https://www.google.com/maps/place/'.$row?->order?->customer_postal_code.'">'.$row?->order?->customer_postal_code.'</a>' ?? '');
            })
            ->addColumn('added_by', function ($row) {
                return $row?->order?->addedby->name ?? '-';
            })
            ->rawColumns(['distance', 'action', 'order_no','location'])
            ->addIndexColumn()
            ->make(true);
    }

    public function checkPrice (Request $request) {

        if (!empty($request->order_id) && !empty($request->amount) && is_numeric($request->amount)) {
            $order = SalesOrder::with(['items', 'addedby.roles'])->where('id', $request->order_id);
            if ($order->exists()) {
                $order = $order->first();
                if (round($order->items->sum('amount')) == round($request->amount)) {

                    $comPrice = $prodQty = $driverRecevies = 0;

                    $thisProductId = $order->items->first()->product_id ?? 0;
                    $thisDriverId = auth()->user()->id;

                    $hasStock = Helper::getAvailableStockFromDriver($thisDriverId, $thisProductId);

                    if (isset($hasStock[$thisProductId]) && $hasStock[$thisProductId] <= 0) {
                        return response()->json(['status' => false, 'message' => 'You don\'t have stock for this product.']);
                    }

                    DB::beginTransaction();

                    try {

                        $procurementCost = ProcurementCost::where('role_id', $order->addedby->roles->first()->id ?? 2)->where('product_id', $thisProductId)->active();
                        $newTotal = is_numeric($request->amount) ? round($request->amount) : round(floatval($request->amount));
                        $prodQty = $order->items->first()->qty ?? 1;

                        //driver amount
                        $p4dDriver = PaymentForDelivery::where('driver_id', $thisDriverId);
                        $assignedDriver = Deliver::where('user_id', $thisDriverId)->where('status', 1)->first();

                        if ($p4dDriver->exists() && $assignedDriver != null) {
                            $thisRange = sprintf('%.2f', $assignedDriver->range);
                            $p4dDriver = $p4dDriver->where('distance', '>=', $thisRange)->orderBy('distance', 'ASC')->first();

                            if ($p4dDriver != null) {
                                $driverRecevies = $p4dDriver->payment * $prodQty;//driver commission for each qty of order;
                            }

                        } else if (PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first() != null) {
                            $driverRecevies = PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first()->payment * $prodQty;//driver commission for each qty of order;
                        }

                        $orderAmountAfterDriverAmountDeduction = $newTotal - $driverRecevies;

                        if ($orderAmountAfterDriverAmountDeduction <= 0) {
                            DB::rollBack();
                            return response()->json(['status' => false, 'messages' => 'Driver\'s payment amount is more than the order amount.']);
                        }

                        DriverWallet::create([
                            'so_id' => $order->id,
                            'driver_id' => $thisDriverId,
                            'amount' => $orderAmountAfterDriverAmountDeduction,
                            'driver_receives' => $driverRecevies
                        ]);

                        $transactionUid = Helper::hash();

                        //Pay to driver
                        Transaction::create([
                            'so_id' => $order->id,
                            'is_approved' => 1,
                            'transaction_id' => $transactionUid,
                            'user_id' => auth()->user()->id,
                            'transaction_type' => 0,
                            'amount_type' => 1,
                            'voucher' => $order->order_no,
                            'amount' => $driverRecevies,
                            'year' => Helper::$financialYear,
                            'added_by' => auth()->user()->id
                        ]);

                        //Pay to admin
                        Transaction::create([
                            'so_id' => $order->id,
                            'is_approved' => 1,
                            'transaction_id' => $transactionUid,
                            'user_id' => auth()->user()->id,
                            'transaction_type' => 0,
                            'amount_type' => 2,
                            'voucher' => $order->order_no,
                            'amount' => $orderAmountAfterDriverAmountDeduction,
                            'year' => Helper::$financialYear,
                            'added_by' => auth()->user()->id
                        ]);

                        if ($procurementCost->exists()) {
                            $procurementCost = $procurementCost->first();

                                $newProductTotal = $newTotal / $prodQty;

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

                                Transaction::create([
                                    'so_id' => $order->id,
                                    'is_approved' => 1,
                                    'transaction_id' => $transactionUid,
                                    'user_id' => $order->seller_id,
                                    'transaction_type' => 1, // if change here then withrawal req. functionality will be effected
                                    'amount_type' => 3,
                                    'voucher' => $order->order_no,
                                    'amount' => $comPrice * $prodQty,
                                    'year' => Helper::$financialYear,
                                    'added_by' => auth()->user()->id
                                ]);
                        }

                        $si = Stock::where('product_id', $thisProductId)->whereIn('form', [1,2,3])->where('type', 0)->where('driver_id', $thisDriverId)->sum('qty');
                        $so = Stock::where('product_id', $thisProductId)->whereIn('form', [1,2,3,4])->where('type', 1)->where('driver_id', $thisDriverId)->sum('qty');
                        $stotal = ($si - $so) - $prodQty;

                        if ($stotal > 0) {
                            Stock::create([
                                'product_id' => $thisProductId,
                                'driver_id' => $thisDriverId,
                                'type' => 1,
                                'date' => now(),
                                'qty' => $prodQty,
                                'added_by' => $thisDriverId,
                                'form' => 4,
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

                        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
                    }

                } else {
                    return response()->json(['status' => true, 'next' => true]);
                }
            }
        }

        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }

    public function priceUnmatched(Request $request) {

        $toBeDeleted = [];
        $comPrice = $prodQty = $driverRecevies = 0;

        if (!file_exists(storage_path('app/public/so-price-change-agreement'))) {
            mkdir(storage_path('app/public/so-price-change-agreement'), 0777, true);
        }

        if (!empty($request->order_id) && !empty($request->amount) && is_numeric($request->amount)) {
            $order = SalesOrder::with(['items', 'addedby.roles'])->where('id', $request->order_id);
            if ($order->exists()) {
                $order = $order->first();
                $thisProductId = $order->items->first()->product_id ?? 0;
                $thisDriverId = auth()->user()->id;

                $hasStock = Helper::getAvailableStockFromDriver($thisDriverId, $thisProductId);

                if (isset($hasStock[$thisProductId]) && $hasStock[$thisProductId] <= 0) {
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

                    $procurementCost = ProcurementCost::where('role_id', $order->addedby->roles->first()->id ?? 2)->where('product_id', $thisProductId)->active();
                    $newTotal = is_numeric($request->amount) ? round($request->amount) : round(floatval($request->amount));
                    $prodQty = $order->items->first()->qty ?? 1;

                    //driver amount
                    $p4dDriver = PaymentForDelivery::where('driver_id', $thisDriverId);
                    $assignedDriver = Deliver::where('user_id', $thisDriverId)->where('status', 1)->first();

                    if ($p4dDriver->exists() && $assignedDriver != null) {
                        $thisRange = sprintf('%.2f', $assignedDriver->range);
                        $p4dDriver = $p4dDriver->where('distance', '>=', $thisRange)->orderBy('distance', 'ASC')->first();

                        if ($p4dDriver != null) {
                            $driverRecevies = $p4dDriver->payment * $prodQty;//driver commission for each qty of order
                        }

                    } else if (PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first() != null) {
                        $driverRecevies = PaymentForDelivery::whereNull('driver_id')->orWhere('driver_id', '')->first()->payment * $prodQty;//driver commission for each qty of order
                    }

                    $orderAmountAfterDriverAmountDeduction = $newTotal - $driverRecevies;

                    if ($orderAmountAfterDriverAmountDeduction <= 0) {
                        DB::rollBack();
                        return response()->json(['status' => false, 'messages' => 'Driver\'s payment amount is more than the order amount.']);
                    }

                    DriverWallet::create([
                        'so_id' => $order->id,
                        'driver_id' => $thisDriverId,
                        'amount' => $orderAmountAfterDriverAmountDeduction,
                        'driver_receives' => $driverRecevies
                    ]);

                    $transactionUid = Helper::hash();

                    //Pay to driver
                    Transaction::create([
                        'so_id' => $order->id,
                        'is_approved' => 1,
                        'transaction_id' => $transactionUid,
                        'user_id' => auth()->user()->id,
                        'transaction_type' => 0,
                        'amount_type' => 1,
                        'voucher' => $order->order_no,
                        'amount' => $driverRecevies,
                        'year' => Helper::$financialYear,
                        'added_by' => auth()->user()->id
                    ]);

                    //Pay to admin
                    Transaction::create([
                        'so_id' => $order->id,
                        'is_approved' => 1,
                        'transaction_id' => $transactionUid,
                        'user_id' => auth()->user()->id,
                        'transaction_type' => 0,
                        'amount_type' => 2,
                        'voucher' => $order->order_no,
                        'amount' => $orderAmountAfterDriverAmountDeduction,
                        'year' => Helper::$financialYear,
                        'added_by' => auth()->user()->id
                    ]);

                    if ($procurementCost->exists()) {
                        $procurementCost = $procurementCost->first();

                            $newProductTotal = $newTotal / $prodQty;

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

                            Transaction::create([
                                'so_id' => $order->id,
                                'is_approved' => 1,
                                'transaction_id' => $transactionUid,
                                'user_id' => $order->seller_id,
                                'transaction_type' => 1, // if change here then withrawal req. functionality will be effected
                                'amount_type' => 3,
                                'voucher' => $order->order_no,
                                'amount' => $comPrice * $prodQty,
                                'year' => Helper::$financialYear,
                                'added_by' => auth()->user()->id
                            ]);
                    }

                    $si = Stock::where('product_id', $thisProductId)->whereIn('form', [1,2,3])->where('type', 0)->where('driver_id', $thisDriverId)->sum('qty');
                    $so = Stock::where('product_id', $thisProductId)->whereIn('form', [1,2,3,4])->where('type', 1)->where('driver_id', $thisDriverId)->sum('qty');
                    $stotal = ($si - $so) - $prodQty;

                    if ($stotal > 0) {
                        Stock::create([
                            'product_id' => $thisProductId,
                            'driver_id' => $thisDriverId,
                            'type' => 1,
                            'date' => now(),
                            'qty' => $prodQty,
                            'added_by' => $thisDriverId,
                            'form' => 4,
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

            $thisUser = User::firstWhere('id', $request->driver_id);

            if ($thisUser == null) {
                return response()->json(['status' => false, 'message' => 'Driver not found.']);
            }
            $driverphonenumber[$thisUser->id] = $thisUser->country_dial_code.$thisUser->phone;
            $thisOrder = SalesOrder::firstWhere('id', $request->order_id);

            if ($thisOrder == null) {
                return response()->json(['status' => false, 'message' => 'Order not found.']);
            }

            if (Deliver::where('so_id', $request->order_id)->whereIn('status', [0, 1])->exists()) {
                $driver = Deliver::where('so_id', $request->order_id)->whereIn('status', [0, 1])->first();

                Deliver::create([
                    'user_id' => $request->driver_id,
                    'so_id' => $request->order_id,
                    'added_by' => auth()->user()->id,
                    'driver_lat' => $thisUser->lat,
                    'driver_long' => $thisUser->long,
                    'delivery_location_lat' => $thisOrder->lat,
                    'delivery_location_long' => $thisOrder->long,
                    'range' => Distance::measure($thisUser->lat, $thisUser->long, $thisOrder->lat, $thisOrder->long),
                    'status' => 0
                ]);

                Notification::create([
                    'user_id' => $request->driver_id,
                    'so_id' => $request->order_id,
                    'title' => 'New Order',
                    'description' => 'Order <strong>' . $thisOrder->order_no . '</strong> is allocated to you please check the order.',
                    'link' => 'sales-orders'
                ]);

                event(new \App\Events\OrderStatusEvent('order-allocation-info', ['driver' => $request->driver_id, 'content' => "Order {$thisOrder->order_no} is allocated to you please check the order.", 'link' => url('sales-orders')]));

                if(!empty($driverphonenumber)) {
                    Helper::sendTwilioMsg($driverphonenumber,1,1,$request->order_id);
                }

                TriggerLog::create([
                    'trigger_id' => 0,
                    'order_id' => $request->order_id,
                    'type' => 4,
                    'allocated_driver_id' => implode(',',[$request->driver_id]),
                ]);

                Deliver::where('id', $driver->id)->update(['status' => 4]);
                SalesOrder::where('id', $request->order_id)->update(['responsible_user' => $request->driver_id]);

                return response()->json(['status' => true, 'message' => 'Driver assigned successfully.']);
            } else if (Deliver::where('so_id', $request->order_id)->where('status', 2)->exists()) {

                $totalOrder = Deliver::where('so_id', $request->order_id)->count();
                $totalRejected = Deliver::where('so_id', $request->order_id)->where('status', 2)->count();

                if ($totalOrder == $totalRejected) {

                    Deliver::where('so_id', $request->order_id)->where('status', 2)->delete();

                    Deliver::create([
                        'user_id' => $request->driver_id,
                        'so_id' => $request->order_id,
                        'added_by' => auth()->user()->id,
                        'driver_lat' => $thisUser->lat,
                        'driver_long' => $thisUser->long,
                        'delivery_location_lat' => $thisOrder->lat,
                        'delivery_location_long' => $thisOrder->long,
                        'range' => Distance::measure($thisUser->lat, $thisUser->long, $thisOrder->lat, $thisOrder->long),
                        'status' => 0
                    ]);

                    Notification::create([
                        'user_id' => $request->driver_id,
                        'so_id' => $request->order_id,
                        'title' => 'New Order',
                        'description' => 'Order <strong>' . $thisOrder->order_no . '</strong> is allocated to you please check the order.',
                        'link' => 'sales-orders'
                    ]);

                    event(new \App\Events\OrderStatusEvent('order-allocation-info', ['driver' => $request->driver_id, 'content' => "Order {$thisOrder->order_no} is allocated to you please check the order.", 'link' => url('sales-orders')]));

                    if(!empty($driverphonenumber)) {
                        Helper::sendTwilioMsg($driverphonenumber,1,1,$request->order_id);
                    }

                    TriggerLog::create([
                        'trigger_id' => 0,
                        'order_id' => $request->order_id,
                        'type' => 4,
                        'allocated_driver_id' => implode(',',[$request->driver_id]),
                    ]);

                    SalesOrder::where('id', $request->order_id)->update(['responsible_user' => $request->driver_id]);

                    return response()->json(['status' => true, 'message' => 'Driver assigned successfully.']);

                } else {
                    $driver = Deliver::where('so_id', $request->order_id)->where('status', 2)->first();
                    Deliver::create([
                        'user_id' => $request->driver_id,
                        'so_id' => $request->order_id,
                        'added_by' => auth()->user()->id,
                        'driver_lat' => $thisUser->lat,
                        'driver_long' => $thisUser->long,
                        'delivery_location_lat' => $thisOrder->lat,
                        'delivery_location_long' => $thisOrder->long,
                        'range' => Distance::measure($thisUser->lat, $thisUser->long, $thisOrder->lat, $thisOrder->long),
                        'status' => 0
                    ]);

                    Notification::create([
                        'user_id' => $request->driver_id,
                        'so_id' => $request->order_id,
                        'title' => 'New Order',
                        'description' => 'Order <strong>' . $thisOrder->order_no . '</strong> is allocated to you please check the order.',
                        'link' => 'sales-orders'
                    ]);

                    event(new \App\Events\OrderStatusEvent('order-allocation-info', ['driver' => $request->driver_id, 'content' => "Order {$thisOrder->order_no} is allocated to you please check the order.", 'link' => url('sales-orders')]));

                    if(!empty($driverphonenumber)) {
                        Helper::sendTwilioMsg($driverphonenumber,1,1,$request->order_id);
                    }

                    TriggerLog::create([
                        'trigger_id' => 0,
                        'order_id' => $request->order_id,
                        'type' => 4,
                        'allocated_driver_id' => implode(',',[$request->driver_id]),
                    ]);

                    Deliver::where('id', $driver->id)->update(['status' => 4]);
                    SalesOrder::where('id', $request->order_id)->update(['responsible_user' => $request->driver_id]);

                    return response()->json(['status' => true, 'message' => 'Driver assigned successfully.']);
                }
            } else {
                Deliver::where('so_id', $request->order_id)->delete();
                Deliver::create([
                    'user_id' => $request->driver_id,
                    'so_id' => $request->order_id,
                    'added_by' => auth()->user()->id,
                    'driver_lat' => $thisUser->lat,
                    'driver_long' => $thisUser->long,
                    'delivery_location_lat' => $thisOrder->lat,
                    'delivery_location_long' => $thisOrder->long,
                    'range' => Distance::measure($thisUser->lat, $thisUser->long, $thisOrder->lat, $thisOrder->long),
                    'status' => 0
                ]);

                Notification::create([
                    'user_id' => $request->driver_id,
                    'so_id' => $request->order_id,
                    'title' => 'New Order',
                    'description' => 'Order <strong>' . $thisOrder->order_no . '</strong> is allocated to you please check the order.',
                    'link' => 'sales-orders'
                ]);

                event(new \App\Events\OrderStatusEvent('order-allocation-info', ['driver' => $request->driver_id, 'content' => "Order {$thisOrder->order_no} is allocated to you please check the order.", 'link' => url('sales-orders')]));

                if(!empty($driverphonenumber)) {
                    Helper::sendTwilioMsg($driverphonenumber,1,1,$request->order_id);
                }

                TriggerLog::create([
                    'trigger_id' => 0,
                    'order_id' => $request->order_id,
                    'type' => 4,
                    'allocated_driver_id' => implode(',',[$request->driver_id]),
                ]);

                SalesOrder::where('id', $request->order_id)->update(['responsible_user' => $request->driver_id]);
                return response()->json(['status' => true, 'message' => 'Driver assigned successfully.']);
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

    public function isCustomerScammer(Request $request) {
        if (!empty($request->customerphone) && !empty($request->country_code)) {

            $isScammer = ScammerContact::where('phone_number', str_replace(' ', '', $request->customerphone))
                        ->where('dial_code', str_replace(' ', '', $request->country_code))
                        ->count() < 2;

            return response()->json($isScammer);
        }

        return response()->json(false);
    }

    public static function againDriverAllocate($saleorderId){

        $errorWhileSavingLatLong = true;
        $latFrom = $longFrom = $toLat = $toLong = $range = '';

        $users = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 3);
        })->whereNotNull('lat')->whereNotNull('long')
        ->active()
        ->select('id', 'lat', 'long')->get()->toArray();

        try {
            if (env('GEOLOCATION_API') == 'true') {
                $key = trim(Setting::first()?->geocode_key);

                $address = trim("{$saleorderId->customer_address_line_1} {$saleorderId->customer_postal_code}");
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
                        'postal_code' => $saleorderId->customer_postal_code,
                        'address' => $saleorderId->customer_address_line_1,
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


        if ($errorWhileSavingLatLong === false) {

            if (!empty($latFrom) && !empty($longFrom)) {

                if (!empty($users)) {
                    $getAllDriversDistance = [];

                    foreach ($users as $row) {
                        $getAllDriversDistance[$row['id']] = Distance::measure($latFrom, $longFrom, $row['lat'], $row['long']);
                    }


                    asort($getAllDriversDistance);

                    $result = self::getDriver($getAllDriversDistance);

                    if ($result['exists']) {
                        $getNearbyDriver = $result['drivers'];
                        $driverids = array_keys($getNearbyDriver);
                    } else {
                        $isNotAvail = true;
                        $successdrivers = [];
                        foreach ($getAllDriversDistance as $tmpDriver => $tmpRange) {
                            if (PaymentForDelivery::where(fn ($b) => $b->whereNull('driver_id')->orWhere('driver_id', ''))->where('distance', '>=', $tmpRange)->exists()) {
                                $successdrivers[$tmpDriver] = $tmpRange;
                            }
                        }
                        if(!empty($successdrivers)) {
                            $getNearbyDriver = $successdrivers;
                            $driverids = array_keys($getNearbyDriver);
                            $isNotAvail = false;
                        }
                        if ($isNotAvail) {
                            return response()->json(['status' => false, 'message' => 'We cannot accept your order because delivery location falls outside from the driver\'s delivery zone.']);
                        }
                    }

                    return response()->json(['status' => true , 'message' => 'Available', 'drivers'=>$getNearbyDriver]);

                } else {
                    return response()->json(['status' => false, 'message' => 'We cannot accept your order because delivery location falls outside from the driver\'s delivery zone.']);
                }

            } else {
                return response()->json(['status' => false, 'message' => 'Please provide accurate address.']);
            }

        } else {
            return response()->json(['status' => false, 'message' => 'Please provide accurate address.']);
        }
    }
}
