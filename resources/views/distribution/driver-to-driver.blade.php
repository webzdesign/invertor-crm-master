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

                        <th class="block-a"> From Driver <span class="text-danger">*</span> </th>

                        <th class="block-b">Product <span class="text-danger">*</span> </th>

                        <th class="block-c"> To Driver <span class="text-danger">*</span> </th>

                        <th class="block-d">Quantity <span class="text-danger">*</span> </th>

                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody class="upsertable">
                    <tr>

                        <td class="block-a">
                            <div style="min-width: 200px;width: 100%" class="removable-from-driver">
                                <select name="from_driver[0]" data-indexid="0" id="from-driver-0" class="select2 select2-hidden-accessible m-from-driver" style="width:100%" data-placeholder="Select a Driver">
                                    @forelse($drivers as $did => $dname)
                                        @if($loop->first)
                                        <option value="" selected> --- Select a Driver --- </option>
                                        @endif
                                        <option value="{{ $did }}">{{ $dname }}</option>
                                        @empty
                                        <option value="" selected> --- No Driver Available --- </option>
                                    @endforelse
                                </select>
                            </div>
                        </td>

                        <td class="block-b">
                            <div style="min-width: 200px;max-width:280px" class="removable-product">
                                <select name="product[0]" data-indexid="0" id="product-0" class="product2 select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product">
                                    <option value="" selected> --- Select a Product --- </option>
                                </select>
                            </div>
                        </td>

                        <td class="block-c">
                            <div style="min-width: 200px;max-width:280px" class="removable-driver">
                                <select name="driver[0]" data-indexid="0" id="driver-0" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver">
                                    @forelse($drivers as $did => $dname)
                                        @if($loop->first)
                                        <option value="" selected> --- Select a Driver --- </option>
                                        @endif
                                        <option value="{{ $did }}">{{ $dname }}</option>
                                        @empty
                                        <option value="" selected> --- No Driver Available --- </option>
                                    @endforelse
                                </select>
                            </div>
                        </td>

                        <td class="block-d">
                            <div style="min-width: 200px;">
                                <input type="number" data-indexid="0" name="quantity[0]" id="quantity-0" class="form-control m-quantity" style="background:#ffffff" min='1'>
                            </div>
                        </td>

                        <td style="width:100px;">
                            <div style="min-width: 100px;">
                                <button type="button" class="btn btn-primary btn-sm addNewRow">+</button>
                                <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="block-a"></td>
                        <td class="block-b"></td>
                        <td class="block-c"></td>
                        <td class="block-d">
                            <div style="min-width: 200px;">
                                <input type="number" class="form-control mt-quantity" style="background:#efefef" value="0" readonly>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
