@extends('manage-note.LayoutNote')
  
@section('content')
<div class="card mt-5">
  <h2 class="card-header">List Note</h2>
  <div class="card-body">
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn btn-primary btn-sm" href="{{ route('manage-note.IndexNote') }}"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong> <br/>
                {{ $note->name }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mt-2">
            <div class="form-group">
                <strong>Description:</strong> <br/>
                {{ $note->description }}
            </div>
        </div>
    </div>
  </div>
</div>
@endsection
