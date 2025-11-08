<?php $page = 'email-reply'; ?>
@extends('superadmin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="content p-0">
        <div class="d-md-flex">

            {{-- ================= Sidebar ================= --}}
            @include('email.email-sidebar')

            {{-- ================= Mail Detail ================= --}}
            <div class="mail-detail bg-white flex-fill border-end border-bottom p-1">
                <div class="active slimscroll h-100">
                    <div class="slimscroll-active-sidebar">


                        {{-- ================= Toolbar ================= --}}
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 border-bottom mb-3 pb-3">
                            <div class="dropdown">
                                <button class="btn btn-white border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <span class="badge badge-dark rounded-circle badge-xs me-1">5</span> Peoples
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a class="dropdown-item rounded-1" href="#">Peoples</a></li>
                                    <li><a class="dropdown-item rounded-1" href="#">Rufana</a></li>
                                    <li><a class="dropdown-item rounded-1" href="#">Sean Hill</a></li>
                                    <li><a class="dropdown-item rounded-1" href="#">Cameron Drake</a></li>
                                </ul>
                            </div>

                            <div class="d-flex align-items-center">
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-arrow-left"></i></a>
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-arrow-back-up"></i></a>
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-arrow-forward"></i></a>
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-bookmarks-filled"></i></a>
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-archive-filled"></i></a>
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-mail-opened-filled"></i></a>
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-printer"></i></a>
                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-star-filled text-warning"></i></a>
                            </div>
                        </div>

                        {{-- ================= Email Header ================= --}}
                        <div class="bg-light-500 rounded p-3 mb-3">
                            <div class="d-flex align-items-center flex-fill border-bottom mb-3 pb-3">
                                <a href="#" class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
                                    <img src="{{ URL::asset('build/img/profiles/avatar-01.jpg') }}" alt="Avatar">
                                </a>
                                <div class="flex-fill">
                                    <div class="d-flex align-items-start justify-content-between flex-wrap row-gap-2">
                                        <div>
                                            <h6 class="mb-1">{{ $email['from_name'] ?? 'Unknown Sender' }}</h6>
                                            <p class="mb-0">{{ $email['subject'] ?? 'No Subject' }}</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="me-2 mb-0">{{ $email['date'] ?? '' }}</p>
                                            <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-printer"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-wrap row-gap-2">
    <p class="fs-12 mb-0 text-dark me-3">
        <span class="text-gray">From: </span>{{ $email['from_email'] ?? '-' }}
    </p>

    <p class="fs-12 mb-0 text-dark me-3">
        <span class="text-gray">To: </span>
        @if(!empty($email['to']))
            {{ implode(', ', array_map(fn($t) => $t['email'], $email['to'])) }}
        @else
            -
        @endif
    </p>

    <p class="fs-12 mb-0 text-dark me-3">
        <span class="text-gray">Cc: </span>
        @if(!empty($email['cc']))
            {{ implode(', ', array_map(fn($c) => $c['email'], $email['cc'])) }}
        @else
            -
        @endif
    </p>

    <p class="fs-12 mb-0 text-dark">
        <span class="text-gray">Bcc: </span>
        @if(!empty($email['bcc']))
            {{ implode(', ', array_map(fn($b) => $b['email'], $email['bcc'])) }}
        @else
            -
        @endif
    </p>
</div>

                        </div>

                        {{-- ================= Email Body ================= --}}
                        <div class="card shadow-none mb-3">
                            <div class="card-body">
                                <div>{!! $email['body'] !!}</div>

                               @if(!empty($email['attachments']))
                                    <div class="d-flex align-items-center email-attach flex-wrap">
                                        @foreach($email['attachments'] as $attachment)
                                            <a href="{{ $attachment['base64'] }}" data-fancybox="gallery" class="avatar avatar-xl me-3 mb-2" target="_blank">
                                                <img src="{{ $attachment['base64'] }}" class="rounded" alt="{{ $attachment['name'] }}">
                                                <span class="avatar avatar-md avatar-rounded"><i class="ti ti-eye"></i></span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        </div>

                        {{-- ================= Reply Section ================= --}}
                        <div class="card shadow-none">
                            <div class="card-body">
                                <form action="{{ route('emails.replyOnEmail', $email['id']) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="border rounded">
                                        <div class="p-3 position-relative pb-2 border-bottom">
                                            <div class="tag-with-img d-flex align-items-center">
                                                <label class="form-label me-2">To</label>
                                                <input type="text" name="to" class="form-control border-0 h-100" value="{{ $email['from_email'] ?? '' }}">
                                                <!-- <input type="text" name="to" class="form-control border-0 h-100" value= "dksmaurya09@gmail.com"> -->
                                            </div>
                                            <div class="d-flex align-items-center email-cc mt-2">
                                                <a href="#" class="d-inline-flex me-2">Cc</a>
                                                <a href="#" class="d-inline-flex">Bcc</a>
                                            </div>
                                        </div>
                                        <div class="p-3">
                                            <div class="mb-3">
                                                <textarea name="message" rows="6" class="form-control border-0 p-0" placeholder="Write your reply..."></textarea>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between border-top p-3">
                                            <div class="d-flex align-items-center">
                                                <label class="btn btn-icon btn-sm rounded-circle me-2">
                                                    <i class="ti ti-paperclip"></i>
                                                    <input type="file" name="attachments[]" hidden multiple>
                                                </label>
                                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-photo"></i></a>
                                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-link"></i></a>
                                            </div>
                                            <div id="attachment-list" class="mt-2"></div>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-trash"></i></a>
                                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center ms-2">
                                                    Send <i class="ti ti-arrow-right ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- SweetAlert2 popup -->
                                @if(session('success'))
                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                    <script>
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: '{{ session('success') }}',
                                            timer: 3000,
                                            timerProgressBar: true,
                                            showConfirmButton: false
                                        });
                                    </script>
                                @endif

                                @if(session('error'))
                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                    <script>
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error!',
                                            text: '{{ session('error') }}',
                                            timer: 3000,
                                            timerProgressBar: true,
                                            showConfirmButton: false
                                        });
                                    </script>
                                @endif
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-dark btn-sm">View Older Messages</a>
                        </div>

                    </div> {{-- slimscroll-active-sidebar --}}
                </div> {{-- active slimscroll --}}
            </div> {{-- mail-detail --}}

        </div> {{-- d-md-flex --}}
    </div> {{-- content --}}
</div> {{-- container-fluid --}}
@endsection

<script>
    const fileInput = document.querySelector('input[name="attachments[]"]');
    const attachmentList = document.getElementById('attachment-list');

    fileInput.addEventListener('change', () => {
        attachmentList.innerHTML = ''; // clear previous list
        Array.from(fileInput.files).forEach(file => {
            const fileName = document.createElement('div');
            fileName.classList.add('badge', 'bg-secondary', 'me-1', 'mb-1', 'p-2');
            fileName.textContent = file.name;
            attachmentList.appendChild(fileName);
        });
    });
</script>

<style>
    #attachment-list .badge {
        display: inline-block;
        font-size: 0.85rem;
        cursor: default;
    }
</style>
