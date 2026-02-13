    </div><!-- /content-wrapper -->
</main><!-- /main-content -->
</div><!-- /dashboard-layout -->

<script>
// Pass PHP vars to JS (available on every page)
const USER_ROLE    = <?= json_encode($role, JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
const USER_ID      = <?= intval($userId) ?>;
const USER_NAME    = <?= json_encode($userName, JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
const COLLEGE_ID   = <?= intval($collegeId ?: 0) ?>;
const COLLEGE_NAME = <?= json_encode($collegeName ?: '', JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
const CURRENT_PAGE = <?= json_encode($currentPage, JSON_INVALID_UTF8_SUBSTITUTE) ?: '""' ?>;
</script>
<script src="assets/js/script.js"></script>
</body>
</html>
