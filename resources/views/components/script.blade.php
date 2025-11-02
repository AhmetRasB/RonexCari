    <!-- jQuery library js -->
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <!-- Bootstrap js -->
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <!-- Apex Chart js -->
    <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
    <!-- Data Table js -->
    <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
    <!-- DataTables Responsive js -->
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <!-- DataTables: Disable all warnings and alerts -->
    <script>
        // Disable DataTables warnings and console errors
        $.fn.dataTable.ext.errMode = 'none';
        
        // Suppress all DataTables console warnings
        if (window.console && window.console.warn) {
            var originalWarn = console.warn;
            console.warn = function() {
                if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('DataTables')) {
                    return; // Suppress DataTables warnings
                }
                originalWarn.apply(console, arguments);
            };
        }
        
        // Suppress DataTables alert/confirm dialogs
        window.alert = (function(originalAlert) {
            return function() {
                if (arguments[0] && typeof arguments[0] === 'string' && (
                    arguments[0].includes('DataTables') || 
                    arguments[0].includes('i18n') ||
                    arguments[0].includes('unknown parameter')
                )) {
                    return; // Suppress DataTables alerts
                }
                return originalAlert.apply(window, arguments);
            };
        })(window.alert);
        
        // Set default language settings to suppress empty table messages
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                emptyTable: "",
                zeroRecords: "",
                info: "",
                infoEmpty: "",
                infoFiltered: "",
                loadingRecords: "",
                processing: "",
                search: "",
                lengthMenu: "",
                paginate: {
                    first: "",
                    last: "",
                    next: "",
                    previous: ""
                }
            }
        });
    </script>
    <!-- Iconify Font js -->
    <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
    <!-- jQuery UI js -->
    <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
    <!-- Vector Map js -->
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-world-mill-en.js') }}"></script>
    <!-- Popup js -->
    <script src="{{ asset('assets/js/lib/magnifc-popup.min.js') }}"></script>
    <!-- Slick Slider js -->
    <script src="{{ asset('assets/js/lib/slick.min.js') }}"></script>
    <!-- prism js -->
    <script src="{{ asset('assets/js/lib/prism.js') }}"></script>
    <!-- file upload js -->
    <script src="{{ asset('assets/js/lib/file-upload.js') }}"></script>
    <!-- audioplayer -->
    <script src="{{ asset('assets/js/lib/audioplayer.js') }}"></script>

    <!-- main js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    
    <!-- Global DataTable Responsive Configuration -->
    <script>
        // DataTable initialization is handled by individual pages using @push('scripts')
        
        // Notification System
        window.loadNotifications = function() {
            fetch('{{ route('notifications.get') }}')
                .then(response => response.json())
                .then(data => {
                    const notificationList = document.getElementById('notificationList');
                    const notificationCount = document.getElementById('notificationCount');
                    const notificationBtn = document.getElementById('notificationBtn');
                    
                    // Update count
                    notificationCount.textContent = data.count;
                    
                    // Update indicator
                    if (data.count > 0) {
                        notificationBtn.classList.add('has-indicator');
                    } else {
                        notificationBtn.classList.remove('has-indicator');
                    }
                    
                    // Update list
                    if (data.notifications && data.notifications.length > 0) {
                        let html = '';
                        data.notifications.forEach(function(notification, index) {
                            const colorClass = notification.color === 'danger' ? 'bg-danger-subtle text-danger-main' : 
                                             notification.color === 'warning' ? 'bg-warning-subtle text-warning-main' : 
                                             'bg-info-subtle text-info-main';
                            
                            const bgClass = index % 2 === 1 ? 'bg-neutral-50' : '';
                            
                            html += `
                                <a href="${notification.link}" class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between ${bgClass}">
                                    <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                                        <span class="w-44-px h-44-px ${colorClass} rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                                            <iconify-icon icon="${notification.icon}" class="icon text-xxl"></iconify-icon>
                                        </span>
                                        <div>
                                            <h6 class="text-md fw-semibold mb-4">${notification.title}</h6>
                                            <p class="mb-0 text-sm text-secondary-light text-w-200-px">${notification.message}</p>
                                        </div>
                                    </div>
                                    <span class="text-sm text-secondary-light flex-shrink-0">${notification.time}</span>
                                </a>
                            `;
                        });
                        notificationList.innerHTML = html;
                    } else {
                        notificationList.innerHTML = `
                            <div class="px-24 py-12 text-center">
                                <iconify-icon icon="solar:bell-off-outline" class="text-secondary-light text-4xl mb-2"></iconify-icon>
                                <p class="mb-0 text-sm text-secondary-light">Bildirim bulunmuyor</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Notification loading error:', error);
                    document.getElementById('notificationList').innerHTML = `
                        <div class="px-24 py-12 text-center">
                            <iconify-icon icon="solar:danger-triangle-outline" class="text-danger text-4xl mb-2"></iconify-icon>
                            <p class="mb-0 text-sm text-danger">Bildirimler yüklenirken hata oluştu</p>
                        </div>
                    `;
                });
        };
        
        // Initialize notifications when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Load notifications when dropdown is opened
            const notificationBtn = document.getElementById('notificationBtn');
            if (notificationBtn) {
                notificationBtn.addEventListener('click', function() {
                    loadNotifications();
                });
                
                // Load notifications on page load
                loadNotifications();
            }
        });
    </script>

    <?php echo (isset($script) ? $script   : '')?>