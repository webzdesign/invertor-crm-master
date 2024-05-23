<?php

namespace App\Http\Controllers;

use App\Models\{SalesOrderStatus, SalesOrder, SalesOrderItem, Deliver, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class SalesOrderStatusController extends Controller
{
    public function index() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::orderBy('sequence', 'ASC')->get();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];
        $orders = [];

        foreach ($statuses as $status) {
            $tempOrder = SalesOrder::join('sales_order_items', 'sales_order_items.so_id', '=', 'sales_orders.id')->selectRaw("sales_orders.id, sales_orders.order_no, sales_orders.date, SUM(sales_order_items.amount) as amount")->where('sales_orders.status', $status->id)->groupBy('sales_order_items.so_id');

            if ($tempOrder->exists()) {
                $orders[$status->id] = $tempOrder->get()->toArray();
            }
        }

        return view('sales-orders-status.index', compact('moduleName', 'statuses', 'colours', 'orders'));
    }

    public function edit() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::orderBy('sequence', 'ASC')->get();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];

        return view('sales-orders-status.edit', compact('moduleName', 'statuses', 'colours'));        
    }

    public function update(Request $request) {
        $this->validate($request, [
            'name.*' => 'required|distinct'
        ], [
            'name.*.required' => 'Enter status name before you save.',
            'name.*.distinct' => 'Status name must be unique.'
        ]);

        $sequences = $request->sequence;
        $names = $request->name;
        $colors = $request->color;

        if (count($sequences) != count($names)) {
            return redirect()->route('sales-order-status-edit')->with('error', 'Add atleast a card to save.');
        }

        DB::beginTransaction();

        try {
            if (count($sequences) > 0) {

                foreach ($sequences as $key => $value) {

                    if (!is_null($value)) {
                        SalesOrderStatus::where('id', $value)->update([
                            'name' => $names[$key],
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    } else {
                        SalesOrderStatus::create([
                            'name' => $names[$key],
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    }
                }

                DB::commit();
                return redirect()->route('sales-order-status')->with('success', 'Sales order status updated successfully.');
            }   
        } catch (\Exception $e) {
            Helper::logger($e->getMessage() . ' ' . $e->getLine());
            DB::rollBack();
            return redirect()->route('sales-order-status-edit')->with('error', Helper::$errorMessage);
        }

        DB::rollBack();
        return redirect()->route('sales-order-status-edit')->with('error', 'Add atleast a card to save.');
    }

    public function sequence(Request $request) {
        if (SalesOrderStatus::where('id', $request->status)->doesntExist()) {
            return response()->json(['status' => false, 'container' => true]);
        }

        if (SalesOrder::where('id', $request->order)->doesntExist()) {
            return response()->json(['status' => false, 'card' => true]);
        }

        if (SalesOrder::where('id', $request->order)->update(['status' => $request->status])) {
            return response()->json(['status' => true]);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function list(Request $request) {
        if(!in_array(auth()->user()->roles->first()->id, [1,2,3])) {
            abort(403);
        }

        $statuses = SalesOrderStatus::active()->select('id', 'name', 'color')->get();

        if (!$request->ajax()) {
            $moduleName = 'Sales Order Status';
    
            return view('sales-orders-status.list', compact('moduleName', 'statuses'));
        }

        $orders = SalesOrder::with(['items', 'ostatus']);
        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();

        if (!in_array(1, $thisUserRoles)) {
            if (in_array(2, $thisUserRoles)) {
                $orders = $orders->where('seller_id', auth()->user()->id);
            } else if (in_array(3, $thisUserRoles)) {
                $driversOrder = Deliver::where('user_id', auth()->user()->id)->select('soi_id')->pluck('soi_id')->toArray();
                $driversOrder = SalesOrderItem::select('so_id')->whereIn('id', $driversOrder)->groupBy('so_id')->pluck('so_id')->toArray();

                $orders = $orders->whereIn('id', $driversOrder);
            }
        }

        $tempCount = $orders->count();

        if (isset($request->order[1]['column']) && $request->order[1]['column'] == 0) {
            $orders = $orders->orderBy('id', 'desc');
        }

        return dataTables($orders)
                ->addColumn('checkbox', function ($row) {
                    return "<input type='checkbox' class='form-check-input single-checkbox' value='{$row->id}' />";
                })
                ->editColumn('order_no', function ($row) {
                    $route = route('sales-orders.view', encrypt($row->id));
                    return "<a target='_blank' href='{$route}' class='color-blue'> {$row->order_no} </a>";
                })
                ->addColumn('status', function ($row) use ($statuses) {
                   
                    $html = 
                    '<div class="status-main button-dropdown position-relative">
                        <label class="status-label" style="background:' . ($row->ostatus->color ?? '') . ';"> ' . ($row->ostatus->name ?? '') . ' </label>
                        <button class="dropdown-toggle status-opener ms-2 d-inline-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 20 19" fill="none">
                            <path d="M0.998047 14.613V18.456H4.84105L16.175 7.12403L12.332 3.28103L0.998047 14.613ZM19.147 4.15203C19.242 4.05721 19.3174 3.94458 19.3688 3.82061C19.4202 3.69664 19.4466 3.56374 19.4466 3.42953C19.4466 3.29533 19.4202 3.16243 19.3688 3.03846C19.3174 2.91449 19.242 2.80186 19.147 2.70703L16.747 0.307035C16.6522 0.212063 16.5396 0.136719 16.4156 0.0853128C16.2916 0.0339065 16.1588 0.00744629 16.0245 0.00744629C15.8903 0.00744629 15.7574 0.0339065 15.6335 0.0853128C15.5095 0.136719 15.3969 0.212063 15.302 0.307035L13.428 2.18403L17.271 6.02703L19.147 4.15203Z" fill="#3C3E42"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu status-modal">
                            <div class="status-dropdown">';


                            foreach ($statuses as $status) {
                                if ($status->id == $row->status) {
                                $html .= '<button type="button" data-sid="' . $status->id . '" data-oid="' . $row->id . '" style="background:' . $status->color . ';" class="status-dropdown-toggle d-flex align-items-center justify-content-between f-14">
                                    <span>' . $status->name . '</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                                        <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                                    </svg>
                                </button>';
                                }
                            }

                                $html .= '<div class="status-dropdown-menu">';

                                foreach ($statuses as $status) {
                                    $html .= '<li data-isajax="true" style="background: '. $status->color .'" data-sid="' . $status->id . '" data-oid="' . $row->id . '" > '. $status->name .' </li>';
                                }

                                $html .= '</div>
                            </div>
                            <div class="status-action-btn mt-2 position-relative -z-1">
                                <button class="status-save-btn btn-primary f-500 f-14 d-inline-block" disabled>Save</button>
                                <button class="refresh-dt hide-dropdown btn-default f-500 f-14 d-inline-block ms-1">Cancel</button>
                            </div>
                        </div>
                    </div>';

                    return $html;
                })
                ->addColumn('date', function ($row) {
                    return \Carbon\Carbon::parse($row->date)->toFormattedDateString();
                })
                ->addColumn('amount', function ($row) {
                    return Helper::currencyFormatter($row->items->sum('amount'), true);
                })
                ->rawColumns(['order_no', 'checkbox', 'status'])
                ->with(['totalOrders' => $tempCount])
                ->make(true);
    }

    public function status (Request $request) {
        $response = false;
        $message = Helper::$errorMessage;
        $color = $text = '';

        if (!empty($request->status) && !empty($request->order)) {
            $isStatus = SalesOrderStatus::where('id', $request->status);
            if ($isStatus->exists()) {
                $color = $isStatus->first()->color;
                $text = $isStatus->first()->name;

                if (SalesOrder::where('id', $request->order)->update(['status' => $request->status])) {
                    $response = true;
                    $message = 'Status Updated successfully';
                }
            }
        }
        return response()->json(['status' => $response, 'message' => $message, 'color' => $color, 'text' => $text]);
    }

    public function statusBulkUpdate(Request $request) {
        if (SalesOrderStatus::where('id', $request->status)->doesntExist()) {
            return redirect()->back()->with('error', Helper::$errorMessage);
        }

        if ($request->ids == 'all') {
            $resp = SalesOrder::query()->update(['status' => $request->status]);
            return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for all orders successfully.' : Helper::$errorMessage));
        } else {
            $resp = SalesOrder::whereIn('id', explode(',', $request->ids))->update(['status' => $request->status]);
            return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for orders successfully.' : Helper::$errorMessage));
        }
    }
}