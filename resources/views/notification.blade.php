@forelse ($notifications as $notification)
<li class="m-0 w-100">
    <a href="{{ route('read-notification', ['id' => $notification['id'], 'url' => $notification['link']]) }}" >
        <h4 class="f-16 mb-0 f-700 c-19">
            {{ $notification['title'] }}
        </h4>
        <p class="f-12 mb-0">
            {!! $notification['description'] !!}
        </p>
        @if(date('Y-m-d H:i:s', strtotime($notification['created_at'] . ' +24 hours')) > date('Y-m-d H:i:s'))
        <p class="f-12 mb-0 text-d-none" style="margin-top: 5px;"> {!! \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() !!} </p>
        @else
        <p class="f-12 mb-0 text-d-none" style="margin-top: 5px;"> {!! date('d-m-Y H:i', strtotime($notification['created_at'])) !!} </p>
        @endif
    </a>
</li>
@empty
<li class="m-0 w-100">
    <a href="javascript:;" class="text-center">
        <h4 class="f-16 mb-0 f-700 c-19">
            <i class="fa fa-bell-slash" style="margin-right: 10px;"></i>
            No notification found
        </h4>
    </a>
</li>
@endforelse