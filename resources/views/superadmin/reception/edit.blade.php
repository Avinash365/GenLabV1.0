@extends('superadmin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Edit Contact</h5>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('superadmin.reception.update', $contact) }}" method="POST">
            @method('PUT')
            @include('superadmin.reception._form')
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('superadmin.reception.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
