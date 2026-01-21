<form action="{{ route('superadmin.reception.update', $contact) }}" method="POST">
    @method('PUT')
    @include('superadmin.reception._form')
    <div class="d-flex justify-content-end gap-2 mt-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
