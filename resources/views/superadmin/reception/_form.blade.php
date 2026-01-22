@csrf
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $contact->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Role</label>
    <input type="text" name="role" class="form-control" value="{{ old('role', $contact->role ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $contact->phone ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Alternate Phone</label>
    <input type="text" name="alternate_phone" class="form-control" value="{{ old('alternate_phone', $contact->alternate_phone ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control">{{ old('address', $contact->address ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control">{{ old('notes', $contact->notes ?? '') }}</textarea>
</div>
