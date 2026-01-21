@extends('superadmin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Contact Details</h5>
    </div>
    <div class="card-body">
        <p><strong>Name:</strong> {{ $contact->name }}</p>
        <p><strong>Role:</strong> {{ $contact->role }}</p>
        <p><strong>Phone:</strong> {{ $contact->phone }}</p>
        <p><strong>Alternate Phone:</strong> {{ $contact->alternate_phone }}</p>
        <p><strong>Address:</strong> {{ $contact->address }}</p>
        <p><strong>Notes:</strong> {{ $contact->notes }}</p>
        <a href="{{ route('superadmin.reception.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('superadmin.reception.edit', $contact) }}" class="btn btn-primary">Edit</a>
    </div>
</div>
@endsection
