<script src="{{ asset('assets/js/jquery3-6-0.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>

<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('assets/js/main.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
<script src="{{ asset('assets/js/jqueryAdditional.min.js') }}"></script>
<script src="{{ asset('assets/js/lodash.min.js') }}"></script>
<script src="{{ asset('assets/js/select2_4_0_13.min.js') }}"></script>
<script src="{{ asset('assets/js/custom.js') }}"></script>
<script src="{{ asset('assets/js/pusher.min.js') }}"></script>

<script type="text/html" id="searchPannel">
    <input class="form-control f-14" placeholder="Search here">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M15.6932 14.2957L10.7036 9.31023C11.386 8.35146 11.791 7.1584 11.791 5.90142C11.791 2.64178 9.14704 0 5.8847 0C2.62254 0.00017572 0 2.64196 0 5.90142C0 9.16105 2.64397 11.8028 5.90631 11.8028C7.18564 11.8028 8.35839 11.3981 9.31795 10.7163L14.3076 15.7018C14.4994 15.8935 14.7553 16 15.0113 16C15.2672 16 15.523 15.8935 15.7149 15.7018C16.0985 15.2971 16.0985 14.6792 15.6935 14.2956L15.6932 14.2957ZM1.96118 5.90155C1.96118 3.72845 3.73104 1.98133 5.88465 1.98133C8.03826 1.9815 9.82938 3.72845 9.82938 5.90155C9.82938 8.07466 8.05952 9.82178 5.90591 9.82178C3.7523 9.82178 1.96118 8.05338 1.96118 5.90155Z" fill="#7B809A" />
    </svg>
</script>

<script>
    var ServerDataTable;

    var hasSessionMessage = "{{session()->has('message') ? true : false}}";
    var hasSessionError = "{{session()->has('error') ? true : false}}";
    var hasSessionWarning = "{{session()->has('warning') ? true : false}}";
    var hasSessionSuccess = "{{session()->has('success') ? true : false}}";

    $(document).ready(function() {
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.go(1);
        };
    });

    $(document).on("select2:open", () => {
        document.querySelector(".select2-container--open .select2-search__field").focus()
    })

    $('#filterInput').html($('#searchPannel').html());
    $('#filterInput > input').keyup(function() {
        ServerDataTable.search($(this).val()).draw();
    });

    $('.select2').each(function() {
        $(this).select2({
            width: '100%',
            allowClear: true,
        }).on("load", function(e) {
            $(this).prop('tabindex',0);
        }).trigger('load');
        $(this).css('width', '100%');
    });

    function formatText (icon) {
        return $('<span><i class="'+$(icon.element).data('icon')+'"></i> ' + icon.text + '</span>');
    };

    $('.select2Model').each(function() {
        if ($(this).hasClass('hasCustomList')) {
            $(this).select2({
                dropdownParent: $(this).parent(),
                width: '100%',
                templateSelection: formatText,
                templateResult: formatText,
                allowClear: true,
            }).on("load", function(e) {
                $(this).prop('tabindex',0);
            }).trigger('load');
        } else {
            $(this).select2({
                dropdownParent: $(this).parent(),
                width: '100%',
                allowClear: true,
            }).on("load", function(e) {
                $(this).prop('tabindex',0);
            }).trigger('load');
        }
    });

    $('#example').datetimepicker({
        format: "DD/MM/YYYY",
        timeZone: ''
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '#delete', function(e) {
        e.preventDefault();
        var linkURL = $(this).attr("href");
        Swal.fire({
            title: 'Are you sure want to delete?',
            text: "As that can't be undone by doing reverse.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.value) {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: linkURL,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 200) {
                            fireSuccessMessage('{{ $moduleName }} Deleted successfully.');
                            $('.datatableMain').DataTable().ajax.reload();
                        } else {
                            fireErrorMessage(response.error);
                        }
                    }
                });

            }
        });
    });

    $(document).on('click', '#activate', function(e) {
        e.preventDefault();
        var linkURL = $(this).attr("href");
        Swal.fire({
            title: 'Are you sure want to activate?',
            text: "As that can be undone by doing reverse.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: linkURL,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 200) {
                            fireSuccessMessage(response.success);
                            $('.datatableMain').DataTable().ajax.reload();
                        } else {
                            fireErrorMessage(response.error);
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '#deactivate', function(e) {
        e.preventDefault();
        var linkURL = $(this).attr("href");
        Swal.fire({
            title: 'Are you sure want to inactivate?',
            text: "As that can be undone by doing reverse.",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: linkURL,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 200) {
                            fireSuccessMessage(response.success);
                            $('.datatableMain').DataTable().ajax.reload();
                        } else {
                            fireErrorMessage(response.error);
                        }
                    }
                });
            }
        });
    });
    $(document).on('click', '#approveswt', function(e) {
        e.preventDefault();
        var linkURL = $(this).attr("href");
        Swal.fire({
            title: 'Are you sure want to approve?',
            text: "",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: linkURL,
                    type: 'GET',
                    success: function(response) {
                        if (response.status == 200) {
                            fireSuccessMessage(response.success);
                            $('.datatableMain').DataTable().ajax.reload();
                        } else {
                            fireErrorMessage(response.error);
                        }
                    }
                });
            }
        });
    });
    function fireSuccessMessage(message) {
        Swal.fire('Success', message, 'success');
    }

    function fireErrorMessage(message) {
        Swal.fire('Error', message, 'error');
    }

    function fireWarningMessage(message) {
        Swal.fire('Warning', message, 'warning');
    }

    if (hasSessionMessage) {
        fireSuccessMessage("{{ session('message') }}");
    }

    if (hasSessionError) {
        fireErrorMessage("{!! session('error') !!}");
    }

    if (hasSessionWarning) {
        fireWarningMessage("{!! session('warning') !!}");
    }

    if (hasSessionSuccess) {
        fireSuccessMessage("{{ session('success') }}");
    }

    function clearModalValue() {
        $('.modal').on('hidden.bs.modal', function(e) {
            $(this)
                .find("input,textarea,select")
                .val('')
                .end()
                .find("input[type=checkbox], input[type=radio]")
                .prop("checked", "")
                .end();
        })
    }

    function fireErrorMessageWithTitle(title, message) {
        saberToast.error({
            title: ""+title+"",
            text: ""+message+"",
            delay: 200,
            duration: 2500,
            rtl: false,
            position: "top-right"
        })
    }

    function fixDataTableScrollHeight(element) {
        let heightY = 10;
        $(`${element} > tbody > tr`).each(function (element) {
            heightY += parseInt($(this).css('height'));
        });

        $(`${element}`).closest('.dataTables_scrollBody').css('height', `${heightY}px`);
    }

    function x(...args) {
        console.log(...args);
    }

    function generateRandomString() {
        const timestamp = Date.now().toString(36);
        const randomPart = Math.random().toString(36).substr(2, 9);
        return `${timestamp}-${randomPart}`;
    }

    function generateTextColor(hexcolor){
        hexcolor = hexcolor.replace("#", "");
        var r = parseInt(hexcolor.substr(0,2),16);
        var g = parseInt(hexcolor.substr(2,2),16);
        var b = parseInt(hexcolor.substr(4,2),16);
        var yiq = ((r*299)+(g*587)+(b*114))/1000;
        return (yiq >= 128) ? '#000' : '#fff';
    }

    function uuid() {
        return "10000000-1000-4000-8000-100000000000".replace(/[018]/g, c =>
            (+c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> +c / 4).toString(16)
        );
    }

    function rgbToHex(rgb) {
        var rgbArray = rgb.match(/\d+/g).map(Number);
        return "#" + ("0" + rgbArray[0].toString(16)).slice(-2) +
        ("0" + rgbArray[1].toString(16)).slice(-2) +
        ("0" + rgbArray[2].toString(16)).slice(-2);
    }

    function isNumeric(arg) {
        try {
            if (typeof arg !== 'undefined' && arg !== '' && arg !== null && !isNaN(arg)) {
                return true;
            }
            return false;
        } catch (err) {
            return false;
        }
    }

    function isNotEmpty(arg) {
        try {
            if (typeof arg !== 'undefined' && typeof arg == 'string' && arg !== '' && arg !== null) {
                return true;
            }
            return false;
        } catch (err) {
            return false;
        }
    }

    $.validator.addMethod("fileType", function(value, element, param) {
        var fileTypes = param.split('|');
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            var extension = files[i].name.split('.').pop().toLowerCase();
            if ($.inArray(extension, fileTypes) === -1) {
                return false;
            }
        }
        return true;
    }, "Only .png, .jpg, and .jpeg extensions supported");

    $.validator.addMethod("maxFiles", function(value, element, param) {
        return element.files.length <= param;
    }, "Maximum 10 files can be uploaded.");

    $.validator.addMethod("fileSizeLimit", function(value, element, param) {
        var totalSize = 0;
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            totalSize += files[i].size;
        }
        return totalSize <= param;
    }, "Total file size must not exceed 20 MB");

</script>
