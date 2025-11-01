<div class="email-sidebar border-end border-bottom">
    <div class="active slimscroll h-100">
        <div class="slimscroll-active-sidebar">
            <div class="p-3">

                <!-- ====================== Accounts ====================== -->
                <div class="mt-4">
                    <h5 class="mb-2">Accounts</h5>
                    <div class="d-block mb-4 pb-4 border-bottom email-tags">

                        @php
                            $activeId = request()->route('id');
                            $activeAccount = $emailAccounts->firstWhere('id', $activeId) ?? $emailAccounts->first();
                            $otherAccounts = $emailAccounts->filter(fn($a) => $a->id !== $activeAccount->id);
                        @endphp

                        <!-- Active Account -->
                        @if($activeAccount)
                            <a href="{{ route('email.index', ['id' => $activeAccount->id]) }}"
                               class="d-flex align-items-center justify-content-between p-2 rounded bg-light border active-account">
                                <span class="d-flex align-items-center fw-medium">
                                    <i class="ti ti-user text-gray me-2"></i>
                                    {{ $activeAccount->email }}
                                </span>
                                <!-- <span class="fw-semibold fs-12 badge text-gray rounded-pill">
                                    {{ $activeAccount->unread_count ?? 0 }}
                                </span> -->
                            </a>
                        @endif

                        <!-- Collapsible Other Accounts -->
                        @if($otherAccounts->count() > 0)
                            <div class="collapse mt-2" id="extraAccounts">
                                @foreach($otherAccounts as $account)
                                    <a href="{{ route('email.index', ['id' => $account->id]) }}"
                                       class="d-flex align-items-center justify-content-between p-2 rounded">
                                        <span class="d-flex align-items-center fw-medium">
                                            <i class="ti ti-user text-gray me-2"></i>
                                            {{ $account->email }}
                                        </span>
                                        <!-- <span class="fw-semibold fs-12 badge text-gray rounded-pill">
                                            {{ $account->unread_count ?? 0 }}
                                        </span> -->
                                    </a>
                                @endforeach 
                                <!-- ====================== Add new  Emails ====================== -->
                                    <a href="javascript:void(0);" 
                                        class="d-flex align-items-center p-2 rounded mb-2 mt-2"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#addAccountModal">
                                            <i class="ti ti-user-plus me-2"></i>
                                            Add New Account
                                    </a>
                            </div>

                            <!-- View More Button -->
                            <div class="view-all mt-2 text-center">
                                <a class="viewall-button fw-medium text-primary" data-bs-toggle="collapse"
                                   href="#extraAccounts" role="button" aria-expanded="false"
                                   aria-controls="extraAccounts">
                                    <span>View More</span>
                                    <i class="fa fa-chevron-down fs-10 ms-2"></i>
                                </a>
                            </div>
                        @endif 

                    </div>
                </div>

                <!-- ====================== Compose Button ====================== -->
                <a href="javascript:void(0);" class="btn btn-primary w-100" id="compose_mail"><i class="ti ti-edit me-2"></i>Compose</a>

                <!-- ====================== Email Folders ====================== -->
                <!-- ====================== Email Folders ====================== -->
                    <div class="mt-4">
                        <h5 class="mb-2">Emails</h5>
                        <div class="d-block mb-4 pb-4 border-bottom email-tags">

                            <!-- Inbox -->
                            <a href="{{ route('email.index', ['id' => $activeAccount->id ?? null]) }}"
                            class="d-flex align-items-center justify-content-between p-2 rounded 
                                    {{ request()->routeIs('email.index') ? 'bg-light fw-bold border' : '' }}">
                                <span class="d-flex align-items-center fw-medium">
                                    <i class="ti ti-inbox text-gray me-2"></i>Inbox
                                </span>
                            </a>

                            <!-- Starred (placeholder only) -->
                            <a href="javascript:void(0);" class="d-flex align-items-center justify-content-between p-2 rounded">
                                <span class="d-flex align-items-center fw-medium">
                                    <i class="ti ti-star text-gray me-2"></i>Starred
                                </span>
                            </a>

                            <!-- Sent -->
                            <a href="{{ route('email.allSentEmail', ['id' => $activeAccount->id ?? null]) }}"
                            class="d-flex align-items-center justify-content-between p-2 rounded 
                                    {{ request()->routeIs('email.allSentEmail') ? 'bg-light fw-bold border' : '' }}">
                                <span class="d-flex align-items-center fw-medium">
                                    <i class="ti ti-rocket text-gray me-2"></i>Send
                                </span>
                            </a>

                            <!-- Drafts -->
                            <a href="javascript:void(0);" class="d-flex align-items-center justify-content-between p-2 rounded">
                                <span class="d-flex align-items-center fw-medium">
                                    <i class="ti ti-file text-gray me-2"></i>Drafts
                                </span>
                            </a>

                            <!-- Deleted -->
                            <a href="javascript:void(0);" class="d-flex align-items-center justify-content-between p-2 rounded">
                                <span class="d-flex align-items-center fw-medium">
                                    <i class="ti ti-trash text-gray me-2"></i>Deleted
                                </span>
                            </a>

                            <!-- Spam -->
                            <a href="javascript:void(0);" class="d-flex align-items-center justify-content-between p-2 rounded">
                                <span class="d-flex align-items-center fw-medium">
                                    <i class="ti ti-info-octagon text-gray me-2"></i>Spam
                                </span>
                            </a>

                            <div>
                                <div class="collapse" id="moreEmailMenu">
                                    <a href="javascript:void(0);" class="d-flex align-items-center justify-content-between p-2 rounded">
                                        <span class="d-flex align-items-center fw-medium">
                                            <i class="ti ti-location-up text-gray me-2"></i>Important
                                        </span>
                                    </a>
                                    <a href="javascript:void(0);" class="d-flex align-items-center justify-content-between p-2 rounded">
                                        <span class="d-flex align-items-center fw-medium">
                                            <i class="ti ti-transition-top text-gray me-2"></i>All Emails
                                        </span>
                                    </a>
                                </div>

                                <div class="view-all mt-2 text-center">
                                    <a class="viewall-button fw-medium text-primary" data-bs-toggle="collapse"
                                    href="#moreEmailMenu" role="button" aria-expanded="false"
                                    aria-controls="moreEmailMenu">
                                        <span>Show More</span>
                                        <i class="fa fa-chevron-down fs-10 ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                
            </div>
        </div>
    </div>
