const MOCK_STUDENTS_DEFAULT = [
  // ── Grade 1 — Mabini ──
  { id:'s001', lrn:'100000000001', gradeLevel:'Grade 1', section:'Mabini', firstName:'Ana', middleName:'B.', lastName:'Garcia', sex:'Female', birthdate:'2018-03-12', age:7, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 1, Minanga, Cagayan de Oro', motherName:'Luz B. Garcia', fatherName:'Roberto Garcia', guardianName:'Luz B. Garcia', guardianRelation:'Mother', contact:'09171234001', email:'ana.garcia@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s002', lrn:'100000000002', gradeLevel:'Grade 1', section:'Mabini', firstName:'Carlo', middleName:'D.', lastName:'Mendoza', sex:'Male', birthdate:'2018-05-20', age:7, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 2, Minanga, Cagayan de Oro', motherName:'Elena D. Mendoza', fatherName:'Jose Mendoza', guardianName:'Elena D. Mendoza', guardianRelation:'Mother', contact:'09171234002', email:'carlo.mendoza@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s003', lrn:'100000000003', gradeLevel:'Grade 1', section:'Mabini', firstName:'Diana', middleName:'F.', lastName:'Cruz', sex:'Female', birthdate:'2018-07-08', age:7, motherTongue:'Cebuano', religion:'Iglesia ni Cristo', address:'Purok 3, Minanga, Cagayan de Oro', motherName:'Perla F. Cruz', fatherName:'Armando Cruz', guardianName:'Perla F. Cruz', guardianRelation:'Mother', contact:'09171234003', email:'diana.cruz@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s004', lrn:'100000000004', gradeLevel:'Grade 1', section:'Mabini', firstName:'Emilio', middleName:'S.', lastName:'Torres', sex:'Male', birthdate:'2018-01-15', age:7, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 4, Minanga, Cagayan de Oro', motherName:'Rosa S. Torres', fatherName:'Manuel Torres', guardianName:'Rosa S. Torres', guardianRelation:'Mother', contact:'09171234004', email:'emilio.torres@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s005', lrn:'100000000005', gradeLevel:'Grade 1', section:'Mabini', firstName:'Fatima', middleName:'L.', lastName:'Villanueva', sex:'Female', birthdate:'2018-09-22', age:7, motherTongue:'Maranao', religion:'Islam', address:'Purok 5, Minanga, Cagayan de Oro', motherName:'Aisha L. Villanueva', fatherName:'Ahmad Villanueva', guardianName:'Aisha L. Villanueva', guardianRelation:'Mother', contact:'09171234005', email:'fatima.villanueva@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },

  // ── Grade 4 — Bonifacio ──
  { id:'s006', lrn:'100000000006', gradeLevel:'Grade 4', section:'Bonifacio', firstName:'Gerald', middleName:'M.', lastName:'Pascual', sex:'Male', birthdate:'2015-04-10', age:10, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 1, Minanga, Cagayan de Oro', motherName:'Nora M. Pascual', fatherName:'Fernando Pascual', guardianName:'Nora M. Pascual', guardianRelation:'Mother', contact:'09171234006', email:'gerald.pascual@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s007', lrn:'100000000007', gradeLevel:'Grade 4', section:'Bonifacio', firstName:'Hannah', middleName:'C.', lastName:'Reyes', sex:'Female', birthdate:'2015-06-18', age:10, motherTongue:'Cebuano', religion:'Born Again', address:'Purok 2, Minanga, Cagayan de Oro', motherName:'Carmen C. Reyes', fatherName:'Eduardo Reyes', guardianName:'Carmen C. Reyes', guardianRelation:'Mother', contact:'09171234007', email:'hannah.reyes@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s008', lrn:'100000000008', gradeLevel:'Grade 4', section:'Bonifacio', firstName:'Ivan', middleName:'R.', lastName:'Lim', sex:'Male', birthdate:'2015-02-28', age:10, motherTongue:'Bisaya', religion:'Roman Catholic', address:'Purok 3, Minanga, Cagayan de Oro', motherName:'Teresita R. Lim', fatherName:'William Lim', guardianName:'Teresita R. Lim', guardianRelation:'Mother', contact:'09171234008', email:'ivan.lim@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },

  // ── Grade 7 — Rizal ──
  { id:'s009', lrn:'123456789001', gradeLevel:'Grade 7', section:'Rizal', firstName:'Juan', middleName:'P.', lastName:'Dela Cruz', sex:'Male', birthdate:'2012-08-14', age:13, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 2, Minanga, Cagayan de Oro', motherName:'Nelia P. Dela Cruz', fatherName:'Roberto Dela Cruz', guardianName:'Nelia P. Dela Cruz', guardianRelation:'Mother', contact:'09181234001', email:'juan.delacruz@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s010', lrn:'123456789002', gradeLevel:'Grade 7', section:'Rizal', firstName:'Maria', middleName:'C.', lastName:'Santos', sex:'Female', birthdate:'2012-03-25', age:13, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 4, Minanga, Cagayan de Oro', motherName:'Gloria C. Santos', fatherName:'Eduardo Santos', guardianName:'Gloria C. Santos', guardianRelation:'Mother', contact:'09181234002', email:'maria.santos@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s011', lrn:'123456789003', gradeLevel:'Grade 7', section:'Rizal', firstName:'Pedro', middleName:'A.', lastName:'Reyes', sex:'Male', birthdate:'2012-11-07', age:13, motherTongue:'Cebuano', religion:'Iglesia ni Cristo', address:'Purok 6, Minanga, Cagayan de Oro', motherName:'Caridad A. Reyes', fatherName:'Alejandro Reyes', guardianName:'Caridad A. Reyes', guardianRelation:'Mother', contact:'09181234003', email:'pedro.reyes@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s012', lrn:'123456789004', gradeLevel:'Grade 7', section:'Rizal', firstName:'Lourdes', middleName:'B.', lastName:'Fernandez', sex:'Female', birthdate:'2012-05-30', age:13, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 7, Minanga, Cagayan de Oro', motherName:'Mercy B. Fernandez', fatherName:'Carlos Fernandez', guardianName:'Mercy B. Fernandez', guardianRelation:'Mother', contact:'09181234004', email:'lourdes.fernandez@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s013', lrn:'123456789005', gradeLevel:'Grade 7', section:'Rizal', firstName:'Ramon', middleName:'E.', lastName:'Bautista', sex:'Male', birthdate:'2012-01-19', age:13, motherTongue:'Bisaya', religion:'Roman Catholic', address:'Purok 8, Minanga, Cagayan de Oro', motherName:'Josefa E. Bautista', fatherName:'Ernesto Bautista', guardianName:'Josefa E. Bautista', guardianRelation:'Mother', contact:'09181234005', email:'ramon.bautista@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },

  // ── Grade 8 — Luna ──
  { id:'s014', lrn:'123456789006', gradeLevel:'Grade 8', section:'Luna', firstName:'Sofia', middleName:'G.', lastName:'Aquino', sex:'Female', birthdate:'2011-04-22', age:14, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 1, Minanga, Cagayan de Oro', motherName:'Leticia G. Aquino', fatherName:'Ramon Aquino', guardianName:'Leticia G. Aquino', guardianRelation:'Mother', contact:'09191234001', email:'sofia.aquino@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s015', lrn:'123456789007', gradeLevel:'Grade 8', section:'Luna', firstName:'Marco', middleName:'T.', lastName:'Ramos', sex:'Male', birthdate:'2011-09-13', age:14, motherTongue:'Cebuano', religion:'Born Again', address:'Purok 2, Minanga, Cagayan de Oro', motherName:'Virginia T. Ramos', fatherName:'Dante Ramos', guardianName:'Virginia T. Ramos', guardianRelation:'Mother', contact:'09191234002', email:'marco.ramos@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s016', lrn:'123456789008', gradeLevel:'Grade 8', section:'Luna', firstName:'Cristina', middleName:'V.', lastName:'Navarro', sex:'Female', birthdate:'2011-12-05', age:14, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 3, Minanga, Cagayan de Oro', motherName:'Bella V. Navarro', fatherName:'Nestor Navarro', guardianName:'Bella V. Navarro', guardianRelation:'Mother', contact:'09191234003', email:'cristina.navarro@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },

  // ── Grade 10 — Mabini (JHS) ──
  { id:'s017', lrn:'123456789009', gradeLevel:'Grade 10', section:'Mabini', firstName:'Jerome', middleName:'O.', lastName:'Castillo', sex:'Male', birthdate:'2009-07-17', age:16, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 5, Minanga, Cagayan de Oro', motherName:'Rosario O. Castillo', fatherName:'Ignacio Castillo', guardianName:'Rosario O. Castillo', guardianRelation:'Mother', contact:'09201234001', email:'jerome.castillo@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s018', lrn:'123456789010', gradeLevel:'Grade 10', section:'Mabini', firstName:'Kathleen', middleName:'D.', lastName:'Soriano', sex:'Female', birthdate:'2009-02-14', age:16, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 6, Minanga, Cagayan de Oro', motherName:'Elsa D. Soriano', fatherName:'Andres Soriano', guardianName:'Elsa D. Soriano', guardianRelation:'Mother', contact:'09201234002', email:'kathleen.soriano@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },

  // ── Grade 11 — STEM ──
  { id:'s019', lrn:'123456789011', gradeLevel:'Grade 11', section:'STEM', firstName:'Lorenzo', middleName:'P.', lastName:'Miranda', sex:'Male', birthdate:'2008-06-30', age:17, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 1, Minanga, Cagayan de Oro', motherName:'Patricia P. Miranda', fatherName:'Luis Miranda', guardianName:'Patricia P. Miranda', guardianRelation:'Mother', contact:'09211234001', email:'lorenzo.miranda@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s020', lrn:'123456789012', gradeLevel:'Grade 11', section:'STEM', firstName:'Michelle', middleName:'R.', lastName:'Santos', sex:'Female', birthdate:'2008-10-25', age:17, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 2, Minanga, Cagayan de Oro', motherName:'Aida R. Santos', fatherName:'Victor Santos', guardianName:'Aida R. Santos', guardianRelation:'Mother', contact:'09211234002', email:'michelle.santos@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s021', lrn:'123456789013', gradeLevel:'Grade 11', section:'STEM', firstName:'Noel', middleName:'C.', lastName:'Garcia', sex:'Male', birthdate:'2008-04-08', age:17, motherTongue:'Bisaya', religion:'Roman Catholic', address:'Purok 3, Minanga, Cagayan de Oro', motherName:'Celia C. Garcia', fatherName:'Rodrigo Garcia', guardianName:'Celia C. Garcia', guardianRelation:'Mother', contact:'09211234003', email:'noel.garcia@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s022', lrn:'123456789014', gradeLevel:'Grade 11', section:'STEM', firstName:'Olivia', middleName:'M.', lastName:'Dela Torre', sex:'Female', birthdate:'2008-08-16', age:17, motherTongue:'Cebuano', religion:'Born Again', address:'Purok 4, Minanga, Cagayan de Oro', motherName:'Gina M. Dela Torre', fatherName:'Oscar Dela Torre', guardianName:'Gina M. Dela Torre', guardianRelation:'Mother', contact:'09211234004', email:'olivia.delatorre@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },

  // ── Grade 11 — ABM ──
  { id:'s023', lrn:'123456789015', gradeLevel:'Grade 11', section:'ABM', firstName:'Paolo', middleName:'N.', lastName:'Cruz', sex:'Male', birthdate:'2008-01-12', age:17, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 5, Minanga, Cagayan de Oro', motherName:'Norma N. Cruz', fatherName:'Benjamin Cruz', guardianName:'Norma N. Cruz', guardianRelation:'Mother', contact:'09211234005', email:'paolo.cruz@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },
  { id:'s024', lrn:'123456789016', gradeLevel:'Grade 11', section:'ABM', firstName:'Queenie', middleName:'S.', lastName:'Flores', sex:'Female', birthdate:'2008-03-29', age:17, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 6, Minanga, Cagayan de Oro', motherName:'Susan S. Flores', fatherName:'Danilo Flores', guardianName:'Susan S. Flores', guardianRelation:'Mother', contact:'09211234006', email:'queenie.flores@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' },

  // ── Grade 12 — HUMSS ──
  { id:'s025', lrn:'123456789017', gradeLevel:'Grade 12', section:'HUMSS', firstName:'Rafael', middleName:'J.', lastName:'Morales', sex:'Male', birthdate:'2007-05-21', age:18, motherTongue:'Cebuano', religion:'Roman Catholic', address:'Purok 7, Minanga, Cagayan de Oro', motherName:'Imee J. Morales', fatherName:'Felix Morales', guardianName:'Imee J. Morales', guardianRelation:'Mother', contact:'09221234001', email:'rafael.morales@student.minanga.edu.ph', schoolYear:'2025-2026', status:'active' }
];

function getStudents() {
  const stored = localStorage.getItem('spsmis_students');
  if (stored) return JSON.parse(stored);
  localStorage.setItem('spsmis_students', JSON.stringify(MOCK_STUDENTS_DEFAULT));
  return [...MOCK_STUDENTS_DEFAULT];
}

function saveStudents(students) {
  localStorage.setItem('spsmis_students', JSON.stringify(students));
}

function getStudentById(id) {
  return getStudents().find(s => s.id === id) || null;
}

function getStudentByLRN(lrn) {
  return getStudents().find(s => s.lrn === lrn) || null;
}

function addStudent(student) {
  const students = getStudents();
  student.id = 's' + Date.now();
  students.push(student);
  saveStudents(students);
  return student;
}

function updateStudent(id, data) {
  const students = getStudents();
  const idx = students.findIndex(s => s.id === id);
  if (idx !== -1) { students[idx] = { ...students[idx], ...data }; saveStudents(students); }
}

function getStudentsByGrade(grade) {
  return getStudents().filter(s => s.gradeLevel === grade);
}

const GRADE_LEVELS = ['Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'];
const SECTIONS = { 'Grade 1':'Mabini', 'Grade 2':'Mabini', 'Grade 3':'Mabini', 'Grade 4':'Bonifacio', 'Grade 5':'Bonifacio', 'Grade 6':'Bonifacio', 'Grade 7':'Rizal', 'Grade 8':'Luna', 'Grade 9':'Luna', 'Grade 10':'Mabini', 'Grade 11':['STEM','ABM','HUMSS'], 'Grade 12':['STEM','ABM','HUMSS'] };
const SCHOOL_YEARS = ['2025-2026','2024-2025','2023-2024'];
