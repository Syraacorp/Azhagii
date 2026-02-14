<?php
$pageTitle = 'Manage Topics';
$currentPage = 'manageTopicsSSR';
require 'includes/auth.php';
requirePageRole(['azhagiiCoordinator', 'superAdmin']);

$cid = $_SESSION['collegeId'];
$uid = $_SESSION['userId'];

// Fetch Courses for Dropdown (Assigned or Created)
if (hasRole('superAdmin')) {
    $q = "SELECT id, title, courseCode FROM courses ORDER BY title ASC";
} else {
    $q = "SELECT id, title, courseCode 
          FROM courses 
          WHERE (id IN (SELECT courseId FROM coursecolleges WHERE collegeId=$cid) OR createdBy=$uid) 
          ORDER BY title ASC";
}
$courses = [];
$r = mysqli_query($conn, $q);
while ($r && $row = mysqli_fetch_assoc($r)) {
    $courses[] = $row;
}

require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="section-toolbar">
    <div class="filter-bar">
        <!-- Course Select (Pre-populated) -->
        <select id="topicCourseSelect" class="form-input form-input-sm" onchange="loadTopicSubjects()">
            <option value="">Select a course</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['title']) ?>
                    <?= $c['courseCode'] ? '(' . htmlspecialchars($c['courseCode']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Subject Select (Loaded via AJAX based on Course) -->
        <select id="topicSubjectSelect" class="form-input form-input-sm" onchange="loadTopics()">
            <option value="">Select a subject/unit</option>
        </select>
    </div>
    <button class="btn btn-primary" onclick="showTopicModal()"><i class="fas fa-plus"></i> Add Topic</button>
</div>

<div class="table-responsive">
    <table class="table" id="topicsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Topic Title</th>
                <th>Description</th>
                <th>Added By</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="topicsBody">
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="fas fa-tags"></i>
                    <p>Select a course and subject above</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<?php require 'includes/footer.php'; ?>

<script>
    function loadTopicSubjects() {
        const courseId = $('#topicCourseSelect').val();
        const subjectSelect = $('#topicSubjectSelect');
        const tbody = $('#topicsBody');

        if (!courseId) {
            subjectSelect.html('<option value="">Select a subject/unit</option>');
            tbody.html('<tr><td colspan="6" class="empty-state"><p>Select a course above</p></td></tr>');
            return;
        }

        subjectSelect.html('<option>Loading...</option>');

        $.post('backend.php', { get_subjects: 1, courseId: courseId }, function (res) {
            if (res.status === 200) {
                let opts = '<option value="">Select a subject/unit</option>';
                res.data.forEach(s => {
                    opts += `<option value="${s.id}">${escapeHtml(s.title)} (${escapeHtml(s.code)})</option>`;
                });
                subjectSelect.html(opts);
            } else {
                subjectSelect.html('<option>Error loading subjects</option>');
            }
        }, 'json');
    }

    function loadTopics() {
        const subjectId = $('#topicSubjectSelect').val();
        const tbody = $('#topicsBody');

        if (!subjectId) {
            tbody.html('<tr><td colspan="6" class="empty-state"><p>Select a subject above</p></td></tr>');
            return;
        }

        tbody.html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.post('backend.php', { get_topics: 1, subjectId: subjectId }, function (res) {
            if (res.status === 200) {
                let html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="6" class="empty-state"><i class="fas fa-tags"></i><p>No topics found</p></td></tr>';
                } else {
                    res.data.forEach((t, i) => {
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td>${escapeHtml(t.title)}</td>
                            <td>${escapeHtml(t.description || '-')}</td>
                            <td>${escapeHtml(t.added_by_name || 'System')}</td>
                            <td><span class="badge badge-${t.status === 'active' ? 'active' : 'inactive'}">${t.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline" onclick="editTopic(${t.id}, '${escapeHtml(t.title)}', '${escapeHtml(t.description || '')}', '${t.status}')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="deleteTopic(${t.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                }
                tbody.html(html);
            } else {
                tbody.html(`<tr><td colspan="6" class="text-center text-danger">Error: ${res.message}</td></tr>`);
            }
        }, 'json');
    }

    function showTopicModal() {
        const subjectId = $('#topicSubjectSelect').val();
        if (!subjectId) {
            Swal.fire('Select Subject', 'Please select a subject/unit first', 'warning');
            return;
        }

        Swal.fire({
            title: 'Add New Topic',
            html: `
                <div class="swal-form text-left">
                    <div class="form-group"><label>Title</label><input id="swal-topicTitle" class="form-input"></div>
                    <div class="form-group"><label>Description</label><textarea id="swal-topicDesc" class="form-input"></textarea></div>
                    <div class="form-group"><label>Status</label>
                        <select id="swal-topicStatus" class="form-input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add Topic',
            preConfirm: () => {
                return {
                    title: $('#swal-topicTitle').val(),
                    desc: $('#swal-topicDesc').val(),
                    status: $('#swal-topicStatus').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                if (!data.title) { Swal.fire('Error', 'Title is required', 'error'); return; }

                $.post('backend.php', {
                    add_topic: 1,
                    subjectId: subjectId,
                    title: data.title,
                    description: data.desc,
                    status: data.status
                }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Added!', res.message, 'success');
                        loadTopics();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function editTopic(id, title, desc, status) {
        Swal.fire({
            title: 'Edit Topic',
            html: `
                <div class="swal-form text-left">
                    <div class="form-group"><label>Title</label><input id="swal-topicTitle" class="form-input" value="${title}"></div>
                    <div class="form-group"><label>Description</label><textarea id="swal-topicDesc" class="form-input">${desc}</textarea></div>
                    <div class="form-group"><label>Status</label>
                        <select id="swal-topicStatus" class="form-input">
                            <option value="active" ${status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${status === 'inactive' ? 'selected' : ''}>Inactive</option>
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            preConfirm: () => {
                return {
                    title: $('#swal-topicTitle').val(),
                    desc: $('#swal-topicDesc').val(),
                    status: $('#swal-topicStatus').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                $.post('backend.php', {
                    update_topic: 1,
                    id: id,
                    title: data.title,
                    description: data.desc,
                    status: data.status
                }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Updated!', res.message, 'success');
                        loadTopics();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    function deleteTopic(id) {
        Swal.fire({
            title: 'Delete Topic?',
            text: "This will remove all content associated with this topic.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('backend.php', { delete_topic: 1, id: id }, function (res) {
                    if (res.status === 200) {
                        Swal.fire('Deleted!', res.message, 'success');
                        loadTopics();
                    } else {
                        Swal.fire('Error', res.message, 'error');
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