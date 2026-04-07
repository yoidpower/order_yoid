/* ============================================================
   YOID Power — main.js
   Handles: Navbar scroll, mobile menu, Swiper carousel,
            scroll animations, contact form (PHPMailer via send.php)
   ============================================================ */

(function () {
  'use strict';

  // ── Hero canvas: scale 1920px reference to viewport width ───
  function scaleHeroCanvas() {
    const canvas = document.querySelector('.hero__canvas');
    const hero   = document.querySelector('.hero');
    if (!canvas || !hero) return;
    const scale = window.innerWidth / 1920;
    canvas.style.transform = 'scale(' + scale + ')';
    hero.style.height = Math.round(960 * scale) + 'px';
  }
  scaleHeroCanvas();
  window.addEventListener('resize', scaleHeroCanvas, { passive: true });

  // ── Navbar: scroll shadow + active state ─────────────────────
  const nav = document.getElementById('mainNav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });
  }

  // ── Mobile menu toggle ────────────────────────────────────────
  const toggle   = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');

  if (toggle && navLinks) {
    toggle.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open);
    });

    // Close menu when a link is clicked
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!nav.contains(e.target)) {
        navLinks.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // ── Swiper: Collection carousel ───────────────────────────────
  if (typeof Swiper !== 'undefined') {
    new Swiper('.collection__swiper', {
      slidesPerView:  'auto',
      spaceBetween:   20,
      grabCursor:     true,
      freeMode:       true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      keyboard:       { enabled: true },
      mousewheel:     { forceToAxis: true },
      breakpoints: {
        0:   { spaceBetween: 12 },
        576: { spaceBetween: 16 },
        992: { spaceBetween: 20 },
      }
    });
  }

  // ── Scroll animations (lightweight AOS-style) ────────────────
  const animatedEls = document.querySelectorAll('[data-aos]');

  if (animatedEls.length) {
    const delayMap = { '100': 100, '200': 200, '300': 300 };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const delay = parseInt(entry.target.dataset.aosDelay || '0', 10);
        setTimeout(() => {
          entry.target.classList.add('aos-animate');
        }, delay);
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    animatedEls.forEach(el => observer.observe(el));
  }

  // ── Contact Form ─────────────────────────────────────────────
  const form      = document.getElementById('contact-form');
  const msgBox    = document.getElementById('form-message');
  const submitBtn = document.getElementById('submit-btn');

  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      // Clear previous state
      hideMessage();
      clearErrors();

      // Client-side validation
      if (!validateForm()) return;

      // Show loading state
      setLoading(true);

      try {
        const response = await fetch('/send.php', {
          method:  'POST',
          body:    new FormData(form),
        });

        const data = await response.json();

        if (data.ok) {
          showMessage('success',
            '✓ Thank you! Your request has been sent. We\'ll be in touch shortly.');
          form.reset();
          // Scroll to message
          msgBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
          showMessage('error',
            data.msg || 'Something went wrong. Please try again or email us at orders@yoidpower.com');
        }
      } catch (err) {
        showMessage('error',
          'Network error. Please check your connection and try again.');
      } finally {
        setLoading(false);
      }
    });
  }

  // ── Form helpers ──────────────────────────────────────────────
  function validateForm() {
    let valid = true;
    const required = ['business_name', 'location_type', 'city_region', 'contact_email'];

    required.forEach(name => {
      const field = form.elements[name];
      if (!field) return;
      if (!field.value.trim()) {
        markInvalid(field);
        valid = false;
      }
    });

    // Email format
    const emailField = form.elements['contact_email'];
    if (emailField && emailField.value.trim()) {
      const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRx.test(emailField.value.trim())) {
        markInvalid(emailField);
        valid = false;
      }
    }

    return valid;
  }

  function markInvalid(field) {
    field.classList.add('is-invalid');
    field.addEventListener('input', () => field.classList.remove('is-invalid'), { once: true });
  }

  function clearErrors() {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  }

  function setLoading(on) {
    if (!submitBtn) return;
    const text   = submitBtn.querySelector('.btn__text');
    const loader = submitBtn.querySelector('.btn__loader');
    submitBtn.disabled = on;
    if (text)   text.style.display   = on ? 'none' : '';
    if (loader) loader.style.display = on ? 'inline-flex' : 'none';
  }

  function showMessage(type, text) {
    if (!msgBox) return;
    msgBox.className = 'form__msg ' + type;
    msgBox.textContent = text;
    msgBox.style.display = 'block';
  }

  function hideMessage() {
    if (!msgBox) return;
    msgBox.style.display = 'none';
    msgBox.className = 'form__msg';
  }

  // ── Smooth active nav link highlighting ─────────────────────
  const sections = document.querySelectorAll('section[id], nav[id]');
  const navAnchors = document.querySelectorAll('.nav-links a');

  if (sections.length && navAnchors.length) {
    const sectionObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.getAttribute('id');
          navAnchors.forEach(a => {
            a.classList.toggle('active', a.getAttribute('href') === '#' + id);
          });
        }
      });
    }, { threshold: 0.4 });

    sections.forEach(s => sectionObserver.observe(s));
  }

})();
