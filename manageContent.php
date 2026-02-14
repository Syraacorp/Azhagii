<?php
$pageTitle = 'Content Management';
$currentPage = 'manageContentSSR';
require 'includes/auth.php';
requirePageRole(['azhagiiCoordinator', 'superAdmin']);

$cid = $_SESSION['collegeId'];
$uid = $_SESSION['userId'];

// Fetch Courses for Dropdown (Assigned or Created)
// Super Admin sees ALL courses. Coordinator sees assigned or own.
if (hasRole('superAdmin')) {
    $q = "SELECT id, title, courseCode FROM courses ORDER BY title ASC";
    $r = mysqli_query($conn, $q);
} else {
    $q = "SELECT id, title, courseCode 
          FROM courses 
          WHERE (id IN (SELECT courseId FROM coursecolleges WHERE collegeId=?) OR createdBy=?) 
          ORDER BY title ASC";
    $stmt = $conn->prepare($q);
    $stmt->bind_param("ii", $cid, $uid);
    $stmt->execute();
    $r = $stmt->get_result();
}
$courses = [];
while ($r && $row = $r->fetch_assoc()) {
    $courses[] = $row;
}
if (isset($stmt)) { $stmt->close(); unset($stmt); }
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <select id="contentCourseSelect" class="form-input form-input-sm" onchange="loadContent()">
            <option value="">Select a course</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['title']) ?>
                    <?= $c['courseCode'] ? '(' . htmlspecialchars($c['courseCode']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showContentModal()"><i class="fas fa-plus"></i> Add Content</button>
</div>
<div id="contentList" class="content-list">
    <div class="empty-state">
        <i class="fas fa-layer-group"></i>
        <p>Select a course above to manage content</p>
    </div>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    function loadContent() {
        const courseId = $('#contentCourseSelect').val();
        const container = $('#contentList');

        if (!courseId) {
            container.html('<div class="empty-state"><i class="fas fa-layer-group"></i><p>Select a course above to manage content</p></div>');
            return;
        }

        container.html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading content...</p></div>');

        $.post('backend.php', { get_content: 1, courseId: courseId }, function (res) {
            if (res.status === 200) {
                if (res.data.length === 0) {
                    container.html('<div class="empty-state"><i class="fas fa-file-alt"></i><p>No content added yet</p></div>');
                } else {
                    let html = '';
                    res.data.forEach(c => { // Assuming array of content objects
                        let icon = 'fa-file';
                        if (c.contentType === 'video') icon = 'fa-video';
                        if (c.contentType === 'pdf') icon = 'fa-file-pdf';
                        if (c.contentType === 'text') icon = 'fa-align-left';

                        html += `
                        <div class="card mb-3 p-3" style="display:flex;align-items:center;justify-content:space-between;">
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <div style="width:40px;height:40px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#666;">
                                    <i class="fas ${icon}"></i>
                                </div>
                                <div>
                                    <h4 style="margin:0;font-size:1rem;">${escapeHtml(c.title)}</h4>
                                    <p style="margin:0;font-size:0.85rem;color:#888;">${escapeHtml(c.subject_title || 'General')} • ${escapeHtml(c.contentType)}</p>
                                </div>
                            </div>
                            <div class="actions">
                                <button class="btn btn-sm btn-outline" onclick="editContent(${c.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="deleteContent(${c.id})"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>`;
                    });
                    container.html(html);
                }
            } else {
                container.html(`<div class="text-center text-danger p-5">Error: ${res.message}</div>`);
            }
        }, 'json');
    }

    function showContentModal() {
        const courseId = $('#contentCourseSelect').val();
        if (!courseId) {
            Swal.fire('Select Course', 'Please select a course first', 'warning');
            return;
        }

        // Fetch subjects for course first
        $.post('backend.php', { get_subjects: 1, courseId: courseId }, function (res) {
            const subjects = res.status === 200 ? res.data : [];
            let subOpts = '<option value="0">General (No Subject)</option>';
            subjects.forEach(s => {
                subOpts += `<option value="${s.id}">${escapeHtml(s.title)}</option>`;
            });

            Swal.fire({
                title: 'Add Content',
                html: `
                    <div class="swal-form text-left">
                        <div class="form-group"><label>Title <span class="text-danger">*</span></label><input id="sw-ctTitle" class="form-input"></div>
                        <div class="form-group"><label>Subject/Unit</label><select id="sw-ctSubject" class="form-input">${subOpts}</select></div>
                        <div class="form-group"><label>Type</label>
                            <select id="sw-ctType" class="form-input" onchange="toggleContentInputs(this.value)">
                                <option value="video">Video URL</option>
                                <option value="pdf">PDF Document</option>
                                <option value="text">Rich Text</option>
                            </select>
                        </div>
                        <div class="form-group" id="grp-url"><label>Video URL (YouTube/Vimeo)</label><input id="sw-ctUrl" class="form-input" placeholder="e.g. https://youtube.com/..."></div>
                        <div class="form-group" id="grp-file" style="display:none;"><label>Upload File</label><input type="file" id="sw-ctFile" class="form-input"></div>
                        <div class="form-group" id="grp-text" style="display:none;"><label>Confirmed Text Content</label><textarea id="sw-ctText" class="form-input" rows="3"></textarea></div>
                        <div class="form-group"><label>Description</label><textarea id="sw-ctDesc" class="form-input" rows="2"></textarea></div>
                    </div>
                `,
                width: '600px',
                showCancelButton: true,
                confirmButtonText: 'Add Content',
                preConfirm: () => {
                    const title = $('#sw-ctTitle').val();
                    const type = $('#sw-ctType').val();
                    if (!title) { Swal.showValidationMessage('Title is required'); return false; }

                    const fd = new FormData();
                    fd.append('add_content', '1');
                    fd.append('courseId', courseId);
                    fd.append('title', title);
                    fd.append('subjectId', $('#sw-ctSubject').val());
                    fd.append('contentType', type);
                    fd.append('description', $('#sw-ctDesc').val());

                    if (type === 'video') fd.append('contentData', $('#sw-ctUrl').val());
                    if (type === 'text') fd.append('contentData', $('#sw-ctText').val());
                    if (type === 'pdf') {
                        const file = document.getElementById('sw-ctFile').files[0];
                        if (file) fd.append('content_file', file);
                    }
                    return fd;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'backend.php',
                        type: 'POST',
                        data: result.value,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (res) {
                            if (res.status === 200) {
                                Swal.fire('Added!', res.message, 'success');
                                loadContent();
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Connection failed', 'error');
                        }
                    });
                }
            });
        }, 'json');
    }

    function toggleContentInputs(type) {
        $('#grp-url, #grp-file, #grp-text').hide();
        if (type === 'video') $('#grp-url').show();
        if (type === 'pdf') $('#grp-file').show();
        if (type === 'text') $('#grp-text').show();
    }

    function editContent(id) {
        Swal.fire('Info', 'Edit functionality to be implemented. Please delete and re-add for now.', 'info');
    }

    function deleteContent(id) {
        Swal.fire({
            title: 'Delete Content?',
            text: "This cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_content: 1, id: id }, function (res) { // Assuming delete_content exists
                    if (res.status === 200) {
                        Swal.fire('Deleted!', res.message, 'success');
                        loadContent();
                    } else {
                        Swal.fire('Error', res.message || 'Function not implemented in backend', 'error');
                    }
                }, 'json');
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
</script>