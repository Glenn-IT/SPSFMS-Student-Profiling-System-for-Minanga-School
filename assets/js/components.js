async function loadComponent(selector, url) {
  const el = document.querySelector(selector);
  if (!el) return;
  try {
    const res = await fetch(BASE + url);
    if (res.ok) {
      el.innerHTML = await res.text();
      el.querySelectorAll('script').forEach(s => {
        const ns = document.createElement('script');
        ns.textContent = s.textContent;
        document.body.appendChild(ns);
      });
    }
  } catch (e) { console.warn('Component load failed:', url, e); }
}

/* ─────────────────────────────────────────────────────────────
   GLOBAL FULL-SCREEN LOADING OVERLAY SYSTEM
   ───────────────────────────────────────────────────────────── */
function injectLoadingOverlay() {
  if (document.getElementById('refresh-loading-overlay')) return;

  const style = document.createElement('style');
  style.id = 'loading-overlay-styles';
  style.textContent = `
    .refresh-loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(15, 23, 42, 0.7);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeInOverlay 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes fadeInOverlay {
      from { opacity: 0; transform: scale(1.02); }
      to { opacity: 1; transform: scale(1); }
    }
    .refresh-loading-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 2.5rem;
      width: 90%;
      max-width: 420px;
      text-align: center;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transform: scale(0.95);
      animation: popInCard 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes popInCard {
      to { transform: scale(1); }
    }
    .refresh-spinner-container {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }
  `;
  document.head.appendChild(style);

  const overlay = document.createElement('div');
  overlay.id = 'refresh-loading-overlay';
  overlay.className = 'refresh-loading-overlay';
  overlay.style.display = 'none';
  overlay.innerHTML = `
    <div class="refresh-loading-card">
      <div class="refresh-spinner-container">
        <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.28em;">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
      <h5 class="fw-bold mt-2 mb-1" id="refresh-loading-title" style="color:#0f172a;">Processing...</h5>
      <p class="text-muted small mb-3" id="refresh-loading-sub">Please wait while we complete your request...</p>
      <div class="progress" style="height: 5px; border-radius: 4px; overflow: hidden; background: #e9ecef;">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%;"></div>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
}

let loadingStartTime = 0;
let loadingTimeout = null;

function showLoading(title = 'Processing...', subtitle = 'Please wait while we complete your request...') {
  injectLoadingOverlay();
  if (loadingTimeout) {
    clearTimeout(loadingTimeout);
    loadingTimeout = null;
  }
  loadingStartTime = Date.now();

  const overlay = document.getElementById('refresh-loading-overlay');
  const titleEl = document.getElementById('refresh-loading-title');
  const subEl = document.getElementById('refresh-loading-sub');

  if (titleEl && title) titleEl.textContent = title;
  if (subEl && subtitle) subEl.textContent = subtitle;

  if (overlay) {
    overlay.style.display = 'flex';
  }
}

function hideLoading(minDisplayMs = 800) {
  const overlay = document.getElementById('refresh-loading-overlay');
  if (!overlay) return;

  const elapsed = Date.now() - loadingStartTime;
  const remaining = Math.max(0, minDisplayMs - elapsed);

  if (loadingTimeout) clearTimeout(loadingTimeout);

  loadingTimeout = setTimeout(() => {
    overlay.style.display = 'none';
    loadingTimeout = null;
  }, remaining);
}

function showRefreshLoading(titleMessage, subMessage) {
  showLoading(titleMessage || 'Updating...', subMessage || 'Auto-refreshing page to apply changes...');
}

function hideRefreshLoading() {
  hideLoading(0);
}

function triggerPageRefresh(successMessage, activeTabOrUrl) {
  showRefreshLoading('Updating...', 'Auto refreshing page to apply changes...');
  if (successMessage) {
    sessionStorage.setItem('pendingToast', JSON.stringify({ message: successMessage, type: 'success' }));
  }
  setTimeout(() => {
    if (activeTabOrUrl && activeTabOrUrl.includes('/')) {
      window.location.href = activeTabOrUrl;
    } else if (activeTabOrUrl) {
      window.location.href = window.location.pathname + '?tab=' + activeTabOrUrl;
    } else {
      window.location.reload();
    }
  }, 900);
}

// Auto-initialize loading overlay and intercept form submissions / action buttons
document.addEventListener('DOMContentLoaded', () => {
  injectLoadingOverlay();

  // Check for pending toast notification after refresh
  const pendingToast = sessionStorage.getItem('pendingToast');
  if (pendingToast) {
    try {
      const data = JSON.parse(pendingToast);
      showToast(data.message, data.type || 'success');
    } catch(e){}
    sessionStorage.removeItem('pendingToast');
  }

  // Intercept form submissions globally for smooth loading feedback
  document.body.addEventListener('submit', function(e) {
    const form = e.target;
    if (form && !form.hasAttribute('data-no-loading')) {
      const customTitle = form.getAttribute('data-loading-title') || 'Processing Request...';
      const customSub = form.getAttribute('data-loading-sub') || 'Please wait while your action is saved...';
      showLoading(customTitle, customSub);
    }
  });
});

function setActiveNavLink(currentPage) {
  document.querySelectorAll('.nav-link, .sidebar-link').forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('href') && link.getAttribute('href').includes(currentPage)) {
      link.classList.add('active');
    }
  });
}


function showDesktopOnlyWarning() {
  if (window.innerWidth < 1024) {
    const overlay = document.getElementById('desktop-only-overlay');
    if (overlay) overlay.style.display = 'flex';
  }
  window.addEventListener('resize', () => {
    const overlay = document.getElementById('desktop-only-overlay');
    if (!overlay) return;
    overlay.style.display = window.innerWidth < 1024 ? 'flex' : 'none';
  });
}

function showToast(message, type = 'info', duration = 3500) {
  const container = document.getElementById('toast-container') || (() => {
    const c = document.createElement('div');
    c.id = 'toast-container';
    c.className = 'toast-container';
    document.body.appendChild(c);
    return c;
  })();

  const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-circle' };
  const colors = { success: '#34a853', error: '#ea4335', info: '#1a73e8', warning: '#fbbc04' };

  const toast = document.createElement('div');
  toast.className = `toast show ${type}`;
  toast.style.cssText = `border-left: 4px solid ${colors[type]}; background:#fff; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.15); padding:.75rem 1rem; margin-bottom:.5rem; display:flex; align-items:center; gap:.6rem; min-width:280px; animation:fadeIn .2s ease;`;
  toast.innerHTML = `<i class="fas ${icons[type]}" style="color:${colors[type]};font-size:1rem;"></i><span style="flex:1;font-size:.875rem;">${message}</span><button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#9aa0a6;font-size:.9rem;">&#x2715;</button>`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), duration);
}

