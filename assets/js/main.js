// Add smooth scrolling to all links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Navbar scroll effect
window.addEventListener('scroll', function () {
    if (window.scrollY > 50) {
        document.querySelector('.navbar').style.backgroundColor = 'rgba(255,255,255,0.95)';
    } else {
        document.querySelector('.navbar').style.backgroundColor = '#f8f9fa';
    }
});

// Close mobile menu when clicking a link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        const navbarCollapse = document.querySelector('.navbar-collapse');
        if (navbarCollapse.classList.contains('show')) {
            new bootstrap.Collapse(navbarCollapse).hide();
        }
    });
});

// Toggle landlord-specific fields based on user type selection
document.addEventListener('DOMContentLoaded', function () {
    const tenantRadio = document.getElementById('user_type_tenant');
    const landlordRadio = document.getElementById('user_type_landlord');
    const landlordFields = document.querySelector('.landlord-fields');
    const phoneInput = document.getElementById('phone');

    function toggleLandlordFields() {
        if (landlordRadio.checked) {
            landlordFields.classList.remove('d-none');
            phoneInput.required = true;
        } else {
            landlordFields.classList.add('d-none');
            phoneInput.required = false;
        }
    }

    tenantRadio.addEventListener('change', toggleLandlordFields);
    landlordRadio.addEventListener('change', toggleLandlordFields);
    // Initialize on load
    toggleLandlordFields();
});