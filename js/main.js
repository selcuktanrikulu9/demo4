/* ===================================================
   MALATYA MOBİLYA — Ana JavaScript (main.js)
   =================================================== */

document.addEventListener('DOMContentLoaded', function () {

  /* ---- NAVBAR SCROLL ---- */
  const navbar = document.getElementById('navbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
    });
  }

  /* ---- HAMBURGER / MOBİLE MENÜ ---- */
  const hamburger = document.querySelector('.hamburger');
  const navMenu = document.querySelector('.nav-menu');
  const navActions = document.querySelector('.nav-actions');

  if (hamburger) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('open');
      if (navMenu) navMenu.classList.toggle('mobile-open');
      if (navActions) navActions.classList.toggle('mobile-open');
    });
    // Dışarı tıklayınca kapat
    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !navMenu?.contains(e.target)) {
        hamburger.classList.remove('open');
        navMenu?.classList.remove('mobile-open');
        navActions?.classList.remove('mobile-open');
      }
    });
  }

  /* ---- FADE-IN ANIMASYONU ---- */
  const fadeEls = document.querySelectorAll('.fade-in');
  if (fadeEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    fadeEls.forEach(el => observer.observe(el));
  }

  /* ---- SAYAÇ ANİMASYONU (hero stats) ---- */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(el => counterObserver.observe(el));
  }

  function animateCounter(el) {
    const target = parseInt(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    const duration = 1400;
    const start = performance.now();
    const update = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target) + suffix;
      if (progress < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  }

  /* ---- FAQ ACCORDION ---- */
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const btn = item.querySelector('.faq-question');
    if (btn) {
      btn.addEventListener('click', () => {
        const isOpen = item.classList.contains('open');
        faqItems.forEach(i => i.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
      });
    }
  });

  /* ---- GALERİ FİLTRE ---- */
  const filterBtns = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      galleryItems.forEach(item => {
        if (filter === 'all' || item.dataset.cat === filter) {
          item.classList.remove('hidden');
        } else {
          item.classList.add('hidden');
        }
      });
    });
  });

  /* ---- LİGHTBOX ---- */
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  if (lightbox && lightboxImg) {
    galleryItems.forEach(item => {
      item.addEventListener('click', () => {
        const img = item.querySelector('img');
        if (img) {
          lightboxImg.src = img.src;
          lightboxImg.alt = img.alt;
          lightbox.classList.add('open');
          document.body.style.overflow = 'hidden';
        }
      });
    });
    const closeBtn = lightbox.querySelector('.lightbox-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', closeLightbox);
    }
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeLightbox();
    });
    function closeLightbox() {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  /* ---- AJAX FORM GÖNDERİMİ ---- */
  const ajaxForms = document.querySelectorAll('.ajax-form');
  ajaxForms.forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = form.querySelector('[type=submit]');
      const originalText = btn.innerHTML;
      btn.innerHTML = '⏳ Gönderiliyor...';
      btn.disabled = true;

      const formData = new FormData(form);

      try {
        const response = await fetch('mailer.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (result.success) {
          const successEl = form.closest('.form-wrap')?.querySelector('.form-success');
          if (successEl) {
            form.style.display = 'none';
            successEl.style.display = 'block';
          } else {
            showToast('Başvurunuz alındı! En kısa sürede sizi arayacağız.', 'success');
            form.reset();
          }
        } else {
          showToast(result.message || 'Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
        }
      } catch (err) {
        showToast('Bağlantı hatası. Lütfen bizi doğrudan arayın.', 'error');
      } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    });
  });

  /* ---- TOAST BİLDİRİM ---- */
  function showToast(msg, type) {
    const existing = document.querySelector('.site-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'site-toast';
    toast.style.cssText = `
      position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(10px);
      background:${type === 'success' ? '#27AE60' : '#C0392B'};color:white;
      padding:14px 24px;border-radius:10px;font-size:0.92rem;font-weight:600;
      box-shadow:0 8px 32px rgba(0,0,0,0.2);z-index:9999;transition:all 0.3s;
      max-width:90vw;text-align:center;
    `;
    toast.textContent = (type === 'success' ? '✅ ' : '❌ ') + msg;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.transform = 'translateX(-50%) translateY(0)'; toast.style.opacity = '1'; }, 50);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(-50%) translateY(10px)';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  /* ---- SMOOTH SCROLL (anchor links) ---- */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* ---- DOSYA YÜKLEYİCİ (teklif formu) ---- */
  const fileInput = document.getElementById('file-upload');
  const fileLabel = document.getElementById('file-label');
  if (fileInput && fileLabel) {
    fileInput.addEventListener('change', () => {
      const count = fileInput.files.length;
      fileLabel.textContent = count > 0 ? `${count} dosya seçildi` : '📎 Fotoğraf Ekle (opsiyonel)';
    });
  }

  /* ---- TEKLIF FORM STEP (teklif-al.html) ---- */
  const stepBtns = document.querySelectorAll('[data-step-next]');
  stepBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const nextStep = btn.dataset.stepNext;
      document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
      const next = document.getElementById('step-' + nextStep);
      if (next) next.classList.add('active');
      updateStepIndicator(parseInt(nextStep));
    });
  });

  function updateStepIndicator(current) {
    document.querySelectorAll('.step-indicator-item').forEach((item, idx) => {
      item.classList.toggle('active', idx + 1 === current);
      item.classList.toggle('done', idx + 1 < current);
    });
  }

});
