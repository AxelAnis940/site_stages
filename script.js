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
            alert("Searching for: " + input.value);
        }
    });
}

// ============================================
// FAVORITE TOGGLE
// ============================================

function toggleFavorite(btn) {
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

            <a href="#" class="apply-btn">Apply Now</a>
        `;

        fragment.appendChild(card);
    });
    
    grid.appendChild(fragment);
}
