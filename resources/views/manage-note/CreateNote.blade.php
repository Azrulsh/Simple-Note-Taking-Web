@extends('manage-note.LayoutNote')
    
@section('content') 
<div class="card mt-5">
  <h2 class="card-header">Add New Note</h2>
  <div class="card-body">
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn btn-primary btn-sm" href="{{ route('manage-note.IndexNote') }}"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <form method="POST" action="{{ route('manage-note.StoreNote') }}">
        @csrf
        <div class="mb-3">
            <label for="inputName" class="form-label"><strong>Name:</strong></label>
            <input 
                type="text" 
                name="name" 
                class="form-control @error('name') is-invalid @enderror" 
                id="inputName" 
                placeholder="Enter the name of the note...">
            @error('name')
                <div class="form-text text-danger">{{ $message }}</div>
            @enderror
        </div>
  
        <div class="mb-3">
            <label for="inputDescription" class="form-label"><strong>Description:</strong></label>
            <textarea 
                class="form-control @error('description') is-invalid @enderror" 
                style="height:150px" 
                name="description" 
                id="inputDescription" 
                placeholder="Enter the description of the note..."></textarea>
            @error('description')
                <div class="form-text text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" id="create" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Create</button>
    </form>
  </div>
</div>
@endsection