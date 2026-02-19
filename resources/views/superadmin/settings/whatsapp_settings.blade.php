@extends('superadmin.layouts.master')

@section('content')
<style>
    .card-footer {
        background-color: transparent !important;
        /* border-top: 1px solid #ebedf2 !important; */
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">WhatsApp API Settings</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('superadmin.whatsapp-settings.update') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3"><i class="fas fa-key me-2"></i>API Configuration</h5>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone Number ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone_number_id" value="{{ old('phone_number_id', optional($setting)->phone_number_id ?? config('services.whatsapp.phone_number_id')) }}" placeholder="e.g. 104xxxxxxxxxxxxx">
                                <small class="text-muted">Found in Meta Developer Portal > WhatsApp > API Setup</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Business Account ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="business_account_id" value="{{ old('business_account_id', optional($setting)->business_account_id ?? config('services.whatsapp.business_id')) }}" placeholder="e.g. 101xxxxxxxxxxxxx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Webhook Verify Token</label>
                                <input type="text" class="form-control" name="webhook_verify_token" value="{{ old('webhook_verify_token', optional($setting)->webhook_verify_token ?? '') }}" placeholder="Enter a random string">
                                <small class="text-muted">Use this exact token when configuring Webhooks in Meta</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                             <h5 class="text-primary mb-3"><i class="fas fa-shield-alt me-2"></i>Authentication</h5>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Access Token <span class="text-danger">*</span></label>
                                <textarea class="form-control font-monospace" name="access_token" rows="8" placeholder="EABx..." style="font-size: 0.85rem;">{{ old('access_token', optional($setting)->access_token ?? config('services.whatsapp.token')) }}</textarea>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Use a <strong>Permanent System User Token</strong>. Temporary tokens expire in 24 hours.
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Default Language Code</label>
                                <input type="text" class="form-control" name="default_language" value="{{ old('default_language', optional($setting)->default_language ?? 'en_US') }}" placeholder="en_US">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="text-primary mb-3"><i class="fas fa-file-code me-2"></i>Template Mapping</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hold Notification</label>
                                <input type="text" class="form-control" name="hold_template_name" value="{{ old('hold_template_name', optional($setting)->hold_template_name ?? 'hold_test1') }}" placeholder="Template Name">
                                <small class="text-muted">Matches Meta Template Name</small>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Report Ready Notification</label>
                                <input type="text" class="form-control" name="report_template_name" value="{{ old('report_template_name', optional($setting)->report_template_name ?? 'report_test4') }}" placeholder="Template Name">
                                <small class="text-muted">Matches Meta Template Name</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Invoice Notification</label>
                                <input type="text" class="form-control" name="dispatch_template_name" value="{{ old('dispatch_template_name', optional($setting)->dispatch_template_name ?? '') }}" placeholder="Template Name">
                                <small class="text-muted">Matches Meta Template Name</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-secondary border-bottom pb-2">Templates Reference</h5>
                            <p class="text-muted small">Use these variable placeholders when creating templates in your Meta Dashboard. The system will automatically replace them with the correct data in the order shown.</p>
                        </div>

                        <!-- Hold Template Card -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header bg-warning text-dark py-2">
                                    <strong>Hold Notification</strong>
                                </div>
                                <div class="card-body p-2" style="font-size: 0.85rem;">
                                    <p class="mb-1"><strong>Variables sent:</strong></p>
                                    <ol class="ps-3 mb-2">
                                        <li>Client Name: {1}</li>
                                        <li>Letter No / Ref No: {2}</li>
                                        <li>Job Order No: {3}</li>
                                        <li>Sample Description: {4}</li>
                                        <li>Reason for Hold: {5}</li>
                                        <li>Hold By (User Name): {6}</li>
                                        <li>Sender Name (Company): {7}</li>
                                    </ol>
                                    <p class="mb-1"><strong>Button {1}:</strong> View Letter (URL)</p>
                                    <hr class="my-2">
                                    <p class="mb-0 text-muted"><em>Example Body:</em><br>
                                    Dear {{1}}, your sample {{4}} (Job: {{3}}) is on hold due to {{5}}. - {{7}}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Report Ready Template Card -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header bg-success text-white py-2">
                                    <strong>Report Ready Notification</strong>
                                </div>
                                <div class="card-body p-2" style="font-size: 0.85rem;">
                                    <p class="mb-1"><strong>Variables sent:</strong></p>
                                    <ol class="ps-3 mb-2">
                                        <li>Client Name: {1}</li>
                                        <li>Reference No: {2}</li>
                                        <li>Invoice No: {3}</li>
                                        <li>Payment Status: {4}</li>
                                        <li>Sender Name (Company): {5}</li>
                                    </ol>
                                    <p class="mb-1"><strong>Button {1}:</strong> View Letter (URL)</p>
                                    <p class="mb-1"><strong>Button {2}:</strong> View Report (URL)</p>
                                    <hr class="my-2">
                                    <p class="mb-0 text-muted"><em>Example Body:</em><br>
                                    Hello {{1}}, your report for Ref {{2}} is ready. Invoice: {{3}} ({{4}}). Regards, {{5}}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Dispatch Template Card -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header bg-info text-white py-2">
                                    <strong>Invoice Notification</strong>
                                </div>
                                <div class="card-body p-2" style="font-size: 0.85rem;">
                                    <p class="mb-1"><strong>Variables sent:</strong></p>
                                    <ol class="ps-3 mb-2">
                                        <li>Client Name: {1}</li>
                                        <li>Reference No: {2}</li>
                                        <li>Job Order No: {3}</li>
                                        <li>Sample Description: {4}</li>
                                        <li>Sender Name (Company): {5}</li>
                                    </ol>
                                    <hr class="my-2">
                                    <p class="mb-0 text-muted"><em>Example Body:</em><br>
                                    Dear {{1}}, Job {{3}} has been uploaded Invoice {{5}} . Thanks, {{6}}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
