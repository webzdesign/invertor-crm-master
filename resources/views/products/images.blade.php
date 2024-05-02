@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/dropzone-basic.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/dropzone.min.css') }}" />
<style>
  .dz-error-message, .dz-error-mark {
    display: none!important;
  }
  .dtcheckbox {
    height: 20px;
    width: 20px;
	}
  .uploaded_image {
    display: flex;
    flex-direction: row;
  }
  .remove {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    right: -12px;
    top: -10px;
    border: none;
  }
  .dz-button {
    position: relative;
    top: 15px;
  }
</style>
@endsection

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">{{ $moduleName }} </li>
@endsection

@section('content')

  <div class="cards">
    <div class="cardsBody pb-0">
        <div class="row">

          <div class="col-md-12 col-sm-12">

          <span class="error" id="filePondErr">  </span>
          <span class="errorContainingImage" style="color:red"></span>
          <div class="dropzone" id="file-dropzone"></div>

          <div id="uploaded_image">
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
          </div>

          </div>

        </div>
      </div>
  </div>

@endsection

@section('script')
<script src="{{ asset('assets/js/dropzone.min.js') }}"></script>
<script>
  Dropzone.autoDiscover = false;
    $(document).ready(function() {
      

      new Dropzone("#file-dropzone", {
        url: "{{ route('product-image', $id) }}",
        acceptedFiles: ".jpeg,.jpg,.png",
        addRemoveLinks: true,
        //maxFiles: 20,
        uploadMultiple :true,
        //parallelUploads: 20,
        headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
        init: function() {
            this.on("error", function(file, message){
                $(this).closest('.dz-error-message').remove()
            });
        },
        removedfile: function(file) {
            var name = file.upload.filename;
            var fileRef;
            return (fileRef = file.previewElement) != null ?
            fileRef.parentNode.removeChild(file.previewElement) : void 0;
        },
        success: function (file, response) {
            $(".errorContainingImage").html("");
            this.removeFile(file);
            $('body').find('#uploaded_image').html('');
            $('body').find('#uploaded_image').html(response);
        }
    });

    $(document).on('click', '.remove-file', function () {
      let self = this;
      let id = this.dataset.id;

        Swal.fire({
            title: 'Are you sure?',
            text: 'This image will be deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            var url = "{{ route('remove-product-images') }}"
            if (result.value) {
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType:'json',
                    data: {
                        '_method' : 'DELETE',
                        'id' : id
                    },
                    success: function(response) {
                        if (response) {
                        $(self).parent().parent().remove();
                        } else {
                        Swal.fire('Error', 'something went wrong.', 'error');
                        }
                    }
                });
            }
        });

   });

    });
</script>
@endsection