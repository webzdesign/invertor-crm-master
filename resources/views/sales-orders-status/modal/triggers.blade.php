<div class="modal fade" id="trigger" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5"> Add Trigger for Order <span id="modal-title"></span> </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manage-order-id" name="id" />
                <input type="hidden" id="manage-order-status-id" name="status" />
                <div class="row">

                    <div class="col-4">
                        <div class="form-group">
                            <div class="box" id="add-task-btn">
                                <img src="{{ asset('assets/images/add.png') }}" alt="Add Task">
                                <div> Add Task </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <div class="box" id="lead-stage-btn">
                                <img src="{{ asset('assets/images/change.png') }}" alt="Change order status">
                                <div> Change order status </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <div class="box">
                            {{-- <div class="box" id="change-user-btn"> --}}
                                <img src="{{ asset('assets/images/swap.png') }}" alt="Change lead's user">
                                <div> Change lead's user </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer no-border">
            </div>
        </div>
    </div>
</div>