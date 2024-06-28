<div class="modal fade" id="change-driver" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered" style="width: 400px;">
        <div class="modal-content">
            <form action="{{ url('') }}" method="POST" id="changeDriver"> @csrf
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5"> <span id="modal-title-change-driver"></span> </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <input type="hidden" name="order_id" id="change-driver-order-id">
                    <div class="row">

                        <label class="c-gr f-500 f-14 w-100 mb-2"> Driver : <span class="text-danger">*</span> </label>
                        <select class="change-driver-select2 select2-hidden-accessible" name="driver_id" id="change-driver-picker">
                            <option value="" selected> --- Select a driver --- </option>
                            @foreach ($drivers as $dname)
                                <option value="{{ $dname['id'] }}"> {{ $dname['name'] }} </option>
                            @endforeach
                        </select>

                    </div>
                </div>
                <div class="modal-footer no-border">
                    <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                    <button type="submit" class="btn-primary f-500 f-14"> Done </button>
                </div>
            </form>
        </div>
    </div>
</div>