function confirmModal(title, message, onConfirm) {
  const existing = document.getElementById('global-confirm-modal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'global-confirm-modal';
  modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9998;display:flex;align-items:center;justify-content:center;';
  modal.innerHTML = `
    <div style="background:#fff;border-radius:12px;padding:1.5rem;max-width:380px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.2);">
      <h5 style="font-weight:700;margin-bottom:.5rem;">${title}</h5>
      <p style="color:#5f6368;font-size:.9rem;margin-bottom:1.25rem;">${message}</p>
      <div style="display:flex;gap:.5rem;justify-content:flex-end;">
        <button id="modal-cancel-btn" class="btn btn-secondary btn-sm">Cancel</button>
        <button id="modal-confirm-btn" class="btn btn-danger btn-sm">Confirm</button>
      </div>
    </div>`;
  document.body.appendChild(modal);
  document.getElementById('modal-cancel-btn').onclick = () => modal.remove();
  document.getElementById('modal-confirm-btn').onclick = () => { modal.remove(); onConfirm(); };
  modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
}

function confirmLogout(link) {
  confirmModal('Logout', 'Are you sure you want to logout?', () => {
    window.location.href = link.href;
  });
  return false;
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });
}

function calcAge(birthdate) {
  const today = new Date();
  const b = new Date(birthdate);
  let age = today.getFullYear() - b.getFullYear();
  const m = today.getMonth() - b.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < b.getDate())) age--;
  return age;
}

function populateGradeDropdown(selectEl, includeAll = false) {
  const grades = ['Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'];
  if (includeAll) selectEl.innerHTML = '<option value="">All Grade Levels</option>';
  grades.forEach(g => {
    const o = document.createElement('option');
    o.value = g; o.textContent = g;
    selectEl.appendChild(o);
  });
}

function exportTableToCSV(filename, headers, rows) {
  const esc = v => `"${String(v ?? '').replace(/"/g, '""')}"`;
  const lines = [headers.map(esc).join(','), ...rows.map(r => r.map(esc).join(','))];
  const blob = new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename.toLowerCase().endsWith('.csv') ? filename : filename + '.csv';
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

function exportTableToPDF(title, headers, rows) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  doc.setFontSize(13);
  doc.text(title, 14, 15);
  doc.autoTable({ head: [headers], body: rows, startY: 21, styles: { fontSize: 9 }, headStyles: { fillColor: [26, 115, 232] } });
  doc.save(title.replace(/[^a-z0-9]+/gi, '_') + '.pdf');
}

function populateSectionDropdown(selectEl, gradeLevel, includeAll = false) {
  const map = {
    'Grade 1':'Mabini','Grade 2':'Mabini','Grade 3':'Mabini',
    'Grade 4':'Bonifacio','Grade 5':'Bonifacio','Grade 6':'Bonifacio',
    'Grade 7':'Rizal','Grade 8':'Luna','Grade 9':'Luna','Grade 10':'Mabini',
    'Grade 11':['STEM','ABM','HUMSS'],'Grade 12':['STEM','ABM','HUMSS']
  };
  if (includeAll) selectEl.innerHTML = '<option value="">All Sections</option>';
  else selectEl.innerHTML = '<option value="">Select Section</option>';
  const s = map[gradeLevel];
  if (!s) return;
  const arr = Array.isArray(s) ? s : [s];
  arr.forEach(sec => {
    const o = document.createElement('option');
    o.value = sec; o.textContent = sec;
    selectEl.appendChild(o);
  });
}
