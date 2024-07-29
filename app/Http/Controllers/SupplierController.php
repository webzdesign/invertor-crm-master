<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\Helper;
use App\Models\User;

class SupplierController extends Controller
{
    protected $moduleName = 'Suppliers';

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;

            return view('suppliers.index', compact('moduleName'));
        }

        $users = User::with(['addedby', 'updatedby'])->whereHas('role', function ($builder) {
            $builder->where('roles.id', 4);
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
            ->addColumn('action', function ($users) {

                $variable = $users;

                $action = "";
                $action .= '<div class="d-flex align-items-center justify-content-center">';
                if (auth()->user()->hasPermission("suppliers.edit")) {
                    $url = route("suppliers.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("suppliers.view")) {
                    $url = route("suppliers.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("suppliers.activeinactive")) {
                    if ($users->id !== auth()->user()->id) {
                        $url = route("suppliers.activeinactive", encrypt($variable->id));
                        $action .= view('buttons.status', compact('variable', 'url'));
                    }
                }
                if (auth()->user()->hasPermission("suppliers.delete")) {
                    if ($users->id !== auth()->user()->id) {
                        $url = route("suppliers.delete", encrypt($variable->id));
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
        $moduleName = 'Supplier';
        $moduleLink = route('suppliers.index');
        $countries = Helper::getCountriesOrderBy();

        return view('suppliers.create', compact('moduleName', 'countries','moduleLink'));
    }

    public function store(SupplierRequest $request)
    {
        DB::beginTransaction();

        try {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make(Str::random(8));
            $user->phone = $request->phone;
            $user->country_dial_code = $request->country_dial_code;
            $user->country_iso_code = $request->country_iso_code;
            $user->country_id = $request->country;
            $user->postal_code = $request->postal_code;
            $user->added_by = auth()->user()->id;
            $user->save();

            $user->roles()->attach(4);
            DB::commit();
            return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logger($e->getMessage(), 'critical');
            return redirect()->back()->with(['error' => Helper::$errorMessage]);
        }
    }

    public function edit($id)
    {
        $moduleName = 'Supplier';
        $moduleLink = route('suppliers.index');
        $user = User::where('id', decrypt($id))->first();

        $countries = Helper::getCountriesOrderBy();

        return view('suppliers.edit', compact('moduleName', 'user', 'countries', 'id','moduleLink'));
    }

    public function update(SupplierRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $user = User::find(decrypt($id));
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->country_dial_code = $request->country_dial_code;
            $user->country_iso_code = $request->country_iso_code;
            $user->country_id = $request->country;
            $user->postal_code = $request->postal_code;
            $user->updated_by = auth()->user()->id;
            $user->save();

            DB::commit();
            return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => Helper::$errorMessage]);
        }
    }

    public function show($id)
    {
        $moduleName = 'Supplier';
        $moduleLink = route('suppliers.index');
        $user = User::where('id', decrypt($id))->first();

        return view('suppliers.view', compact('moduleName', 'user','moduleLink'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $user = User::find(decrypt($id));
            $user->roles()->detach();
            $user->delete();

            DB::commit();
            return response()->json(['success' => 'Supplier deleted successfully.', 'status' => 200]);
        } catch (\Exception $e) {
            DB::rollBack();
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
                return response()->json(['success' => 'Supplier activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Supplier inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
