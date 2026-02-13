$(document).ready(function () {

  // ══════════════════════════════════════════
  //  THEME TOGGLE
  // ══════════════════════════════════════════
  const savedTheme = localStorage.getItem('ziya-theme') || 'dark';
  $('body').attr('data-theme', savedTheme);
  updateThemeIcon(savedTheme);

  $('#themeToggle').click(function () {
    const current = $('body').attr('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    $('body').attr('data-theme', next);
    localStorage.setItem('ziya-theme', next);
    updateThemeIcon(next);
  });

  function updateThemeIcon(theme) {
    $('#themeToggle i').attr('class', theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon');
  }

  // ══════════════════════════════════════════
  //  SIDEBAR MOBILE TOGGLE
  // ══════════════════════════════════════════
  $('#menu-toggle').click(function () {
    $('#sidebar').toggleClass('active');
    $('.sidebar-overlay').toggleClass('active');
  });
  $('.sidebar-overlay').click(function () {
    $('#sidebar').removeClass('active');
    $(this).removeClass('active');
  });

  // ══════════════════════════════════════════
  //  USER DROPDOWN
  // ══════════════════════════════════════════
  $('#userDropdownToggle').click(function (e) {
    e.stopPropagation();
    $(this).toggleClass('active');
    $('#userDropdownMenu').toggleClass('show');
  });

  $(document).click(function (e) {
    if (!$(e.target).closest('.user-dropdown-wrapper').length) {
      $('#userDropdownToggle').removeClass('active');
      $('#userDropdownMenu').removeClass('show');
    }
  });

  // ══════════════════════════════════════════
  //  HELPER: AJAX POST
  // ══════════════════════════════════════════
  function api(data, cb, errCb) {
    $.post('backend.php', data, function (res) {
      if (typeof res === 'string') try { res = JSON.parse(res); } catch (e) { }
      cb(res);
    }, 'json').fail(function (xhr) {
      console.error('API Error:', xhr);
      if (errCb) errCb(xhr);
      else Swal.fire({ icon: 'error', title: 'Error', text: 'Connection failed. Please try again.' });
    });
  }

  // ══════════════════════════════════════════
  //  PAGE ROUTER — auto-load data per page
  // ══════════════════════════════════════════
  if (typeof CURRENT_PAGE !== 'undefined') {
    switch (CURRENT_PAGE) {
      case 'dashboard':         loadRoleDashboard(); break;
      case 'manageColleges':    loadColleges(); break;
      case 'manageUsers':       loadCollegeDropdowns(); loadUsers(); break;
      case 'manageCourses':     loadCourses(); break;
      case 'courseAssignments':  loadCourseDropdowns(); break;
      case 'myCourses':         loadCoordinatorCourses(); break;
      case 'manageContent':     loadCoordinatorCourseDropdowns('contentCourseSelect'); break;
      case 'myStudents':        loadCoordinatorCourseDropdowns('studentCourseSelect'); break;
      case 'browseCourses':     loadBrowseCourses(); break;
      case 'myLearning':        loadMyLearning(); break;
      case 'courseViewer':      loadCourseFromUrl(); break;
      case 'profile':           loadProfile(); break;
    }
  }

  // ══════════════════════════════════════════
  //  ROLE-SPECIFIC DASHBOARDS
  // ══════════════════════════════════════════
  function loadRoleDashboard() {
    api({ get_dashboard_stats: 1 }, function (res) {
      if (res.status !== 200) return;
      const d = res.data;
      let html = '';

      if (USER_ROLE === 'superAdmin') {
        html += statCard('fa-university', 'Colleges', d.colleges, '#4285f4');
        html += statCard('fa-users', 'Total Users', d.users, '#9b72cb');
        html += statCard('fa-book', 'Courses', d.courses, '#34d399');
        html += statCard('fa-user-graduate', 'Students', d.students, '#fbbf24');
        html += statCard('fa-chalkboard-teacher', 'Coordinators', d.coordinators, '#f87171');
        html += statCard('fa-clipboard-list', 'Enrollments', d.enrollments, '#a78bfa');
        if (d.recent_users) {
          let tbody = '';
          d.recent_users.forEach(u => {
            tbody += `<tr><td>${esc(u.name)}</td><td>${esc(u.email)}</td><td><span class="badge badge-role">${roleLabel(u.role)}</span></td><td>${esc(u.college_name || '-')}</td><td>${formatDate(u.created_at)}</td></tr>`;
          });
          $('#recentUsersBody').html(tbody);
        }
      } else if (USER_ROLE === 'adminZiyaa') {
        html += statCard('fa-users', 'Users', d.users, '#4285f4');
        html += statCard('fa-book', 'Courses', d.courses, '#9b72cb');
        html += statCard('fa-university', 'Colleges', d.colleges, '#34d399');
        html += statCard('fa-clipboard-list', 'Enrollments', d.enrollments, '#fbbf24');
        // Load admin-specific extras
        loadAdminDashboardExtras();
      } else if (USER_ROLE === 'ziyaaCoordinator') {
        html += statCard('fa-book-open', 'Assigned Courses', d.courses, '#4285f4');
        html += statCard('fa-user-graduate', 'My Students', d.students, '#9b72cb');
        html += statCard('fa-file-alt', 'Content Uploaded', d.content, '#34d399');
        html += statCard('fa-clipboard-list', 'Enrollments', d.enrollments, '#fbbf24');
        // Load coordinator recent students
        loadCoordinatorDashboardExtras();
      } else if (USER_ROLE === 'ziyaaStudents') {
        html += statCard('fa-book-reader', 'Enrolled Courses', d.enrolled, '#4285f4');
        html += statCard('fa-check-circle', 'Completed', d.completed, '#34d399');
        html += statCard('fa-compass', 'Available Courses', d.available, '#9b72cb');
        html += statCard('fa-chart-line', 'Avg Progress', d.avg_progress + '%', '#fbbf24');
        // Load student continue learning
        loadStudentDashboardExtras();
      }

      $('#stats-container').html(html);
    });
  }

  // ── Admin Dashboard Extras ──
  function loadAdminDashboardExtras() {
    // User breakdown
    api({ get_users: 1 }, function (res) {
      if (res.status !== 200) return;
      const roles = {};
      res.data.forEach(u => { roles[u.role] = (roles[u.role] || 0) + 1; });
      let html = '<div class="breakdown-list">';
      const roleMap = { ziyaaCoordinator: { label: 'Coordinators', color: '#9b72cb' }, ziyaaStudents: { label: 'Students', color: '#4285f4' } };
      Object.keys(roleMap).forEach(r => {
        const count = roles[r] || 0;
        const info = roleMap[r];
        html += `<div class="breakdown-item"><span class="breakdown-dot" style="background:${info.color};"></span><span>${info.label}</span><strong>${count}</strong></div>`;
      });
      html += '</div>';
      $('#adminUserBreakdown').html(html);
    });
    // Recent courses
    api({ get_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      const recent = res.data.slice(0, 5);
      if (recent.length === 0) {
        $('#adminRecentCourses').html('<div class="empty-state" style="padding:1rem;"><p>No courses yet</p></div>');
        return;
      }
      let html = '<div class="breakdown-list">';
      recent.forEach(c => {
        html += `<div class="breakdown-item"><span class="badge badge-${c.status === 'active' ? 'active' : c.status === 'draft' ? 'draft' : 'inactive'}">${c.status}</span><span>${esc(c.title)}</span><small style="color:var(--text-muted);">${c.enrollment_count} enrolled</small></div>`;
      });
      html += '</div>';
      $('#adminRecentCourses').html(html);
    });
  }

  // ── Coordinator Dashboard Extras ──
  function loadCoordinatorDashboardExtras() {
    api({ get_enrollments: 1 }, function (res) {
      if (res.status !== 200) return;
      const recent = res.data.slice(0, 8);
      if (recent.length === 0) {
        $('#coordRecentStudents').html('<tr><td colspan="5" class="empty-state"><i class="fas fa-user-graduate"></i><p>No enrollments yet</p></td></tr>');
        return;
      }
      let html = '';
      recent.forEach(e => {
        html += `<tr>
          <td>${esc(e.student_name)}</td>
          <td>${esc(e.course_title)}</td>
          <td><div class="progress-bar-wrap" style="min-width:80px;"><div class="progress-bar-fill" style="width:${e.progress}%;"></div></div><span style="font-size:0.8rem;">${e.progress}%</span></td>
          <td><span class="badge badge-${e.status === 'completed' ? 'active' : e.status === 'active' ? 'draft' : 'inactive'}">${e.status}</span></td>
          <td>${formatDate(e.enrolled_at)}</td></tr>`;
      });
      $('#coordRecentStudents').html(html);
    });
  }

  // ── Student Dashboard Extras ──
  function loadStudentDashboardExtras() {
    api({ get_my_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      // Show in-progress courses (not completed)
      const inProgress = res.data.filter(c => c.enroll_status !== 'completed').slice(0, 4);
      if (inProgress.length === 0) {
        $('#continueLearningGrid').html('<div class="empty-state"><i class="fas fa-graduation-cap"></i><p>No courses in progress. <a href="browseCourses.php">Browse courses</a> to get started!</p></div>');
        return;
      }
      let html = '';
      inProgress.forEach(c => {
        html += `<div class="course-card" style="cursor:pointer;" onclick="window.location.href='courseViewer.php?course_id=${c.id}&enrollment_id=${c.enrollment_id}'">
          <div class="course-card-thumb">${c.thumbnail ? `<img src="${c.thumbnail}">` : '<i class="fas fa-book"></i>'}</div>
          <div class="course-card-body">
            <h3>${esc(c.title)}</h3>
            <p>${esc((c.description || '').substring(0, 80))}</p>
            <div style="margin-top:0.75rem;">
              <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:${c.progress}%;"></div></div>
              <div style="display:flex;justify-content:space-between;margin-top:0.3rem;font-size:0.8rem;color:var(--text-muted);">
                <span>${c.progress}% complete</span><span>${c.content_count} lessons</span>
              </div>
            </div>
          </div>
          <div class="course-card-footer">
            <span class="badge badge-draft">${c.enroll_status}</span>
            <span style="font-size:0.8rem;color:var(--text-muted);">Enrolled ${formatDate(c.enrolled_at)}</span>
          </div>
        </div>`;
      });
      $('#continueLearningGrid').html(html);
    });
  }

  function statCard(icon, label, value, color) {
    return `<div class="stat-card">
      <div class="stat-icon" style="background:${color}15;color:${color};"><i class="fas ${icon}"></i></div>
      <div><div class="stat-value">${value ?? 0}</div><div class="stat-label">${label}</div></div>
    </div>`;
  }

  // ══════════════════════════════════════════
  //  COLLEGES (superAdmin)
  // ══════════════════════════════════════════
  window.loadColleges = function () {
    api({ get_colleges: 1 }, function (res) {
      if (res.status !== 200) return;
      let html = '';
      if (res.data.length === 0) {
        html = '<tr><td colspan="7" class="empty-state"><i class="fas fa-university"></i><p>No colleges yet</p></td></tr>';
      }
      res.data.forEach((c, i) => {
        html += `<tr>
          <td>${i + 1}</td><td>${esc(c.name)}</td><td><code>${esc(c.code)}</code></td><td>${esc(c.city || '-')}</td>
          <td>${c.user_count}</td>
          <td><span class="badge badge-${c.status === 'active' ? 'active' : 'inactive'}">${c.status}</span></td>
          <td class="actions">
            <button class="btn btn-outline btn-sm" onclick="showCollegeModal(${c.id})"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-sm" onclick="deleteCollege(${c.id})"><i class="fas fa-trash"></i></button>
          </td></tr>`;
      });
      $('#collegesBody').html(html);
    });
  };

  window.showCollegeModal = function (id) {
    const isEdit = !!id;
    const load = isEdit ? new Promise(r => {
      api({ get_colleges: 1 }, res => {
        const c = res.data.find(x => x.id == id);
        r(c || {});
      });
    }) : Promise.resolve({});

    load.then(c => {
      Swal.fire({
        title: isEdit ? 'Edit College' : 'Add College',
        html: `<div class="swal-form">
          <div class="form-group"><label class="form-label">Name</label><input id="sCollegeName" class="form-input" value="${esc(c.name || '')}"></div>
          <div class="form-group"><label class="form-label">Code</label><input id="sCollegeCode" class="form-input" value="${esc(c.code || '')}" ${isEdit ? '' : 'placeholder="e.g. MKCE"'}></div>
          <div class="form-group"><label class="form-label">City</label><input id="sCollegeCity" class="form-input" value="${esc(c.city || '')}"></div>
          <div class="form-group"><label class="form-label">Address</label><textarea id="sCollegeAddr" class="form-input" rows="2">${esc(c.address || '')}</textarea></div>
          ${isEdit ? `<div class="form-group"><label class="form-label">Status</label><select id="sCollegeStatus" class="form-input"><option value="active" ${c.status === 'active' ? 'selected' : ''}>Active</option><option value="inactive" ${c.status === 'inactive' ? 'selected' : ''}>Inactive</option></select></div>` : ''}
        </div>`,
        showCancelButton: true, confirmButtonText: isEdit ? 'Update' : 'Add', confirmButtonColor: '#4285f4',
        preConfirm: () => {
          const data = {
            name: $('#sCollegeName').val(), code: $('#sCollegeCode').val(),
            city: $('#sCollegeCity').val(), address: $('#sCollegeAddr').val()
          };
          if (!data.name || !data.code) { Swal.showValidationMessage('Name and Code are required'); return false; }
          if (isEdit) { data.id = id; data.status = $('#sCollegeStatus').val(); data.update_college = 1; }
          else { data.add_college = 1; }
          return new Promise(resolve => {
            api(data, res => {
              if (res.status === 200) resolve(res);
              else Swal.showValidationMessage(res.message);
            });
          });
        }
      }).then(result => {
        if (result.isConfirmed) {
          toast('success', result.value.message);
          loadColleges();
        }
      });
    });
  };

  window.deleteCollege = function (id) {
    Swal.fire({
      title: 'Delete College?', text: 'All associated data will be affected.', icon: 'warning',
      showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete'
    }).then(result => {
      if (result.isConfirmed) {
        api({ delete_college: 1, id: id }, res => {
          toast(res.status === 200 ? 'success' : 'error', res.message);
          if (res.status === 200) loadColleges();
        });
      }
    });
  };

  // ══════════════════════════════════════════
  //  USERS (superAdmin, adminZiyaa)
  // ══════════════════════════════════════════
  let collegesCache = [];

  function loadCollegeDropdowns() {
    api({ get_colleges: 1 }, function (res) {
      if (res.status !== 200) return;
      collegesCache = res.data;
      let opts = '<option value="">All Colleges</option>';
      res.data.forEach(c => { opts += `<option value="${c.id}">${esc(c.name)}</option>`; });
      $('#userCollegeFilter').html(opts);
    });
  }

  window.loadUsers = function () {
    const data = { get_users: 1 };
    const rf = $('#userRoleFilter').val();
    const cf = $('#userCollegeFilter').val();
    if (rf) data.role_filter = rf;
    if (cf) data.college_filter = cf;
    api(data, function (res) {
      if (res.status !== 200) return;
      let html = '';
      if (res.data.length === 0) html = '<tr><td colspan="7" class="empty-state"><i class="fas fa-users"></i><p>No users found</p></td></tr>';
      res.data.forEach((u, i) => {
        html += `<tr>
          <td>${i + 1}</td><td>${esc(u.name)}</td><td>${esc(u.email)}</td>
          <td><span class="badge badge-role">${roleLabel(u.role)}</span></td>
          <td>${esc(u.college_name || '-')}</td>
          <td><span class="badge badge-${u.status === 'active' ? 'active' : 'inactive'}">${u.status}</span></td>
          <td class="actions">
            <button class="btn btn-outline btn-sm" onclick="showUserModal(${u.id})"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})"><i class="fas fa-trash"></i></button>
          </td></tr>`;
      });
      $('#usersBody').html(html);
    });
  };

  window.showUserModal = function (id) {
    const isEdit = !!id;
    const loadUser = isEdit ? new Promise(r => {
      api({ get_users: 1 }, res => { r(res.data.find(x => x.id == id) || {}); });
    }) : Promise.resolve({});

    const loadCollegesP = collegesCache.length ? Promise.resolve(collegesCache) : new Promise(r => {
      api({ get_colleges: 1 }, res => { collegesCache = res.data || []; r(collegesCache); });
    });

    Promise.all([loadUser, loadCollegesP]).then(([u, colleges]) => {
      let collegeOpts = '<option value="">No College (for admins)</option>';
      colleges.forEach(c => { collegeOpts += `<option value="${c.id}" ${u.college_id == c.id ? 'selected' : ''}>${esc(c.name)}</option>`; });

      let roleOpts = '';
      if (USER_ROLE === 'superAdmin') {
        roleOpts = `<option value="superAdmin" ${u.role === 'superAdmin' ? 'selected' : ''}>Super Admin</option>
          <option value="adminZiyaa" ${u.role === 'adminZiyaa' ? 'selected' : ''}>Admin Ziyaa</option>`;
      }
      roleOpts += `<option value="ziyaaCoordinator" ${u.role === 'ziyaaCoordinator' ? 'selected' : ''}>Coordinator</option>
        <option value="ziyaaStudents" ${u.role === 'ziyaaStudents' ? 'selected' : ''}>Student</option>`;

      Swal.fire({
        title: isEdit ? 'Edit User' : 'Add User', width: 500,
        html: `<div class="swal-form">
          <div class="form-group"><label class="form-label">Name</label><input id="sUserName" class="form-input" value="${esc(u.name || '')}"></div>
          <div class="form-group"><label class="form-label">Email</label><input id="sUserEmail" class="form-input" type="email" value="${esc(u.email || '')}"></div>
          <div class="form-group"><label class="form-label">Password ${isEdit ? '(leave blank to keep)' : ''}</label><input id="sUserPass" class="form-input" type="password" placeholder="${isEdit ? 'Unchanged' : 'Password'}"></div>
          <div class="form-group"><label class="form-label">Role</label><select id="sUserRole" class="form-input">${roleOpts}</select></div>
          <div class="form-group"><label class="form-label">College</label><select id="sUserCollege" class="form-input">${collegeOpts}</select></div>
          <div class="form-group"><label class="form-label">Phone</label><input id="sUserPhone" class="form-input" value="${esc(u.phone || '')}"></div>
          ${isEdit ? `<div class="form-group"><label class="form-label">Status</label><select id="sUserStatus" class="form-input"><option value="active" ${u.status === 'active' ? 'selected' : ''}>Active</option><option value="inactive" ${u.status === 'inactive' ? 'selected' : ''}>Inactive</option></select></div>` : ''}
        </div>`,
        showCancelButton: true, confirmButtonText: isEdit ? 'Update' : 'Add', confirmButtonColor: '#4285f4',
        preConfirm: () => {
          const data = {
            name: $('#sUserName').val(), email: $('#sUserEmail').val(),
            role: $('#sUserRole').val(), college_id: $('#sUserCollege').val(),
            phone: $('#sUserPhone').val()
          };
          const pass = $('#sUserPass').val();
          if (pass) data.password = pass;
          if (!data.name || !data.email) { Swal.showValidationMessage('Name and Email are required'); return false; }
          if (!isEdit && !pass) { Swal.showValidationMessage('Password is required'); return false; }
          if (isEdit) { data.id = id; data.status = $('#sUserStatus').val(); data.update_user = 1; }
          else { data.password = pass; data.add_user = 1; }
          return new Promise(resolve => {
            api(data, res => {
              if (res.status === 200) resolve(res);
              else Swal.showValidationMessage(res.message);
            });
          });
        }
      }).then(result => {
        if (result.isConfirmed) {
          toast('success', result.value.message);
          loadUsers();
        }
      });
    });
  };

  window.deleteUser = function (id) {
    Swal.fire({
      title: 'Delete User?', text: 'This action cannot be undone.', icon: 'warning',
      showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete'
    }).then(r => {
      if (r.isConfirmed) api({ delete_user: 1, id }, res => { toast(res.status === 200 ? 'success' : 'error', res.message); if (res.status === 200) loadUsers(); });
    });
  };

  // ══════════════════════════════════════════
  //  COURSES (superAdmin, adminZiyaa)
  // ══════════════════════════════════════════
  let coursesCache = [];

  window.loadCourses = function () {
    api({ get_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      coursesCache = res.data;
      let html = '';
      if (res.data.length === 0) html = '<tr><td colspan="8" class="empty-state"><i class="fas fa-book"></i><p>No courses yet</p></td></tr>';
      res.data.forEach((c, i) => {
        html += `<tr>
          <td>${i + 1}</td><td>${esc(c.title)}</td><td>${esc(c.category || '-')}</td>
          <td>${c.college_count}</td><td>${c.enrollment_count}</td><td>${c.content_count}</td>
          <td><span class="badge badge-${c.status === 'active' ? 'active' : c.status === 'draft' ? 'draft' : 'inactive'}">${c.status}</span></td>
          <td class="actions">
            <button class="btn btn-outline btn-sm" onclick="showCourseModal(${c.id})"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-sm" onclick="deleteCourse(${c.id})"><i class="fas fa-trash"></i></button>
          </td></tr>`;
      });
      $('#coursesBody').html(html);
    });
  };

  window.showCourseModal = function (id) {
    const isEdit = !!id;
    const load = isEdit ? new Promise(r => {
      const c = coursesCache.find(x => x.id == id);
      r(c || {});
    }) : Promise.resolve({});

    load.then(c => {
      Swal.fire({
        title: isEdit ? 'Edit Course' : 'Add Course', width: 500,
        html: `<div class="swal-form">
          <div class="form-group"><label class="form-label">Title</label><input id="sCourseTitle" class="form-input" value="${esc(c.title || '')}"></div>
          <div class="form-group"><label class="form-label">Description</label><textarea id="sCourseDesc" class="form-input" rows="3">${esc(c.description || '')}</textarea></div>
          <div class="form-group"><label class="form-label">Category</label><input id="sCourseCat" class="form-input" value="${esc(c.category || '')}" placeholder="e.g. Programming, Science"></div>
          <div class="form-group"><label class="form-label">Status</label><select id="sCourseStatus" class="form-input">
            <option value="draft" ${c.status === 'draft' ? 'selected' : ''}>Draft</option>
            <option value="active" ${c.status === 'active' ? 'selected' : ''}>Active</option>
            <option value="archived" ${c.status === 'archived' ? 'selected' : ''}>Archived</option>
          </select></div>
        </div>`,
        showCancelButton: true, confirmButtonText: isEdit ? 'Update' : 'Create', confirmButtonColor: '#4285f4',
        preConfirm: () => {
          const data = {
            title: $('#sCourseTitle').val(), description: $('#sCourseDesc').val(),
            category: $('#sCourseCat').val(), status: $('#sCourseStatus').val()
          };
          if (!data.title) { Swal.showValidationMessage('Title is required'); return false; }
          if (isEdit) { data.id = id; data.update_course = 1; }
          else { data.add_course = 1; }
          return new Promise(resolve => {
            api(data, res => {
              if (res.status === 200) resolve(res);
              else Swal.showValidationMessage(res.message);
            });
          });
        }
      }).then(result => {
        if (result.isConfirmed) { toast('success', result.value.message); loadCourses(); }
      });
    });
  };

  window.deleteCourse = function (id) {
    Swal.fire({
      title: 'Delete Course?', text: 'All content and enrollments will be lost.', icon: 'warning',
      showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Delete'
    }).then(r => {
      if (r.isConfirmed) api({ delete_course: 1, id }, res => { toast(res.status === 200 ? 'success' : 'error', res.message); if (res.status === 200) loadCourses(); });
    });
  };

  // ══════════════════════════════════════════
  //  ASSIGNMENTS (superAdmin, adminZiyaa)
  // ══════════════════════════════════════════
  function loadCourseDropdowns() {
    api({ get_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      coursesCache = res.data;
      let opts = '<option value="">Select a course</option>';
      res.data.forEach(c => { opts += `<option value="${c.id}">${esc(c.title)}</option>`; });
      $('#assignCourseSelect').html(opts);
    });
  }

  window.loadAssignments = function () {
    const cid = $('#assignCourseSelect').val();
    if (!cid) { $('#assignmentsBody').html('<tr><td colspan="6" class="empty-state"><i class="fas fa-link"></i><p>Select a course above</p></td></tr>'); return; }
    api({ get_course_assignments: 1, course_id: cid }, function (res) {
      if (res.status !== 200) return;
      let html = '';
      if (res.data.length === 0) html = '<tr><td colspan="6" class="empty-state"><i class="fas fa-link"></i><p>No colleges assigned</p></td></tr>';
      res.data.forEach((a, i) => {
        html += `<tr>
          <td>${i + 1}</td><td>${esc(a.college_name)}</td><td><code>${esc(a.college_code)}</code></td>
          <td>${esc(a.assigned_by_name || '-')}</td><td>${formatDate(a.assigned_at)}</td>
          <td class="actions"><button class="btn btn-danger btn-sm" onclick="unassignCourse(${cid},${a.college_id})"><i class="fas fa-unlink"></i></button></td></tr>`;
      });
      $('#assignmentsBody').html(html);
    });
  };

  window.showAssignModal = function () {
    const cid = $('#assignCourseSelect').val();
    if (!cid) { toast('warning', 'Select a course first'); return; }
    const loadC = collegesCache.length ? Promise.resolve(collegesCache) : new Promise(r => {
      api({ get_colleges: 1 }, res => { collegesCache = res.data || []; r(collegesCache); });
    });
    loadC.then(colleges => {
      let opts = '';
      colleges.forEach(c => { opts += `<option value="${c.id}">${esc(c.name)} (${esc(c.code)})</option>`; });
      Swal.fire({
        title: 'Assign Course to College',
        html: `<div class="swal-form"><div class="form-group"><label class="form-label">College</label><select id="sAssignCollege" class="form-input">${opts}</select></div></div>`,
        showCancelButton: true, confirmButtonText: 'Assign', confirmButtonColor: '#4285f4',
        preConfirm: () => {
          const college_id = $('#sAssignCollege').val();
          return new Promise(resolve => {
            api({ assign_course: 1, course_id: cid, college_id }, res => {
              if (res.status === 200) resolve(res);
              else Swal.showValidationMessage(res.message);
            });
          });
        }
      }).then(result => {
        if (result.isConfirmed) { toast('success', result.value.message); loadAssignments(); }
      });
    });
  };

  window.unassignCourse = function (courseId, collegeId) {
    Swal.fire({
      title: 'Remove Assignment?', text: 'Students from this college will lose access.', icon: 'warning',
      showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Remove'
    }).then(r => {
      if (r.isConfirmed) api({ unassign_course: 1, course_id: courseId, college_id: collegeId }, res => {
        toast(res.status === 200 ? 'success' : 'error', res.message); if (res.status === 200) loadAssignments();
      });
    });
  };

  // ══════════════════════════════════════════
  //  COORDINATOR: Courses, Content, Students
  // ══════════════════════════════════════════
  function loadCoordinatorCourses() {
    api({ get_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      let html = '';
      if (res.data.length === 0) html = '<div class="empty-state"><i class="fas fa-book-open"></i><p>No courses assigned to your college yet</p></div>';
      res.data.forEach(c => {
        html += `<div class="course-card">
          <div class="course-card-thumb">${c.thumbnail ? `<img src="${c.thumbnail}">` : '<i class="fas fa-book"></i>'}</div>
          <div class="course-card-body">
            <h3>${esc(c.title)}</h3>
            <p>${esc((c.description || '').substring(0, 100))}${c.description && c.description.length > 100 ? '...' : ''}</p>
          </div>
          <div class="course-card-footer">
            <span class="badge badge-${c.status === 'active' ? 'active' : 'draft'}">${c.status}</span>
            <span style="font-size:0.85rem;color:var(--text-muted);">${c.content_count} items &middot; ${c.enrollment_count} enrolled</span>
          </div>
        </div>`;
      });
      $('#coordCoursesGrid').html(html);
    });
  }

  function loadCoordinatorCourseDropdowns(targetId) {
    targetId = targetId || 'contentCourseSelect';
    api({ get_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      coursesCache = res.data;
      let opts = '<option value="">Select a course</option>';
      res.data.forEach(c => { opts += `<option value="${c.id}">${esc(c.title)}</option>`; });
      $(`#${targetId}`).html(opts);
    });
  }

  window.loadContent = function () {
    const cid = $('#contentCourseSelect').val();
    if (!cid) { $('#contentList').html('<div class="empty-state"><i class="fas fa-file-alt"></i><p>Select a course above</p></div>'); return; }
    api({ get_content: 1, course_id: cid }, function (res) {
      if (res.status !== 200) return;
      let html = '';
      if (res.data.length === 0) html = '<div class="empty-state"><i class="fas fa-file-alt"></i><p>No content yet. Click "Add Content" to get started.</p></div>';
      res.data.forEach((c, i) => {
        const iconClass = c.content_type === 'video' ? 'video' : c.content_type === 'pdf' ? 'pdf' : c.content_type === 'link' ? 'link' : 'text';
        const iconName = c.content_type === 'video' ? 'fa-play-circle' : c.content_type === 'pdf' ? 'fa-file-pdf' : c.content_type === 'link' ? 'fa-external-link-alt' : 'fa-align-left';
        html += `<div class="content-item">
          <div class="content-icon ${iconClass}"><i class="fas ${iconName}"></i></div>
          <div class="content-body">
            <h4>${esc(c.title)}</h4>
            <p>${esc((c.description || '').substring(0, 120))}</p>
            <div style="margin-top:0.3rem;font-size:0.75rem;color:var(--text-muted);">Order: ${c.sort_order} &middot; <span class="badge badge-${c.status === 'active' ? 'active' : 'inactive'}">${c.status}</span></div>
          </div>
          <div class="content-actions">
            <button class="btn btn-outline btn-sm" onclick="showContentModal(${c.id}, ${cid})"><i class="fas fa-edit"></i></button>
            <button class="btn btn-danger btn-sm" onclick="deleteContent(${c.id})"><i class="fas fa-trash"></i></button>
          </div>
        </div>`;
      });
      $('#contentList').html(html);
    });
  };

  window.showContentModal = function (id, courseId) {
    const isEdit = !!id;
    courseId = courseId || $('#contentCourseSelect').val();
    if (!courseId) { toast('warning', 'Select a course first'); return; }

    const load = isEdit ? new Promise(r => {
      api({ get_content: 1, course_id: courseId }, res => { r(res.data.find(x => x.id == id) || {}); });
    }) : Promise.resolve({});

    load.then(c => {
      Swal.fire({
        title: isEdit ? 'Edit Content' : 'Add Content', width: 520,
        html: `<div class="swal-form">
          <div class="form-group"><label class="form-label">Title</label><input id="sContentTitle" class="form-input" value="${esc(c.title || '')}"></div>
          <div class="form-group"><label class="form-label">Description</label><textarea id="sContentDesc" class="form-input" rows="2">${esc(c.description || '')}</textarea></div>
          <div class="form-group"><label class="form-label">Type</label><select id="sContentType" class="form-input">
            <option value="video" ${c.content_type === 'video' ? 'selected' : ''}>Video (YouTube URL)</option>
            <option value="pdf" ${c.content_type === 'pdf' ? 'selected' : ''}>PDF (URL)</option>
            <option value="text" ${c.content_type === 'text' ? 'selected' : ''}>Text</option>
            <option value="link" ${c.content_type === 'link' ? 'selected' : ''}>Link</option>
          </select></div>
          <div class="form-group"><label class="form-label">Content Data (URL or Text)</label><textarea id="sContentData" class="form-input" rows="3" placeholder="Enter URL or text content">${esc(c.content_data || '')}</textarea></div>
          <div class="form-group"><label class="form-label">Sort Order</label><input id="sContentSort" class="form-input" type="number" value="${c.sort_order || 0}"></div>
          ${isEdit ? `<div class="form-group"><label class="form-label">Status</label><select id="sContentStatus" class="form-input"><option value="active" ${c.status === 'active' ? 'selected' : ''}>Active</option><option value="inactive" ${c.status === 'inactive' ? 'selected' : ''}>Inactive</option></select></div>` : ''}
        </div>`,
        showCancelButton: true, confirmButtonText: isEdit ? 'Update' : 'Add', confirmButtonColor: '#4285f4',
        preConfirm: () => {
          const data = {
            course_id: courseId, title: $('#sContentTitle').val(), description: $('#sContentDesc').val(),
            content_type: $('#sContentType').val(), content_data: $('#sContentData').val(), sort_order: $('#sContentSort').val()
          };
          if (!data.title) { Swal.showValidationMessage('Title is required'); return false; }
          if (isEdit) { data.id = id; data.status = $('#sContentStatus').val(); data.update_content = 1; }
          else { data.add_content = 1; }
          return new Promise(resolve => {
            api(data, res => {
              if (res.status === 200) resolve(res);
              else Swal.showValidationMessage(res.message);
            });
          });
        }
      }).then(result => {
        if (result.isConfirmed) { toast('success', result.value.message); loadContent(); }
      });
    });
  };

  window.deleteContent = function (id) {
    Swal.fire({
      title: 'Delete Content?', icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#dc2626', confirmButtonText: 'Delete'
    }).then(r => {
      if (r.isConfirmed) api({ delete_content: 1, id }, res => { toast(res.status === 200 ? 'success' : 'error', res.message); if (res.status === 200) loadContent(); });
    });
  };

  window.loadCourseStudents = function () {
    const cid = $('#studentCourseSelect').val();
    if (!cid) { $('#courseStudentsBody').html('<tr><td colspan="7" class="empty-state"><i class="fas fa-user-graduate"></i><p>Select a course above</p></td></tr>'); return; }
    api({ get_course_students: 1, course_id: cid }, function (res) {
      if (res.status !== 200) return;
      let html = '';
      if (res.data.length === 0) html = '<tr><td colspan="7" class="empty-state"><i class="fas fa-user-graduate"></i><p>No students enrolled</p></td></tr>';
      res.data.forEach((s, i) => {
        html += `<tr>
          <td>${i + 1}</td><td>${esc(s.student_name)}</td><td>${esc(s.student_email)}</td><td>${esc(s.phone || '-')}</td>
          <td><div class="progress-bar-wrap" style="min-width:80px;"><div class="progress-bar-fill" style="width:${s.progress}%;"></div></div><span style="font-size:0.8rem;">${s.progress}%</span></td>
          <td><span class="badge badge-${s.status === 'completed' ? 'active' : s.status === 'active' ? 'draft' : 'inactive'}">${s.status}</span></td>
          <td>${formatDate(s.enrolled_at)}</td></tr>`;
      });
      $('#courseStudentsBody').html(html);
    });
  };

  // ══════════════════════════════════════════
  //  STUDENT: Browse, Learning, Course Viewer
  // ══════════════════════════════════════════
  function loadBrowseCourses() {
    api({ get_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      api({ get_my_courses: 1 }, function (eRes) {
        const enrolled = (eRes.data || []).map(e => e.id);
        let html = '';
        if (res.data.length === 0) html = '<div class="empty-state"><i class="fas fa-compass"></i><p>No courses available for your college yet</p></div>';
        res.data.forEach(c => {
          const isEnrolled = enrolled.includes(c.id);
          html += `<div class="course-card">
            <div class="course-card-thumb">${c.thumbnail ? `<img src="${c.thumbnail}">` : '<i class="fas fa-book"></i>'}</div>
            <div class="course-card-body">
              <h3>${esc(c.title)}</h3>
              <p>${esc((c.description || '').substring(0, 100))}${c.description && c.description.length > 100 ? '...' : ''}</p>
              <div style="margin-top:0.5rem;"><span class="badge badge-role">${esc(c.category || 'General')}</span></div>
            </div>
            <div class="course-card-footer">
              <span style="font-size:0.85rem;color:var(--text-muted);">${c.content_count} lessons</span>
              ${isEnrolled ? '<span class="badge badge-active">Enrolled</span>' : `<button class="btn btn-primary btn-sm" onclick="enrollCourse(${c.id})"><i class="fas fa-plus"></i> Enroll</button>`}
            </div>
          </div>`;
        });
        $('#browseCoursesGrid').html(html);
      });
    });
  }

  window.enrollCourse = function (courseId) {
    Swal.fire({
      title: 'Enroll in Course?', text: 'You will get access to all course content.', icon: 'question',
      showCancelButton: true, confirmButtonColor: '#4285f4', confirmButtonText: 'Enroll'
    }).then(r => {
      if (r.isConfirmed) api({ enroll_student: 1, course_id: courseId }, res => {
        toast(res.status === 200 ? 'success' : 'error', res.message);
        if (res.status === 200) loadBrowseCourses();
      });
    });
  };

  function loadMyLearning() {
    api({ get_my_courses: 1 }, function (res) {
      if (res.status !== 200) return;
      let html = '';
      if (res.data.length === 0) html = '<div class="empty-state"><i class="fas fa-graduation-cap"></i><p>You haven\'t enrolled in any courses yet.<br><a href="browseCourses.php">Browse courses</a></p></div>';
      res.data.forEach(c => {
        html += `<div class="course-card" style="cursor:pointer;" onclick="viewCourse(${c.id}, ${c.enrollment_id})">
          <div class="course-card-thumb">${c.thumbnail ? `<img src="${c.thumbnail}">` : '<i class="fas fa-book"></i>'}</div>
          <div class="course-card-body">
            <h3>${esc(c.title)}</h3>
            <p>${esc((c.description || '').substring(0, 80))}</p>
            <div style="margin-top:0.75rem;">
              <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:${c.progress}%;"></div></div>
              <div style="display:flex;justify-content:space-between;margin-top:0.3rem;font-size:0.8rem;color:var(--text-muted);">
                <span>${c.progress}% complete</span><span>${c.content_count} lessons</span>
              </div>
            </div>
          </div>
          <div class="course-card-footer">
            <span class="badge badge-${c.enroll_status === 'completed' ? 'active' : 'draft'}">${c.enroll_status}</span>
            <span style="font-size:0.8rem;color:var(--text-muted);">Enrolled ${formatDate(c.enrolled_at)}</span>
          </div>
        </div>`;
      });
      $('#myLearningGrid').html(html);
    });
  }

  // Navigate to course viewer page with query params
  window.viewCourse = function (courseId, enrollmentId) {
    window.location.href = `courseViewer.php?course_id=${courseId}&enrollment_id=${enrollmentId}`;
  };

  // Load course content from URL params (courseViewer page)
  function loadCourseFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const courseId = params.get('course_id');
    const enrollmentId = params.get('enrollment_id');
    if (!courseId) {
      $('#courseViewContainer').html('<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>No course selected. <a href="myLearning.php">Go to My Learning</a></p></div>');
      return;
    }
    // Get course info + content
    api({ get_courses: 1 }, function (cRes) {
      const course = (cRes.data || []).find(c => c.id == courseId) || {};
      api({ get_content: 1, course_id: courseId }, function (res) {
        if (res.status !== 200) return;
        let html = `<div class="course-viewer-header">
          <h2>${esc(course.title || 'Course')}</h2>
          <p>${esc(course.description || '')}</p>
        </div>`;

        if (res.data.length === 0) {
          html += '<div class="empty-state"><i class="fas fa-file-alt"></i><p>No content available yet for this course.</p></div>';
        } else {
          html += '<div class="viewer-content-list">';
          res.data.forEach((c, i) => {
            const iconClass = c.content_type === 'video' ? 'video' : c.content_type === 'pdf' ? 'pdf' : c.content_type === 'link' ? 'link' : 'text';
            const iconName = c.content_type === 'video' ? 'fa-play-circle' : c.content_type === 'pdf' ? 'fa-file-pdf' : c.content_type === 'link' ? 'fa-external-link-alt' : 'fa-align-left';

            let contentHtml = '';
            if (c.content_type === 'video') {
              const embedUrl = getYouTubeEmbed(c.content_data);
              contentHtml = embedUrl ? `<iframe class="video-embed" src="${embedUrl}" allowfullscreen></iframe>` : `<a href="${esc(c.content_data)}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-external-link-alt"></i> Watch Video</a>`;
            } else if (c.content_type === 'pdf') {
              contentHtml = `<a href="${esc(c.content_data)}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-file-pdf"></i> Open PDF</a>`;
            } else if (c.content_type === 'link') {
              contentHtml = `<a href="${esc(c.content_data)}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-external-link-alt"></i> Open Link</a>`;
            } else {
              contentHtml = `<div style="white-space:pre-wrap;font-size:0.95rem;line-height:1.7;">${esc(c.content_data)}</div>`;
            }

            html += `<div class="viewer-item" onclick="toggleViewerItem(this)">
              <div class="content-icon ${iconClass}"><i class="fas ${iconName}"></i></div>
              <div style="flex:1;"><strong>${i + 1}. ${esc(c.title)}</strong><br><span style="font-size:0.8rem;color:var(--text-muted);">${esc(c.description || '')}</span></div>
              <i class="fas fa-chevron-down" style="color:var(--text-muted);transition:transform 0.2s;"></i>
            </div>
            <div class="viewer-item-content">${contentHtml}</div>`;
          });
          html += '</div>';

          if (enrollmentId) {
            html += `<div style="margin-top:2rem;text-align:center;">
              <button class="btn btn-success" onclick="updateCourseProgress(${enrollmentId}, ${res.data.length})"><i class="fas fa-check"></i> Mark Course Complete</button>
            </div>`;
          }
        }

        $('#courseViewContainer').html(html);
      });
    });
  }

  window.toggleViewerItem = function (el) {
    const $content = $(el).next('.viewer-item-content');
    $content.toggleClass('show');
    $(el).find('.fa-chevron-down').css('transform', $content.hasClass('show') ? 'rotate(180deg)' : 'rotate(0)');
  };

  window.updateCourseProgress = function (enrollmentId, totalItems) {
    Swal.fire({
      title: 'Mark as Complete?', text: 'This will set your progress to 100%.', icon: 'question',
      showCancelButton: true, confirmButtonColor: '#16a34a', confirmButtonText: 'Complete'
    }).then(r => {
      if (r.isConfirmed) api({ update_progress: 1, id: enrollmentId, progress: 100 }, res => {
        toast(res.status === 200 ? 'success' : 'error', res.message);
      });
    });
  };

  // ══════════════════════════════════════════
  //  PROFILE
  // ══════════════════════════════════════════
  function loadProfile() {
    api({ get_profile: 1 }, function (res) {
      if (res.status !== 200) return;
      const u = res.data;
      $('#profileName').val(u.name);
      $('#profileEmail').val(u.email);
      $('#profilePhone').val(u.phone || '');
      $('#profileCollege').val(u.college_name || 'N/A');
      $('#profilePassword').val('');
    });
  }

  $('#profileForm').submit(function (e) {
    e.preventDefault();
    const data = { update_profile: 1, name: $('#profileName').val(), phone: $('#profilePhone').val() };
    const pass = $('#profilePassword').val();
    if (pass) data.password = pass;
    api(data, function (res) { toast(res.status === 200 ? 'success' : 'error', res.message); });
  });

  // ══════════════════════════════════════════
  //  UTILITIES
  // ══════════════════════════════════════════
  function esc(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  function roleLabel(role) {
    const map = { superAdmin: 'Super Admin', adminZiyaa: 'Admin', ziyaaCoordinator: 'Coordinator', ziyaaStudents: 'Student' };
    return map[role] || role;
  }

  function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  }

  function toast(icon, msg) {
    Swal.fire({ icon, title: msg, toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
  }

  function getYouTubeEmbed(url) {
    if (!url) return '';
    const match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    return match ? `https://www.youtube.com/embed/${match[1]}` : '';
  }

});
