<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = 'Users';
    }

    public function index()
    {
        $moduleName = $this->moduleName;
        $roles = Role::active()->exceptSuperAdmin()->get();

        return view('users.index', compact('moduleName', 'roles'));
    }

    public function DataTable(Request $request)
    {
        $users = User::with(['roles', 'addedby', 'updatedby'])->whereHas("role", function($query) {
            $query->where('roles.id', "!=", 1);
        })->select('users.*');

        if ($filterRole = $request->filterRole) {
            if ($filterRole != '') {
                $users->whereHas('roles', function($q) use($filterRole) {
                    $q->where('role_id', $filterRole);
                });
            }
        }

        if (isset($request->filterStatus)) {
            if ($request->filterStatus != '') {
                $users->where('status', $request->filterStatus);
            }
        }

        if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
            $users->orderBy('id', 'desc');
        }

        return dataTables()->eloquent($users)
            ->editColumn('addedby.name', function($user) {
                return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($user->created_at))."'>".$user->addedby->name."</span>";
            })
            ->editColumn('updatedby.name', function($user) {
                if ($user->updatedby->name != '-') {
                    return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($user->updated_at))."'>".$user->updatedby->name."</span>";
                } else {
                    return $user->updatedby->name;
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
                if (auth()->user()->hasPermission("users.edit")) {
                    $url = route("users.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("users.view")) {
                    $url = route("users.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("users.activeinactive")) {
                    $url = route("users.activeinactive", encrypt($variable->id));
                    $action .= view('buttons.status', compact('variable', 'url')); 
                }
                if (auth()->user()->hasPermission("users.delete")) {
                    $url = route("users.delete", encrypt($variable->id));
                    $action .= view('buttons.delete', compact('variable', 'url')); 
                }
                $action .= '</div>';

                return $action;
            })
            ->editColumn("status",function($users) {
                if ($users->status == 1) {
                    return "<span class='badge bg-success'>Active</span>";
                } else {
                    return "<span class='badge bg-danger'>InActive</span>";
                }
            })
            ->rawColumns(['action', 'status', 'role.name', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        $moduleName = $this->moduleName;
        $roles = Role::active()->exceptSuperAdmin()->get();
        $url = url('/');

        return view('users.create', compact('moduleName', 'roles'));
    }

    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->added_by = Auth()->User()->id;
            $user->save();
            $user->roles()->attach($request->role);

            DB::commit();

            return response()->json(['success' => $this->moduleName.' Added Successfully.', 'status' => 200]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 500]);
        }
    }

    public function edit($id)
    {
        $user = User::with('roles')->where('id', decrypt($id))->first();
        $roles = Role::active()->exceptSuperAdmin()->get();
        
        return view('users.edit', compact('moduleName', 'user', 'roles'));
    }

    public function update(UserRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::find(decrypt($request->id));
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password =  isset($request->password) ? Hash::make($request->password) : $user->password;
            $user->updated_by = Auth()->User()->id;
            $user->save();
            $user->roles()->sync($request->role);

            DB::commit();
    
            return response()->json(['success' => $this->moduleName.' Updated Successfully.', 'status' => 200]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 500]);
        }
    }

    public function show($id)
    {
        $data['user'] = User::with('roles')->where('id', decrypt($id))->get();
        $data['roles'] = Role::active()->get();
        
        return response()->json($data);
    }

    public function destroy($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->roles()->detach();
            $user->delete();
            return response()->json(['success' => $this->moduleName.' Deleted Successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 500]);
        }
    }

    public function status($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();

            if ($user->status == 1) {
                return response()->json(['success' => $this->moduleName.' activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => $this->moduleName.' deactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'status' => 500]);
        }        
    }

    public function checkUserPhoneNumber(Request $request)
    {
        if (!isset($request->uid)) {
            $user = User::where('phone', $request->phone)->first();
        } else {
            $user = User::where('phone', $request->phone)->where('id', '!=', decrypt($request->uid))->first();
        }
        if ($user) {
            return response()->json(false);
        } else {
            return response()->json(true);
        }
    }
}
