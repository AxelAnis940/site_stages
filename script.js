// ============================================
// AUTHENTICATION & PERMISSIONS SYSTEM
// ============================================

// Permission Matrix
const permissionsMatrix = {
    public: {
        browse: true,
        viewInternships: true,
        search: true,
    },
    student: {
        browse: true,
        viewInternships: true,
        search: true,
        applyToInternship: true,
        saveFavorites: true,
        viewApplications: true,
        editProfile: true,
    },
    pilote: {
        browse: true,
        viewInternships: true,
        search: true,
        postJob: true,
        viewApplications: true,
        managePostings: true,
        editProfile: true,
        viewAnalytics: true,
    },
    admin: {
        browse: true,
        viewInternships: true,
        search: true,
        applyToInternship: true,
        saveFavorites: true,
        viewApplications: true,
        editProfile: true,
        postJob: true,
        managePostings: true,
        viewAnalytics: true,
        manageUsers: true,
        viewLogs: true,
    },
};

// Test Credentials Database
const users = [
    {
        id: '1',
        name: 'John Student',
        email: 'student@example.com',
        password: 'password123',
        role: 'student',
    },
    {
        id: '2',
        name: 'Jane Pilote',
        email: 'pilote@example.com',
        password: 'password123',
        role: 'pilote',
    },
    {
        id: '3',
        name: 'Admin User',
        email: 'admin@example.com',
        password: 'password123',
        role: 'admin',
    },
];

// Current User State
let currentUser = null;

function normalizeRole(role) {
    const normalizedRole = typeof role === 'string' ? role.trim().toLowerCase() : role;
    return normalizedRole === 'recruiter' ? 'pilote' : normalizedRole;
}

function syncCurrentUserRole() {
    if (!currentUser) {
        return 'public';
    }

    const normalizedRole = normalizeRole(currentUser.role);
    if (normalizedRole !== currentUser.role) {
        currentUser.role = normalizedRole;
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
    }

    return normalizedRole;
}

function getSuggestedAppUrls() {
    const urls = [
        'http://localhost/index.html?v=20260320',
        'http://localhost/dev_web/index.html?v=20260320',
    ];

    return urls.join('\n');
}

function ensurePhpServerAvailable() {
    if (window.location.protocol === 'http:' || window.location.protocol === 'https:') {
        return true;
    }

    alert(
        'Cette page est ouverte hors serveur PHP.\n\n' +
        'Ouvre le projet depuis XAMPP/Apache avec une URL comme :\n' +
        getSuggestedAppUrls() +
        '\n\n' +
        'Si besoin, lance aussi setup_db.php depuis le navigateur.'
    );

    return false;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Load user from localStorage
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        try {
            currentUser = JSON.parse(savedUser);
            syncCurrentUserRole();
            updateUIForLoggedInUser();
        } catch (e) {
            console.error('Error loading saved user:', e);
            logout();
        }
    }
});

// ============================================
// AUTH MODAL MANAGEMENT
// ============================================

function openAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.style.display = 'flex';
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.style.display = 'block';
        }
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
document.addEventListener('click', (e) => {
    const modal = document.getElementById('authModal');
    if (modal && e.target === modal) {
        closeAuthModal();
    }
});

// ============================================
// LOGIN HANDLERS
// ============================================

