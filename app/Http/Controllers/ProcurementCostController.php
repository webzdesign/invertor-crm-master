<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcurementCostRequest;
use App\Models\ProcurementCost;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Helpers\Helper;
use App\Models\Role;

class ProcurementCostController extends Controller
{
    protected $moduleName = 'Procurement Cost';

    public function index(Request $request) {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            $categories = Category::select('id', 'name')->pluck('name', 'id')->toArray();
            $roles = Role::select('id', 'name')->pluck('name', 'id')->toArray();

            return view('p-cost.index', compact('moduleName', 'categories', 'roles'));
        }

        $costs = ProcurementCost::query();

        if (isset($request->filterStatus)) {
            if ($request->filterStatus != '') {
                $costs->where('status', $request->filterStatus);
            }
        }

        if (isset($request->filterProduct)) {
            if ($request->filterProduct != '') {
                $costs->where('product_id', $request->filterProduct);
            }
        }

        if (isset($request->filterCategory)) {
            if ($request->filterCategory != '') {
                $costs->where('category_id', $request->filterCategory);
            }
        }

        if (isset($request->filterRole)) {
            if ($request->filterRole != '') {
                $costs->where('role_id', $request->filterRole);
            }
        }

        if (!empty(trim($request->search['value']))) {
            $search = trim($request->search['value']);
            $costs->whereHas('product', function ($builder) use ($search) {
                $builder->where('name', 'LIKE', "%{$search}%");
            });
        }

        return dataTables()->eloquent($costs)
            ->editColumn('addedby.name', function($category) {
                return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($category->created_at))."'>".$category->addedby->name."</span>";
            })
            ->editColumn('updatedby.name', function($category) {
                if ($category->updatedby->name != '-') {
                    return "<span data-mdb-toggle='tooltip' title='".date('d-m-Y h:i:s A', strtotime($category->updated_at))."'>".$category->updatedby->name."</span>";
                } else {
                    return $category->updatedby->name;
                }
            })
            ->editColumn('product_id', function ($cost) {
                return $cost->product->name ?? '-';
            })
            ->editColumn('role_id', function ($cost) {
                return $cost->role->name ?? '-';
            })
            ->editColumn('base_price', function ($cost) {
                return Helper::currency($cost->base_price);
            })
            ->editColumn('min_sales_price', function ($cost) {
                return Helper::currency($cost->min_sales_price);
            })
            ->editColumn('default_commission_price', function ($cost) {
                return Helper::currency($cost->default_commission_price);
            })
            ->addColumn('action', function ($users) {
                $variable = $users;

                $action = "";
                $action .= '<div class="whiteSpace">';
                if (auth()->user()->hasPermission("procurement-cost.edit")) {
                    $url = route("procurement-cost.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("procurement-cost.view")) {
                    $url = route("procurement-cost.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("procurement-cost.activeinactive")) {
                    $url = route("procurement-cost.activeinactive", encrypt($variable->id));
                    $action .= view('buttons.status', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("procurement-cost.delete")) {
                    $url = route("procurement-cost.delete", encrypt($variable->id));
                    $action .= view('buttons.delete', compact('variable', 'url'));
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
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function check(Request $request)
    {
        $cost = ProcurementCost::where('product_id', trim($request->product_id))->where('role_id', trim($request->role_id));

        if ($request->has('id') && !empty(trim($request->id))) {
            $cost = $cost->where('id', '!=', decrypt($request->id));
        }

        return response()->json($cost->doesntExist());
    }

    public function create()
    {
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $roles = Role::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $moduleName = 'Procurement Cost';
        $moduleLink = route('procurement-cost.index');
        return view('p-cost.create', compact('moduleName', 'categories', 'roles','moduleLink'));
    }

    public function store(ProcurementCostRequest $request)
    {
        $user = new ProcurementCost();
        $user->product_id = $request->product;
        $user->category_id = $request->category;
        $user->role_id = $request->role;
        $user->base_price = round($request->base_price);
        $user->min_sales_price = round($request->min_sales_price);
        $user->default_commission_price = round($request->default_commission_price);
        $user->added_by = auth()->user()->id;
        $user->save();

        return redirect()->route('procurement-cost.index')->with('success', 'Procurement cost added successfully.');
    }

    public function edit(Request $request, $id)
    {
        $cost = ProcurementCost::find(decrypt($id));
        $categories = Category::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $roles = Role::active()->select('id', 'name')->pluck('name', 'id')->toArray();
        $moduleName = 'Procurement Cost';
        $moduleLink = route('procurement-cost.index');
        return view('p-cost.edit', compact('moduleName', 'categories', 'cost', 'id', 'roles','moduleLink'));
    }

    public function update(ProcurementCostRequest $request, $id)
    {
        $user = ProcurementCost::find(decrypt($id));
        $user->product_id = $request->product;
        $user->category_id = $request->category;
        $user->role_id = $request->role;
        $user->base_price = round($request->base_price);
        $user->min_sales_price = round($request->min_sales_price);
        $user->default_commission_price = round($request->default_commission_price);
        $user->updated_by = auth()->user()->id;
        $user->save();

        return redirect()->route('procurement-cost.index')->with('success', 'Procurement cost updated successfully.');
    }

    public function show(Request $request, $id)
    {
        $cost = ProcurementCost::find(decrypt($id));
        $moduleName = 'Procurement Cost';
        $moduleLink = route('procurement-cost.index');
        return view('p-cost.view', compact('moduleName', 'cost','moduleLink'));
    }

    public function destroy($id)
    {
        $cost = ProcurementCost::find(decrypt($id));

        if ($cost->delete()) {
            return response()->json(['success' => 'Procurement cost deleted Successfully.', 'status' => 200]);
        } else {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function status($id)
    {
        try {
            $cost = ProcurementCost::find(decrypt($id));
            $cost->status = $cost->status == 1 ? 0 : 1;
            $cost->save();

            if ($cost->status == 1) {
                return response()->json(['success' => 'Procurement cost activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Procurement cost inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
