// Shared auth helpers loaded on every portal page

function initAdminPage(pageKey) {
  const session = requireAuth('admin');
  if (!session) return null;
  showDesktopOnlyWarning();
  setUserDisplay(session);
  highlightNav(pageKey);
  return session;
}

function initTeacherPage(pageKey) {
  const session = requireAuth('teacher');
  if (!session) return null;
  showDesktopOnlyWarning();
  setUserDisplay(session);
  highlightNav(pageKey);
  return session;
}

function setUserDisplay(session) {
  const nameEl = document.getElementById('user-display-name');
  const roleEl = document.getElementById('user-display-role');
  const avatarEl = document.getElementById('user-avatar-text');
  if (nameEl) nameEl.textContent = session.name;
  if (roleEl) roleEl.textContent = session.position || session.role.charAt(0).toUpperCase() + session.role.slice(1);
  if (avatarEl) avatarEl.textContent = session.name.charAt(0).toUpperCase();
}

function highlightNav(pageKey) {
  document.querySelectorAll('.sidebar-link[data-page]').forEach(link => {
    link.classList.toggle('active', link.dataset.page === pageKey);
  });
}

function handleLogout(e) {
  if (e) e.preventDefault();
  confirmModal('Confirm Logout', 'Are you sure you want to logout?', () => logout());
}
