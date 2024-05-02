<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PurchaseOrderRequest;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Category;

class PurchaseOrderController extends Controller
{
    protected $moduleName = 'Purchase Orders';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
    
            return view('po.index', compact('moduleName'));
        }

        $users = PurchaseOrder::with(['items', 'addedby', 'updatedby']);

        if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
            $users->orderBy('id', 'desc');
        }

        return dataTables()->eloquent($users)
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
            ->editColumn("role.name", function($users) {
                $roleName = '';
                foreach ($users->roles as $role) {
                    $roleName .= $role->name.'<br />';
                }
                return $roleName;
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
}
