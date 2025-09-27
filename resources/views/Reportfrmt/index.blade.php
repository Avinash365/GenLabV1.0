{{-- resources/views/superadmin/editor.blade.php --}}
@extends('superadmin.layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Top bar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Generate / Edit Report Format</h4>
        <button class="btn btn-success" type="button" data-bs-toggle="offcanvas" data-bs-target="#reportList" aria-controls="reportList">
            <i class="fa fa-plus"></i> Edit Report Format
        </button>
    </div>

    {{-- Success / Error messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif 

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="editorForm" method="POST" action="{{ route('editor.save') }}">
        @csrf
        <input type="hidden" name="editing_report_id" id="editing_report_id" value="{{ old('editing_report_id', session('editing_report_id', '')) }}">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="report_no" class="form-label">Report Name</label>
                <input type="text" name="report_no" id="report_no" class="form-control" 
                       value="{{ old('report_no', session('report_no', '')) }}" required>
            </div>
            <div class="col-md-6">
                <label for="report_description" class="form-label">Description</label>
                <input type="text" name="report_description" id="report_disc" class="form-control" 
                       value="{{ old('report_description', session('report_description', '')) }}" required>
            </div>
        </div>

        <textarea id="jodit-editor" name="content">{{ old('content', session('content', '')) }}</textarea>

        <div class="d-flex justify-content-end gap-2 mt-3 mb-2">
            <button type="submit" class="btn btn-primary">Save / Update</button>
            <button type="submit" class="btn btn-secondary" onclick="document.getElementById('editing_report_id').value='';">Generate New</button>
        </div>
    </form>
</div>

{{-- Offcanvas Sidebar --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="reportList" aria-labelledby="reportListLabel">
    <div class="offcanvas-header">
        <h5 id="reportListLabel">Saved Reports</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">

        {{-- 🔍 Search bar --}}
        <div class="mb-3">
            <input type="text" id="searchReports" class="form-control" placeholder="Search report by name...">
        </div>

        <div id="reportListContainer">
            @forelse($reports as $report)
                <div class="card mb-2 report-card">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <span class="report-title"><strong>{{ $report->report_no }}</strong></span>

                        <div class="d-flex gap-1">
                            {{-- Load report button --}}
                            <button type="button" class="btn btn-sm btn-outline-primary load-report" 
                                data-id="{{ $report->id }}"
                                data-content="{{ Storage::disk('public')->get($report->file_path) }}"
                                data-name="{{ $report->report_no }}"
                                data-description="{{ $report->report_description }}"
                                title="Load Report">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            {{-- Delete button --}}
                            <button type="button" class="btn btn-sm btn-danger delete-report-btn" 
                                    data-id="{{ $report->id }}" 
                                    title="Delete Report">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <p>No reports saved yet.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteReportModal" tabindex="-1" aria-labelledby="deleteReportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteReportForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title" id="deleteReportModalLabel">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this report?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Yes, Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Jodit Editor --}}
<link href="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jodit@latest/es2021/jodit.fat.min.js"></script>

<script>
    // Initialize Jodit with table border override
    const editor = Jodit.make('#jodit-editor', { 
    height: 400,
    iframe: true,
    iframeStyle: `
        table, th, td {
            border: 1px solid #000;
            border-collapse: collapse;
        }
    `
});


    const editingIdInput = document.getElementById('editing_report_id');

    // Load report into editor
    document.querySelectorAll('.load-report').forEach(btn => {
        btn.addEventListener('click', function() {
            const content = this.dataset.content;
            const name = this.dataset.name;
            const description = this.dataset.description;
            const id = this.dataset.id;

            editor.value = content;
            document.getElementById('report_no').value = name;
            document.getElementById('report_disc').value = description;
            editingIdInput.value = id;

            bootstrap.Offcanvas.getInstance(document.getElementById('reportList')).hide();
        });
    });

    // Delete report modal
    document.querySelectorAll('.delete-report-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const reportId = this.dataset.id;
            const form = document.getElementById('deleteReportForm');
            form.action = `/editor/delete/${reportId}`;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteReportModal'));
            deleteModal.show();
        });
    });

    // 🔍 Search filter
    document.getElementById('searchReports').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.report-card').forEach(card => {
            const title = card.querySelector('.report-title').textContent.toLowerCase();
            card.style.display = title.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection
