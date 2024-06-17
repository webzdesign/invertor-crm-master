<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Permission;
use App\Models\UserRole;
use App\Helpers\Helper;
use App\Models\Role;

class RoleController extends Controller
{
    protected $moduleName = 'Roles';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            return view('roles.index', compact('moduleName'));
        }

        $roles = Role::with(['addedby', 'updatedby'])->whereNotIn("id", [4]);

        if (isset($request->filterStatus)) {
            if ($request->filterStatus != '') {
                $roles->where('status', $request->filterStatus);
            }
        }

        if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
            $roles->orderBy('id', 'desc');
        }

        return dataTables()->eloquent($roles)
            ->editColumn('addedby.name', function($role) {
                return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($role->created_at))."'>".$role->addedby->name."</span>";
            })
            ->editColumn('updatedby.name', function($role) {
                if ($role->updatedby->name != '-') {
                    return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($role->updated_at))."'>".$role->updatedby->name."</span>";
                } else {
                    return $role->updatedby->name;
                }
            })
            ->addColumn('action', function ($roles) {

                $variable = $roles;

                $action = "";
                $action .= '<div class="whiteSpace">';

                if (!in_array(auth()->user()->roles()->first()->id, [1,2,3])) {
                    if (auth()->user()->hasPermission("roles.edit")) {
                        $url = route("roles.edit", encrypt($variable->id));
                        $action .= view('buttons.edit', compact('variable', 'url'));
                    }
                    if (auth()->user()->hasPermission("roles.activeinactive")) {
                        $url = route("roles.activeinactive", encrypt($variable->id));
                        $action .= view('buttons.status', compact('variable', 'url'));
                    }
                    if (auth()->user()->hasPermission("roles.delete")) {
                        $url = route("roles.delete", encrypt($variable->id));
                        $action .= view('buttons.delete', compact('variable', 'url'));
                    }
                }

                if (auth()->user()->hasPermission("roles.view")) {
                    $url = route("roles.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }

                if (in_array(1, auth()->user()->roles->pluck('id')->toArray()) && $variable->status == 1) {
                    $rid = encrypt($variable->id);
                    $uid = encrypt(auth()->user()->id);
                    $url = url("register/{$rid}/{$uid}");
                    $action .= "<div class='tableCards d-inline-block me-1 pb-0'><div class='editDlbtn'><a data-toggle='tooltip' data-url='{$url}' title='Copy Signup Link' class='deleteBtn copy-register-link' > <i class='fa fa-copy text-white' aria-hidden='true'></i> </a></div></div>";
                }

                $action .= '</div>';

                return $action;
            })
            ->editColumn("status", function($users) {
                if ($users->status == 1) {
                    return "<span class='badge bg-success'>Active</span>";
                } else {
                    return "<span class='badge bg-danger'>Inactive</span>";
                }
            })
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        $moduleName = 'Role';
        $moduleLink = route('roles.index');

        if (auth()->user()->roles->where('id', 1)->count()) {
            $permission = Permission::where('model', '!=', 'SalesOrder')->get()->groupBy('model');
        } else {
            $userRoles = auth()->user()->roles->pluck('id')->toArray() ?? [];
            $permission = PermissionRole::whereIn('role_id', $userRoles)->select('permission_id')->pluck('permission_id')->toArray() ?? [];
            $permission = Permission::where('model', '!=', 'SalesOrder')->whereIn('id', $permission)->get()->groupBy('model');
        }

        return view('roles.create', compact('moduleName', 'permission','moduleLink'));
    }

    public function store(RoleRequest $request)
    {
        DB::beginTransaction();

        $role = new Role();
        $role->name = $request->name;
        $role->slug = Str::slug($request->name,"-");
        $role->description = $request->description;
        $role->added_by = auth()->User()->id;
        $role->save();

        $role->permissions()->sync($request->permission);

        DB::commit();

        return redirect()->route('roles.index')->with('success', 'Role added successfully.');
    }

    public function show($id)
    {
        $id = decrypt($id);
        $role = Role::find($id);
        $moduleName = 'Role';
        $moduleLink = route('roles.index');
        if (auth()->user()->roles->where('id', 1)->count()) {
            $permission = Permission::get()->groupBy('model');
        } else {
            $userRoles = auth()->user()->roles->pluck('id')->toArray() ?? [];
            $permission = PermissionRole::whereIn('role_id', $userRoles)->select('permission_id')->pluck('permission_id')->toArray() ?? [];
            $permission = Permission::whereIn('id', $permission)->get()->groupBy('model');
        }

        $rolePermissions = PermissionRole::where('role_id', $id)->pluck('permission_id')->toArray();

        return view('roles.view', compact('moduleName', 'permission', 'rolePermissions', 'role','moduleLink'));
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $role = Role::find($id);
        $moduleName = 'Role';
        $moduleLink = route('roles.index');
        if (auth()->user()->roles->where('id', 1)->count()) {
            $permission = Permission::where('model', '!=', 'SalesOrder')->get()->groupBy('model');
        } else {
            $userRoles = auth()->user()->roles->pluck('id')->toArray() ?? [];
            $permission = PermissionRole::whereIn('role_id', $userRoles)->select('permission_id')->pluck('permission_id')->toArray() ?? [];
            $permission = Permission::where('model', '!=', 'SalesOrder')->whereIn('id', $permission)->get()->groupBy('model');
        }

        $rolePermissions = PermissionRole::where('role_id', $id)->pluck('permission_id')->toArray();

        return view('roles.edit',compact('moduleName', 'permission', 'rolePermissions', 'role','moduleLink'));
    }

    public function update(RoleRequest $request, $id)
    {
        $id = decrypt($id);

        $role = Role::find($id);
        $role->name = $request->name;
        $role->slug = Str::slug($request->name,"-");
        $role->description = $request->description;
        $role->updated_by = auth()->User()->id;
        $role->save();

        $role->permissions()->detach();
        $role->permissions()->sync($request->permission);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $id = decrypt($id);

        if ($id == '1') {
            return response()->json(['error' => 'Can\'t delete system defined role.','status' => 500]);
        }

        if (UserRole::where('role_id', $id)->exists()) {
            return response()->json(['error' => 'Can\'t delete this role. This role is assigned to some users.','status' => 500]);
        }

        DB::beginTransaction();

        try {
            Role::find($id)->delete();
            PermissionRole::where('role_id', $id)->delete();

            DB::commit();
            return response()->json(['success' => 'Role deleted successfully.','status' => 200]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => Helper::$errorMessage,'status' => 500]);
        }
    }

    public function status($id)
    {
        $id = decrypt($id);

        $role = Role::find($id);
        $role->status = $role->status == 1 ? 0 : 1;
        $role->save();

        return response()->json(['success' => $role->status == 1 ? 'Role activated successfully.' : 'Role inactivated successfully.','status' => 200]);
    }

    public function checkRoleExist(Request $request)
    {
        $response = self::checkUniqueRoleName($request->name,$request->id);

        return response()->json($response);
    }

    public static function checkUniqueRoleName($role, $id)
    {
        if (!isset($id)) {
            $role = Role::where('name', $role)->first();
        } else {
            $role = Role::where('name', $role)->where('id', '!=', decrypt($id))->first();
        }

        if ($role) {
            return false;
        } else {
            return true;
        }
    }
}
