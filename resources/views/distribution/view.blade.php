@extends('layouts.master')

@section('css')
    <style>
        .customLayout th {
            white-space: nowrap;
            font-size: 13px;
        }

        .srNumberClass {
            font-size: 10px !important;
        }
    </style>
@endsection

@section('content')
    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2"> {{ $moduleName }}</h2>
    <form action="{{ route('distribution.store') }}" method="POST" id="assignStock"> @csrf
        <div class="cards">
            <div class="cardsBody pb-0">

                <div class="row">

                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label for="type" class="c-gr f-500 f-16 w-100 mb-2">Distribution Type</label>
                            <input type="text" readonly class="form-control" value="{{ $types[$d->type] }}">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Comment</label>
                            <textarea readonly cols="30" rows="10" class="form-control">{{ $d->comment }}</textarea>
                        </div>
                    </div>

                    <div class="container-for-blade">
                        @if ($d->type == '1')
                            <div class="col-md-12">
                                <div
                                    class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                                    <span>Products</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="table-responsive">
                                        <input type="hidden">
                                        <table class="table table-bordered customLayout">
                                            <thead>
                                                <tr>

                                                    <th class="block-a">Product <span class="text-danger"></span> </th>

                                                    <th class="block-b"> Driver <span class="text-danger"></span> </th>

                                                    <th class="block-c">Quantity <span class="text-danger"></span> </th>

                                                </tr>
                                            </thead>

                                            <tbody class="upsertable">
                                                @forelse($d->items as $key => $item)
                                                    <tr>

                                                        <td class="block-a">
                                                            <div style="min-width: 200px;width: 100%"
                                                                class="removable-product">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ $item->product->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="block-b">
                                                            <div style="min-width: 200px;width: 100%"
                                                                class="removable-driver">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ $item->todriver->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="block-c">
                                                            <div style="min-width: 200px;">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ round($item->qty) }}">
                                                            </div>
                                                        </td>

                                                    </tr>
                                                @empty
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="block-a"></td>
                                                    <td class="block-b"></td>
                                                    <td class="block-d">
                                                        <div style="min-width: 200px;">
                                                            <input type="text" class="form-control mt-quantity"
                                                                style="background:#efefef"
                                                                value="{{ round($d->items->sum('qty')) }}"
                                                                readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @elseif($d->type == '2')
                            <div class="col-md-12">
                                <div
                                    class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                                    <span>Products</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="table-responsive">
                                        <input type="hidden">
                                        <table class="table table-bordered customLayout">
                                            <thead>
                                                <tr>

                                                    <th class="block-a"> From Driver <span class="text-danger">*</span>
                                                    </th>

                                                    <th class="block-b">Product <span class="text-danger">*</span> </th>

                                                    <th class="block-c"> To Driver <span class="text-danger">*</span> </th>

                                                    <th class="block-d">Quantity <span class="text-danger">*</span> </th>

                                                </tr>
                                            </thead>

                                            <tbody class="upsertable">
                                                @forelse($d->items as $key => $item)
                                                    <tr>

                                                        <td class="block-a">
                                                            <div style="min-width: 200px;width: 100%"
                                                                class="removable-from-driver">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ $item->fromdriver->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="block-b">
                                                            <div style="min-width: 200px;width: 100%"
                                                                class="removable-product">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ $item->product->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="block-c">
                                                            <div style="min-width: 200px;width: 100%"
                                                                class="removable-driver">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ $item->todriver->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="block-d">
                                                            <div style="min-width: 200px;">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ round($item->qty) }}">
                                                            </div>
                                                        </td>

                                                    </tr>
                                                @empty
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="block-a"></td>
                                                    <td class="block-b"></td>
                                                    <td class="block-c"></td>
                                                    <td class="block-d">
                                                        <div style="min-width: 200px;">
                                                            <input type="text" class="form-control mt-quantity"
                                                                style="background:#efefef"
                                                                value="{{ round($d->items->sum('qty')) }}"
                                                                readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @elseif($d->type == '3')
                            <div class="col-md-12">
                                <div
                                    class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                                    <span>Products</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="table-responsive">
                                        <input type="hidden">
                                        <table class="table table-bordered customLayout">
                                            <thead>
                                                <tr>

                                                    <th class="block-a"> Driver <span class="text-danger">*</span> </th>

                                                    <th class="block-b">Product <span class="text-danger">*</span> </th>

                                                    <th class="block-c">Quantity <span class="text-danger">*</span> </th>
                                                </tr>
                                            </thead>

                                            <tbody class="upsertable">
                                                @forelse($d->items as $key => $item)
                                                    <tr>

                                                        <td class="block-a">
                                                            <div style="min-width: 200px;width: 100%"
                                                                class="removable-driver">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ $item->fromdriver->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="block-b">
                                                            <div style="min-width: 200px;width: 100%"
                                                                class="removable-product">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ $item->product->name }}">
                                                            </div>
                                                        </td>

                                                        <td class="block-c">
                                                            <div style="min-width: 200px;">
                                                                <input type="text" readonly class="form-control"
                                                                    value="{{ round($item->qty) }}">
                                                            </div>
                                                        </td>

                                                    </tr>
                                                @empty
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="block-a"></td>
                                                    <td class="block-b"></td>
                                                    <td class="block-c">
                                                        <div style="min-width: 200px;">
                                                            <input type="text" class="form-control mt-quantity"
                                                                style="background:#efefef"
                                                                value="{{ round($d->items->sum('qty')) }}"
                                                                readonly>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    @forelse($d->docs as $key => $doc)
                        @if ($loop->first)
                            <label class="c-gr f-500 f-16 w-100 mb-2"> Documents </label>
                        @endif
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <a href="{{ $doc->doc }}" target="_blank">
                                @if ($doc->type == 1)
                                    <img src="{{ asset('assets/images/pdf.png') }}" alt="{{ $doc->name }}"
                                        style="width: 80px;">
                                @else
                                    <img src="{{ $doc->doc }}" class="w-100 shadow-1-strong rounded"
                                        style="object-fit:cover;height:100px;width:100%;margin:5px 0px;border:1px solid black;">
                                @endif
                            </a>
                        </div>
                    @empty
                        <center>
                            <p><strong>No files uploaded for this distribution.</strong></p>
                        </center>
                    @endforelse
                </div>

            </div>

            <div class="cardsFooter d-flex justify-content-center">
                <a href="{{ route('distribution.index') }}">
                    <button type="button" class="btn-default f-500 f-14">Cancel</button>
                </a>
            </div>
        </div>
    </form>
@endsection
