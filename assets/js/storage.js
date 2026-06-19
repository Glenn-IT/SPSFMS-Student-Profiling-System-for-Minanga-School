const SESSION_KEY = 'spsmis_session';

function setSession(user) {
  localStorage.setItem(SESSION_KEY, JSON.stringify({
    id: user.id,
    role: user.role,
    name: user.name,
    username: user.username,
    position: user.position || '',
    lrn: user.lrn || null,
    gradeLevel: user.gradeLevel || null,
    section: user.section || null
  }));
}

function getSession() {
  const s = localStorage.getItem(SESSION_KEY);
  return s ? JSON.parse(s) : null;
}

function clearSession() {
  localStorage.removeItem(SESSION_KEY);
}

function requireAuth(expectedRole) {
  const session = getSession();
  if (!session) { window.location.href = '/SPSFMS-Student-Profiling-System-for-Minanga-School/views/auth/login.html'; return null; }
  if (expectedRole && session.role !== expectedRole) {
    redirectByRole(session.role);
    return null;
  }
  return session;
}

function redirectByRole(role) {
  const base = '/SPSFMS-Student-Profiling-System-for-Minanga-School';
  if (role === 'admin') window.location.href = base + '/views/admin/dashboard.html';
  else if (role === 'teacher') window.location.href = base + '/views/teacher/dashboard.html';
  else if (role === 'student') window.location.href = base + '/views/student/dashboard.html';
}

function logout() {
  clearSession();
  window.location.href = '/SPSFMS-Student-Profiling-System-for-Minanga-School/views/auth/login.html';
}
