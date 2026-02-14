<?php
$pageTitle = 'Course Viewer';
$currentPage = 'courseViewerSSR';
require 'includes/auth.php';
requirePageRole('azhagiiStudents');

if (!isset($_GET['id'])) {
    header("Location: myLearning.php");
    exit;
}
$courseId = intval($_GET['id']);
$userId = $_SESSION['userId'];

// 1. Fetch Course & Enrollment Details
$q = "SELECT c.*, e.progress, e.completed_topics 
      FROM courses c 
      INNER JOIN enrollments e ON c.id = e.courseId 
      WHERE c.id = $courseId AND e.userId = $userId 
      LIMIT 1";
$r = mysqli_query($conn, $q);
$course = mysqli_fetch_assoc($r);

if (!$course) {
    // Not enrolled or invalid course
    require 'includes/header.php';
    require 'includes/sidebar.php';
    echo '<div style="padding:2rem;text-align:center;"><h3>Course not found or you are not enrolled.</h3><a href="myLearning.php" class="btn btn-primary" style="margin-top:1rem;">Back to My Learning</a></div>';
    require 'includes/footer.php';
    exit;
}

// Parse completed topics
$completedTopics = [];
if (!empty($course['completed_topics'])) {
    $completedTopics = json_decode($course['completed_topics'], true) ?? [];
}

// 2. Fetch Subjects and Topics
$subjects = [];
$firstTopic = null;

$sq = "SELECT * FROM subjects WHERE courseId = $courseId ORDER BY id ASC"; // Assuming ID order or add 'order' column
$sr = mysqli_query($conn, $sq);
while ($sr && $sub = mysqli_fetch_assoc($sr)) {
    $sub['topics'] = [];
    $sid = $sub['id'];
    $tq = "SELECT * FROM topics WHERE subjectId = $sid AND status='active' ORDER BY id ASC";
    $tr = mysqli_query($conn, $tq);
    while ($tr && $t = mysqli_fetch_assoc($tr)) {
        $sub['topics'][] = $t;
        if (!$firstTopic)
            $firstTopic = $t;
    }
    $subjects[] = $sub;
}

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="course-viewer-layout">
    <!-- Sidebar: Course Content -->
    <div class="cv-sidebar">
        <div class="cv-header">
            <a href="myLearning.php" class="btn-back"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h4 style="margin:0;font-size:0.95rem;line-height:1.2;"><?= htmlspecialchars($course['title']) ?></h4>
                <div class="progress-bar-sm" style="margin-top:0.4rem;height:4px;background:rgba(255,255,255,0.1);">
                    <div class="progress-fill" style="width:<?= $course['progress'] ?>%;background:#4ade80;"></div>
                </div>
                <small style="color:rgba(255,255,255,0.6);font-size:0.75rem;"><?= $course['progress'] ?>%
                    Complete</small>
            </div>
        </div>
        <div class="cv-modules">
            <?php foreach ($subjects as $idx => $s): ?>
                <div class="cv-module">
                    <div class="cv-module-header" onclick="toggleModule(this)">
                        <span>Unit <?= $idx + 1 ?>: <?= htmlspecialchars($s['title']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="cv-module-content">
                        <?php if (empty($s['topics'])): ?>
                            <div style="padding:0.75rem 1rem;font-size:0.8rem;color:var(--text-muted);font-style:italic;">No
                                topics</div>
                        <?php else: ?>
                            <?php foreach ($s['topics'] as $t):
                                $isCompleted = in_array($t['id'], $completedTopics);
                                $iconClass = $isCompleted ? 'fa-check-circle text-success' : 'fa-play-circle';
                                $iconStyle = $isCompleted ? 'color:#4ade80;' : '';
                                ?>
                                <div class="cv-topic-item" onclick="loadTopic(<?= $t['id'] ?>, this)"
                                    data-topic-id="<?= $t['id'] ?>" data-video="<?= htmlspecialchars($t['videoUrl'] ?? '') ?>"
                                    data-title="<?= htmlspecialchars($t['title']) ?>"
                                    data-desc="<?= htmlspecialchars($t['description'] ?? '') ?>">
                                    <i class="fas <?= $iconClass ?>" style="<?= $iconStyle ?>"></i>
                                    <span><?= htmlspecialchars($t['title']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Content: Player -->
    <div class="cv-content">
        <div id="playerEmptyState"
            style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;color:var(--text-muted);">
            <i class="fas fa-play-circle" style="font-size:4rem;margin-bottom:1rem;opacity:0.3;"></i>
            <h3>Select a topic to start learning</h3>
        </div>

        <div id="playerContainer" style="display:none;">
            <div class="video-wrapper">
                <iframe id="mainVideo" src="" frameborder="0" allowfullscreen></iframe>
            </div>
            <div class="topic-details">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                    <h2 id="topicTitle"></h2>
                    <button class="btn btn-primary" id="btnMarkComplete" onclick="markTopicComplete()">
                        <i class="fas fa-check"></i> Mark Complete
                    </button>
                    <button class="btn btn-success" id="btnCompleted" style="display:none;" disabled>
                        <i class="fas fa-check-double"></i> Completed
                    </button>
                </div>
                <div class="topic-desc" id="topicDesc"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Course Viewer Layout */
    .content-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
        height: 100vh;
        overflow: hidden;
    }

    .course-viewer-layout {
        display: grid;
        grid-template-columns: 350px 1fr;
        height: 100vh;
    }

    /* Sidebar */
    .cv-sidebar {
        background: #1a1b23;
        border-right: 1px solid #2d2e36;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .cv-header {
        padding: 1rem;
        border-bottom: 1px solid #2d2e36;
        background: #15161c;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-back {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        transition: 0.2s;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .cv-modules {
        flex: 1;
        overflow-y: auto;
    }

    .cv-module {
        border-bottom: 1px solid #2d2e36;
    }

    .cv-module-header {
        padding: 1rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 500;
        font-size: 0.9rem;
        background: #1a1b23;
        transition: 0.2s;
    }

    .cv-module-header:hover {
        background: #22232b;
    }

    .cv-module-content {
        display: none;
        background: #15161c;
    }

    .cv-module.active .cv-module-header {
        color: var(--primary);
    }

    .cv-module.active .cv-module-content {
        display: block;
    }

    .cv-topic-item {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        font-size: 0.9rem;
        color: #a1a1aa;
        transition: 0.2s;
        border-left: 3px solid transparent;
    }

    .cv-topic-item:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    .cv-topic-item.active {
        background: rgba(66, 133, 244, 0.1);
        color: #fff;
        border-left-color: var(--primary);
    }

    .cv-topic-item i {
        width: 20px;
        text-align: center;
    }

    /* Content */
    .cv-content {
        height: 100%;
        overflow-y: auto;
        position: relative;
        background: #0f1014;
    }

    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%;
        /* 16:9 */
        height: 0;
        background: #000;
    }

    .video-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .topic-details {
        padding: 2rem;
        max-width: 900px;
        margin: 0 auto;
    }

    .topic-desc {
        color: #d4d4d8;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .course-viewer-layout {
            grid-template-columns: 1fr;
            grid-template-rows: auto 1fr;
        }

        .cv-sidebar {
            height: auto;
            max-height: 40vh;
        }

        .video-wrapper {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    }
