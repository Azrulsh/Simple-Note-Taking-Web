@forelse ($remarks as $remark)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $remark->user->name }}</td>
        <td>{{ $remark->description }}</td>
        <td>
            @if ($remark->user_id === Auth::id())
                <a href="#" class='btn btn-primary edit' data-id='{{ $remark->id }}' data-description='{{ $remark->description }}'>
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
                <a href="#" class='btn btn-danger delete' data-id='{{ $remark->id }}'>
                    <i class="fa-solid fa-trash"></i> Delete
                </a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center">No remarks found for this note.</td>
    </tr>
@endforelse
