<div class="modal fade" id="close-order" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ url('') }}" method="POST" id="closing-order-amount-form" enctype="multipart/form-data">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="exampleModalLongTitle"> <span id="modal-title"></span> </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="order_id" id="closing-order-id">

                        <div class="col-12 mb-2 amount-field">
                            <label class="c-gr f-500 f-12 w-100 mb-2"> ENTER AMOUNT : <span class="text-danger">*</span> </label>
                            <input type="text" id="order-closing-amount" name="amount" class="form-control" placeholder="Enter amount" />
                        </div>

                        <div class="col-12 mb-2 document-field" style="display: none;">
                            <label class="c-gr f-500 f-12 w-100 mb-2"> UPLOAD PROOF IMAGE : <span class="text-danger">*</span> </label>
                            <input type="file" id="order-closing-document" name="file[]" class="form-control" multiple />
                        </div>

                    </div>
                </div>
                <div class="modal-footer no-border">
                    <button type="submit" class="btn-primary f-500 f-14" id="close-order-sbmt-btn"> Next </button>
                </div>
            </form>
        </div>
    </div>
</div>