// Zonix EATS - Scripts
console.log("Zonix EATS Loaded");

// Initialize any tooltips or popovers if needed (Optional for now)
document.addEventListener("DOMContentLoaded", function () {
  
  // Preloader Logic
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
          // Safety fallback: Force remove after 3s if load event hangs
          setTimeout(fadeOut, 3000);
      }
  }

  // Live Activity (Toasts) Logic
  const toastContainer = document.getElementById('toastContainer');
  const cities = ['Caracas', 'Maracaibo', 'Valencia', 'Barquisimeto', 'Chacao', 'Lechería'];
  const foods = ['una Pizza 🍕', 'Sushi 🍣', 'Hamburguesas 🍔', 'Arepas 🫓', 'Pollo Frito 🍗', 'Postres 🍩'];
  const names = ['María', 'José', 'Carlos', 'Ana', 'Luis', 'Sofía', 'Daniel', 'Elena'];

  function showRandomToast() {
    if (!toastContainer) return;

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

    toastContainer.appendChild(toastEl);

    // Auto remove after 5s
    setTimeout(() => {
        toastEl.classList.remove('show');
        setTimeout(() => toastEl.remove(), 500);
    }, 5000);
  }

  // Start loop after 3s, then random interval 8-15s
  setTimeout(() => {
      showRandomToast();
      setInterval(() => {
          showRandomToast();
      }, Math.floor(Math.random() * (15000 - 8000 + 1) + 8000)); 
  }, 3000);

  // Skeleton Loader Logic (Legacy support if element exists)
  const skeleton = document.getElementById('skeleton-loader');

  // Mobile Menu Logic
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const closeMenuBtn = document.getElementById('closeMenuBtn');
  const offcanvasMenu = document.getElementById('offcanvasMenu');
  const offcanvasBackdrop = document.getElementById('offcanvasBackdrop');

  function toggleMenu() {
      offcanvasMenu.classList.toggle('show');
      offcanvasBackdrop.classList.toggle('show');
      document.body.style.overflow = offcanvasMenu.classList.contains('show') ? 'hidden' : '';
  }

  if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMenu);
  if (closeMenuBtn) closeMenuBtn.addEventListener('click', toggleMenu);
  if (offcanvasBackdrop) offcanvasBackdrop.addEventListener('click', toggleMenu);

  // Sticky Navbar Logic
  const navbar = document.querySelector('.navbar-zonix');
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      navbar.classList.add('navbar-sticky');
    } else {
      navbar.classList.remove('navbar-sticky');
    }
  });

  // Category Slider Logic
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

      // --- MOUSE DRAG & TOUCH SWIPE LOGIC ---
      let isDown = false;
      let startX;
      let scrollLeft;

      categoriesContainer.addEventListener('mousedown', (e) => {
        isDown = true;
        categoriesContainer.classList.add('active'); // Add cursor: grabbing if in CSS
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
        const walk = (x - startX) * 2; // Scroll-fast multiplier
        categoriesContainer.scrollLeft = scrollLeft - walk;
      });
      
      // Touch support is native with overflow-x: auto, but this helps snap
  }

  // Scroll Reveal Logic
  const reveals = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
          if (entry.isIntersecting) {
              entry.target.classList.add('active');
              observer.unobserve(entry.target);
          }
      });
  }, { threshold: 0.1 });

  reveals.forEach((element) => observer.observe(element));
});

  // Back to Top Logic
  const backToTopBtn = document.getElementById("backToTop");
  
  if(backToTopBtn) {
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

  // Native Lazy Loading (Polyfill support if needed, but modern browsers handle loading="lazy")
  const images = document.querySelectorAll("img");
  images.forEach(img => {
      img.setAttribute("loading", "lazy");
  });


  // --- v6 Logic ---

  // 1. Search Logic
  const searchInputs = document.querySelectorAll(".nav-search-input, .form-control");
  const promoCards = document.querySelectorAll(".col-lg-zonix-3"); // Selecting the column wrapper to hide
  
  searchInputs.forEach(input => {
      input.addEventListener("input", (e) => {
          const query = e.target.value.toLowerCase();
          
          if(query.length > 0) {
              // Scroll to promotions if typing
              const promoSection = document.querySelector(".py-5.bg-light");
              if(promoSection && window.scrollY < 500) {
                 // Optional: promoSection.scrollIntoView({behavior: "smooth"}); 
              }

              promoCards.forEach(cardCol => {
                  const cardText = cardCol.textContent.toLowerCase();
                  if(cardText.includes(query)) {
                      cardCol.style.display = "";
                      cardCol.classList.add("animate-pulse"); // Visual feedback
                      setTimeout(() => cardCol.classList.remove("animate-pulse"), 500);
                  } else {
                      cardCol.style.display = "none";
                  }
              });
          } else {
              promoCards.forEach(cardCol => cardCol.style.display = "");
          }
      });
  });

  // 2. Toast Notification System
  const toastContainer = document.createElement("div");
  toastContainer.className = "toast-container";
  document.body.appendChild(toastContainer);

  window.showToast = function(message, icon = "check_circle") {
      const toast = document.createElement("div");
      toast.className = "toast-zonix";
      toast.innerHTML = `<span class="material-symbols-outlined text-green-500">${icon}</span> ${message}`;
      
      toastContainer.appendChild(toast);
      
      // Trigger reflow
      void toast.offsetWidth;
      
      toast.classList.add("show");
      
      setTimeout(() => {
          toast.classList.remove("show");
          setTimeout(() => toast.remove(), 300);
      }, 3000);
  };

  // Attach Toasts to Buttons
  const actionButtons = document.querySelectorAll(".btn-zonix-primary, .card-promo, .btn-zonix-glass, .app-badge");
  actionButtons.forEach(btn => {
      btn.addEventListener("click", (e) => {
          // Prevent default if it is a link with #
          const href = btn.getAttribute("href");
          
          let msg = "Acción realizada correctamente";
          const text = btn.textContent.toLowerCase();

          if(text.includes("descargar") || text.includes("instalar") || text.includes("app") || btn.closest('.app-badge')) {
               msg = "Redirigiendo a App Store... 📲";
          } else if(text.includes("registrar") || text.includes("repartidor") || text.includes("vende")) {
               msg = "Abriendo formulario de registro... 📝";
          } else if(text.includes("video")) {
               msg = "Reproduciendo demo de la App... ▶️";
          }

          if(!href || href === "#") {
              e.preventDefault();
              window.showToast(msg);
          }
      });
  });

  // 3. Cookie Consent
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


  // Smart App Banner Logic
  const smartBanner = document.getElementById("smartBanner");
  let lastScrollY = window.scrollY;

  if(smartBanner) {
      window.addEventListener("scroll", () => {
          // Only show on mobile/tablet widths
          if(window.innerWidth < 992) {
              const currentScrollY = window.scrollY;
              
              // Show if scrolled down more than 300px and scrolling UP
              if (currentScrollY > 300 && currentScrollY < lastScrollY) {
                  smartBanner.classList.add("visible");
              } else {
                  smartBanner.classList.remove("visible");
              }
              lastScrollY = currentScrollY;
          }
      });
  }

  // Dynamic Year
  const yearSpan = document.getElementById("footerYear");
  if(yearSpan) {
      yearSpan.textContent = new Date().getFullYear();
  }


// Sticky Download Bar Logic (1 Week Dismissal)
document.addEventListener("DOMContentLoaded", function () {
    const stickyBar = document.getElementById("mobileStickyCTA");
    const closeStickyBtn = document.getElementById("closeStickyBtn");

    if (stickyBar && closeStickyBtn) {
        const lastDismissed = localStorage.getItem("zonix_sticky_dismissed");
        const oneWeek = 7 * 24 * 60 * 60 * 1000; // 7 days in ms
        const now = new Date().getTime();

        // Check if should show
        if (!lastDismissed || (now - parseInt(lastDismissed) > oneWeek)) {
            // Remove d-none to show it
            stickyBar.classList.remove("d-none");
        }

        // Handle Close
        closeStickyBtn.addEventListener("click", () => {
            stickyBar.classList.add("d-none");
            localStorage.setItem("zonix_sticky_dismissed", new Date().getTime().toString());
        });
    }
});

