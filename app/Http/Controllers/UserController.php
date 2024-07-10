<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UserRequest;
use App\Models\UserPermission;
use App\Models\PermissionRole;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\AddressLog;
use App\Models\Setting;
use App\Models\Deliver;
use App\Models\Country;
use App\Helpers\Helper;
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
            $roles = Role::where('id', '!=', '4')->get();

            return view('users.index', compact('moduleName', 'roles'));
        }

        $users = User::with(['roles', 'addedby', 'updatedby'])->whereHas('role', function ($builder) {
            $builder->where('roles.id', '!=', '4');
        });

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
                if (auth()->user()->hasPermission("users.edit")) {
                    $url = route("users.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("users.view")) {
                    $url = route("users.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("users.activeinactive")) {
                    if ($users->id !== auth()->user()->id) {
                        $url = route("users.activeinactive", encrypt($variable->id));
                        $action .= view('buttons.status', compact('variable', 'url'));
                    }
                }
                if (auth()->user()->hasPermission("users.delete")) {
                    if ($users->id !== auth()->user()->id) {
                        $url = route("users.delete", encrypt($variable->id));
                        $action .= view('buttons.delete', compact('variable', 'url'));
                    }
                }
                $action .= '</div>';

                return $action;
            })
            ->editColumn("status",function($users) {
                if ($users->status == 1) {
                    return "<span class='badge bg-success'>Active</span>";
                } else {
                    return "<span class='badge bg-danger'>Inactive</span>";
                }
            })
            ->rawColumns(['action', 'status', 'role.name', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        $moduleName = 'User';
        $moduleLink = route('users.index');
        $roles = Role::active()->where('id', '!=', '4')->get();
        $countries = Helper::getCountriesOrderBy();

        $permission = auth()->user()->roles->pluck('id')->toArray();
        $permission = PermissionRole::whereIn('role_id', $permission)->select('permission_id')->pluck('permission_id')->toArray();

        $userPermission = UserPermission::where('user_id', auth()->user()->id)->select('permission_id')->pluck('permission_id')->toArray();
        $permission = array_unique(array_merge($userPermission, $permission));
        array_push($permission, 45);

        $permission = Permission::whereIn('id', $permission)->get()->groupBy('model');

        return view('users.create', compact('moduleName', 'roles', 'countries', 'permission','moduleLink'));
    }

    public function store(UserRequest $request)
    {
        DB::beginTransaction();

        try {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->address_line_1 = $request->address_line_1;
            $user->phone = $request->phone;
            $user->country_dial_code = $request->country_dial_code;
            $user->country_iso_code = $request->country_iso_code;
            $user->country_id = $request->country;
            $user->city_id = $request->city;
            $user->postal_code = $request->postal_code;
            $user->added_by = auth()->user()->id;
            $user->save();

            $perm = $request->permission;

            if ($request->role == '3' && !empty($perm)) {
                $perm = array_diff($perm, Permission::select('id')->where('model', 'SalesOrderStatus')->pluck('id')->toArray());
            }

            $user->roles()->attach($request->role);
            $user->userpermission()->attach($perm);

            $errorWhileSavingLatLong = false;

            if ($request->role == '3') {
                $errorWhileSavingLatLong = true;

                $key = trim(Setting::first()?->geocode_key);

                if (env('GEOLOCATION_API') == 'true') {
                    if (!empty($key)) {
                        $address = trim("{$user->address_line_1} {$user->city_id} {$user->postal_code} {$user->country_id}");
                        $address = str_replace(' ', '+', $address);
                        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$key}";

                        $data = json_decode(file_get_contents($url), true);

                        if ($data['status'] == "OK") {
                            $lat = $data['results'][0]['geometry']['location']['lat'];
                            $long = $data['results'][0]['geometry']['location']['lng'];

                            if (!empty($lat)) {
                                $u = User::find($user->id);
                                $u->lat = $lat;
                                $u->long = $long;
                                $u->save();

                                $errorWhileSavingLatLong = false;
                            }

                            AddressLog::create([
                                'city' => $user->city_id,
                                'country' => Country::where('id', $user->country_id)->first()->name ?? $user->country_id,
                                'postal_code' => $user->postal_code,
                                'address' => $user->address_line_1,
                                'lat' => $lat,
                                'long' => $long,
                                'user_id' => $user->id,
                                'added_by' => auth()->user()->id,
                            ]);
                        }
                    }
                } else {

                    $u = User::find($user->id);
                    $u->lat = '22.2735381';
                    $u->long = '70.764107';
                    $u->save();

                    $errorWhileSavingLatLong = false;
                }
            }

            DB::commit();

            if ($errorWhileSavingLatLong === false) {
                return redirect()->route('users.index')->with('success', 'User added successfully.');
            } else {
                return redirect()->route('users.edit', encrypt($user->id))->with('warning', 'Please provide accurate address.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logger("User Add: " . $e->getMessage() . " on Line no : " . $e->getLine());
            return redirect()->back()->with(['error' => Helper::$errorMessage]);
        }
    }

    public function edit($id)
    {
        $moduleName = 'User';
        $moduleLink = route('users.index');
        $user = User::with('roles')->where('id', decrypt($id))->first();
        $roles = Role::active()->where('id', '!=', '4')->get();
        $countries = Helper::getCountriesOrderBy();
        $states = State::active()->where('country_id', $user->country_id)->select('id', 'name')->pluck('name', 'id')->toArray();
        $cities = City::active()->where('state_id', $user->state_id)->select('id', 'name')->pluck('name', 'id')->toArray();

        $permission = PermissionRole::where('role_id', $user->roles->first()->id)->select('permission_id')->pluck('permission_id')->toArray() ?? [];

        array_push($permission, 45);
        $permission = Permission::whereIn('id', $permission)->get()->groupBy('model');

        if (in_array(1, $user->roles->pluck('id')->toArray())) {
            $userPermissions = Permission::select('id')->pluck('id')->toArray();
        } else {
            $userPermissions = UserPermission::where('user_id', $user->id)->select('permission_id')->pluck('permission_id')->toArray();
        }

        return view('users.edit', compact('moduleName', 'user', 'roles', 'countries', 'states', 'cities', 'id', 'userPermissions', 'permission','moduleLink'));
    }

    public static function addressChanged ($user, $country, $city, $postalcode, $address) {
        if (trim($user->country_id) !== trim($country)) {
           return true;
        }

        if (trim($user->city_id) !== trim($city)) {
            return true;
        }

        if (trim($user->postal_code) !== trim($postalcode)) {
            return true;
        }

        if (trim($user->address_line_1) !== trim($address)) {
            return true;
        }

         return false;
    }

    public function update(UserRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $user = User::find(decrypt($id));

            if (in_array('3', $user->roles->pluck('id')->toArray()) && $request->role != '3') {

                if (Deliver::where('user_id', $user->id)->where('status', 1)->exists()) {
                    DB::rollBack();
                    return redirect()->route('users.edit', $id)->with('warning', 'Can\'t change role for this user at the moment.');
                } else {

                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->phone = $request->phone;
                    $user->country_dial_code = $request->country_dial_code;
                    $user->country_iso_code = $request->country_iso_code;
                    $user->country_id = $request->country;
                    $user->city_id = $request->city;
                    $user->address_line_1 = $request->address_line_1;
                    $user->postal_code = $request->postal_code;
                    $user->password =  !empty(trim($request->password)) ? Hash::make($request->password) : $user->password;
                    $user->updated_by = auth()->user()->id;
                    $user->save();

                    $user->roles()->sync($request->role);
                    $user->userpermission()->sync($request->permission);

                    DB::commit();

                    return redirect()->route('users.index')->with('success', 'User updated successfully.');

                }

            } else {

                $addressChanged = self::addressChanged($user, $request->country, $request->city, $request->postal_code, $request->address_line_1);
                $errorWhileSavingLatLong = true;
                $notDriver = false;

                if (!in_array('3', $user->roles->pluck('id')->toArray()) && $request->role != '3') {
                    $notDriver = true;
                }

                if ((!in_array('3', $user->roles->pluck('id')->toArray()) && $request->role == '3') || $request->role == '3' && $addressChanged) {
                    $key = trim(Setting::first()?->geocode_key);

                    if (env('GEOLOCATION_API') == 'true') {
                        if (!empty($key)) {
                            $address = trim("{$request->address_line_1} {$request->city} {$request->postal_code} {$request->country}");
                            $address = str_replace(' ', '+', $address);
                            $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$key}";
    
                            $data = json_decode(file_get_contents($url), true);
    
                            if ($data['status'] == "OK") {
                                $lat = $data['results'][0]['geometry']['location']['lat'];
                                $long = $data['results'][0]['geometry']['location']['lng'];
    
                                if (!empty($lat)) {
                                    $u = User::find($user->id);
                                    $u->lat = $lat;
                                    $u->long = $long;
                                    $u->save();
    
                                    $errorWhileSavingLatLong = false;
                                }
    
                                AddressLog::create([
                                    'city' => $user->city_id,
                                    'country' => Country::where('id', $user->country_id)->first()->name ?? $user->country_id,
                                    'postal_code' => $user->postal_code,
                                    'address' => $user->address_line_1,
                                    'lat' => $lat,
                                    'long' => $long,
                                    'user_id' => $user->id,
                                    'added_by' => auth()->user()->id,
                                ]);
                            }
                        }
                    } else {
                        $lat = '22.2735381';
                        $long = '70.764107';
    
                        $errorWhileSavingLatLong = false;
                    }

                } else if ($request->role == '3' && $addressChanged === false) {
                    $errorWhileSavingLatLong = false;
                }

                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->country_dial_code = $request->country_dial_code;
                $user->country_iso_code = $request->country_iso_code;
                $user->country_id = $request->country;
                $user->city_id = $request->city;
                $user->address_line_1 = $request->address_line_1;
                $user->postal_code = $request->postal_code;
                $user->password =  !empty(trim($request->password)) ? Hash::make($request->password) : $user->password;
                $user->updated_by = auth()->user()->id;
                $user->save();

                $perm = $request->permission;

                if ($request->role == '3' && !empty($perm)) {
                    $perm = array_diff($perm, Permission::select('id')->where('model', 'SalesOrderStatus')->pluck('id')->toArray());
                }

                $user->roles()->sync($request->role);
                $user->userpermission()->sync($perm);

                DB::commit();

                if ($errorWhileSavingLatLong === false) {
                    return redirect()->route('users.index')->with('success', 'User updated successfully.');
                } else {
                    if ($notDriver) {
                        return redirect()->route('users.index')->with('success', 'User updated successfully.');
                    } else {
                        return redirect()->route('users.edit', $id)->with('warning', 'Please provide accurate address.');
                    }
                }
            }

        } catch (\Exception $e) {
            Helper::logger("User Edit: " . $e->getMessage() . " on Line no : " . $e->getLine());
            DB::rollBack();
            return redirect()->back()->with(['error' => Helper::$errorMessage]);
        }
    }

    public function show($id)
    {
        $moduleName = 'User';
        $moduleLink = route('users.index');
        $user = User::with('roles')->where('id', decrypt($id))->first();
        $roles = Role::active()->get();

        $permission = PermissionRole::where('role_id', $user->roles->first()->id)->select('permission_id')->pluck('permission_id')->toArray() ?? [];

        array_push($permission, 45);
        $permission = Permission::whereIn('id', $permission)->get()->groupBy('model');

        if (in_array(1, $user->roles->pluck('id')->toArray())) {
            $userPermissions = Permission::select('id')->pluck('id')->toArray();
        } else {
            $userPermissions = UserPermission::where('user_id', $user->id)->select('permission_id')->pluck('permission_id')->toArray();
        }

        return view('users.view', compact('moduleName', 'user', 'roles', 'userPermissions', 'permission','moduleLink'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $user = User::find(decrypt($id));

            if (Deliver::where('user_id', $user->id)->whereIn('status', [0, 1])->exists()) {
                DB::rollBack();
                return response()->json(['error' => 'Can not delete this driver user.', 'status' => 500]);
            }

            UserPermission::where('user_id', $user->id)->delete();
            $user->roles()->detach();
            $user->userpermission()->detach();
            $user->delete();

            DB::commit();
            return response()->json(['success' => 'User deleted successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function status($id)
    {
        try {
            $user = User::find(decrypt($id));

            if (Deliver::where('user_id', $user->id)->where('status', [0, 1])->exists()) {
                return response()->json(['error' => 'Can not inactive this driver user at the moment.', 'status' => 500]);
            }

            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();

            if ($user->status == 1) {
                return response()->json(['success' => 'User activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'User inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function checkUserEmail(Request $request)
    {

        $user = User::where('email', trim($request->email));
        if(isset($request->role_id) && $request->role_id !="") {

            $user->whereHas('role', function ($q)use($request) {
                $q->where('roles.id', $request->role_id);
            });
        }
        if ($request->has('id') && !empty(trim($request->id))) {
            $user = $user->where('id', '!=', decrypt($request->id));
        }

        return response()->json($user->doesntExist());
    }

    public function rolePermissions(Request $request) {
        $role = Role::where('id', $request->id);

        if ($role->exists()) {

            if ($request->user) {
                if ($request->id == 1) {
                    $userPermissions = Permission::select('id')->pluck('id')->toArray();
                } else {
                    $userPermissions = UserPermission::where('user_id', $request->user)->select('permission_id')->pluck('permission_id')->toArray();
                }
            } else {
                $userPermissions = [];
            }

            $permission = PermissionRole::where('role_id', $request->id)->select('permission_id')->pluck('permission_id')->toArray() ?? [];
            $permission = Permission::whereIn('id', $permission)->get()->groupBy('model');
            $roleId = $role->first()->id ?? 0;

            return response()->json(['status' => true, 'html' => view('users.permissions', compact('permission', 'userPermissions', 'roleId'))->render()]);
        }

        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }

    public function register(Request $request, $role, $uid = 1) {
        try {

            if ($request->method() == 'GET') {

                if ($uid == 1) {
                    $uid = encrypt(1);
                }

                $url = url("register/{$role}/{$uid}");
                $countries = Helper::getCountriesOrderBy();

                if (Role::where('id', decrypt($role))->active()->doesntExist()) {
                    return redirect()->route('login')->with('error', 'This link is not valid for registration.');
                }


                return view('auth.register', compact('url', 'countries'));
            } else if ($request->method() == 'POST') {

                try {
                    $role = decrypt($role);

                    if (Role::find($role) !== null && $role != 1 && Role::where('id', $role)->active()->exists()) {
                        $this->validate($request, [
                            'name' => 'required',
                            'email' => "required|email|unique:users,email,NULL,id,deleted_at,NULL",
                            'password' => 'required|min:8|max:16',
                            'confirm_password' => 'same:password',
                            'country' => 'required',
                            'city' => 'required',
                            'postal_code' => 'required|max:8'
                        ], [
                            'name.required'        => 'Name is required.',
                            'email.required'       => 'Email is required.',
                            'email.email'          => 'Email format is invalid.',
                            'email.unique'         => 'This email is already exists.',
                            'password.required'    => 'Create a Password.',
                            'password.min'         => 'Minimum length should be 8 characters.',
                            'password.max'         => 'Maximum length should be 16 characters.',
                            'country.required'     => 'Select a country.',
                            'city.required'        => 'Enter city.',
                            'postal_code.required' => 'Enter postal code.',
                            'postal_code.max'      => 'Maximum 8 characters allowed for postal code.'
                        ]);

                            $user = new User();
                            $user->name = $request->name;
                            $user->email = $request->email;
                            $user->phone = $request->phone;
                            $user->country_dial_code = $request->country_dial_code;
                            $user->country_iso_code = $request->country_iso_code;
                            $user->password = Hash::make($request->password);
                            $user->country_id = $request->country;
                            $user->city_id = $request->city;
                            $user->postal_code = $request->postal_code;
                            $user->added_by = decrypt($uid);
                            $user->save();

                            $user->roles()->attach([$role]);

                            if (auth()->check()) {
                                auth()->logout();
                            }

                            session()->flush();
                            $authenticate = auth()->attempt(['email' => $request->email, 'password' => $request->password]);
                    } else {
                        return redirect()->route('login')->with('error', 'This link is not valid for registration.');
                    }
                } catch (\Exception $e) {
                    return redirect()->route('login')->with('error', 'This link is not valid for registration.');
                }

                    if ($authenticate) {
                        return redirect()->intended('dashboard');
                    } else {
                        return redirect()->route('login')->with('success', 'Registration was successful.');
                    }

            } else {
                return redirect()->route('login');
            }
        } catch(\Exception $e) {
            Helper::logger($e->getMessage() . ' ' . $e->getLine());
            $response = redirect()->route('login');

            if (!auth()->check()) {
                return $response->with('error', 'This link is not valid for registration.');
            }

            return $response;
        }
    }
}
