<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\RequiredDocument;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Permission;
use App\Models\UserRole;
use App\Helpers\Helper;
use App\Models\Role;
use App\Models\UserAssignRole;
use App\Models\SalesOrderStatus;

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
            $roles->orderBy('id', 'asc');
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
                $action .= '<div class="d-flex align-items-center justify-content-center">';

                if (!in_array($roles->id, [1])) {
                    if (auth()->user()->hasPermission("roles.edit")) {
                        $url = route("roles.edit", encrypt($variable->id));
                        $action .= view('buttons.edit', compact('variable', 'url'));

                        $action .= '
                        <div class="tableCards d-inline-block me-1 pb-0">
                            <div class="editDlbtn">
                                <a data-bs-toggle="tooltip" class="editBtn modal-edit-btn" title="Required document for registration" href="' . (route('set-required-documents', encrypt($variable->id))) . '">
                                    <i class="fa fa-file-text text-white" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                        ';
                    }
                }

                if (!in_array($roles->id, [1,2,3])) {
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
            ->editColumn("is_user_activation", function($users) {
                if ($users->is_user_activation == 1) {
                    return "<i class='fa fa-check-circle-o text-success'></i> Need activation";
                } else {
                    return "<i class='fa fa-times-circle text-danger'></i> Don't need activation";
                }
            })
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name','is_user_activation'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        $moduleName = 'Role';
        $moduleLink = route('roles.index');

        if (auth()->user()->roles->where('id', 1)->count()) {
            $permission = Permission::get()->groupBy('model');
        } else {
            $userRoles = auth()->user()->roles->pluck('id')->toArray() ?? [];
            $permission = PermissionRole::whereIn('role_id', $userRoles)->select('permission_id')->pluck('permission_id')->toArray() ?? [];
            $permission = Permission::whereIn('id', $permission)->get()->groupBy('model');
        }
        $roleDetails = Role::active()->where('id', '!=', '4')->pluck('name','id')->toArray();
        $statuses = SalesOrderStatus::select('name', 'id')->pluck('name', 'id')->toArray();
        $userassignrole = array();

        return view('roles.create', compact('moduleName', 'permission','moduleLink','roleDetails','userassignrole','statuses'));
    }

    public function store(RoleRequest $request)
    {
        // $permission = ($request->permission !=null ? $request->permission : array());
        // if(!in_array(45,$permission)) {
        //     array_push($permission,"45");
        // }

        DB::beginTransaction();

        $role = new Role();
        $role->name = $request->name;
        $role->slug = Str::slug($request->name,"-");
        $role->description = $request->description;
        $role->added_by = auth()->User()->id;
        $role->is_user_activation = $request->is_user_activation;
        $role->filter_status = (isset($request->access_order_status_id) && !empty($request->access_order_status_id)) ? implode(',',$request->access_order_status_id) : null;
        $role->save();

        $role->permissions()->sync($request->permission);

        if(isset($request->assign_role_id) && !empty($request->assign_role_id)) {
            $assign_role_id = implode(',',array_unique($request->assign_role_id));
            UserAssignRole::updateOrCreate([
                'main_role_id'   => $role->id,
            ],[
                'assign_role_id' => $assign_role_id
            ]);
        }
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
        $roleDetails = Role::active()->where('id', '!=', '4')->pluck('name','id')->toArray();
        $assigndata = UserAssignRole::where('main_role_id',$id)->first();
        $userassignrole = (!empty($assigndata)) ? explode(',',$assigndata->assign_role_id) : array();
        $statuses = SalesOrderStatus::select('name', 'id')->pluck('name', 'id')->toArray();
        $roleaccessfilter = (isset($role->filter_status) && $role->filter_status !=null) ? explode(',',$role->filter_status): array();
        return view('roles.view', compact('moduleName', 'permission', 'rolePermissions', 'role','moduleLink','roleDetails','userassignrole','statuses','roleaccessfilter'));
    }

    public function edit($id)
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
        $roleDetails = Role::active()->where('id', '!=', '4')->pluck('name','id')->toArray();
        $assigndata = UserAssignRole::where('main_role_id',$id)->first();
        $userassignrole = (!empty($assigndata)) ? explode(',',$assigndata->assign_role_id) : array();
        $statuses = SalesOrderStatus::select('name', 'id')->pluck('name', 'id')->toArray();
        $roleaccessfilter = (isset($role->filter_status) && $role->filter_status !=null) ? explode(',',$role->filter_status): array();
        return view('roles.edit',compact('moduleName', 'permission', 'rolePermissions', 'role','moduleLink','roleDetails','userassignrole','statuses','roleaccessfilter'));
    }

    public function update(RoleRequest $request, $id)
    {
        $id = decrypt($id);

        $permission = ($request->permission !=null ? $request->permission :array());


        // if($id !=1 && !in_array(45,$permission)) {
        //     array_push($permission,"45");
        // }

        $role = Role::find($id);
        $role->name = $request->name;
        $role->description = $request->description;
        $role->updated_by = auth()->User()->id;
        $role->is_user_activation = $request->is_user_activation;
        $role->filter_status = (isset($request->access_order_status_id) && !empty($request->access_order_status_id)) ? implode(',',$request->access_order_status_id) : null;
        $role->save();

        $role->permissions()->detach();
        $role->permissions()->sync($request->permission);
        if(isset($request->assign_role_id) && !empty($request->assign_role_id)) {
            $assign_role_id = implode(',',array_unique($request->assign_role_id));
            UserAssignRole::updateOrCreate([
                'main_role_id'   => $role->id,
            ],[
                'assign_role_id' => $assign_role_id
            ]);
        }
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
            UserAssignRole::where('main_role_id',$id)->delete();

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

    public function setDocs(Request $request, $id) {

        $moduleName = 'Roles';
        $role = Role::find(decrypt($id));

        return view('roles.document', compact('moduleName', 'role', 'id'));
    }

    public function saveDocs(Request $request, $id) {

        $this->validate($request, [
            'role' => 'required',
            'document_name.*' => 'required'
        ], [
            'role' => 'Select a role.',
            'document_name.*.required' => 'Enter document name.'
        ]);

        $role = Role::find(decrypt($id));

        if (RequiredDocument::where('role_id', decrypt($id))->doesntExist() && $role) {

            $names = $request->document_name;

            if (is_array($names)) {
                $filteredNames = array_filter($request->document_name);
                if (is_countable($filteredNames) && count($filteredNames) == count($names)) {

                    DB::beginTransaction();

                    try {
                        foreach ($filteredNames as $key => $name) {
                            RequiredDocument::create([
                                'role_id' => $role->id,
                                'name' => $name,
                                'description' => $request->document_description[$key] ?? '',
                                'sequence' => $key,
                                'allow_only_specific_file_format' => isset($request->allow_only_specific_file_format[$key]) && $request->allow_only_specific_file_format[$key] == 'on' ? true : false,
                                'allowed_file' => isset($request->doc_type[$key]) ? implode(',', $request->doc_type[$key]) : null,
                                'maximum_upload_count' => $request->doc_max_file_count[$key] ?? 1,
                                'maximum_upload_size' => $request->doc_max_file_size[$key] ?? 10485760,
                                'is_required' => isset($request->is_required[$key]) && $request->is_required[$key] == 'on' ? true : false
                            ]);
                        }

                        DB::commit();
                        return redirect()->route('roles.index')->with('success', 'Documents set successfully.');

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Helper::logger($e->getMessage());
                        return redirect()->back()->with('error', Helper::$errorMessage);
                    }

                }
            }

            return redirect()->back()->with('error', Helper::$errorMessage);
        } else if (RequiredDocument::where('role_id', decrypt($id))->exists() && $role) {

            $names = $request->document_name;
            $shouldKeep = [];

            if (is_array($names)) {
                $filteredNames = array_filter($request->document_name);
                if (is_countable($filteredNames) && count($filteredNames) == count($names)) {

                    DB::beginTransaction();

                    try {
                        $sequence = 0;

                        foreach ($filteredNames as $key => $name) {
                            if (isset($request->id[$key]) && RequiredDocument::where('id', $request->id[$key])->exists()) {
                                RequiredDocument::where('id', $request->id[$key])->update([
                                    'name' => $name,
                                    'description' => $request->document_description[$key] ?? '',
                                    'sequence' => $sequence,
                                    'allow_only_specific_file_format' => isset($request->allow_only_specific_file_format[$key]) && $request->allow_only_specific_file_format[$key] == 'on' ? true : false,
                                    'allowed_file' => isset($request->doc_type[$key]) ? implode(',', $request->doc_type[$key]) : null,
                                    'maximum_upload_count' => $request->doc_max_file_count[$key] ?? 1,
                                    'maximum_upload_size' => $request->doc_max_file_size[$key] ?? 10485760,
                                    'is_required' => isset($request->is_required[$key]) && $request->is_required[$key] == 'on' ? true : false
                                ]);

                                $shouldKeep[] = $request->id[$key];
                            } else {
                                $shouldKeep[] = RequiredDocument::create([
                                    'role_id' => $role->id,
                                    'name' => $name,
                                    'description' => $request->document_description[$key] ?? '',
                                    'sequence' => $sequence,
                                    'allow_only_specific_file_format' => isset($request->allow_only_specific_file_format[$key]) && $request->allow_only_specific_file_format[$key] == 'on' ? true : false,
                                    'allowed_file' => isset($request->doc_type[$key]) ? implode(',', $request->doc_type[$key]) : null,
                                    'maximum_upload_count' => $request->doc_max_file_count[$key] ?? 1,
                                    'maximum_upload_size' => $request->doc_max_file_size[$key] ?? 10485760,
                                    'is_required' => isset($request->is_required[$key]) && $request->is_required[$key] == 'on' ? true : false
                                ])->id;
                            }

                            $sequence++;
                        }

                        RequiredDocument::where('role_id', $role->id)->whereNotIn('id', $shouldKeep)->delete();

                        DB::commit();
                        return redirect()->route('roles.index')->with('success', 'Documents set successfully.');

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Helper::logger($e->getMessage() . " LINE NO : " .  $e->getLine());
                        return redirect()->back()->with('error', Helper::$errorMessage);
                    }

                }
            }
            return redirect()->back()->with('error', Helper::$errorMessage);
        } else {
            return redirect()->back()->with('error', Helper::$notFound);
        }
    }
}
