<div class="cards mt-4">
    <div class="cardsBody pb-0">
        <div class="row">

            <div class="col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="order_number" class="c-gr f-500 f-16 w-100 mb-2">Order Number :</label>
                    <input class="form-control" id="order_number" type="text" value="{{ $orderNo }}" readonly
                        style="background:#efefef">
                </div>
            </div>

            <div class="col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Name :
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="customer-name" placeholder="Enter customer name"
                        name="customername" value="{{ old('customername') }}">
                    @if ($errors->has('customername'))
                        <span class="text-danger d-block">{{ $errors->first('customername') }}</span>
                    @endif
                </div>
            </div>

            <div class="col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Phone Number :
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" name="customerphone" id="customer-phone"
                        value="{{ old('customerphone') }}">
                    <input type="hidden" name="country_dial_code" id="country_dial_code">
                    <input type="hidden" name="country_iso_code" id="country_iso_code"
                        value="{{ old('country_iso_code') }}">
                    @if ($errors->has('customerphone'))
                        <span class="text-danger d-block">{{ $errors->first('customerphone') }}</span>
                    @endif
                </div>
            </div>

            <div class="col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Facebook URL :
                    </label>
                    <input type="url" class="form-control" name="customerfb" id="customer-fb"
                        placeholder="Enter customer facebook url" value="{{ old('customerfb') }}">
                    @if ($errors->has('customerfb'))
                        <span class="text-danger d-block">{{ $errors->first('customerfb') }}</span>
                    @endif
                </div>
            </div>

            <div class="col-md-4 col-sm-12">
                <div class="form-group">
                    <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Delivery Date :
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" readonly name="order_del_date" placeholder="Order Delivery Date"
                        id="order_del_date" class="form-control datepicker" style="background:#ffffff"
                        value="{{ old('order_del_date') }}">
                    @if ($errors->has('order_del_date'))
                        <span class="text-danger d-block">{{ $errors->first('order_del_date') }}</span>
                    @endif
                </div>
            </div>


            <div>
                <div class="col-md-12">
                    <div class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
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

                                        <th>Category <span class="text-danger">*</span> </th>

                                        <th>Product <span class="text-danger">*</span> </th>

                                        <th>Quantity <span class="text-danger">*</span> </th>

                                        <th>Price <span class="text-danger">*</span> </th>

                                        <th>Amount </th>

                                        <th>Remarks </th>
                                    </tr>
                                </thead>

                                <tbody class="upsertable">
                                    <tr>

                                        <td>
                                            <div style="min-width: 200px;width: 100%">
                                                <input type="text" readonly class="form-control"
                                                    value="{{ $category->name }}">
                                                <input type="hidden" name="category[]" id="mcategory"
                                                    value="{{ $category->id }}">
                                            </div>
                                        </td>

                                        <td>
                                            <div style="min-width: 200px;width: 100%">
                                                <input type="hidden" name="lat" value="{{ $latFrom }}">
                                                <input type="hidden" name="long" value="{{ $longFrom }}">
                                                <input type="hidden" name="range" value="{{ $range }}">
                                                <input type="text" readonly class="form-control"
                                                    value="{{ $product->name }}">
                                                <input type="hidden" name="product[]" data-minprice="{{ $minSalesPrice }}" id="mproduct"
                                                    value="{{ $product->id }}">
                                            </div>
                                        </td>

                                        <td>
                                            <div style="min-width: 200px;">
                                                <input type="number" data-indexid="0" name="quantity[]"
                                                    id="mquantity" class="form-control" min="1">
                                            </div>
                                        </td>

                                        <td>
                                            <div style="min-width: 200px;">
                                                <input type="number" data-indexid="0" id="mprice" name="price[]" id="mprice"
                                                    class="form-control" min="0">
                                            </div>
                                        </td>

                                        <td>
                                            <div style="min-width: 200px;">
                                                <input type="number" data-indexid="0" id="mamount" name="amount[]" id="maount"
                                                    class="form-control" readonly>
                                            </div>
                                        </td>


                                        <td>
                                            <div style="min-width: 200px;">
                                                <input type="hidden" name="postal_code" value="{{ $postalcode }}">
                                                <input type="hidden" name="address_line_1" value="{{ $addressline }}">

                                                <input type="text" data-indexid="0" tabindex="0"
                                                    maxlength="255" name="remarks[]" id="mremarks"
                                                    class="form-control">
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td>
                                            <input type="hidden" name="driver_id" value="{{ $driverDetail->id }}">
                                            <input type="hidden" name="driver_lat" value="{{ $driverDetail->lat }}">
                                            <input type="hidden" name="driver_long" value="{{ $driverDetail->long }}">
                                            <p><Strong>Driver</Strong> : {{ $driverDetail->name }} </p>
                                        </td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cardsFooter d-flex justify-content-center">
        <button type="submit" class="btn-primary f-500 f-14">Save</button>
    </div>
</div>
