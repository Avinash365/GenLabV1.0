<?php $page = 'email'; ?>
@extends('superadmin.layouts.app') 
@section('content')
<!-- Toast container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
    <!-- Success Toast -->
    @if(session('success'))
    <div id="successToast" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    @endif

    <!-- Error Toast -->
    @if(session('error'))
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
    <div id="validationToast" class="toast align-items-center text-bg-warning border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    @endif
</div>
<!-- End Toast container -->

    <div class="">
        <div class="content p-0">
            <div class="d-md-flex">
                @include('email.email-sidebar')
                <div class="bg-white flex-fill border-end border-bottom mail-notifications">
                    <div class="active slimscroll h-100">
                        <div class="slimscroll-active-sidebar">	
                            <div class="p-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                                    <div>
                                        <h5 class="mb-1">Inbox</h5>
                                       
                                    </div>
                                   
                                        <form method="GET" action="{{ route('email.index', ['id' => $activeId]) }}" class="d-flex align-items-center">
    <div class="position-relative input-icon me-3">
        <span class="input-icon-addon">
            <i class="ti ti-search"></i>
        </span>
        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search Email">
    </div>
    <button type="submit" class="btn btn-sm btn-primary me-2">
        <i class="ti ti-search"></i> Search
    </button>
    <a href="{{ route('email.index', ['id' => $activeId]) }}" class="btn btn-sm btn-secondary">
        <i class="ti ti-refresh"></i> Reset
    </a>
</form>
                                        <!-- <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-filter-edit"></i></a>
                                            <a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-settings"></i></a>
                                            <a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-refresh"></i></a>
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="list-group list-group-flush mails-list">
                                @foreach($emails as $email) 
                                <div class="list-group-item border-bottom p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="form-check form-check-md d-flex align-items-center flex-shrink-0 me-2">
                                            <input class="form-check-input" type="checkbox">
                                        </div> 
                                        @php
                                            $params = ['id' => $email['id'], 'uid' => $email['uid']];
                                            if (isset($type)) {
                                                $params['type'] = $type;
                                            }
                                        @endphp
                                        <div class="d-flex align-items-center flex-wrap row-gap-2 flex-fill">
                                            <a href="{{ route('emails.reply', $params)}}" class="avatar bg-purple avatar-rounded me-2">
                                                <span class="avatar-title">📨</span>
                                            </a>
                                            <div class="flex-fill">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <div> 
                                                        @if(!isset($type))
                                                            <h6 class="mb-1"><a href="{{ route('emails.reply', $params) }}">{{ $email['from_name'] ?? 'Unknown' }}</a></h6>
                                                        @endif
                                                        <span class="fw-semibold">{{ $email['subject'] ?? 'No Subject' }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <div class="dropdown">
                                                            <button class="btn btn-icon btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ti ti-dots"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="{{url('email-reply')}}">Open Email</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Reply</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Reply All</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Forward</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Forward As Attachment</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Mark As Unread</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Move to Junk</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Mute</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Delete</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Archive</a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item rounded-1" href="javascript:void(0);">Move To</a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <span><i class="ti ti-point-filled text-success"></i>{{ $email['date'] ?? '' }}</span>
                                                    </div>
                                                </div>
                                                <p>{{ \Illuminate\Support\Str::limit($email['body'], 50) ?? '' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="d-flex align-items-center btn btn-sm bg-transparent-dark me-2"><i class="ti ti-folder-open me-2"></i>3</span>
                                            <span class="d-flex align-items-center btn btn-sm bg-transparent-dark"><i class="ti ti-photo me-2"></i>+24</span>
                                        </div>
                                
                                    </div>
                                </div> 
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="mt-3 px-3">
    {{ $emails->links('pagination::bootstrap-5') }}
</div> 

@endsection
