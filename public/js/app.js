// Zonix Glasses — front web scripts
document.addEventListener('DOMContentLoaded', function () {
  const preloader = document.getElementById('preloader');
  if (preloader) {
    const fadeOut = () => {
      setTimeout(() => {
        preloader.style.opacity = '0';
        preloader.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
          if (preloader.parentNode) preloader.parentNode.removeChild(preloader);
        }, 500);
      }, 500);
    };
    if (document.readyState === 'complete') {
      fadeOut();
    } else {
      window.addEventListener('load', fadeOut);
      setTimeout(fadeOut, 3000);
    }
  }

  const navbar = document.querySelector('.navbar-app');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('navbar-sticky', window.scrollY > 50);
    });
  }

  const backToTopBtn = document.getElementById('backToTop');
  if (backToTopBtn) {
    window.addEventListener('scroll', () => {
      backToTopBtn.classList.toggle('show', window.scrollY > 300);
    });
    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  const cookieBanner = document.getElementById('cookieBanner');
  const acceptCookiesBtn = document.getElementById('acceptCookies');
  if (!localStorage.getItem('app_cookies_accepted') && cookieBanner) {
    setTimeout(() => cookieBanner.classList.add('show'), 1500);
  }
  if (acceptCookiesBtn) {
    acceptCookiesBtn.addEventListener('click', () => {
      localStorage.setItem('app_cookies_accepted', 'true');
      cookieBanner.classList.remove('show');
    });
  }

  const yearSpan = document.getElementById('footerYear');
  if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
  }

  const stickyBar = document.getElementById('mobileStickyCTA');
  const closeStickyBtn = document.getElementById('closeStickyBtn');
  if (stickyBar && closeStickyBtn) {
    const lastDismissed = localStorage.getItem('app_sticky_dismissed');
    const oneWeek = 7 * 24 * 60 * 60 * 1000;
    const now = Date.now();
    if (!lastDismissed || now - parseInt(lastDismissed, 10) > oneWeek) {
      stickyBar.classList.remove('d-none');
    }
    closeStickyBtn.addEventListener('click', () => {
      stickyBar.classList.add('d-none');
      localStorage.setItem('app_sticky_dismissed', String(now));
    });
  }
});
