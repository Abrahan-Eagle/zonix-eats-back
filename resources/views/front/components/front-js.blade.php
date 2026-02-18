<script>
    document.addEventListener('DOMContentLoaded', function () {
        // =============================================
        // LOADING OVERLAY
        // =============================================
        setTimeout(() => {
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                    document.body.classList.add('loaded');
                }, 500);
            }
        }, 800);

        // =============================================
        // NAVBAR SCROLL EFFECT
        // =============================================
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }, { passive: true });
        }

        // =============================================
        // INTERSECTION OBSERVER - Scroll Animations
        // =============================================
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -80px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = entry.target.dataset.wowDelay || '0s';
                    entry.target.style.transitionDelay = delay;
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-up, .zoom-in').forEach(el => observer.observe(el));

        // =============================================
        // FEATURE CARD HOVER EFFECT (Mouse tracking)
        // =============================================
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                card.style.background = `
                    radial-gradient(600px circle at ${x}% ${y}%, rgba(52, 211, 153, 0.08), transparent 40%),
                    rgba(255, 255, 255, 0.03)
                `;
            });
            card.addEventListener('mouseleave', () => {
                card.style.background = 'rgba(255, 255, 255, 0.03)';
            });
        });

        // =============================================
        // SMOOTH SCROLL (Navigation links)
        // =============================================
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#' || href === '#!') return;
                e.preventDefault();
                const target = document.querySelector(href);
                if (target && navbar) {
                    const navHeight = navbar.offsetHeight;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;
                    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                }
            });
        });

        // =============================================
        // PARALLAX BLOBS
        // =============================================
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    const scrolled = window.pageYOffset;
                    document.querySelectorAll('.blob').forEach((blob, index) => {
                        blob.style.transform = `translateY(${scrolled * (0.05 + index * 0.02)}px)`;
                    });
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // =============================================
        // COUNTER ANIMATION (Stats Section)
        // =============================================
        const counters = document.querySelectorAll('.stat-number[data-count-to]');
        if (counters.length) {
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const target = parseInt(el.getAttribute('data-count-to'));
                        const suffix = el.getAttribute('data-suffix') || '';
                        animateCounter(el, 0, target, 2000, suffix);
                        counterObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(c => counterObserver.observe(c));
        }

        function animateCounter(el, start, end, duration, suffix) {
            let startTs = null;
            const step = (ts) => {
                if (!startTs) startTs = ts;
                const progress = Math.min((ts - startTs) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * (end - start) + start) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }

        // =============================================
        // FAQ TOGGLE (Show More / Less)
        // =============================================
        const faqToggle = document.getElementById('faqToggleBtn');
        const faqExtra = document.getElementById('faqExtra');
        if (faqToggle && faqExtra) {
            faqToggle.addEventListener('click', () => {
                faqExtra.classList.toggle('show');
                faqToggle.classList.toggle('active');
                const isShowing = faqExtra.classList.contains('show');
                const textEl = faqToggle.querySelector('.faq-toggle-text');
                if (textEl) {
                    textEl.textContent = isShowing ? 'Ver menos preguntas' : 'Ver más preguntas';
                }
            });
        }

        // =============================================
        // EMAIL CAPTURE FORMS
        // =============================================
        document.querySelectorAll('[id^="emailForm"]').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = form.querySelector('input[type="email"]');
                const successId = form.id.replace('Form', 'Success');
                const successEl = document.getElementById(successId);

                if (input && input.value && input.checkValidity()) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Enviando...';
                    }

                    // Detectar origen del formulario
                    const source = form.id.includes('Hero') ? 'hero' : 'cta';

                    fetch('/api/waitlist', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ email: input.value, source: source })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            form.style.display = 'none';
                            if (successEl) successEl.classList.add('show');
                        }
                    })
                    .catch(() => {
                        // Si falla la red, mostrar éxito visual igualmente
                        form.style.display = 'none';
                        if (successEl) successEl.classList.add('show');
                    });
                }
            });
        });
    });
</script>
