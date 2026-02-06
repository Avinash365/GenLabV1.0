@extends('superadmin.layouts.app')
@section('title', 'Join Meeting')
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
            <div class="col-auto float-end">
                <a href="{{ route('superadmin.zoom-meetings.index') }}" class="btn btn-outline-secondary">Leave</a>
            </div>
        </div>
    </div>

    <!-- Container for Zoom Meeting Component -->
    <!-- Important: Specify explicit height/width for the container -->
    <div id="meetingSDKElement" style="width: 100%; height: 75vh; border: 1px solid #e3e3e3; position: relative; background: #fff;">
        <!-- The Meeting SDK Component will be rendered here -->
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
                            width: 1000,
                            height: 600
                        },
                        ribbon: {
                            width: 300,
                            height: 600
                        }
                    }
                },
                // optional UI customizations
                // meetingInfo: ['topic', 'host', 'mn', 'pwd', 'telPwd', 'invite', 'participant', 'dc', 'enctype'],
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
            zak: "", // Zoom Access Token if needed for host
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
