@extends('manage-note.LayoutNote')
    
@section('content')
<div class="card mt-5">
  <h2 class="card-header">Edit Note</h2>
  <div class="card-body">
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn btn-primary btn-sm" href="{{ route('manage-note.IndexNote') }}"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <form action="{{ route('manage-note.UpdateNote',$note->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="inputName" class="form-label"><strong>Name:</strong></label>
            <input 
                type="text" 
                name="name" 
                value="{{ $note->name }}"
                class="form-control @error('name') is-invalid @enderror" 
                id="inputName" 
                placeholder="Name">
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
                placeholder="Description">{{ $note->description }}</textarea>
            @error('description')
                <div class="form-text text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" id="edit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Edit</button>
    </form>
  </div>
</div>
@endsection