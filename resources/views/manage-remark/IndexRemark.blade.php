@extends('Layout')
@section('title', 'Index Remark Page') 
@section('content')
<div class="card mt-5">
    <h2 class="card-header">Remark for Note : {{ $note->name }}</h2>
    <div class="card-body">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <h2>
                <button type="button" id="add" data-bs-toggle="modal" data-bs-target="#addmodal" class="btn btn-success pull-right"> 
                    <i class="fa fa-plus"></i> Create Remark
                </button>
            </h2>
            <h2>
                <a class="btn btn-primary pull-right" href="{{ route('manage-note.IndexNote') }}"> 
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </h2>
        </div>
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th width="50px">No</th>
                    <th>Creator</th>
                    <th>Remark Description</th>
                    <th width="190px">Action</th>
                </tr>
            </thead>
            <tbody id="remarkbody">
            <!-- Displayed using AJAX request (showRemark function) -->
            </tbody>      
        </table>
    </div>
</div>
@endsection

@push('modals')
    @include('manage-remark.ModalRemark')
@endpush

@section('script')
<script type="text/javascript">
    $(document).ready(function(){
        // Get note_id from query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const noteId = urlParams.get('note_id'); // Extract note_id

        if (!noteId) {
            alert('Note ID is missing. Please return to the main page and try again.');
            window.location.href = "{{ route('manage-note.IndexNote') }}";
            return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        showRemark(noteId);

        // Reset Add Remark form when modal is closed
        $('#addmodal').on('hidden.bs.modal', function() {
            $('#addremark')[0].reset();
        });

        // Reset Edit Remark form when modal is closed
        $('#editmodal').on('hidden.bs.modal', function() {
            $('#editremark')[0].reset();
        });

        // Create Remark function
        $('#addremark').on('submit', function(e){
            e.preventDefault();
            var form = $(this).serialize();
            var url = $(this).attr('action');
            $.ajax({
                type: 'POST',
                url: url,
                data: form + `&note_id=${noteId}`, // Append note_id to the request data
                dataType: 'json',
                    success: function(){
                    $('#addmodal').modal('hide');
                    $('#addremark')[0].reset();
                    showRemark(noteId); // Refresh remarks for this note
                },
                error: function(xhr){
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};

                        const message = Object.values(errors)
                            .flat()
                            .join('\n');

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: message || 'Please check your input.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to save remark. Please try again.'
                        });
                    }
                }
            });
        });

        // Display remark on edit form
        $(document).on('click', '.edit', function(event){
            event.preventDefault();
            var id = $(this).data('id');
            var description = $(this).data('description');

            // Check if the user is the remark creator
            $.ajax({
                type: 'POST', 
                url: "{{ route('manage-remark.CheckEditRemark') }}",
                data: { remark_id: id },
                success: function(response) {
                    $('#editmodal').modal('show');
                    $('#description').val(description);
                    $('#remarkid').val(id); 
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        $('#blockEditModal').modal('show');
                        return;
                    }

                    let message = 'Unable to check edit permission. Please try again.';

                    if (xhr.status === 401) {
                        message = 'Your session has expired. Please log in again.';
                    } else if (xhr.status === 404) {
                        message = 'Remark not found or has been deleted.';
                    } else if (xhr.status === 419) {
                        message = 'Your session has expired. Please refresh the page.';
                    } else if (xhr.status === 0) {
                        message = 'Network error. Please check your connection.';
                    } else if (xhr.status >= 500) {
                        message = 'A server error occurred. Please try again later.';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Edit Permission Check Failed',
                        text: message
                    });
                }
            });
        });

        // Edit Remark function
        $('#editremark').on('submit', function(e){
            e.preventDefault();

            var form = $(this).serialize();
            var url = $(this).attr('action');

            $.ajax({
                type: 'POST',
                url: url,
                data: form,
                dataType: 'json',

                success: function() {
                    $('#editmodal').modal('hide');
                    showRemark(noteId);
                },

                error: function(xhr) {
                    if (xhr.status === 422) {
                        const message =
                            xhr.responseJSON?.errors?.description?.[0] ||
                            xhr.responseJSON?.message ||
                            'Please check your input.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: message
                        });

                    } else if (xhr.status === 403) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unauthorized',
                            text: xhr.responseJSON?.error ||
                                'You are not authorized to update this remark.'
                        });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to update remark. Please try again.'
                        });
                    }
                }
            });
        });

        // Delete confirmation
        $(document).on('click', '.delete', function(event){
            event.preventDefault();
            var id = $(this).data('id');

            $.ajax({
                type: 'POST', 
                url: "{{ route('manage-remark.CheckDeleteRemark') }}", 
                data: { remark_id: id },
                success: function(response) {
                    $('#deleteremark').data('id', id); 
                    $('#deletemodal').modal('show');
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        $('#blockDeleteModal').modal('show');
                        return;
                    }

                    let message = 'Unable to check delete permission. Please try again.';

                    if (xhr.status === 401) {
                        message = 'Your session has expired. Please log in again.';
                    } else if (xhr.status === 404) {
                        message = 'Remark not found or has been deleted.';
                    } else if (xhr.status === 419) {
                        message = 'Your session has expired. Please refresh the page.';
                    } else if (xhr.status === 0) {
                        message = 'Network error. Please check your connection.';
                    } else if (xhr.status >= 500) {
                        message = 'A server error occurred. Please try again later.';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Permission Check Failed',
                        text: message
                    });
                }
            });
        });

        // Delete Remark function
        $('#deleteremark').click(function(){
            var id = $(this).data('id');

            $.ajax({
                type: 'POST',
                url: "{{ route('manage-remark.DeleteRemark') }}",
                data: { id: id },
                dataType: 'json',

                success: function() {
                    $('#deletemodal').modal('hide');
                    showRemark(noteId);
                },

                error: function(xhr) {
                    if (xhr.status === 403) {
                        $('#deletemodal').modal('hide');
                        $('#blockDeleteModal').modal('show');

                    } else if (xhr.status === 419) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Session Expired',
                            text: 'Please refresh the page and try again.'
                        });

                    } else if (xhr.status === 422 || xhr.status === 404) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: xhr.responseJSON?.errors?.id?.[0]
                                || xhr.responseJSON?.message
                                || 'Remark not found or invalid.'
                        });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to delete remark. Please try again.'
                        });
                    }
                }
            });
        });

    });

    function showRemark(noteId) {
        $.ajax({
            type: 'GET',
            url: "{{ route('manage-remark.ListRemark') }}",
            data: {
                note_id: noteId
            },
            dataType: 'html',

            success: function(data) {
                $('#remarkbody').empty().html(data);
            },

            error: function(xhr) {
                let message = 'Unable to load remarks. Please try again.';

                if (xhr.status === 401) {
                    message = 'Your session has expired. Please log in again.';
                } else if (xhr.status === 419) {
                    message = 'Your session has expired. Please refresh the page.';
                } else if (xhr.status === 500) {
                    message = 'A server error occurred. Please try again later.';
                } else if (xhr.status === 0) {
                    message = 'Network error. Please check your connection.';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Load Remarks',
                    text: message
                });
            }
        });
    }
</script>
@endsection
