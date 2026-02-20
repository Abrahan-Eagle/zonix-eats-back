// Zonix EATS - Scripts
console.log("Zonix EATS Loaded");

document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Preloader Logic
    const preloader = document.getElementById('preloader');
    if (preloader) {
        const fadeOut = () => {
            setTimeout(() => {
                preloader.style.opacity = '0';
                preloader.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    if(preloader.parentNode) preloader.parentNode.removeChild(preloader);
                }, 500);
            }, 1000);
        };

        if (document.readyState === 'complete') {
            fadeOut();
        } else {
            window.addEventListener('load', fadeOut);
            setTimeout(fadeOut, 3000); // Safety fallback
        }
    }

    // 2. Live Activity (Toasts) Logic
    const liveActivityContainer = document.getElementById('toastContainer'); // Renamed to avoid global conflict
    const cities = ['Caracas', 'Maracaibo', 'Valencia', 'Barquisimeto', 'Chacao', 'Lechería'];
    const foods = ['una Pizza 🍕', 'Sushi 🍣', 'Hamburguesas 🍔', 'Arepas 🫓', 'Pollo Frito 🍗', 'Postres 🍩'];
    const names = ['María', 'José', 'Carlos', 'Ana', 'Luis', 'Sofía', 'Daniel', 'Elena'];

    function showRandomToast() {
        if (!liveActivityContainer) return;

        const city = cities[Math.floor(Math.random() * cities.length)];
        const food = foods[Math.floor(Math.random() * foods.length)];
        const name = names[Math.floor(Math.random() * names.length)];
        
        const toastEl = document.createElement('div');
        toastEl.className = 'toast show fade bg-white border-0 shadow-lg rounded-xl mb-3';
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
          <div class="d-flex align-items-center p-3">
            <div class="rounded-circle bg-green-100 text-green-600 p-2 me-3">
              <span class="material-symbols-outlined fs-5">shopping_bag</span>
            </div>
            <div>
              <p class="mb-0 text-sm text-navy font-bold">${name} en ${city}</p>
              <p class="mb-0 text-xs text-slate-500">Acaba de pedir ${food}</p>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast"></button>
          </div>
        `;

        liveActivityContainer.appendChild(toastEl);

        setTimeout(() => {
            toastEl.classList.remove('show');
            setTimeout(() => toastEl.remove(), 500);
        }, 5000);
    }

    // Start loop for random toasts
    if (liveActivityContainer) {
        setTimeout(() => {
            showRandomToast();
            setInterval(() => {
                showRandomToast();
            }, Math.floor(Math.random() * (15000 - 8000 + 1) + 8000)); 
        }, 3000);
    }

    // 3. Mobile Menu Logic
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const closeMenuBtn = document.getElementById('closeMenuBtn');
    const offcanvasMenu = document.getElementById('offcanvasMenu');
    const offcanvasBackdrop = document.getElementById('offcanvasBackdrop');

    function toggleMenu() {
        if (offcanvasMenu && offcanvasBackdrop) {
            offcanvasMenu.classList.toggle('show');
            offcanvasBackdrop.classList.toggle('show');
            document.body.style.overflow = offcanvasMenu.classList.contains('show') ? 'hidden' : '';
        }
    }

    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMenu);
    if (closeMenuBtn) closeMenuBtn.addEventListener('click', toggleMenu);
    if (offcanvasBackdrop) offcanvasBackdrop.addEventListener('click', toggleMenu);

    // 4. Sticky Navbar Logic
    const navbar = document.querySelector('.navbar-zonix');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-sticky');
            } else {
                navbar.classList.remove('navbar-sticky');
            }
        });
    }

    // 5. Category Slider Logic
    const categoriesContainer = document.getElementById('categoriesContainer');
    const catBtnPrev = document.getElementById('catBtnPrev');
    const catBtnNext = document.getElementById('catBtnNext');

    if (categoriesContainer && catBtnPrev && catBtnNext) {
        catBtnNext.addEventListener('click', () => {
            categoriesContainer.scrollBy({ left: 300, behavior: 'smooth' });
        });
        catBtnPrev.addEventListener('click', () => {
            categoriesContainer.scrollBy({ left: -300, behavior: 'smooth' });
        });

        // Mouse Drag Logic
        let isDown = false;
        let startX;
        let scrollLeft;

        categoriesContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            categoriesContainer.classList.add('active');
            startX = e.pageX - categoriesContainer.offsetLeft;
            scrollLeft = categoriesContainer.scrollLeft;
        });
        categoriesContainer.addEventListener('mouseleave', () => {
            isDown = false;
            categoriesContainer.classList.remove('active');
        });
        categoriesContainer.addEventListener('mouseup', () => {
            isDown = false;
            categoriesContainer.classList.remove('active');
        });
        categoriesContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - categoriesContainer.offsetLeft;
            const walk = (x - startX) * 2;
            categoriesContainer.scrollLeft = scrollLeft - walk;
        });
    }

    // 6. Scroll Reveal Logic
    const reveals = document.querySelectorAll('.reveal');
    if (reveals.length > 0) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        reveals.forEach((element) => revealObserver.observe(element));
    }

    // 7. Back to Top Logic
    const backToTopBtn = document.getElementById("backToTop");
    if (backToTopBtn) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add("show");
            } else {
                backToTopBtn.classList.remove("show");
            }
        });

        backToTopBtn.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    // 8. Search Logic (Front Filter)
    const searchInputs = document.querySelectorAll(".nav-search-input, .form-control");
    const promoCards = document.querySelectorAll(".col-lg-zonix-3");
    
    searchInputs.forEach(input => {
        input.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase();
            if(query.length > 0) {
                promoCards.forEach(cardCol => {
                    const cardText = cardCol.textContent.toLowerCase();
                    if(cardText.includes(query)) {
                        cardCol.style.display = "";
                    } else {
                        cardCol.style.display = "none";
                    }
                });
            } else {
                promoCards.forEach(cardCol => cardCol.style.display = "");
            }
        });
    });

    // 9. Toast Notification System (Global Mock)
    const globalToastContainer = document.createElement("div");
    globalToastContainer.className = "toast-container";
    document.body.appendChild(globalToastContainer);

    window.showToast = function(message, icon = "check_circle") {
        const toast = document.createElement("div");
        toast.className = "toast-zonix";
        toast.innerHTML = `<span class="material-symbols-outlined text-green-500">${icon}</span> ${message}`;
        globalToastContainer.appendChild(toast);
        
        void toast.offsetWidth; // Trigger reflow
        toast.classList.add("show");
        
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // 10. Cookie Consent
    const cookieBanner = document.getElementById("cookieBanner");
    const acceptCookiesBtn = document.getElementById("acceptCookies");

    if (!localStorage.getItem("zonix_cookies_accepted") && cookieBanner) {
        setTimeout(() => {
            cookieBanner.classList.add("show");
        }, 2000);
    }

    if (acceptCookiesBtn) {
        acceptCookiesBtn.addEventListener("click", () => {
            localStorage.setItem("zonix_cookies_accepted", "true");
            cookieBanner.classList.remove("show");
        });
    }

    // 11. Smart App Banner Logic
    const smartBanner = document.getElementById("smartBanner");
    let lastScrollY = window.scrollY;

    if (smartBanner) {
        window.addEventListener("scroll", () => {
            if (window.innerWidth < 992) {
                const currentScrollY = window.scrollY;
                if (currentScrollY > 300 && currentScrollY < lastScrollY) {
                    smartBanner.classList.add("visible");
                } else {
                    smartBanner.classList.remove("visible");
                }
                lastScrollY = currentScrollY;
            }
        });
    }

    // 12. Dynamic Year
    const yearSpan = document.getElementById("footerYear");
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // 13. Sticky Download Bar Logic
    const stickyBar = document.getElementById("mobileStickyCTA");
    const closeStickyBtn = document.getElementById("closeStickyBtn");

    if (stickyBar && closeStickyBtn) {
        const lastDismissed = localStorage.getItem("zonix_sticky_dismissed");
        const oneWeek = 7 * 24 * 60 * 60 * 1000;
        const now = new Date().getTime();

        if (!lastDismissed || (now - parseInt(lastDismissed) > oneWeek)) {
            stickyBar.classList.remove("d-none");
        }

        closeStickyBtn.addEventListener("click", () => {
            stickyBar.classList.add("d-none");
            localStorage.setItem("zonix_sticky_dismissed", new Date().getTime().toString());
        });
    }
});

