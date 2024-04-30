<div class="tableCards d-inline-block me-1 pb-0">
    <div class="editDlbtn">
        @if($variable->status == 0)
        <a data-bs-toggle="tooltip" title="Active" href="{{ $url }}" style="background: #ffc107 !important;" id="activate" class="editBtn modal-activate-btn" data-uniqueid="{{ encrypt($variable->id) }}"> <i class="fa fa-check text-dark" aria-hidden="true"></i> </a>
        @else
        <a data-bs-toggle="tooltip" title="Deactive" href="{{ $url }}" style="background: #ffc107 !important;" id="deactivate" class="editBtn modal-deactivate-btn" data-uniqueid="{{ encrypt($variable->id) }}"> <i class="fa fa-close text-dark" aria-hidden="true"></i> </a>
        @endif
    </div>
</div>