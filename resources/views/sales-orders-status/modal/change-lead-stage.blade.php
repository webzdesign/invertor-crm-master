<div class="modal fade" id="lead-stage" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered" style="width: 400px;">
        <div class="modal-content">
            <form action="{{ route('put-order-on-cron') }}" method="POST" id="putOnCron"> @csrf
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5"> <span id="modal-title-lead-stage"></span> </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="manage-order-id-for-change-lead-stage" name="clid" />
                    <input type="hidden" id="manage-order-time-for-change-lead-stage" name="cltime"
                        value="1" />
                    <input type="hidden" id="manage-order-status-for-change-lead-stage" name="clstatus" />
                    <div class="row">

                        <div class="col-12 mb-2">
                            <div class="form-group">

                                <label class="c-gr f-500 f-16 w-100 mb-2"> Status Trigger : </label>
                                <div class="status-dropdown status-dropdown-2" style="z-index:2;">
                                    <button type="button"
                                        class="status-dropdown-toggle status-dropdown-toggle-2 d-flex align-items-center justify-content-between f-14">
                                        <span> Immediatly </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12"
                                            width="12" viewBox="0 0 330 330">
                                            <path id="XMLID_225_"
                                                d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z" />
                                        </svg>
                                    </button>
                                    <div class="status-dropdown-menu can-hide-time-picker">
                                        <li class="f-14 sel-time" data-time="1"> Immediatly </li>
                                        <li class="f-14 sel-time" data-time="2"> 5 minutes </li>
                                        <li class="f-14 sel-time" data-time="3"> 10 minutes </li>
                                        <li class="f-14 sel-time" data-time="4"> One day </li>
                                        <li class="f-14 d-flex sel-time" data-time="5"
                                            style="flex-direction:row;align-items:center;justify-content:left;">
                                            <span>Select interval</span>
                                            <div class="d-flex w-75"
                                                style="flex-direction:row;align-items:center;justify-content:right;">
                                                <input type="text" class="hour form-control" name="hour"
                                                    id="hour" placeholder="hour" />
                                                <input type="text" class="minute form-control" name="minute"
                                                    id="minute" placeholder="minute" />
                                            </div>
                                        </li>
                                    </div>
                                </div>
                                <div id="status-dropdown-2-error" class="text-danger"></div>

                                <div class="dropdown-menu-inner-sub-overlay d-none"></div>

                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">

                                <label class="c-gr f-500 f-16 w-100 mb-2"> Status : </label>
                                <div id="stage-container">
                                    <div class="status-dropdown">
                                        @foreach ($statuses as $status)
                                            @if ($loop->first)
                                                <button type="button" style="background:{{ $status->color }};"
                                                    class="status-dropdown-toggle status-dropdown-toggle-status d-flex align-items-center justify-content-between f-14">
                                                    <span>{{ $status['name'] }}</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#000000"
                                                        height="12" width="12" viewBox="0 0 330 330">
                                                        <path id="XMLID_225_"
                                                            d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z" />
                                                    </svg>
                                                </button>
                                            @endif
                                        @endforeach
                                        <div class="status-dropdown-menu">
                                            @foreach ($statuses as $status)
                                                <li class="f-14" data-sid="{{ $status->id }}"
                                                    style="background: {{ $status->color }};"> {{ $status->name }}
                                                </li>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

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