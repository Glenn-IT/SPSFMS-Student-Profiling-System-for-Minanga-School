const BASE = '/SPSFMS-Student-Profiling-System-for-Minanga-School';

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
