<div class="modal fade" id="trigger-options-modal" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <input type="hidden" id="performing-status" />
                <div class="row">

                    <div class="col-6">
                        <div class="form-group">
                            <div class="box" id="manage-status-btn">
                                <img src="{{ asset('assets/images/wrench.png') }}" alt="Manage statuses">
                                <div> Manage </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <div class="box" id="add-task-btn">
                                <img src="{{ asset('assets/images/add.png') }}" alt="Add task">
                                <div> Add Task </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <div class="box" id="lead-stage-btn">
                                <img src="{{ asset('assets/images/change.png') }}" alt="Change order status">
                                <div> Change order status </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-group">
                            <div class="box" id="responsible-user">
                            {{-- <div class="box" id="change-user-btn"> --}}
                                <img src="{{ asset('assets/images/swap.png') }}" alt="Change user">
                                <div> Change responsible user </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>