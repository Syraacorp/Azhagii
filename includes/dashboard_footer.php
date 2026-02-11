</div> <!-- End Dashboard Content -->
</div> <!-- End Main Content Wrapper -->
</div> <!-- End Dashboard Wrapper -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
<script>
    // Dashboard specific scripts
    $(document).ready(function () {
        // Sidebar Toggle
        $('#menu-toggle').click(function () {
            $('#sidebar').toggleClass('active');
        });

        // User Dropdown
        $('#user-dropdown-trigger').click(function (e) {
            e.stopPropagation();
            $('#user-dropdown').toggleClass('show');
            // Use flex display when showing
            if ($('#user-dropdown').hasClass('show')) {
                $('#user-dropdown').css('display', 'flex');
                setTimeout(() => $('#user-dropdown').css('opacity', '1').css('transform', 'translateY(0)'), 10);
            } else {
                $('#user-dropdown').css('opacity', '0').css('transform', 'translateY(-10px)');
                setTimeout(() => $('#user-dropdown').css('display', 'none'), 200);
            }
        });

        // Close dropdown when clicking outside
        $(document).click(function () {
            $('#user-dropdown').removeClass('show');
            $('#user-dropdown').css('opacity', '0').css('transform', 'translateY(-10px)');
            setTimeout(() => $('#user-dropdown').css('display', 'none'), 200);
        });
    });
</script>
</body>

</html>