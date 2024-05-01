<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    protected $moduleName = 'Users';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            $roles = Role::active()->get();
    
            return view('users.index', compact('moduleName', 'roles'));
        }

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
        $moduleName = 'User';
        $roles = Role::active()->get();
        $countries = Country::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $url = url('/');

        return view('users.create', compact('moduleName', 'roles', 'countries'));
    }

    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->address_line_1 = $request->address_line_1;
            $user->address_line_2 = $request->address_line_2;
            $user->country_id = $request->country;
            $user->state_id = $request->state;
            $user->city_id = $request->city;
            $user->added_by = auth()->user()->id;
            $user->save();
            $user->roles()->attach($request->role);

            DB::commit();

            return redirect()->route('users.index')->with('success', 'User Created successfully.');

        } catch (\Exception $e) {
            Helper::logger($e->getMessage(), 'critical');
            return redirect()->back()->with(['error' => Helper::$errorMessage]);
        }
    }

    public function edit($id)
    {
        $moduleName = 'User';
        $user = User::with('roles')->where('id', decrypt($id))->first();
        $roles = Role::active()->get();
        $countries = Country::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $states = State::active()->where('country_id', $user->country_id)->select('id', 'name')->pluck('name', 'id')->toArray();
        $cities = City::active()->where('state_id', $user->state_id)->select('id', 'name')->pluck('name', 'id')->toArray();

        return view('users.edit', compact('moduleName', 'user', 'roles', 'countries', 'states', 'cities', 'id'));
    }

    public function update(UserRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = User::find(decrypt($id));
            $user->name = $request->name;
            $user->email = $request->email;
            $user->country_id = $request->country;
            $user->state_id = $request->state;
            $user->city_id = $request->city;
            $user->address_line_1 = $request->address_line_1;
            $user->address_line_2 = $request->address_line_2;
            $user->password =  !empty(trim($request->password)) ? Hash::make($request->password) : $user->password;
            $user->updated_by = auth()->user()->id;
            $user->save();
            $user->roles()->sync($request->role);

            DB::commit();
    
            return redirect()->route('users.index')->with('success', 'User Updated successfully.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => Helper::$errorMessage]);
        }
    }

    public function show($id)
    {
        $moduleName = 'User';
        $user = User::with('roles')->where('id', decrypt($id))->first();
        $roles = Role::active()->get();

        return view('users.view', compact('moduleName', 'user', 'roles'));
    }

    public function destroy($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->roles()->detach();
            $user->delete();
            return response()->json(['success' => $this->moduleName.' Deleted Successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
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
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }        
    }

    public function checkUserEmail(Request $request)
    {
        $user = User::where('email', trim($request->email));

        if ($request->has('id') && !empty(trim($request->id))) {
            $user = $user->where('id', '!=', decrypt($request->id));
        }

        return response()->json($user->doesntExist());
    }
}
