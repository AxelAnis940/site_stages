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
    recruiter: {
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
        name: 'Jane Recruiter',
        email: 'recruiter@example.com',
        password: 'password123',
        role: 'recruiter',
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Load user from localStorage
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        try {
            currentUser = JSON.parse(savedUser);
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

function openAuthModal(formType = 'login') {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.style.display = 'flex';
        switchAuthForm(formType);
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function switchAuthForm(formType) {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    if (formType === 'login') {
        if (loginForm) loginForm.style.display = 'block';
        if (signupForm) signupForm.style.display = 'none';
    } else {
        if (loginForm) loginForm.style.display = 'none';
        if (signupForm) signupForm.style.display = 'block';
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
// LOGIN & SIGNUP HANDLERS
// ============================================

function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const role = document.getElementById('loginRole').value;
    
    // Find user in database
    const user = users.find(u => u.email === email && u.password === password && u.role === role);
    
    if (user) {
        // Login successful
        currentUser = {
            id: user.id,
            name: user.name,
            email: user.email,
            role: user.role,
            loginTime: new Date().toISOString(),
        };
        
        // Save to localStorage
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        
        // Reset form
        document.querySelector('#loginForm form').reset();
        
        // Update UI
        closeAuthModal();
        updateUIForLoggedInUser();
        
        console.log('Login successful:', currentUser);
    } else {
        alert('Invalid credentials. Please try again.\n\nTest credentials:\nstudent@example.com / password123\nrecruiter@example.com / password123\nadmin@example.com / password123');
    }
}

function handleSignup(event) {
    event.preventDefault();
    
    const name = document.getElementById('signupName').value;
    const email = document.getElementById('signupEmail').value;
    const password = document.getElementById('signupPassword').value;
    const role = document.getElementById('signupRole').value;
    
    // Check if user already exists
    if (users.some(u => u.email === email)) {
        alert('This email is already registered.');
        return;
    }
    
    // Create new user (for demo purposes, only adds to current session)
    const newUser = {
        id: String(users.length + 1),
        name: name,
        email: email,
        password: password,
        role: role,
    };
    
    users.push(newUser);
    
    // Auto-login the new user
    currentUser = {
        id: newUser.id,
        name: newUser.name,
        email: newUser.email,
        role: newUser.role,
        loginTime: new Date().toISOString(),
    };
    
    localStorage.setItem('currentUser', JSON.stringify(currentUser));
    
    // Reset form
    document.querySelector('#signupForm form').reset();
    
    // Update UI
    closeAuthModal();
    updateUIForLoggedInUser();
    
    console.log('Signup successful:', currentUser);
}

function logout() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    updateUIForLoggedOutUser();
    closeAuthModal();
    console.log('User logged out');
}

// ============================================
// UI UPDATE FUNCTIONS
// ============================================

function updateUIForLoggedInUser() {
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
    
    // Show Post Job button for recruiters
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
    return permissionsMatrix[currentUser.role][action] || false;
}

function checkPermission(action) {
    if (!hasPermission(action)) {
        alert('You do not have permission to access this feature. Please log in with the appropriate role.');
        return false;
    }
    return true;
}

function updateProtectedContent() {
    // Update student-only content
    const studentContent = document.querySelectorAll('.protected-student');
    studentContent.forEach(el => {
        if (currentUser && currentUser.role === 'student') {
            el.classList.add('visible');
        } else {
            el.classList.remove('visible');
        }
    });
    
    // Update recruiter-only content
    const recruiterContent = document.querySelectorAll('.protected-recruiter');
    recruiterContent.forEach(el => {
        if (currentUser && currentUser.role === 'recruiter') {
            el.classList.add('visible');
        } else {
            el.classList.remove('visible');
        }
    });
    
    // Update admin-only content
    const adminContent = document.querySelectorAll('.protected-admin');
    adminContent.forEach(el => {
        if (currentUser && currentUser.role === 'admin') {
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
    btn.textContent = btn.classList.contains("active") ? "❤️" : "🤍";
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
            tag.innerHTML = `${value} <button onclick="removeFilter('${cat}','${value}')">×</button>`;
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
