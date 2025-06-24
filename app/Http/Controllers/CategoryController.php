<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\CategoryFilterOptions;
use App\Models\CategoryFilters;
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
                $action .= '<div class="d-flex align-items-center justify-content-center">';
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
        $selection_name = $request->input('seclection_name');
        $selection = $request->input('selection');
        $selection_value = $request->input('value');

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Helper::slug($request->name);
        $category->added_by = auth()->user()->id;
        $category->save();

        if(isset($category->id) && !empty($category->id)) {
            
            foreach ($selection_name as $key => $Sname) {
                if(!empty($Sname)) {                 
                    $category_filters = new CategoryFilters();
                
                    $category_filters->category_id = $category->id;
                    $category_filters->name = $Sname;
                    
                    if(isset($selection[$key]) && !empty($selection[$key])) {
                        $category_filters->selection = $selection[$key];
                    }

                    $category_filters->save();
                
                    if(!empty($category_filters->id)) {

                        $cat_filter_last_id = $category_filters->id;

                        if(isset($selection_value[$key]) && !empty($selection_value[$key])) {

                            foreach ($selection_value[$key] as $Svalue) {
                                $category_filters_options = new CategoryFilterOptions();
                                
                                $category_filters_options->category_filter_id = $cat_filter_last_id;
                                
                                if(isset($Svalue) && !empty($Svalue)) {
                                    $category_filters_options->value = $Svalue;
                                }
                                
                                $category_filters_options->save();

                            }
                        }
                    }
                }
            } 
        }

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function edit($id)
    {
        $moduleName = 'Category';
        $moduleLink = route('categories.index');
        $category = Category::with(['filters.options'])->where('id', decrypt($id))->first();
    
        return view('categories.edit', compact('moduleName', 'id', 'category','moduleLink'));
    }

    public function update(CategoryRequest $request, $id)
    {
        // echo '<pre>';
        // print_r($request->all());
        // exit;
        $category = Category::find(decrypt($id));
        $category->name = $request->name;
        $category->slug = Helper::slug($request->name);
        $category->updated_by = auth()->user()->id;
        $category->save();
        
        $selection_filter_id = $request->input('selection_filter_id');
        $selection_name = $request->input('seclection_name');
        $selection = $request->input('selection');
        $selection_value = $request->input('value');
        $filter_options_value_id = $request->input('filter_options_value_id');
        $deleted_selection_id = $request->input('deleted_selection_id');
        $deleted_values_id = $request->input('deleted_values_id');

        if(isset($category->id) && !empty($category->id)) {
            
            if (!empty($deleted_selection_id)) {
                $selection_Delete_ids = array_filter(explode(',', $deleted_selection_id));
                if (!empty($selection_Delete_ids)) {
                    CategoryFilters::whereIn('id', $selection_Delete_ids)->delete();
                }
            }

            if (!empty($deleted_values_id)) {
                $Delete_ids = array_filter(explode(',', $deleted_values_id));
                if (!empty($Delete_ids)) {
                    CategoryFilterOptions::whereIn('id', $Delete_ids)->delete();
                }
            }

            foreach ($selection_name as $key => $Sname) {
                if(isset($selection_filter_id[$key]) && !empty($selection_filter_id[$key])) {

                    $category_filters = CategoryFilters::find($selection_filter_id[$key]);

                    if(!empty($category_filters) && count($category_filters->toArray()) > 0) {
                        $category_filters->name = $Sname;
                        if(isset($selection[$key]) && !empty($selection[$key])) {
                            $category_filters->selection = $selection[$key];
                        } else {
                            $category_filters->selection = 0;
                        }

                        $category_filters->update();
                    
                        if(!empty($category_filters->id)) {

                            if(isset($selection_value[$key]) && !empty($selection_value[$key])) {
                                foreach ($selection_value[$key] as $valueKey => $Svalue) {
                                    if(isset($filter_options_value_id[$key][$valueKey]) && !empty($filter_options_value_id[$key][$valueKey])) {
                                        $category_filters_options = CategoryFilterOptions::find($filter_options_value_id[$key][$valueKey]);

                                        if(!empty($category_filters_options) && count($category_filters_options->toArray()) > 0) {                                            
                                            if(isset($Svalue) && !empty($Svalue)) {
                                                $category_filters_options->value = $Svalue;
                                            }   
                                        }
                                        $category_filters_options->update();
                                    } else {
                                        $category_filters_options = new CategoryFilterOptions();
                                
                                        if(isset($category_filters->id) && !empty(isset($category_filters->id))) {
                                            $filter_last_id = $category_filters->id;
                                        } else {
                                            $filter_last_id = $selection_filter_id[$key][$valueKey];
                                        }

                                        $category_filters_options->category_filter_id = $filter_last_id;
                                        
                                        if(isset($Svalue) && !empty($Svalue)) {
                                            $category_filters_options->value = $Svalue;
                                        }
                                        
                                        $category_filters_options->save();
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if(isset($Sname) && !empty($Sname)) {
                        if(!isset($selection_filter_id[$key]) && isset($category->id)) {
                            $category_filters = new CategoryFilters();
                            
                            $category_filters->category_id = $category->id;
                            $category_filters->name = $Sname;
                            
                            if(isset($selection[$key]) && !empty($selection[$key])) {
                                $category_filters->selection = $selection[$key];
                            }
                            
                            $category_filters->save();
                        }
                        if(isset($selection_value[$key]) && !empty($selection_value[$key])) {
                            foreach ($selection_value[$key] as $valueKey => $Svalue) {
                                $category_filters_options = new CategoryFilterOptions();
                                
                                if(isset($category_filters->id) && !empty(isset($category_filters->id))) {
                                    $filter_last_id = $category_filters->id;
                                } else {
                                    $filter_last_id = $selection_filter_id[$key][$valueKey];
                                }
                                
                                $category_filters_options->category_filter_id = $filter_last_id;
                                
                                if(isset($Svalue) && !empty($Svalue)) {
                                    $category_filters_options->value = $Svalue;
                                }
                                
                                $category_filters_options->save();
                            }
                        }
                    }
                }
            } 
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function show($id)
    {
        $moduleName = 'Category';
        $moduleLink = route('categories.index');
        $category = Category::with(['filters.options'])->where('id', decrypt($id))->first();
        
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
