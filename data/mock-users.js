const MOCK_USERS = [
  {
    id: 'admin-001',
    role: 'admin',
    username: 'admin',
    password: 'admin123',
    name: 'Maria L. Reyes',
    email: 'admin@minanga.edu.ph',
    position: 'School Administrator',
    avatar: null,
    status: 'active',
    securityQuestion: 'What is the name of your first pet?',
    securityAnswer: 'Bantay'
  },
  {
    id: 'teacher-001',
    role: 'teacher',
    username: 'teacher',
    password: 'teacher123',
    name: 'Ricardo G. Santos',
    email: 'rsantos@minanga.edu.ph',
    position: 'Grade 7 Adviser / Math Teacher',
    avatar: null,
    status: 'active',
    securityQuestion: 'What is your mother\'s maiden name?',
    securityAnswer: 'Dela Cruz'
  },
  {
    id: 'teacher-002',
    role: 'teacher',
    username: 'teacher2',
    password: 'teacher123',
    name: 'Josephine A. Villanueva',
    email: 'jvillanueva@minanga.edu.ph',
    position: 'Grade 1 Adviser / Filipino Teacher',
    avatar: null,
    status: 'active',
    securityQuestion: 'What city were you born in?',
    securityAnswer: 'Cagayan de Oro'
  },
  {
    id: 'student-001',
    role: 'student',
    username: 'student2025',
    password: 'student123',
    name: 'Juan P. Dela Cruz',
    email: 'juan.delacruz@student.minanga.edu.ph',
    lrn: '123456789001',
    gradeLevel: 'Grade 7',
    section: 'Rizal',
    avatar: null,
    status: 'active',
    securityQuestion: 'What is the name of your elementary school?',
    securityAnswer: 'Minanga Elementary'
  }
];

function getUserByCredentials(username, password) {
  return MOCK_USERS.find(u => u.username === username && u.password === password) || null;
}

function getUserById(id) {
  return MOCK_USERS.find(u => u.id === id) || null;
}

function getAllAccounts() {
  const stored = JSON.parse(localStorage.getItem('spsmis_accounts') || 'null');
  return stored || MOCK_USERS.map(u => ({ ...u }));
}

function saveAccounts(accounts) {
  localStorage.setItem('spsmis_accounts', JSON.stringify(accounts));
}