function handleLogin(event) {
    event.preventDefault();

    if (!ensurePhpServerAvailable()) {
        return;
    }

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const role = document.getElementById('loginRole').value;

    if (!email || !password || !role) {
        alert('Veuillez remplir tous les champs.');
        return;
    }

    // Send login data to server for validation
    const fd = new FormData();
    fd.append('email', email);
    fd.append('password', password);
    fd.append('role', role);

    const targetUrl = new URL('users.php?action=login', window.location.href).toString();

    fetch(targetUrl, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(res => {
        if (!res.ok) return res.text().then(t => Promise.reject(new Error('HTTP ' + res.status + ': ' + t)));
        const ct = res.headers.get('content-type') || '';
        if (ct.indexOf('application/json') === -1) {
            return res.text().then(t => Promise.reject(new Error('Server error: ' + t)));
        }
        return res.json();
    })
      .then(data => {
          console.log('Login response:', data);
          if (data.success && data.user) {
              // Login successful - save user to localStorage
              currentUser = {
                  id: data.user.id,
                  name: data.user.name,
                  email: data.user.email,
                  role: normalizeRole(data.user.role),
                  loginTime: new Date().toISOString(),
              };
              localStorage.setItem('currentUser', JSON.stringify(currentUser));
              document.querySelector('#loginForm form').reset();
              closeAuthModal();
              updateUIForLoggedInUser();
              alert('Connexion rÃ©ussie!');
          } else {
              alert('Erreur: ' + (data.error || 'Impossible de se connecter'));
          }
      }).catch(err => {
          console.error('Login error:', err);
          alert('Erreur rÃ©seau ou serveur lors de la connexion. DÃ©tails: ' + err.message);
      });
}

async function logout() {
    if (ensurePhpServerAvailable()) {
        const targetUrl = new URL('users.php?action=logout', window.location.href).toString();
        try {
            await fetch(targetUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        } catch (err) {
            console.error('Logout error:', err);
        }
    }

    currentUser = null;
    localStorage.removeItem('currentUser');
    updateUIForLoggedOutUser();
    closeAuthModal();
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
    console.log('User logged out');
}

// ============================================
// UI UPDATE FUNCTIONS
// ============================================

function updateUIForLoggedInUser() {
    syncCurrentUserRole();

    // Hide sign in button, show logout and user menu
    const signInBtnDesktop = document.getElementById('signInBtnDesktop');
    const logoutBtnDesktop = document.getElementById('logoutBtnDesktop');
    const userMenuDesktop = document.getElementById('userMenuDesktop');
    const postJobBtnDesktop = document.getElementById('postJobBtnDesktop');
    
    if (signInBtnDesktop) signInBtnDesktop.style.display = 'none';
    if (userMenuDesktop) {
        userMenuDesktop.style.display = 'block';
        const userNameDisplay = document.getElementById('userNameDisplay');
        if (userNameDisplay) {
            userNameDisplay.textContent = currentUser.name.split(' ')[0];
        }
    }
    
    // Show Post Job button for pilotes
    if (postJobBtnDesktop) {
        if (hasPermission('postJob')) {
            postJobBtnDesktop.style.display = 'block';
        }
    }
    
    // Update protected content visibility
    updateProtectedContent();
}

function updateUIForLoggedOutUser() {
    const signInBtnDesktop = document.getElementById('signInBtnDesktop');
    const logoutBtnDesktop = document.getElementById('logoutBtnDesktop');
    const userMenuDesktop = document.getElementById('userMenuDesktop');
    const postJobBtnDesktop = document.getElementById('postJobBtnDesktop');
    
    if (signInBtnDesktop) signInBtnDesktop.style.display = 'block';
    if (logoutBtnDesktop) logoutBtnDesktop.style.display = 'none';
    if (userMenuDesktop) userMenuDesktop.style.display = 'none';
    if (postJobBtnDesktop) postJobBtnDesktop.style.display = 'none';
    
    // Hide protected content
    updateProtectedContent();
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }
}

// Close user menu when clicking outside
document.addEventListener('click', (e) => {
    const userMenu = document.getElementById('userMenuDesktop');
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && userMenu && !userMenu.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// ============================================
// PERMISSION CHECKING
// ============================================

function hasPermission(action) {
    if (!currentUser) {
        return permissionsMatrix.public[action] || false;
    }
    const currentRole = syncCurrentUserRole();
    return permissionsMatrix[currentRole]?.[action] || false;
}

function checkPermission(action) {
    if (!hasPermission(action)) {
        alert('You do not have permission to access this feature. Please log in with the appropriate role.');
        return false;
    }
    return true;
}

function updateProtectedContent() {
    const currentRole = syncCurrentUserRole();

    // Update student-only content
    const studentContent = document.querySelectorAll('.protected-student');
    studentContent.forEach(el => {
        if (currentUser && currentRole === 'student') {
            el.classList.add('visible');
        } else {
            el.classList.remove('visible');
        }
    });
    
    // Update pilote-only content
    const piloteContent = document.querySelectorAll('.protected-pilote');
    piloteContent.forEach(el => {
        if (currentUser && currentRole === 'pilote') {
            el.classList.add('visible');
        } else {
            el.classList.remove('visible');
        }
    });
    
    // Update admin-only content
    const adminContent = document.querySelectorAll('.protected-admin');
    adminContent.forEach(el => {
        if (currentUser && currentRole === 'admin') {
            el.classList.add('visible');
        } else {
            el.classList.remove('visible');
        }
    });
}

function closeTestCredsModal() {
    const modal = document.getElementById('testCredsModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ============================================
// MOBILE MENU TOGGLE
// ============================================

function toggleMenu() {
    const menu = document.getElementById("mobileMenu");
    if (menu.style.display === "flex") {
        menu.style.display = "none";
    } else {
        menu.style.display = "flex";
    }
}

// Close menu when clicking outside
document.addEventListener('click', (e) => {
    const menu = document.getElementById("mobileMenu");
    const toggle = document.querySelector(".menu-toggle");
    if (menu && toggle && !menu.contains(e.target) && !toggle.contains(e.target)) {
        menu.style.display = "none";
    }
});

// ============================================
// SEARCH FUNCTIONALITY
// ============================================

const input = document.getElementById("searchInput");
if (input) {
    input.addEventListener("keyup", (e) => {
        if (e.key === "Enter") {
            if (checkPermission('search')) {
                alert("Searching for: " + input.value);
            }
        }
    });
}

// ============================================
// FAVORITE TOGGLE
// ============================================

function toggleFavorite(btn) {
    if (!checkPermission('saveFavorites')) {
        return;
    }
    btn.classList.toggle("active");
    btn.textContent = btn.classList.contains("active") ? "â¤ï¸" : "ðŸ¤";
}

// ============================================
// FILTER DROPDOWN
// ============================================

let activeDropdown = null;
const selected = {};

function toggleDropdown(name) {
    // Close all dropdowns
    if (activeDropdown === name) {
        const dropdown = document.querySelector(`[data-filter="${name}"] .dropdown`);
        if (dropdown) dropdown.style.display = 'none';
        activeDropdown = null;
    } else {
        // Close previous dropdown
        if (activeDropdown) {
            const prevDropdown = document.querySelector(`[data-filter="${activeDropdown}"] .dropdown`);
            if (prevDropdown) prevDropdown.style.display = 'none';
        }
        // Open new dropdown
        const dropdown = document.querySelector(`[data-filter="${name}"] .dropdown`);
        if (dropdown) {
            dropdown.style.display = 'block';
            activeDropdown = name;
        }
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.filter') && activeDropdown) {
        const dropdown = document.querySelector(`[data-filter="${activeDropdown}"] .dropdown`);
        if (dropdown) dropdown.style.display = 'none';
        activeDropdown = null;
    }
});

document.querySelectorAll('.dropdown input').forEach(input => {
    input.addEventListener('change', () => {
        const category = input.closest('.filter').dataset.filter;
        selected[category] ??= [];

        if (input.checked) {
            selected[category].push(input.value);
        } else {
            selected[category] = selected[category].filter(v => v !== input.value);
        }
        renderActiveFilters();
    });
});

function renderActiveFilters() {
    const container = document.getElementById('activeFilters');
    if (!container) return;
    
    const fragment = document.createDocumentFragment();

    Object.entries(selected).forEach(([cat, values]) => {
        values.forEach(value => {
            const tag = document.createElement('div');
            tag.className = 'filter-tag';
            tag.innerHTML = `${value} <button onclick="removeFilter('${cat}','${value}')">Ã—</button>`;
            fragment.appendChild(tag);
        });
    });
    
    container.innerHTML = '';
    container.appendChild(fragment);
}

function removeFilter(cat, value) {
    selected[cat] = selected[cat].filter(v => v !== value);
    document
        .querySelector(`[data-filter="${cat}"] input[value="${value}"]`)
        .checked = false;
    renderActiveFilters();
}

function clearFilters() {
    Object.keys(selected).forEach(k => selected[k] = []);
    document.querySelectorAll('.dropdown input').forEach(i => i.checked = false);
    renderActiveFilters();
}

// ============================================
// INTERNSHIP DATA & GRID RENDERING
// ============================================

const internships = [
    {
        id: '1',
        company: 'TechVision AI',
        role: 'AI/ML Intern',
        description: 'Work on cutting-edge machine learning models that power the future of AI.',
        location: 'San Francisco',
        type: 'Full-time',
        duration: '6 months',
        salary: '$25/hr',
        featured: true,
    },
    {
        id: '2',
        company: 'CreativeStudio',
        role: 'Product Design Intern',
        description: 'Design beautiful and intuitive interfaces for millions of users.',
        location: 'Remote',
        type: 'Part-time',
        duration: '3 months',
    },
    {
        id: '3',
        company: 'DataFlow Systems',
        role: 'Data Science Intern',
        description: 'Analyze complex datasets and create actionable insights.',
        location: 'New York',
        type: 'Full-time',
        duration: '4 months',
        salary: '$22/hr',
    },
    {
        id: '4',
        company: 'CloudNine',
        role: 'Backend Engineer',
        description: 'Build scalable backend systems serving millions of requests.',
        location: 'Toronto',
        type: 'Full-time',
        duration: '6 months',
        salary: '$28/hr',
    },
    {
        id: '5',
        company: 'InnovateLabs',
        role: 'Frontend Developer',
        description: 'Create stunning web experiences with modern technologies.',
        location: 'Remote',
        type: 'Full-time',
        duration: '3 months',
    },
    {
        id: '6',
        company: 'FinTech Pro',
        role: 'Finance Analytics',
        description: 'Develop financial models and analytical dashboards.',
        location: 'London',
        type: 'Full-time',
        duration: '6 months',
        salary: '$24/hr',
    },
];

const grid = document.getElementById('internshipGrid');

if (grid) {
    const fragment = document.createDocumentFragment();
    
    internships.forEach(item => {
        const card = document.createElement('div');
        card.className = `internship-card ${item.featured ? 'featured' : ''}`;

        card.innerHTML = `
            <div class="company">${item.company}</div>
            <div class="role">${item.role}</div>
            <div class="description">${item.description}</div>

            <div class="meta">
                <span class="badge">${item.location}</span>
                <span class="badge">${item.type}</span>
                <span class="badge">${item.duration}</span>
                ${item.salary ? `<span class="badge">${item.salary}</span>` : ''}
            </div>

            <a href="#" class="apply-btn" onclick="handleApplyClick(event)">Apply Now</a>
        `;

        fragment.appendChild(card);
    });
    
    grid.appendChild(fragment);
}

function handleApplyClick(event) {
    event.preventDefault();
    if (checkPermission('applyToInternship')) {
        alert('Application submitted! We will review your profile and get back to you soon.');
    }
}
