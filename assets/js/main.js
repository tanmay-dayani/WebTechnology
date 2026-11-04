/**
 * StudentHub Portal - Core Interactivity
 * Practical 4: DOM Manipulation, Event Handling, Modal Popup,
 * Hamburger Menu, Collapsible FAQ, and Theme Switcher with localStorage
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Theme Switcher (Practical 4 & Advanced Extension)
  const themeToggleBtn = document.getElementById('theme-toggle-btn');
  const savedTheme = localStorage.getItem('studenthub_theme') || 'light';
  applyTheme(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', function () {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      applyTheme(newTheme);
      localStorage.setItem('studenthub_theme', newTheme);
    });
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    if (themeToggleBtn) {
      themeToggleBtn.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
      themeToggleBtn.setAttribute('aria-label', `Switch to ${theme === 'dark' ? 'Light' : 'Dark'} Mode`);
    }
  }

  // 2. Hamburger Mobile Menu Toggle
  const hamburgerBtn = document.getElementById('hamburger-btn');
  const navMenu = document.getElementById('nav-menu');

  if (hamburgerBtn && navMenu) {
    hamburgerBtn.addEventListener('click', function () {
      navMenu.classList.toggle('show-mobile');
      const isExpanded = navMenu.classList.contains('show-mobile');
      hamburgerBtn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    });
  }

  // 3. Notification Banner Dismissal
  const bannerCloseBtn = document.getElementById('banner-close-btn');
  const noticeBanner = document.getElementById('notice-banner');

  if (bannerCloseBtn && noticeBanner) {
    bannerCloseBtn.addEventListener('click', function () {
      noticeBanner.classList.add('dismissed');
      sessionStorage.setItem('banner_dismissed', 'true');
    });

    if (sessionStorage.getItem('banner_dismissed') === 'true') {
      noticeBanner.classList.add('dismissed');
    }
  }

  // 4. Collapsible FAQ Accordion (Practical 4)
  const accordionHeaders = document.querySelectorAll('.accordion-header');
  accordionHeaders.forEach(function (header) {
    header.addEventListener('click', function () {
      const body = this.nextElementSibling;
      const isOpen = body.classList.contains('open');

      // Optional: close other open items for single-accordion behavior
      document.querySelectorAll('.accordion-body').forEach(b => b.classList.remove('open'));

      if (!isOpen) {
        body.classList.add('open');
        this.setAttribute('aria-expanded', 'true');
      } else {
        body.classList.remove('open');
        this.setAttribute('aria-expanded', 'false');
      }
    });
  });

  // 5. Hero Content Slider / Testimonial Carousel (Practical 4)
  const slides = document.querySelectorAll('.slider-slide');
  let currentSlide = 0;

  if (slides.length > 1) {
    setInterval(function () {
      slides[currentSlide].style.display = 'none';
      currentSlide = (currentSlide + 1) % slides.length;
      slides[currentSlide].style.display = 'block';
    }, 4000);
  }
});

// 6. Global Modal Functions (Practical 4)
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Close modal if user clicks on the backdrop overlay
window.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

