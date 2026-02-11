@extends('superadmin.layouts.app')
@section('title', 'Join Meeting')
@push('styles')
<style>
    .zoom-shell {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .zoom-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .zoom-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 20px;
        align-items: center;
        font-size: 13px;
        color: #4b5563;
    }

    .zoom-meta strong {
        color: #111827;
        font-weight: 600;
    }

    .zoom-sdk-frame {
        width: 100%;
        height: calc(155vh - 280px);
        min-height: 520px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #0f172a;
        position: relative;
        overflow: hidden;
    }

    .zoom-helper {
        font-size: 12px;
        color: #6b7280;
        display: flex;
        gap: 6px;
        align-items: center;
    }

    @media (max-width: 991px) {
        .zoom-sdk-frame {
            height: calc(100vh - 320px);
            min-height: 420px;
        }
    }

    @media (max-width: 575px) {
        .zoom-sdk-frame {
            height: 70vh;
            min-height: 360px;
        }
    }
</style>
@endpush

@section('content')
<!-- No global Zoom Bootstrap CSS to avoid conflicts with App Theme -->
<!-- <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.1.6/css/bootstrap.css" /> -->
<!-- <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.1.6/css/react-select.css" /> -->

<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center lisit-inline">
            <div class="col">
                <h3 class="page-title">{{ $meeting->topic }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active">Live Meeting (Component View)</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="zoom-shell">
        <div class="zoom-toolbar">
            <div>
                <div class="fw-semibold">Meeting Room</div>
                <div class="zoom-meta">
                    <span>Meeting ID: <strong>{{ $meeting->meeting_id }}</strong></span>
                    <span>Host: <strong>{{ $meeting->creator?->name ?? 'Host' }}</strong></span>
                    <span>You: <strong>{{ $userName }}</strong></span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="zoom-helper">Use the in-meeting toolbar for mic, camera, share, and chat.</div>
                <a href="{{ route('superadmin.zoom-meetings.index') }}" class="btn btn-outline-secondary">Leave</a>
            </div>
        </div>

        <!-- Container for Zoom Meeting Component -->
        <div id="meetingSDKElement" class="zoom-sdk-frame">
            <!-- The Meeting SDK Component will be rendered here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Use the Embedded (Component View) SDK -->
<script src="https://source.zoom.us/3.5.2/lib/vendor/react.min.js"></script>
<script src="https://source.zoom.us/3.5.2/lib/vendor/react-dom.min.js"></script>
<script src="https://source.zoom.us/3.5.2/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/3.5.2/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/3.5.2/lib/vendor/lodash.min.js"></script>
<script src="https://source.zoom.us/zoom-meeting-embedded-3.5.2.min.js"></script>

<script>
    const client = ZoomMtgEmbedded.createClient();

    let meetingSDKElement = document.getElementById('meetingSDKElement');

    function startMeeting() {
        client.init({
            zoomAppRoot: meetingSDKElement,
            language: 'en-US',
            customize: {
                video: {
                    isResizable: true,
                    viewSizes: {
                        default: {
                            width: 1230,
                            height: 700
                        },
                        ribbon: {
                            width: 300,
                            height: 600
                        }
                    }
                },
                // Add specific UI features including recording buttons
                meetingInfo: ['topic', 'host', 'mn', 'pwd', 'telPwd', 'invite', 'participant', 'dc', 'enctype'],
            }
        });

        client.join({
            sdkKey: "{{ config('services.zoom.sdk_client_id') ?? config('services.zoom.client_id') }}",
            signature: "{{ $signature }}",
            meetingNumber: "{{ $meeting->meeting_id }}",
            password: "{{ $meeting->password }}",
            userName: "{{ $userName }}",
            // userEmail: "optional@email.com", 
            // tk: "registrant_token_if_needed",
        }).then(() => {
            console.log('Joined successfully');
        }).catch((error) => {
            console.error('Join error', error);
            alert("Error joining meeting: " + JSON.stringify(error));
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        startMeeting();
    });
</script>
@endpush
