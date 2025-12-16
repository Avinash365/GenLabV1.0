/**
 * AJAX Sidebar Navigation to persist Fullscreen Mode
 * Intercepts clicks on sidebar links and updates content wrapper without full reload.
 */

$(document).ready(function () {
    // Select all internal links to support persistent fullscreen across the app
    $(document).on('click', 'a', function (e) {
        let href = $(this).attr('href');
        let target = $(this).attr('target');

        // Logic to determine if we should intercept:
        // 1. Must have an href
        if (!href) return;
        // 2. Must not be a hash link or javascript
        if (href.startsWith('#') || href.startsWith('javascript:')) return;
        // 3. Must not be external (target blank)
        if (target === '_blank') return;
        // 4. Must be same origin (internal link)
        if (href.indexOf(window.location.origin) !== 0 && !href.startsWith('/')) {
            // Check if it's a relative path (doesn't start with http)
            if (href.startsWith('http')) return; // External full URL
        }

        // Exclude specific bypass class if needed
        if ($(this).hasClass('no-ajax')) return;

        // Exclude logout (usually form submit or specific route)
        if (href.includes('logout')) return;


        // Prevent default navigation
        e.preventDefault();

        // Update Active State Visuals immediately
        $('#sidebar-menu a').removeClass('active');
        $(this).addClass('active');
        // Handle submenu parenting active state
        $(this).closest('ul').closest('li').addClass('active'); // simplified logic

        // Show Loader
        if (window.LoadingOverlay) window.LoadingOverlay.show();
        else {
            // Fallback loader if not present
            $('body').append('<div id="ajax-loader" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;">Loading...</div>');
        }

        // Fetch Content
        fetch(href)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                // Parse HTML
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');

                // Extract .page-wrapper content
                let newContent = doc.querySelector('.page-wrapper');
                let currentWrapper = document.querySelector('.page-wrapper');

                if (newContent && currentWrapper) {
                    currentWrapper.innerHTML = newContent.innerHTML;

                    // Update Page Title
                    document.title = doc.title;

                    // Update History
                    window.history.pushState({ path: href }, '', href);

                    // Re-initialize Plugins
                    reinitPlugins();
                } else {
                    // Fallback if structure mismatches (e.g. login page)
                    window.location.href = href;
                }
            })
            .catch(error => {
                console.error('Navigation fetch invalid:', error);
                window.location.href = href; // Fallback to normal navigation
            })
            .finally(() => {
                if (window.LoadingOverlay) window.LoadingOverlay.hide();
                $('#ajax-loader').remove();
            });
    });

    // Handle Back/Forward Browser Buttons
    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.path) {
            // Simplified: reload to ensure correct state, or duplicate fetch logic here
            window.location.reload();
        }
    });
});

function reinitPlugins() {
    // Re-init Feather Icons
    if (typeof feather !== 'undefined') feather.replace();

    // Re-init Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Re-init Popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Re-init Select2
    if ($('.select').length > 0) {
        $('.select').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });
    }

    // Re-init DatePicker
    if ($('.datetimepicker').length > 0) {
        $('.datetimepicker').datetimepicker({
            format: 'DD-MM-YYYY',
            icons: {
                up: "fas fa-angle-up",
                down: "fas fa-angle-down",
                next: 'fas fa-angle-right',
                previous: 'fas fa-angle-left'
            }
        });
    }

    // Re-init Summernote
    if ($('.summernote').length > 0) {
        $('.summernote').summernote({
            height: 200,
            minHeight: null,
            maxHeight: null,
            focus: false
        });
    }

    // Re-init DataTables (Basic)
    if ($('.datatable').length > 0) {
        // Destroy existing if any? No, new DOM.
        $('.datatable').DataTable({
            "bFilter": true,
            "sDom": 'fBtlpi',
            "ordering": true,
            "language": {
                search: ' ',
                sLengthMenu: '_MENU_',
                searchPlaceholder: "Search",
                sLengthMenu: 'Row Per Page _MENU_ Entries',
                info: "_START_ - _END_ of _TOTAL_ items",
                paginate: {
                    next: ' <i class=" fa fa-angle-right"></i>',
                    previous: '<i class="fa fa-angle-left"></i> '
                },
            },
            initComplete: (settings, json) => {
                $('.dataTables_filter').appendTo('#tableSearch');
                $('.dataTables_filter').appendTo('.search-input');
            },
        });
    }

    // Re-init Owl Carousel
    if ($('.owl-carousel').length > 0) {
        // Owl Carousel creates complex DOM, naive re-init might fail if not careful, 
        // but since we replaced the whole wrapper, it should be fresh.
        // We'd need specific configs for specific sliders (like pos-category).
        // This is a "best effort" - specific sliders might need their own config blocks copied here.
    }
}
