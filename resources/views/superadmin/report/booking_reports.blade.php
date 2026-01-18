<div style="max-width:900px;padding:20px;">
    <h5 class="mb-3">Uploaded Reports</h5>
    @if($rows->isEmpty())
        <div class="text-muted">No reports uploaded for this booking.</div>
    @else
        <div class="list-group">
            @foreach($rows as $r)
                <div class="list-group-item d-flex align-items-center justify-content-between">
                    <div style="flex:1;min-width:0;">
                        @php
                            $name = $r->report_description ?? ($r->file_path ? basename($r->file_path) : 'Report');
                            $viewUrl = null;
                            if (!empty($r->pdf_path)) $viewUrl = $r->pdf_path;
                            elseif (!empty($r->generated_report_path)) $viewUrl = $r->generated_report_path;
                            elseif (!empty($r->file_path)) $viewUrl = $r->file_path;
                            // normalize relative paths to full url
                            if ($viewUrl && !preg_match('/^https?:\/\//i', $viewUrl)) {
                                $viewUrl = url($viewUrl);
                            }
                        @endphp
                        @if(!empty($viewUrl))
                            <a href="{{ $viewUrl }}" target="_blank" rel="noopener" class="text-decoration-none text-dark" style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;">{{ $name }}</a>
                        @else
                            <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $name }}</div>
                        @endif
                                <div class="small text-muted mt-1">
                            @if(!empty($r->report_no))
                                Report No: {{ $r->report_no }}
                            @endif
                            @if(!empty($r->ult_r_no))
                                @if(!empty($r->report_no)) &middot; @endif Ult R No: {{ $r->ult_r_no }}
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 ms-3">
                        {{-- pages badge --}}
                        <div style="min-width:36px;text-align:center;background:#f1f5f9;border-radius:18px;padding:6px 8px;font-size:13px;color:#374151;">
                            @if(!empty($r->pages))
                                {{ $r->pages }}p
                            @else
                                -
                            @endif
                        </div>

                        {{-- uploader badge --}}
                        <div style="background:#0b6bff;color:#fff;border-radius:12px;padding:6px 10px;font-size:13px;">
                            {{ $r->uploader_name ?? 'Unknown' }}
                        </div>

                        {{-- view link and date --}}
                        <div style="text-align:right;min-width:160px;">
                            <!-- @if(!empty($r->pdf_path))
                                <a href="{{ url($r->pdf_path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">View PDF</a>
                            @elseif(!empty($r->generated_report_path))
                                <a href="{{ url($r->generated_report_path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">View</a>
                            @elseif(!empty($r->file_path))
                                <a href="{{ url($r->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">View</a>
                            @endif -->
                            <div class="small text-muted">{{ $r->uploaded_at ? \Carbon\Carbon::parse($r->uploaded_at)->format('Y-m-d H:i:s') : '' }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
