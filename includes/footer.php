</div><!-- /content-wrapper -->
</main><!-- /main-content -->
</div><!-- /dashboard-layout -->

<script>
    // Pass PHP vars to JS (available on every page)
    const USER_ROLE = <?= json_encode($role, JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
    const USER_ID = <?= intval($userId) ?>;
    const USER_NAME = <?= json_encode($userName, JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
    const COLLEGE_ID = <?= intval($collegeId ?: 0) ?>;
    const COLLEGE_NAME = <?= json_encode($collegeName ?: '', JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
    const CURRENT_PAGE = <?= json_encode($currentPage, JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;

    // Global Scroll Progress
    window.addEventListener('scroll', () => {
        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
        const clientHeight = document.documentElement.clientHeight || document.body.clientHeight;

        // Only show if scrollable
        if (scrollHeight > clientHeight) {
            const scrolled = (scrollTop / (scrollHeight - clientHeight)) * 100;
            const bar = document.getElementById('globalProgressBar');
            if (bar) bar.style.width = scrolled + '%';
        }
    });
</script>
<script src="assets/js/script.js?v=<?= time() ?>"></script>
</body>

</html>