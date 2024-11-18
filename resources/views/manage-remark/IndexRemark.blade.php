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
                    }
                }
            });
        });

        // Edit Remark function
        $('#editremark').on('submit', function(e){
            e.preventDefault();
            var form = $(this).serialize();
            var url = $(this).attr('action');
            $.post(url, form, function(data){
                $('#editmodal').modal('hide');
                showRemark(noteId); 
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
                    }
                }
            });
        });

        // Delete Remark function
        $('#deleteremark').click(function(){
            var id = $(this).data('id');
            $.post("{{ route('manage-remark.DeleteRemark') }}", { id: id }, function(){
                $('#deletemodal').modal('hide');
                showRemark(noteId); 
            });
        });

    });

    function showRemark(noteId){
        $.get("{{ route('manage-remark.ListRemark') }}", { note_id: noteId }, function(data){
            $('#remarkbody').empty().html(data);
        });
    }
</script>
@endsection
