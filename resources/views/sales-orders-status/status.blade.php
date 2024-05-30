<div class="status-dropdown">
    @if(!$statuses->isEmpty())
    @foreach ($statuses as $status)
    @if($loop->first)
    <button type="button" style="background:{{ $status->color }};color: {{ Helper::generateTextColor($status->color) }};" class="status-dropdown-toggle status-dropdown-toggle-status d-flex align-items-center justify-content-between f-14">
        <span>{{ $status['name'] }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
            <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
        </svg>
    </button>
    @endif
    @endforeach
    <div class="status-dropdown-menu">
        @foreach ($statuses as $status)
        <li class="f-14 selectable-2" data-sid="{{ $status->id }}" style="background: {{ $status->color }};color: {{ Helper::generateTextColor($status->color) }};"> {{ $status->name }} </li>
        @endforeach
    </div>
    @else
        <p> Current status is <strong> {{ strtoupper($cs ?? '') }} </strong> </p>
    @endif
</div>