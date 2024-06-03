<div class="modal fade" id="manage-next-possible-status" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('sales-order-manage-role') }}" method="POST" id="manage-role-form"> @csrf
            <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5"> Manage "<span id="modal-title"></span>" status </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manage-status-id" name="id" />
                <div class="row">

                    <div class="col-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Possible status : </label>
                            <button type="button" id="status-adder-into-modal" class="btn-primary f-500 f-14"> <i class="fa fa-plus"></i> Add </button>
                            <div id="multiple-row-container">
                                <table class="table table-bordered">
                                    <tbody class="upsertable">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer no-border">
                <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                <button type="submit" class="btn-primary f-500 f-14"> Save </button>
            </div>
            </form>
        </div>
    </div>
</div>