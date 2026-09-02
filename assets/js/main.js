document.addEventListener('DOMContentLoaded', function () {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.getElementById('navbarMenu');
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function () {
            navMenu.classList.toggle('active');
        });
    }

    const heroDroneVideo = document.getElementById('heroDroneVideo');
    const heroVideoToggle = document.getElementById('heroVideoToggle');
    if (heroDroneVideo && heroVideoToggle) {
        heroVideoToggle.addEventListener('click', function () {
            if (heroDroneVideo.paused) {
                heroDroneVideo.play();
                heroVideoToggle.innerHTML = '<i class="bi bi-camera-video-fill"></i> <span>Live Drone View</span>';
            } else {
                heroDroneVideo.pause();
                heroVideoToggle.innerHTML = '<i class="bi bi-play-fill"></i> <span>Resume Drone</span>';
            }
        });
    }

    const alertCloseBtns = document.querySelectorAll('.alert-close');
    alertCloseBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const alert = this.closest('.alert');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                alert.style.transition = 'all 0.3s ease';
                setTimeout(function () {
                    alert.remove();
                }, 300);
            }
        });
    });

    const liveSearchInput = document.getElementById('liveEventSearch');
    const categoryFilterSelect = document.getElementById('categoryFilterSelect');
    const eventCards = document.querySelectorAll('.event-card-item');

    function filterEvents() {
        const query = liveSearchInput ? liveSearchInput.value.toLowerCase().trim() : '';
        const selectedCategory = categoryFilterSelect ? categoryFilterSelect.value : 'all';

        let visibleCount = 0;
        eventCards.forEach(function (card) {
            const title = card.getAttribute('data-title') ? card.getAttribute('data-title').toLowerCase() : '';
            const club = card.getAttribute('data-club') ? card.getAttribute('data-club').toLowerCase() : '';
            const venue = card.getAttribute('data-venue') ? card.getAttribute('data-venue').toLowerCase() : '';
            const categoryId = card.getAttribute('data-category-id') || '';

            const matchesQuery = !query || title.includes(query) || club.includes(query) || venue.includes(query);
            const matchesCategory = selectedCategory === 'all' || categoryId === selectedCategory;

            if (matchesQuery && matchesCategory) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noEventsAlert = document.getElementById('noEventsAlert');
        if (noEventsAlert) {
            noEventsAlert.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (liveSearchInput) {
        liveSearchInput.addEventListener('input', filterEvents);
    }

    if (categoryFilterSelect) {
        categoryFilterSelect.addEventListener('change', filterEvents);
    }

    const categoryPills = document.querySelectorAll('.category-pill-btn');
    categoryPills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            categoryPills.forEach(function (p) { p.classList.remove('active'); });
            this.classList.add('active');
            const catId = this.getAttribute('data-category-id');
            if (categoryFilterSelect) {
                categoryFilterSelect.value = catId;
            }
            filterEvents();
        });
    });

    const regForm = document.getElementById('studentRegisterForm');
    if (regForm) {
        regForm.addEventListener('submit', function (e) {
            const password = document.getElementById('reg_password');
            const confirmPassword = document.getElementById('reg_confirm_password');
            const passError = document.getElementById('passwordMismatchError');

            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                if (passError) {
                    passError.style.display = 'block';
                } else {
                    alert('Passwords do not match. Please re-enter.');
                }
                confirmPassword.focus();
            }
        });
    }

    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    const printBtns = document.querySelectorAll('.btn-print');
    printBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.print();
        });
    });
});
