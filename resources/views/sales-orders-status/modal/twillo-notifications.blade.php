<div class="modal fade" id="twillo-notification" tabindex="-1" aria-labelledby="exampleModalLabelT" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered" style="width: 400px;">
        <div class="modal-content">
            <form action="{{ route('twillo-notification-save') }}" method="POST" id="twillonotifiction"> @csrf
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5">NOTIFICATION FOR <span id="modal-title-twillo-notification"></span> </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <div class="form-group">
                                <label class="c-gr f-500 f-12 w-100 mb-2"> RESPONSIBLE USER : <span class="text-danger">*</span> </label>
                                <select class="select2-hidden-accessible change-user-picker-notification" name="user" id="change-user-picker-notification">
                                </select>
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="form-group">
                                <label class="c-gr f-500 f-12 w-100 mb-2"> Notification Message : <span class="text-danger">*</span> </label>
                                <select class="select2-hidden-accessible twillo-template-notification" name="message" id="message">
                                </select>
                                {{-- <textarea class="form-control h-25" name="message" id="message"></textarea> --}}
                            </div>
                        </div>
                        <input type="hidden" name="status_id" id="status_id" >
                        <input type="hidden" name="sequence" id="sequence" >
                        <input type="hidden" name="twillo_trigger_id" id="twillo_trigger_id" >
                        <input type="hidden" name="id" id="id" >
                    </div>
                </div>
                <div class="modal-footer no-border">
                    <div class="me-auto">
                        <button class="btn-default f-500 f-14" id="delete-btn-change-user-twillo-notification" type="button"> <i class="fa fa-trash"></i> </button>
                    </div>
                    <div class="hideable-user-change-sbmt-btn-twillo-notification">
                        <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                        <button type="submit" class="btn-primary f-500 f-14"> Done </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
