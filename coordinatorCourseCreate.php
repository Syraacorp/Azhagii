<?php
$pageTitle   = 'Create Course';
$currentPage = 'coordinatorCourseCreate';
require 'includes/auth.php';
requirePageRole('azhagiiCoordinator');
require 'includes/header.php';
require 'includes/sidebar.php';
?>

<div class="card" style="max-width:800px;">
    <h3 style="margin-bottom:0.5rem;">Submit a New Course</h3>
    <p style="color:var(--text-muted);margin-bottom:1.5rem;">Fill in the course details below. Your course will be submitted for admin approval.</p>

    <form id="coordCourseForm" enctype="multipart/form-data">
        <div class="responsive-grid-2">
            <div class="form-group">
                <label class="form-label">Course Title *</label>
                <input type="text" name="title" class="form-input" required placeholder="e.g. Data Structures">
            </div>
            <div class="form-group">
                <label class="form-label">Course Code</label>
                <input type="text" name="courseCode" class="form-input" placeholder="e.g. CS201">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-input" rows="3" placeholder="Brief description of the course"></textarea>
        </div>

        <div class="responsive-grid-3">
            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-input" placeholder="e.g. Computer Science">
            </div>
            <div class="form-group">
                <label class="form-label">Course Type</label>
                <select name="courseType" class="form-input">
                    <option value="theory">Theory</option>
                    <option value="lab">Lab / Practical</option>
                    <option value="elective">Elective</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-input">
                    <option value="">Select</option>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                    <option value="3">Semester 3</option>
                    <option value="4">Semester 4</option>
                    <option value="5">Semester 5</option>
                    <option value="6">Semester 6</option>
                    <option value="7">Semester 7</option>
                    <option value="8">Semester 8</option>
                </select>
            </div>
        </div>

        <div class="responsive-grid-2">
            <div class="form-group">
                <label class="form-label">Regulation</label>
                <input type="text" name="regulation" class="form-input" placeholder="e.g. R2021">
            </div>
            <div class="form-group">
                <label class="form-label">Academic Year</label>
                <input type="text" name="academicYear" class="form-input" placeholder="e.g. 2024-2025">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Syllabus PDF (max 2MB)</label>
            <input type="file" name="syllabus" class="form-input" accept=".pdf">
        </div>

        <!-- Units / Subjects Section -->
        <div style="margin-top:1.5rem;margin-bottom:1rem;">
            <h4 style="margin-bottom:0.75rem;"><i class="fas fa-layer-group" style="color:var(--accent-blue);margin-right:0.5rem;"></i>Units / Subjects</h4>
            <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:1rem;">Add up to 5 units. You can add topics to each unit after the course is created.</p>
        </div>
        <div id="unitsContainer">
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">1.</span>
                <input type="text" name="unit_1" class="form-input" placeholder="Unit 1 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">2.</span>
                <input type="text" name="unit_2" class="form-input" placeholder="Unit 2 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">3.</span>
                <input type="text" name="unit_3" class="form-input" placeholder="Unit 3 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">4.</span>
                <input type="text" name="unit_4" class="form-input" placeholder="Unit 4 title" style="flex:1;">
            </div>
            <div class="unit-entry" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:center;">
                <span style="color:var(--text-muted);font-weight:600;min-width:24px;">5.</span>
                <input type="text" name="unit_5" class="form-input" placeholder="Unit 5 title" style="flex:1;">
            </div>
        </div>

        <div class="responsive-btn-row" style="margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit for Approval</button>
            <a href="myCourses.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<!-- My Submitted Courses -->
<div class="dashboard-section-title" style="margin-top:2rem;"><i class="fas fa-history"></i> My Submitted Courses</div>
<div class="table-responsive">
    <table class="table" id="mySubmittedCoursesTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Code</th>
                <th>Semester</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="mySubmittedCoursesBody"></tbody>
    </table>
</div>

<?php require 'includes/footer.php'; ?>
