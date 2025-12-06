// Pin2Fix - Common JavaScript Functions

const API_BASE = '/api';

// Session Management
const Session = {
    set: (user) => {
        localStorage.setItem('pin2fix_user', JSON.stringify(user));
    },
    get: () => {
        const user = localStorage.getItem('pin2fix_user');
        return user ? JSON.parse(user) : null;
    },
    clear: () => {
        localStorage.removeItem('pin2fix_user');
    },
    isLoggedIn: () => {
        return Session.get() !== null;
    },
    getRole: () => {
        const user = Session.get();
        return user ? user.role : null;
    },
    getUserId: () => {
        const user = Session.get();
        return user ? user.userId : null;
    }
};

// API Helper Functions
const Api = {
    get: async (endpoint) => {
        try {
            const response = await fetch(`${API_BASE}${endpoint}`);
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },
    post: async (endpoint, data) => {
        try {
            const response = await fetch(`${API_BASE}${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },
    put: async (endpoint, data) => {
        try {
            const response = await fetch(`${API_BASE}${endpoint}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API PUT Error:', error);
            throw error;
        }
    },
    delete: async (endpoint) => {
        try {
            const response = await fetch(`${API_BASE}${endpoint}`, {
                method: 'DELETE'
            });
            return await response.json();
        } catch (error) {
            console.error('API DELETE Error:', error);
            throw error;
        }
    },
    upload: async (endpoint, formData) => {
        try {
            const response = await fetch(`${API_BASE}${endpoint}`, {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            console.error('API Upload Error:', error);
            throw error;
        }
    }
};

// SweetAlert Helpers
const Alert = {
    success: (title, text = '') => {
        return Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            confirmButtonColor: '#2563eb'
        });
    },
    error: (title, text = '') => {
        return Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            confirmButtonColor: '#2563eb'
        });
    },
    warning: (title, text = '') => {
        return Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            confirmButtonColor: '#2563eb'
        });
    },
    info: (title, text = '') => {
        return Swal.fire({
            icon: 'info',
            title: title,
            text: text,
            confirmButtonColor: '#2563eb'
        });
    },
    confirm: (title, text = '') => {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        });
    },
    loading: (title = 'Loading...') => {
        Swal.fire({
            title: title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },
    close: () => {
        Swal.close();
    }
};

// Status Helpers
const StatusHelper = {
    getClass: (status) => {
        const statusMap = {
            'PENDING': 'status-pending',
            'TRIAGED': 'status-triaged',
            'ASSIGNED': 'status-assigned',
            'IN_PROGRESS': 'status-in-progress',
            'WORK_COMPLETED_PENDING_HEAD_APPROVAL': 'status-work-completed',
            'PENDING_GOV_APPROVAL': 'status-pending-gov',
            'COMPLETED': 'status-completed',
            'REOPENED': 'status-reopened',
            'REJECTED': 'status-rejected'
        };
        return statusMap[status] || 'status-pending';
    },
    getLabel: (status) => {
        const labelMap = {
            'PENDING': 'Pending',
            'TRIAGED': 'Triaged',
            'ASSIGNED': 'Assigned',
            'IN_PROGRESS': 'In Progress',
            'WORK_COMPLETED_PENDING_HEAD_APPROVAL': 'Pending Head Approval',
            'PENDING_GOV_APPROVAL': 'Pending Gov Approval',
            'COMPLETED': 'Completed',
            'REOPENED': 'Reopened',
            'REJECTED': 'Rejected'
        };
        return labelMap[status] || status;
    }
};

// Date Formatter
const DateHelper = {
    format: (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    relative: (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
        if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        return 'Just now';
    }
};

// Navigation
const Nav = {
    to: (page) => {
        window.location.href = page;
    },
    toDashboard: () => {
        const role = Session.getRole();
        switch (role) {
            case 'CITIZEN':
                Nav.to('/citizen/dashboard.html');
                break;
            case 'GOV_BODY':
                Nav.to('/gov/dashboard.html');
                break;
            case 'DEPT_HEAD':
                Nav.to('/dept/dashboard.html');
                break;
            case 'AREA_HEAD':
                Nav.to('/area/dashboard.html');
                break;
            case 'WORKER':
                Nav.to('/worker/dashboard.html');
                break;
            case 'ADMIN':
                Nav.to('/admin/dashboard.html');
                break;
            default:
                Nav.to('/login.html');
        }
    },
    logout: () => {
        Session.clear();
        Alert.success('Logged Out', 'You have been logged out successfully.').then(() => {
            Nav.to('/login.html');
        });
    }
};

// Auth Guard
const AuthGuard = {
    check: (allowedRoles = []) => {
        if (!Session.isLoggedIn()) {
            Nav.to('/login.html');
            return false;
        }
        if (allowedRoles.length > 0 && !allowedRoles.includes(Session.getRole())) {
            Alert.error('Access Denied', 'You do not have permission to access this page.');
            Nav.toDashboard();
            return false;
        }
        return true;
    }
};

// Notification Badge Update
const updateNotificationBadge = async () => {
    const userId = Session.getUserId();
    if (!userId) return;

    try {
        const response = await Api.get(`/notifications/user/${userId}/count`);
        if (response.success) {
            const badge = document.getElementById('notification-count');
            if (badge) {
                if (response.data > 0) {
                    badge.textContent = response.data;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error updating notification badge:', error);
    }
};

// Initialize page common elements
const initPage = () => {
    // Update user info in navbar
    const user = Session.get();
    if (user) {
        const userNameEl = document.getElementById('user-name');
        if (userNameEl) {
            userNameEl.textContent = user.name;
        }
        updateNotificationBadge();
    }
};

// Run on page load
document.addEventListener('DOMContentLoaded', initPage);
