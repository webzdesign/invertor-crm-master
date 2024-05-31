<div class="modal fade" id="change-user" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered" style="width: 400px;">
        <div class="modal-content">
            <form action="{{ route('change-user-for-order') }}" method="POST" id="changeUser"> @csrf
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5"> <span id="modal-title-lead-stage"></span> </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="manage-order-id-for-change-lead-stage" name="cuid" />
                    <input type="hidden" id="manage-order-time-for-change-lead-stage" name="cutime" value="1" />
                    <div class="row">

                        <div class="col-12 mb-2">
                            <div class="form-group">

                                <label class="c-gr f-500 f-16 w-100 mb-2"> User : </label>
                                <select class="select2 select2-hidden-accessible" id="change-user-select" name="user">
                                        
                                </select>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer no-border hideable">
                    <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                    <button type="submit" class="btn-primary f-500 f-14"> Done </button>
                </div>
            </form>
        </div>
    </div>
</div>