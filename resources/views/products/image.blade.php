<div class="row mt-3 sort_images">
    @forelse($images as $image)
      <div class="col-md-2 mb-3 imageListitem">
        <div style="* border: 1px solid #c7c7c7">
            <a href="{{ $image->image }}" target="_blank"><img src="{{ $image->image }}" class="getdata w-100 shadow-1-strong rounded" style="object-fit: cover;height:100px;" /></a>
            <button type="button" class="center remove remove-file bg-danger d-flex align-items-center justify-content-center position-absolute" data-id="{{$image->id}}">
              <i class="fa fa-close" style="color: white;" aria-hidden="true"></i>
            </button>
        </div>
      </div>
      @empty
      @endforelse
  </div>