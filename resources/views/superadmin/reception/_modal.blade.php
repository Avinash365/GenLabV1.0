<div class="p-2">
    <h5 class="mb-2">Contact Details</h5>
    <div class="mb-2"><strong>Name:</strong> {{ $contact->name }}</div>
    <div class="mb-2"><strong>Role:</strong> {{ $contact->role }}</div>
    <div class="mb-2"><strong>Phone:</strong> {{ $contact->phone }}</div>
    <div class="mb-2"><strong>Alternate Phone:</strong> {{ $contact->alternate_phone }}</div>
    <div class="mb-2"><strong>Address:</strong> {{ $contact->address }}</div>
    <div class="mb-2"><strong>Notes:</strong> {{ $contact->notes }}</div>
    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('superadmin.reception.edit', $contact) }}" class="btn btn-primary">Edit</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>
