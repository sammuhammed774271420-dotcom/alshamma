// SALAM REAL ESTATE - MAIN JS

document.addEventListener('DOMContentLoaded', function () {

    // ===== HERO SLIDER =====
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    let currentSlide = 0;
    let sliderInterval;

    function goToSlide(n) {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        currentSlide = (n + slides.length) % slides.length;
        if (slides[currentSlide]) slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }

    function startSlider() {
        if (slides.length < 2) return;
        sliderInterval = setInterval(nextSlide, 5000);
    }

    function resetSlider() {
        clearInterval(sliderInterval);
        startSlider();
    }

    if (slides.length > 0) {
        goToSlide(0);
        startSlider();

        document.querySelectorAll('.slider-dot').forEach((dot, i) => {
            dot.addEventListener('click', () => { goToSlide(i); resetSlider(); });
        });

        const prevBtn = document.querySelector('.slider-arrow.prev');
        const nextBtn = document.querySelector('.slider-arrow.next');
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetSlider(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetSlider(); });

        let touchStartX = 0;
        const slider = document.querySelector('.hero-slider');
        if (slider) {
            slider.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; });
            slider.addEventListener('touchend', e => {
                const diff = touchStartX - e.changedTouches[0].screenX;
                if (Math.abs(diff) > 50) { diff > 0 ? nextSlide() : prevSlide(); resetSlider(); }
            });
        }
    }

    // ===== MOBILE MENU =====
    const menuBtn = document.getElementById('mobileMenuBtn');
    const nav = document.getElementById('mainNav');
    if (menuBtn && nav) {
        menuBtn.addEventListener('click', () => {
            nav.classList.toggle('open');
            const icon = menuBtn.querySelector('i');
            if (icon) { icon.classList.toggle('fa-bars'); icon.classList.toggle('fa-times'); }
        });
        document.addEventListener('click', (e) => {
            if (!menuBtn.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('open');
            }
        });
    }

    // ===== SCROLL TO TOP =====
    const scrollBtn = document.getElementById('scrollTop');
    if (scrollBtn) {
        window.addEventListener('scroll', () => {
            scrollBtn.classList.toggle('visible', window.scrollY > 300);
        });
        scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // ===== COUNTER ANIMATION =====
    const counters = document.querySelectorAll('.stat-number[data-target]');
    if (counters.length > 0 && window.IntersectionObserver) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target'));
                    const suffix = el.getAttribute('data-suffix') || '';
                    let start = 0;
                    const duration = 1500;
                    const step = target / (duration / 16);
                    const timer = setInterval(() => {
                        start += step;
                        if (start >= target) { start = target; clearInterval(timer); }
                        el.textContent = Math.round(start).toLocaleString() + suffix;
                    }, 16);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(c => observer.observe(c));
    }

    // ===== PROJECT FILTER =====
    const filterTabs = document.querySelectorAll('.filter-tab');
    const projectCards = document.querySelectorAll('.project-card[data-status]');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter');
            projectCards.forEach(card => {
                card.style.display = (filter === 'all' || card.getAttribute('data-status') === filter) ? '' : 'none';
            });
        });
    });

    // ===== IMAGE PREVIEW =====
    document.querySelectorAll('.img-upload-input').forEach(input => {
        input.addEventListener('change', function () {
            const preview = document.getElementById(this.dataset.preview);
            if (preview && this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // ===== SCROLL ANIMATIONS =====
    if (window.IntersectionObserver) {
        const animateEls = document.querySelectorAll('.service-card, .project-card, .team-card, .whyus-card, .vm-card, .stat-item');
        const anim = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'translateY(0)';
                    anim.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });
        animateEls.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            anim.observe(el);
        });
    }

    // ===== CONFIRM DELETE =====
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('هل أنت متأكد من الحذف؟ لا يمكن التراجع عن هذا الإجراء.')) {
                e.preventDefault();
            }
        });
    });

    // ===== CONTACT FORM =====
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function () {
            const btn = this.querySelector('[type=submit]');
            if (btn) { btn.disabled = true; btn.textContent = 'جاري الإرسال...'; }
        });
    }

    // ===== OFFERS CAROUSEL =====
    (function () {
        var track      = document.getElementById('offersTrack');
        var outer      = document.getElementById('offersTrackOuter');
        var dotsWrap   = document.getElementById('offersDots');
        var btnPrev    = document.getElementById('offersPrev');
        var btnNext    = document.getElementById('offersNext');
        if (!track || !outer) return;

        var cards       = Array.from(track.querySelectorAll('.offer-card'));
        var total       = cards.length;
        if (total === 0) return;

        // كم بطاقة تُعرض في وقت واحد
        function visibleCount() {
            var w = window.innerWidth;
            if (w >= 1024) return 3;
            if (w >= 640)  return 2;
            return 1;
        }

        var current     = 0;
        var maxIndex    = Math.max(0, total - visibleCount());
        var autoTimer   = null;
        var isDragging  = false;
        var startX      = 0;
        var scrollLeft  = 0;

        // ── بناء النقاط ────────────────────────────────────────
        function buildDots() {
            if (!dotsWrap) return;
            dotsWrap.innerHTML = '';
            maxIndex = Math.max(0, total - visibleCount());
            var count = maxIndex + 1;
            for (var i = 0; i < count; i++) {
                var d = document.createElement('button');
                d.className = 'offers-dot' + (i === current ? ' active' : '');
                d.setAttribute('aria-label', 'الانتقال إلى العرض ' + (i + 1));
                d.dataset.idx = i;
                d.addEventListener('click', function () {
                    goTo(parseInt(this.dataset.idx));
                    resetAuto();
                });
                dotsWrap.appendChild(d);
            }
        }

        // ── التحريك ────────────────────────────────────────────
        function goTo(n) {
            maxIndex = Math.max(0, total - visibleCount());
            current  = Math.max(0, Math.min(n, maxIndex));

            // احسب عرض البطاقة + الفجوة
            var cardW   = cards[0].offsetWidth;
            var gap     = 20;
            var offset  = current * (cardW + gap);
            track.style.transform = 'translateX(' + offset + 'px)';

            // نقاط
            if (dotsWrap) {
                Array.from(dotsWrap.querySelectorAll('.offers-dot')).forEach(function (d, i) {
                    d.classList.toggle('active', i === current);
                });
            }
        }

        function next() { goTo(current >= maxIndex ? 0 : current + 1); }
        function prev() { goTo(current <= 0 ? maxIndex : current - 1); }

        function startAuto() {
            if (total <= visibleCount()) return;
            autoTimer = setInterval(next, 4000);
        }
        function resetAuto() { clearInterval(autoTimer); startAuto(); }

        // ── أزرار التنقل ────────────────────────────────────────
        if (btnPrev) btnPrev.addEventListener('click', function () { prev(); resetAuto(); });
        if (btnNext) btnNext.addEventListener('click', function () { next(); resetAuto(); });

        // ── النقر على البطاقة ────────────────────────────────────
        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                if (!isDragging && this.dataset.link) {
                    window.location.href = this.dataset.link;
                }
            });
        });

        // ── السحب باللمس (موبايل) ────────────────────────────────
        outer.addEventListener('touchstart', function (e) {
            startX = e.changedTouches[0].clientX;
        }, { passive: true });
        outer.addEventListener('touchend', function (e) {
            var diff = startX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) {
                diff > 0 ? next() : prev();
                resetAuto();
            }
        }, { passive: true });

        // ── السحب بالماوس ────────────────────────────────────────
        outer.addEventListener('mousedown', function (e) {
            isDragging = false;
            startX = e.clientX;
        });
        outer.addEventListener('mousemove', function (e) {
            if (!e.buttons) return;
            if (Math.abs(e.clientX - startX) > 5) isDragging = true;
        });
        outer.addEventListener('mouseup', function (e) {
            var diff = startX - e.clientX;
            if (isDragging && Math.abs(diff) > 40) {
                diff > 0 ? next() : prev();
                resetAuto();
            }
        });

        // ── تهيئة + إعادة الحساب عند تغيير الحجم ─────────────────
        function init() {
            buildDots();
            goTo(current);
            startAuto();
        }

        window.addEventListener('resize', function () {
            buildDots();
            goTo(Math.min(current, Math.max(0, total - visibleCount())));
        });

        init();
    })();

    // ===== ADMIN: File upload label =====
    document.querySelectorAll('.upload-area').forEach(area => {
        const input = area.querySelector('input[type=file]');
        if (input) {
            area.addEventListener('click', () => input.click());
            input.addEventListener('change', function () {
                if (this.files.length > 0) {
                    area.querySelector('span') && (area.querySelector('span').textContent = this.files.length > 1 ? `تم اختيار ${this.files.length} صور` : this.files[0].name);
                }
            });
        }
    });
});
