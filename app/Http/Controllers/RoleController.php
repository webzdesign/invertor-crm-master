<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Role;

class RoleController extends Controller
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = 'Role';
    }   

    public function index()
    {
        $moduleName = $this->moduleName;
        return view('roles.index', compact('moduleName'));
    }

    // public function DataTable(Request $request)
    // {
    //     $roles = Role::with(['addedby', 'updatedby'])->where("roles.id", "!=", 1)->select('roles.*');

    //     if (isset($request->filterStatus)) {
    //         if ($request->filterStatus != '') {
    //             $roles->where('status', $request->filterStatus);
    //         }
    //     }

    //     if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
    //         $roles->orderBy('id', 'desc');
    //     }

    //     return dataTables()->eloquent($roles)
    //         ->editColumn('addedby.name', function($role) {
    //             return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($role->created_at))."'>".$role->addedby->name."</span>";
    //         })
    //         ->editColumn('updatedby.name', function($role) {
    //             if ($role->updatedby->name != '-') {
    //                 return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($role->updated_at))."'>".$role->updatedby->name."</span>";
    //             } else {
    //                 return $role->updatedby->name;
    //             }
    //         })
    //         ->addColumn('action', function ($roles) use($checkYearIsClosed) {
    //             $variable = $roles;
    //             $action = "";
    //             $action .= '<div class="whiteSpace">';
    //             if (auth()->user()->hasPermission("roles.edit") && !$checkYearIsClosed) {
    //                 $url = route("roles.edit", encrypt($variable->id));
    //                 $action .= view('buttons.edit', compact('variable', 'url')); 
    //             }
    //             if (auth()->user()->hasPermission("roles.view")) {
    //                 $url = route("roles.view", encrypt($variable->id));
    //                 $action .= view('buttons.view', compact('variable', 'url'));
    //             }
    //             if (auth()->user()->hasPermission("roles.activeinactive") && !$checkYearIsClosed) {
    //                 $url = route("roles.activeinactive", encrypt($variable->id));
    //                 $action .= view('buttons.status', compact('variable', 'url'));
    //             }
    //             if (auth()->user()->hasPermission("roles.delete") && !$checkYearIsClosed) {
    //                 $url = route("roles.delete", encrypt($variable->id));
    //                 $action .= view('buttons.delete', compact('variable', 'url')); 
    //             }
    //             $action .= '</div>';
                
    //             return $action;
    //         })
    //         ->editColumn("status", function($users) {
    //             if ($users->status == 1) {
    //                 return "<span class='badge bg-success'>Active</span>";
    //             } else {
    //                 return "<span class='badge bg-danger'>InActive</span>";
    //             }
    //         })
    //         ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name'])
    //         ->addIndexColumn()
    //         ->make(true);
    // }
}
