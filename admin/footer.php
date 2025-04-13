        </div> <!-- End of container-fluid -->
        </div> <!-- End of main-content -->

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Common admin JS functions
            document.addEventListener('DOMContentLoaded', function() {
                // Enable tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // DataTable initialization (if the script is included)
                if (typeof $.fn.DataTable !== 'undefined') {
                    $('.datatable').DataTable({
                        responsive: true
                    });
                }
            });
        </script>
        </body>

        </html>