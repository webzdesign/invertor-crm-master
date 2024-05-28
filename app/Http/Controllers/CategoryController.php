<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Helpers\Helper;
use App\Models\ProcurementCost;
use App\Models\Product;

class CategoryController extends Controller
{
    protected $moduleName = 'Categories';

    public function index(Request $request) {
        if (!$request->ajax()) {
            $moduleName = $this->moduleName;
            return view('categories.index', compact('moduleName'));
        }

        $categories = Category::query();

        if (isset($request->filterStatus)) {
            if ($request->filterStatus != '') {
                $categories->where('status', $request->filterStatus);
            }
        }

        return dataTables()->eloquent($categories)
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
            ->addColumn('action', function ($users) {
                $variable = $users;

                $action = "";
                $action .= '<div class="whiteSpace">';
                if (auth()->user()->hasPermission("categories.edit")) {
                    $url = route("categories.edit", encrypt($variable->id));
                    $action .= view('buttons.edit', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("categories.view")) {
                    $url = route("categories.view", encrypt($variable->id));
                    $action .= view('buttons.view', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("categories.activeinactive")) {
                    $url = route("categories.activeinactive", encrypt($variable->id));
                    $action .= view('buttons.status', compact('variable', 'url'));
                }
                if (auth()->user()->hasPermission("categories.delete")) {
                    $url = route("categories.delete", encrypt($variable->id));
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
            ->rawColumns(['action', 'status', 'addedby.name', 'updatedby.name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function checkCategory(Request $request) {
        $category = Category::where('slug', Helper::slug(trim($request->name)));

        if ($request->has('id') && !empty(trim($request->id))) {
            $category = $category->where('id', '!=', decrypt($request->id));
        }

        return response()->json($category->doesntExist());
    }

    public function create(Request $request) {
        $moduleName = 'Category';
        $moduleLink = route('categories.index');
        return view('categories.create', compact('moduleName','moduleLink'));
    }

    public function store(CategoryRequest $request)
    {
        $user = new Category();
        $user->name = $request->name;
        $user->slug = Helper::slug($request->name);
        $user->added_by = auth()->user()->id;
        $user->save();

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function edit($id)
    {
        $moduleName = 'Category';
        $moduleLink = route('categories.index');
        $category = Category::where('id', decrypt($id))->first();

        return view('categories.edit', compact('moduleName', 'id', 'category','moduleLink'));
    }

    public function update(CategoryRequest $request, $id)
    {
        $user = Category::find(decrypt($id));
        $user->name = $request->name;
        $user->slug = Helper::slug($request->name);
        $user->updated_by = auth()->user()->id;
        $user->save();

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function show($id)
    {
        $moduleName = 'Category';
        $moduleLink = route('categories.index');
        $category = Category::where('id', decrypt($id))->first();

        return view('categories.view', compact('moduleName', 'category','moduleLink'));
    }

    public function destroy($id)
    {
        $category = Category::find(decrypt($id));

        if (Product::where('category_id', $category->id)->exists()) {
            return response()->json(['error' => 'This category contains some products.', 'status' => 500]);
        }

        if ($category->delete()) {
            ProcurementCost::where('category_id', decrypt($id))->delete();
            return response()->json(['success' => 'Category deleted successfully.', 'status' => 200]);
        } else {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }

    public function status($id)
    {
        try {
            $user = Category::find(decrypt($id));
            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();

            if ($user->status == 1) {
                return response()->json(['success' => 'Category activated successfully.', 'status' => 200]);
            } else {
                return response()->json(['success' => 'Category inactivated successfully.', 'status' => 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => Helper::$errorMessage, 'status' => 500]);
        }
    }
}
