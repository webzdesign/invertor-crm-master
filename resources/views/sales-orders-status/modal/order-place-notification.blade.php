<div class="modal fade" id="order-place-notification" tabindex="-1" aria-labelledby="exampleModalLabelN" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered" style="width: 400px;">
        <div class="modal-content">
            <form action="{{ route('order-place-notification-save') }}" method="POST" id="orderplacenotifiction"> @csrf
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5"> <span id="modal-title-change-user-notification">ADD ORDER PLACE NOTIFICATION</span> </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        {{-- <div class="col-12 mb-2">
                            <div class="form-group">
                                <label class="c-gr f-500 f-12 w-100 mb-2"> RESPONSIBLE USER : <span class="text-danger">*</span> </label>
                                <select class="select2-hidden-accessible change-user-picker-notification" name="user" id="change-user-picker-notification">
                                </select>
                            </div>
                        </div> --}}
                        <div class="col-12 mb-2">
                            <div class="form-group">
                                <label class="c-gr f-500 f-12 w-100 mb-2"> ORDER ALLOCATE NOTIFICATION TO DRIVER : <span class="text-danger">*</span> </label>
                                <select name="allocate_notification" id="allocate_notification" class="select2-hidden-accessible">
                                    <option value=""></option>
                                    @if(!empty($twillotemplate))
                                        @foreach($twillotemplate as $template)
                                        <option value="{{$template->contentsid}}" @if($allocate_notification == $template->contentsid) selected @endif>{{$template->templatename}}</option>
                                        @endforeach
                                    @endif
                                </select>
                                {{-- <textarea class="form-control h-25" name="allocate_notification" id="allocate_notification">{{$allocate_notification}}</textarea> --}}
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="form-group">
                                <label class="c-gr f-500 f-12 w-100 mb-2"> ORDER ACCEPT NOTIFICATION TO SELLER : <span class="text-danger">*</span> </label>
                                {{-- <textarea class="form-control h-25" name="accept_notification" id="accept_notification">{{$accept_notification}}</textarea> --}}
                                <select name="accept_notification" id="accept_notification" class="select2-hidden-accessible">
                                    <option value=""></option>
                                    @if(!empty($twillotemplate))
                                        @foreach($twillotemplate as $template)
                                        <option value="{{$template->contentsid}}" @if($accept_notification == $template->contentsid) selected @endif>{{$template->templatename}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer no-border">
                    <div class="me-auto">
                        <button class="btn-default f-500 f-14" id="delete-btn-change-user-notification"> <i class="fa fa-trash"></i> </button>
                    </div>
                    <div class="hideable-user-change-sbmt-btn-notification">
                        <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                        <button type="submit" class="btn-primary f-500 f-14"> Done </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
