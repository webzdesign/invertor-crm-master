<div class="modal fade" id="change-user" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered" style="width: 400px;">
        <div class="modal-content">
            <form action="{{ route('sales-order-responsible-user-save') }}" method="POST" id="changeUser"> @csrf
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5"> <span id="modal-title-change-user"></span> </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="manage-order-id-for-change-user" name="cuid" />
                    <input type="hidden" id="manage-order-time-for-change-user" name="cutime" value="1" />
                    <input type="hidden" id="manage-order-type-for-change-user" class="manage-order-type-for-change-user" name="cutype" value="1" />
                    <input type="hidden" id="editing-change-user" value="0" />
                    <input type="hidden" id="change-user-name-label" value="" />
                    
                    <div class="row">

                        <div class="col-12">

                            <label class="c-gr f-500 f-12 w-100 mb-2"> PIPELINE TRIGGERS : <span class="text-danger">*</span> </label>
                            <div class="status-dropdown-inner-3 mb-2">
                                <button type="button" style="background:#fff;color: #000;"
                                    class="status-dropdown-toggle-inner-3 d-flex align-items-center justify-content-between f-14">
                                    <span class="add-task-def-selected"> Execute: Immediately after moved to this status </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12"
                                        width="12" viewBox="0 0 330 330">
                                        <path id="XMLID_225_"
                                            d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z" />
                                    </svg>
                                </button>

                                <div class="status-dropdown-menu-inner-3">
                                    <li class="f-14 selectable-inner-p-3" data-mtype="1"> <button type="button" data-firstclass=".dropdown-menu-inner-3-sub" class="no-btn opt-3-1" data-selchild="1" data-left="18" data-top="38" data-parent="1"> Immediately </button> After moved to this status </li>
                                    <li class="f-14 selectable-inner-p-3" data-mtype="2"> <button type="button" data-firstclass=".dropdown-menu-inner-3-sub" class="no-btn opt-3-2" data-selchild="1" data-left="18" data-top="67" data-parent="2"> Immediately </button> After created into this status </li>
                                    <li class="f-14 selectable-inner-p-3" data-mtype="3"> <button type="button" data-firstclass=".dropdown-menu-inner-3-sub" class="no-btn opt-3-3" data-selchild="1" data-left="18" data-top="92" data-parent="3"> Immediately </button> After moved or created into this status </li>
                                </div>
                            </div>

                            <div class="dropdown-menu-inner-3-sub-overlay d-none"></div>

                            {{-- time picker --}}
                            <div class="dropdown-menu-inner-3-sub zindex-1 dis-none" data-parenttype="1">
                                <ul class="p-0 m-0 status-dropdown-menu-inner-3-ul">
                                    <li class="f-14 selectable-inner-3" data-ttype="1" > Immediately </li>
                                    <li class="f-14 selectable-inner-3" data-ttype="2" > 5 minutes </li>
                                    <li class="f-14 selectable-inner-3" data-ttype="3" > 10 minutes </li>
                                    <li class="f-14 selectable-inner-3" data-ttype="4" > One day </li>
                                    <li class="d-flex align-items-center justify-content-between f-14 selectable-inner-3" data-ttype="5">
                                        <span>
                                            Select interval
                                        </span>
                                        <div class="d-flex w-50" style="flex-direction:row;align-items:center;justify-content:right;">
                                            <input type="text" class="change-user-hour form-control" name="change_user_hour" id="change-user-hour" placeholder="hour">
                                            <input type="text" class="change-user-minute form-control" name="change_user_minute" id="change-user-minute" placeholder="minute">
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            {{-- time picker --}}

                            <div id="cu-type-error" class="text-danger-error"></div>
                        </div>

                        <div class="col-12 mb-2">
                            <div class="form-group">

                                <label class="c-gr f-500 f-12 w-100 mb-2"> RESPONSIBLE USER : <span class="text-danger">*</span> </label>
                                <select class="select2-hidden-accessible change-user-picker" name="user" id="change-user-picker">
                                </select>

                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer no-border">
                    <div class="me-auto">
                        <button class="btn-default f-500 f-14" id="delete-btn-change-user"> <i class="fa fa-trash"></i> </button>
                    </div>
                    <div class="hideable-user-change-sbmt-btn">
                        <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                        <button type="submit" class="btn-primary f-500 f-14"> Done </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>