</style>

<script>
    let currentTopicId = null;
    const completedTopics = <?= json_encode($completedTopics) ?>;

    function toggleModule(header) {
        $(header).parent().toggleClass('active');
        $(header).find('i').toggleClass('fa-chevron-down fa-chevron-up');
    }

    function loadTopic(id, el) {
        currentTopicId = id;

        // Update UI
        $('.cv-topic-item').removeClass('active');
        if (el) $(el).addClass('active');
        else $(`.cv-topic-item[data-topic-id="${id}"]`).addClass('active');

        const $el = $(`.cv-topic-item[data-topic-id="${id}"]`);
        const videoUrl = $el.data('video');
        const title = $el.data('title');
        const desc = $el.data('desc');

        $('#playerEmptyState').hide();
        $('#playerContainer').show();

        // Helper to get embed URL (basic support)
        let embedUrl = videoUrl;
        if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
            const match = videoUrl.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            if (match) embedUrl = 'https://www.youtube.com/embed/' + match[1];
        }

        $('#mainVideo').attr('src', embedUrl);
        $('#topicTitle').text(title);
        $('#topicDesc').html(desc ? desc.replace(/\n/g, '<br>') : '');

        // Complete Logic
        if (completedTopics.includes(id)) {
            $('#btnMarkComplete').hide();
            $('#btnCompleted').show();
        } else {
            $('#btnMarkComplete').show();
            $('#btnCompleted').hide();
        }
    }

    function markTopicComplete() {
        if (!currentTopicId) return;

        const btn = $('#btnMarkComplete');
        const oldHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.post('backend.php', { mark_topic_complete: 1, courseId: <?= $courseId ?>, topicId: currentTopicId }, function (res) {
            if (res.status === 200) {
                completedTopics.push(currentTopicId);
                btn.hide();
                $('#btnCompleted').show();
                // Update sidebar icon
                const $icon = $(`.cv-topic-item[data-topic-id="${currentTopicId}"] i`);
                $icon.removeClass('fa-play-circle').addClass('fa-check-circle text-success').css('color', '#4ade80');

                // Optional: Update progress bar in sidebar (requires reload or calc)
                // For now, simple toast
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                Toast.fire({ icon: 'success', title: 'Topic Completed!' });
            } else {
                Swal.fire('Error', res.message, 'error');
                btn.prop('disabled', false).html(oldHtml);
            }
        }, 'json');
    }

    $(document).ready(function () {
        // Open first module
        $('.cv-module').first().addClass('active');

        // Auto-load first topic if available
        const firstTopic = $('.cv-topic-item').first();
        if (firstTopic.length) {
            loadTopic(firstTopic.data('topic-id'), firstTopic);
        }
    });
</script>

<?php // Note: No footer require to keep full screen layout ?>
</body>

</html>