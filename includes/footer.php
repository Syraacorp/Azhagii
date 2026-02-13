    </div><!-- /content-wrapper -->
</main><!-- /main-content -->
</div><!-- /dashboard-layout -->

<script>
// Pass PHP vars to JS (available on every page)
const USER_ROLE    = '<?= $role ?>';
const USER_ID      = <?= $userId ?>;
const USER_NAME    = '<?= addslashes($userName) ?>';
const COLLEGE_ID   = <?= $collegeId ?: 0 ?>;
const COLLEGE_NAME = '<?= addslashes($collegeName) ?>';
const CURRENT_PAGE = '<?= $currentPage ?>';
</script>
<script src="assets/js/script.js"></script>
</body>
</html>