</div>

<!-- ====================== Add Account Modal ====================== -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white" id="addAccountLabel">Add New Account</h5>
                <button type="button" class="btn-close custom-btn-close bg-transparent fs-16 text-white position-static"
                        data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <form id="addAccountForm" action="{{ route('email.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@domain.com" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Incoming Mail Server (IMAP)</label>
                            <input type="text" name="imap_host" class="form-control" placeholder="mail.example.com" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="imap_port" class="form-control" placeholder="993" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Encryption</label>
                            <select name="encryption" class="form-select">
                                <option value="SSL">SSL</option>
                                <option value="TLS">TLS</option>
                                <option value="STARTTLS">STARTTLS</option>
                                <option value="NONE">None</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Outgoing Mail Server (SMTP)</label>
                            <input type="text" name="smtp_host" class="form-control" placeholder="smtp.example.com" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="smtp_port" class="form-control" placeholder="465" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="SSL">SSL</option>
                                <option value="TLS">TLS</option>
                                <option value="STARTTLS">STARTTLS</option>
                                <option value="NONE">None</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center ms-2">
                        Add Account <i class="ti ti-check ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div> 
</div>

<!-- ====================== Compose Mail ====================== -->
<div id="compose-view" class="position-fixed" style="width:600px; bottom:20px; right:20px; z-index:1055;">
    <div class="bg-white border-0 rounded compose-view shadow">
        <!-- Header -->
        <div class="compose-header d-flex align-items-center justify-content-between bg-dark p-3" id="compose-header">
            <h5 class="text-white mb-0">Compose New Email</h5>
            <div class="d-flex align-items-center">
                <a href="javascript:void(0);" class="d-inline-flex me-2 text-white fs-16" id="compose-minimize"><i class="ti ti-minus"></i></a>
                <a href="javascript:void(0);" class="d-inline-flex me-2 text-white fs-16" id="compose-maximize"><i class="ti ti-maximize"></i></a>
                <button type="button" class="btn-close custom-btn-close bg-transparent fs-16 text-white position-static" id="compose-close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        </div>

        <!-- ====================== Form ====================== -->
        <form id="composeForm" action="{{ route('emails.send') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input id="through-input" type="hidden" value="{{ $activeAccount->id ?? '' }}" name="id">

            <!-- To / Cc / Bcc -->
            <div class="p-3 border-bottom">
                <div class="tag-with-img d-flex align-items-center mb-2">
                    <label class="form-label me-2 mb-0">To</label>
                    <input id="to-input" type="text" class="form-control border-0 h-100" placeholder="Add recipient">
                </div>
                <div id="to-tags" class="mb-2"></div>

                <div class="d-flex gap-2 mb-2">
                    <a href="javascript:void(0);" id="show-cc" class="small">Cc</a>
                    <a href="javascript:void(0);" id="show-bcc" class="small">Bcc</a>
                </div>

                <div id="cc-section" class="d-none mb-2">
                    <label class="form-label me-2 mb-0">Cc</label>
                    <input id="cc-input" type="text" class="form-control" placeholder="Add Cc">
                    <div id="cc-tags"></div>
                </div>

                <div id="bcc-section" class="d-none mb-2">
                    <label class="form-label me-2 mb-0">Bcc</label>
                    <input id="bcc-input" type="text" class="form-control" placeholder="Add Bcc">
                    <div id="bcc-tags"></div>
                </div>
            </div>

            <!-- Subject & Message -->
            <div class="p-3 border-bottom">
                <input type="text" class="form-control mb-3" name="subject" placeholder="Subject" required>
                <textarea class="form-control" name="message" rows="6" placeholder="Compose your message..." required></textarea>
            </div>

            <!-- Attachments -->
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <label for="attachments" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                        <i class="ti ti-paperclip me-1"></i> Attach Files
                    </label>
                    <input type="file" id="attachments" name="attachments[]" multiple hidden>
                    <div id="attachment-names" class="mt-2 d-flex flex-wrap"></div>
                </div>

                <div class="d-flex align-items-center gap-1">
                    <a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-calendar-repeat"></i></a>
                    <a href="javascript:void(0);" class="btn btn-icon btn-sm rounded-circle"><i class="ti ti-trash"></i></a>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center ms-2">
                        Send <i class="ti ti-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ====================== JS Logic ====================== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const compose = document.getElementById('compose-view');
    const header = document.getElementById('compose-header');

    // ========== Drag ==========
    let isDragging = false, offsetX, offsetY;
    header.addEventListener('mousedown', (e) => {
        isDragging = true;
        offsetX = e.clientX - compose.getBoundingClientRect().left;
        offsetY = e.clientY - compose.getBoundingClientRect().top;
    });
    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            compose.style.left = (e.clientX - offsetX) + 'px';
            compose.style.top = (e.clientY - offsetY) + 'px';
        }
    });
    document.addEventListener('mouseup', () => isDragging = false);

    // ========== Minimize / Maximize / Close ==========
    document.getElementById('compose-minimize').addEventListener('click', () => {
        const form = compose.querySelector('form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
    document.getElementById('compose-maximize').addEventListener('click', () => {
        if (compose.classList.contains('maximized')) {
            compose.classList.remove('maximized');
            compose.style.width = '600px';
            compose.style.height = 'auto';
            compose.style.top = '';
            compose.style.left = '';
        } else {
            compose.classList.add('maximized');
            compose.style.width = '100vw';
            compose.style.height = '100vh';
            compose.style.top = '0';
            compose.style.left = '0';
        }
    });
    document.getElementById('compose-close').addEventListener('click', () => {
        compose.style.display = 'none';
    });

    // ========== Show Cc / Bcc ==========
    document.getElementById('show-cc').addEventListener('click', () => {
        document.getElementById('cc-section').classList.remove('d-none');
        document.getElementById('show-cc').style.display = 'none';
    });
    document.getElementById('show-bcc').addEventListener('click', () => {
        document.getElementById('bcc-section').classList.remove('d-none');
        document.getElementById('show-bcc').style.display = 'none';
    });

    // ========== Email Tag Inputs ==========
    function setupTags(inputId, containerId, hiddenName) {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);

        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && input.value.trim() !== '') {
                e.preventDefault();
                const email = input.value.trim();
                const tag = document.createElement('span');
                tag.className = 'badge bg-primary me-1 mb-1';
                tag.innerHTML = `${email} <span style="cursor:pointer;">&times;</span>`;

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = hiddenName + '[]';
                hidden.value = email;

                tag.appendChild(hidden);
                container.appendChild(tag);

                tag.querySelector('span').addEventListener('click', () => tag.remove());
                input.value = '';
            }
        });
    }
    setupTags('to-input', 'to-tags', 'to');
    setupTags('cc-input', 'cc-tags', 'cc');
    setupTags('bcc-input', 'bcc-tags', 'bcc');

    // ========== Attachments (Preview Multiple) ==========
    const attachmentInput = document.getElementById('attachments');
    const attachmentNames = document.getElementById('attachment-names');
    let allFiles = [];

    attachmentInput.addEventListener('change', function () {
        allFiles = [...allFiles, ...Array.from(this.files)];
        this.value = ''; // Allow reselecting same files
        renderAttachments();
    });

    function renderAttachments() {
        attachmentNames.innerHTML = '';
        allFiles.forEach((file, index) => {
            const span = document.createElement('span');
            span.className = 'badge bg-secondary me-1 mb-1 d-inline-flex align-items-center';
            span.style.padding = '6px 10px';
            span.innerHTML = `
                <i class="ti ti-paperclip me-1"></i> ${file.name}
                <span class="ms-2" style="cursor:pointer;">&times;</span>
            `;
            span.querySelector('span').addEventListener('click', () => {
                allFiles.splice(index, 1);
                renderAttachments();
            });
            attachmentNames.appendChild(span);
        });

        const dataTransfer = new DataTransfer();
        allFiles.forEach(file => dataTransfer.items.add(file));
        attachmentInput.files = dataTransfer.files;
    }
});
</script>




<!-- ====================== Script ====================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Reset Add Account form when closed
        const addAccountModal = document.getElementById('addAccountModal');
        addAccountModal.addEventListener('hidden.bs.modal', () => {
            document.getElementById('addAccountForm').reset();
        });

        // Toggle button text for extra accounts and email folders
        const toggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
        toggles.forEach(btn => {
            const target = document.querySelector(btn.getAttribute('href'));
            target.addEventListener('shown.bs.collapse', () => {
                btn.querySelector('span').textContent = 'View Less';
                btn.querySelector('i').classList.replace('fa-chevron-down', 'fa-chevron-up');
            });
            target.addEventListener('hidden.bs.collapse', () => {
                btn.querySelector('span').textContent = 'View More';
                btn.querySelector('i').classList.replace('fa-chevron-up', 'fa-chevron-down');
            });
        });
    });
</script>
