<?php

namespace App\Http\Controllers;

use App\Models\DistributionAttachment;
use Illuminate\Support\Facades\DB;
use App\Models\DistributionItem;
use App\Models\Distribution;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;

class DistributionController extends Controller
{
    protected $moduleName = 'Distribution';
    protected static $types = [1 => 'Storage to Driver', 2 => 'Driver to Driver', 3 => 'Driver to Storage'];

    public function index(Request $request) {
        if (!$request->ajax()) {

            $moduleName = $this->moduleName;
            $types = self::$types;

            $drivers = User::whereHas('role', function ($builder) {
                $builder->where('roles.id', 3);
            })->selectRaw("id, CONCAT(name, ' - (', email, ')') as name")->withTrashed()->pluck('name', 'id')->toArray();

            return view('distribution.index', compact('moduleName', 'drivers', 'types'));
        }

        $distribution = Distribution::with(['items.fromdriver' => function ($builder) {
            return $builder->withTrashed();
        }, 'items.todriver' => function ($builder) {
            return $builder->withTrashed();
        }]);

        if ($request->has('filterType') && !empty(trim($request->filterType))) {
            $distribution = $distribution->where('type', trim($request->filterType));
        }

        if ($request->has('filterDriver') && !empty(trim($request->filterDriver))) {
            $driver = trim($request->filterDriver);
            $distribution = $distribution->whereHas('items', function ($builder) use ($driver) {
                $builder->where('from_driver', $driver)->orWhere('to_driver', $driver);
            });
        }

        if ($request->has('filterFrom') && !empty(trim($request->filterFrom))) {
            $distribution = $distribution->where('created_at', '>=', date('Y-m-d H:i:s', strtotime($request->filterFrom)));
        }

        if ($request->has('filterTo') && !empty(trim($request->filterTo))) {
            $distribution = $distribution->where('created_at', '<=', date('Y-m-d H:i:s', strtotime($request->filterTo)));
        }

        if (isset($request->order[0]['column']) && $request->order[0]['column'] == 0) {
            $distribution = $distribution->orderBy('id', 'desc');
        }

        return dataTables()
                ->eloquent($distribution)
                ->addColumn('type', function ($row) {
                    $html = "<strong>";

                    if ($row->type == '1') {
                        $html .= 'Storage to Driver';
                    } else if ($row->type == '2') {
                        $html .= 'Driver to Driver';
                    } else if ($row->type == '3') {
                        $html .= 'Driver to Storage';
                    }

                    $html .= "</strong>";

                    return $html;
                })
                ->addColumn('product', function ($row) {
                    $html = '<table class="table table-bordered inner-table-of-datatable" style="margin-bottom:0;">';

                    if ($row->type == '1') {
                        foreach ($row->items as $item) {
                            $html .= "<tr>
                            <td> - </td>
                            <td> " . $item->product->name . " </td>
                            <td> " . $item->todriver->name . (isset($item->todriver->city_id) ? " - ".$item->todriver->city_id : '')."</td>
                            <td> " . round($item->qty) . " </td>
                             </tr>";
                        }
                    } else if ($row->type == '2') {
                        foreach ($row->items as $item) {
                            $html .= "<tr>
                            <td> " . $item->fromdriver->name . (isset($item->fromdriver->city_id) ? " - ".$item->fromdriver->city_id : '')."</td>
                            <td> " . $item->product->name . " </td>
                            <td> " . $item->todriver->name . (isset($item->todriver->city_id) ? " - ".$item->todriver->city_id : '')." </td>
                            <td> " . round($item->qty) . " </td>
                             </tr>";
                        }
                    } else if ($row->type == '3') {
                        foreach ($row->items as $item) {
                            $html .= "<tr>
                            <td> " . $item->fromdriver->name . (isset($item->fromdriver->city_id) ? " - ".$item->fromdriver->city_id : '')." </td>
                            <td> " . $item->product->name . " </td>
                            <td> - </td>
                            <td> " . round($item->qty) . " </td>
                             </tr>";
                        }
                    }

                    $html .= '</table>';

                    return $html;
                })
                ->addColumn('action', function ($users) {

                    $variable = $users;

                    $action = "";
                    $action .= '<div class="d-flex align-items-center justify-content-center">';

                    if (auth()->user()->hasPermission("distribution.view")) {
                        $url = route("distribution.view", encrypt($variable->id));
                        $action .= view('buttons.view', compact('variable', 'url'));
                    }

                    $action .= '</div>';

                    return $action;

                })
                ->editColumn('created_at', function ($users) {
                    return date('d-m-Y', strtotime($users->created_at));
                })
                ->addIndexColumn()
                ->rawColumns(['action', 'product', 'type'])
                ->toJson();
    }

    public function create() {

        $drivers = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 3);
        })->selectRaw("concat(name, ' - (', email, ')') as name, id")->active()->pluck('name', 'id')->toArray();

        $moduleName = 'Assign Stock';
        $types = self::$types;

        return view('distribution.create', compact('moduleName', 'types', 'drivers'));
    }

    public function getBlade(Request $request) {
        $type = $request->type;

        if (in_array($type, ['1', '2', '3'])) {

            $drivers = User::whereHas('role', function ($builder) {
                $builder->where('roles.id', 3);
            })->selectRaw("concat(name, ' - (', email, ')') as name, id")->active()
            ->pluck('name', 'id')->toArray();

            if ($type == '1') {
                return response()->json(['status' => true, 'html' => view('distribution.storage-to-driver', compact('drivers'))->render()]);

            } else if ($type == '2') {
                return response()->json(['status' => true, 'html' => view('distribution.driver-to-driver', compact('drivers'))->render()]);

            } else if ($type == '3') {
                return response()->json(['status' => true, 'html' => view('distribution.driver-to-storage', compact('drivers'))->render()]);

            }
        }

        return response()->json(['status' => false]);
    }

    public function getProducts(Request $request) {
        $data = [];

        if ($request->has('searchQuery') && !empty(trim($request->searchQuery)) && in_array($request->type, ['1', '2', '3'])) {

            $searchQuery = $request->searchQuery;
            $prodArr = Product::select('id', 'name')->where('name', 'LIKE', "%{$searchQuery}%")->pluck('name', 'id')->toArray();

            if ($request->type == '1') {

                $stockInItems = Stock::where('type', '0')
                                ->whereIn('form', ['1', '3'])
                                ->whereNull('driver_id')
                                ->groupBy('product_id')
                                ->select('product_id')
                                ->pluck('product_id')
                                ->toArray();

                $products = [];

                foreach ($stockInItems as $item) {
                    $inStock = Stock::where('type', '0')
                    ->whereIn('form', ['1', '3'])
                    ->where('product_id', $item)
                    ->whereNull('driver_id')
                    ->select('qty')
                    ->sum('qty');

                    $outStock = Stock::where('type', '1')
                    ->where('product_id', $item)
                    ->whereIn('form', ['3'])
                    ->whereNull('driver_id')
                    ->select('qty')
                    ->sum('qty');

                    $availStock = intval($inStock) - intval($outStock);

                    if ($availStock > 0 && isset($prodArr[$item])) {
                        $products[] = [
                            'id' => $item,
                            'text' => $prodArr[$item],
                            'stock' => $availStock
                        ];
                    }
                }

                $data = $products;
            } else if ($request->type == '2' && !empty(trim($request->driver))) {

                $stockInItems = Stock::where('type', '0')
                                ->where('driver_id', $request->driver)
                                ->whereIn('form', ['1', '3'])
                                ->groupBy('product_id')
                                ->select('product_id')
                                ->pluck('product_id')
                                ->toArray();

                $products = [];

                foreach ($stockInItems as $item) {
                    $inStock = Stock::where('type', '0')
                    ->whereIn('form', ['1', '3'])
                    ->where('product_id', $item)
                    ->where('driver_id', $request->driver)
                    ->select('qty')
                    ->sum('qty');

                    $outStock = Stock::where('type', '1')
                    ->where('product_id', $item)
                    ->whereIn('form', ['3'])
                    ->where('driver_id', $request->driver)
                    ->select('qty')
                    ->sum('qty');

                    $availStock = intval($inStock) - intval($outStock);

                    if ($availStock > 0 && isset($prodArr[$item])) {
                        $products[] = [
                            'id' => $item,
                            'text' => $prodArr[$item],
                            'stock' => $availStock
                        ];
                    }

                }

                $data = $products;
            } else if ($request->type == '3' && !empty(trim($request->driver))) {

                $stockInItems = Stock::where('type', '0')
                                ->where('driver_id', $request->driver)
                                ->whereIn('form', ['1', '3'])
                                ->groupBy('product_id')
                                ->select('product_id')
                                ->pluck('product_id')
                                ->toArray();

                $products = [];

                foreach ($stockInItems as $item) {
                    $inStock = Stock::where('type', '0')
                    ->whereIn('form', ['1', '3'])
                    ->where('product_id', $item)
                    ->where('driver_id', $request->driver)
                    ->select('qty')
                    ->sum('qty');

                    $outStock = Stock::where('type', '1')
                    ->where('product_id', $item)
                    ->whereIn('form', ['3'])
                    ->where('driver_id', $request->driver)
                    ->select('qty')
                    ->sum('qty');

                    $availStock = intval($inStock) - intval($outStock);

                    if ($availStock > 0 && isset($prodArr[$item])) {
                        $products[] = [
                            'id' => $item,
                            'text' => $prodArr[$item],
                            'stock' => $availStock
                        ];
                    }

                }

                $data = $products;
            }

        }

        return response()->json($data);
    }

    public function store(Request $request) {

        $validations = [
            'type' => 'required',
            'docs' => 'max:10',
            'docs.*' => 'file|mimes:png,jpg,jpeg,pdf|max:20480',
            'product.*' => 'required',
            'quantity.*' => 'required|numeric|min:1',
            'driver.*' => 'required'
        ];

        $messages = [
            'type.required' => 'Select a Type.',
            'docs.max' => 'Maximum 10 files can be uploaded.',
            'docs.*.mimes' => 'Only .png, .jpg, .jpeg and .pdf extensions supported.',
            'docs.*.max' => 'Maximum 20MB files can be uploaded.',
            'quantity.*.required' => 'Enter quantity.',
            'quantity.*.numeric' => 'Enter valid format.',
            'quantity.*.min' => 'Quantity can\'t be less than 1.',
            'product.*.numeric' => 'Select a product.',
            'driver.*.required' => 'Select a driver.'
        ];

        if ($request->type == '2') {
            $validations['from_driver.*'] = 'required';
            $messages['from_driver.*.required'] = 'Select a driver.';
        }

        $this->validate($request, $validations, $messages);

        if (!file_exists(storage_path('app/public/distribution-docs'))) {
            mkdir(storage_path('app/public/distribution-docs'), 0777, true);
        }

        DB::beginTransaction();

        try {
            $userId = auth()->user()->id;
            $products = array_filter($request->product);
            $quantities = array_filter($request->quantity);
            $storageOutBoundErrors = [];

            if (!(count($products) > 0 && count($quantities) > 0)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Select product and enter quantity to assign stock');
            }

            if ($request->type == '1') {
                $tempHelper = Helper::getAvailableStockFromStorage();
                $addProdQty = [];
                foreach ($products as $pk => $p) {
                    $addProdQty[$p] = isset($addProdQty[$p]) ? ($addProdQty[$p] + $quantities[$pk]) : $quantities[$pk];
                }

                foreach ($addProdQty as $pk => $qty) {
                    if (isset($tempHelper[$pk]) && $tempHelper[$pk] < $qty) {
                        $storageOutBoundErrors[] = '<strong>' . $tempHelper[$pk] . '</strong> quantity of <strong>' . Helper::productName()[$pk] . "</strong> are available in storage and you assigned <strong>{$qty}</strong>.";
                    }
                }
            } else {
                $addProdQty = [];
                $frmDrvr = $request->driver;

                if ($request->type == '2') {
                    $frmDrvr = $request->from_driver;
                }

                foreach ($frmDrvr as $dk => $dr) {
                    $tempHelper = Helper::getAvailableStockFromDriver($dr);
                    $addProdQty[$dr][$products[$dk]] = isset($addProdQty[$dr][$products[$dk]]) ? ($addProdQty[$dr][$products[$dk]] + $quantities[$dk]) : $quantities[$dk];
                }

                foreach ($addProdQty as $prodIdArr) {
                    foreach ($prodIdArr as $prodIdKey => $qty) {
                        if (isset($tempHelper[$prodIdKey]) && $tempHelper[$prodIdKey] < $qty) {
                            $storageOutBoundErrors[] = '<strong>' . Helper::userName($dk, 'Driver') . '</strong> has <strong>' . $tempHelper[$prodIdKey] . '</strong> quantity of <strong>' . Helper::productName()[$prodIdKey] . "</strong> are available and you assigned <strong>{$qty}</strong>.";
                        }
                    }
                }
            }

            $storageOutBoundErrors = array_unique($storageOutBoundErrors);

            if (count($storageOutBoundErrors) > 0) {
                DB::rollBack();
                return redirect()->back()->with('error', implode("<br/>", $storageOutBoundErrors));
            }

            if ($request->type == '1') {
                $stockArrayIn = $stockArrayOut = $itemsArray = [];

                $distrib = new Distribution;
                $distrib->dis_id = Helper::generateDistributionNumber();
                $distrib->type = 1;
                $distrib->comment = $request->comment;
                $distrib->added_by = $userId;
                $distrib->save();

                if($request->hasFile('docs')) {
                    foreach ($request->file('docs') as $file) {
                        $name = 'DISTRIBUTION-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(storage_path('app/public/distribution-docs'), $name);

                        if (file_exists(storage_path("app/public/distribution-docs/{$name}"))) {
                            DistributionAttachment::create(['distribution_id' => $distrib->id,'name' => $name]);
                        }
                    }
                }

                foreach ($products as $key => $value) {
                    $itemsArray[] = [
                        'distribution_id' => $distrib->id,
                        'product_id' => $value,
                        'qty' => $quantities[$key] ?? 0,
                        'to_driver' => $request->driver[$key] ?? null,
                        'created_at' => now()
                    ];

                    // deduct from storage
                    $stockArrayOut[] = [
                        'product_id' => $value,
                        'type' => 1,
                        'date' => now(),
                        'qty' => $quantities[$key] ?? 0,
                        'added_by' => $userId,
                        'form' => 1,
                        'form_record_id' => $distrib->id,
                        'created_at' => now()
                    ];
                    // deduct from storage

                    // assign to driver
                    $stockArrayIn[] = [
                        'product_id' => $value,
                        'driver_id' => $request->driver[$key] ?? null,
                        'type' => 0,
                        'date' => now(),
                        'qty' => $quantities[$key] ?? 0,
                        'added_by' => $userId,
                        'form' => 3,
                        'form_record_id' => $distrib->id,
                        'created_at' => now()
                    ];
                    // assign to driver
                }

                DistributionItem::insert($itemsArray);
                Stock::insert($stockArrayOut);
                Stock::insert($stockArrayIn);
                DB::commit();

                return redirect()->route('distribution.index')->with('success', 'Stock assigned from storage to driver successfully.');
            } else if ($request->type == '2') {
                $stockArrayIn = $stockArrayOut = $itemsArray = [];

                $distrib = new Distribution;
                $distrib->dis_id = Helper::generateDistributionNumber();
                $distrib->type = 2;
                $distrib->comment = $request->comment;
                $distrib->added_by = $userId;
                $distrib->save();

                if($request->hasFile('docs')) {
                    foreach ($request->file('docs') as $file) {
                        $name = 'DISTRIBUTION-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(storage_path('app/public/distribution-docs'), $name);

                        if (file_exists(storage_path("app/public/distribution-docs/{$name}"))) {
                            DistributionAttachment::create(['distribution_id' => $distrib->id,'name' => $name]);
                        }
                    }
                }

                foreach ($products as $key => $value) {
                    $itemsArray[] = [
                        'distribution_id' => $distrib->id,
                        'product_id' => $value,
                        'qty' => $quantities[$key] ?? 0,
                        'from_driver' => $request->from_driver[$key] ?? null,
                        'to_driver' => $request->driver[$key] ?? null,
                        'created_at' => now()
                    ];

                    // deduct from driver
                    $stockArrayOut[] = [
                        'product_id' => $value,
                        'driver_id' => $request->from_driver[$key] ?? null,
                        'type' => 1,
                        'date' => now(),
                        'qty' => $quantities[$key] ?? 0,
                        'added_by' => $userId,
                        'form' => 3,
                        'form_record_id' => $distrib->id,
                        'created_at' => now()
                    ];
                    // deduct from driver

                    // assign to driver
                    $stockArrayIn[] = [
                        'product_id' => $value,
                        'driver_id' => $request->driver[$key] ?? null,
                        'type' => 0,
                        'date' => now(),
                        'qty' => $quantities[$key] ?? 0,
                        'added_by' => $userId,
                        'form' => 3,
                        'form_record_id' => $distrib->id,
                        'created_at' => now()
                    ];
                    // assign to driver
                }

                DistributionItem::insert($itemsArray);
                Stock::insert($stockArrayOut);
                Stock::insert($stockArrayIn);
                DB::commit();

                return redirect()->route('distribution.index')->with('success', 'Stock assigned from driver to driver successfully.');
            } else if ($request->type == '3') {
                $stockArrayIn = $stockArrayOut = $itemsArray = [];

                $distrib = new Distribution;
                $distrib->dis_id = Helper::generateDistributionNumber();
                $distrib->type = 3;
                $distrib->comment = $request->comment;
                $distrib->added_by = $userId;
                $distrib->save();

                if($request->hasFile('docs')) {
                    foreach ($request->file('docs') as $file) {
                        $name = 'DISTRIBUTION-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(storage_path('app/public/distribution-docs'), $name);

                        if (file_exists(storage_path("app/public/distribution-docs/{$name}"))) {
                            DistributionAttachment::create(['distribution_id' => $distrib->id,'name' => $name]);
                        }
                    }
                }

                foreach ($products as $key => $value) {
                    $itemsArray[] = [
                        'distribution_id' => $distrib->id,
                        'product_id' => $value,
                        'qty' => $quantities[$key] ?? 0,
                        'from_driver' => $request->driver[$key] ?? null,
                        'created_at' => now()
                    ];

                    // deduct from driver
                    $stockArrayOut[] = [
                        'product_id' => $value,
                        'driver_id' => $request->driver[$key] ?? null,
                        'type' => 1,
                        'date' => now(),
                        'qty' => $quantities[$key] ?? 0,
                        'added_by' => $userId,
                        'form' => 3,
                        'form_record_id' => $distrib->id,
                        'created_at' => now()
                    ];
                    // deduct from driver

                    // assign to storage
                    $stockArrayIn[] = [
                        'product_id' => $value,
                        'type' => 0,
                        'date' => now(),
                        'qty' => $quantities[$key] ?? 0,
                        'added_by' => $userId,
                        'form' => 1,
                        'form_record_id' => $distrib->id,
                        'created_at' => now()
                    ];
                    // assign to storage
                }

                DistributionItem::insert($itemsArray);
                Stock::insert($stockArrayOut);
                Stock::insert($stockArrayIn);
                DB::commit();

                return redirect()->route('distribution.index')->with('success', 'Stock assigned from driver to storage successfully.');
            }

            DB::rollBack();
            return redirect()->back()->with('error', Helper::$errorMessage);

        } catch (\Exception $e) {
            Helper::logger($e->getMessage() . ' Line No. :' . $e->getLine());
            DB::rollBack();
            return redirect()->back()->with('error', Helper::$errorMessage);
        }

    }

    public function show(Request $request, $id)
    {
        $moduleName = 'View Assigned Stock';

        $d = Distribution::with(['docs', 'items.fromdriver' => function ($builder) {
            return $builder->withTrashed();
        }, 'items.todriver' => function ($builder) {
            return $builder->withTrashed();
        }])->where('id', decrypt($id))->with('items')->first();

        $types = self::$types;

        return view('distribution.view', compact('moduleName', 'd', 'types'));
    }

}
