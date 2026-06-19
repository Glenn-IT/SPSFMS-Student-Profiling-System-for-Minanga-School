const SUBJECTS_JHS = ['Filipino','English','Mathematics','Science','Araling Panlipunan','Edukasyon sa Pagpapakatao','Technology and Livelihood Education','MAPEH'];
const SUBJECTS_ELEM = ['Filipino','English','Mathematics','Science','Araling Panlipunan','Edukasyon sa Pagpapakatao','MAPEH','Mother Tongue'];
const SUBJECTS_SHS_CORE = ['Oral Communication','Reading and Writing','Komunikasyon at Pananaliksik','21st Century Literature','Contemporary Philippine Arts','Media and Information Literacy','General Mathematics','Statistics and Probability','Earth and Life Science','Physical Science','Introduction to Philosophy','Physical Education and Health'];

const MOCK_GRADES_DEFAULT = {
  // Grade 7 - Rizal students
  's009': { // Juan Dela Cruz
    schoolYear: '2025-2026', gradeLevel: 'Grade 7', section: 'Rizal',
    subjects: {
      'Filipino':                    { q1:88, q2:87, q3:89, q4:90, final:89, remarks:'Passed' },
      'English':                     { q1:85, q2:86, q3:84, q4:87, final:86, remarks:'Passed' },
      'Mathematics':                 { q1:90, q2:92, q3:91, q4:93, final:92, remarks:'Passed' },
      'Science':                     { q1:87, q2:88, q3:86, q4:89, final:88, remarks:'Passed' },
      'Araling Panlipunan':         { q1:83, q2:84, q3:85, q4:86, final:85, remarks:'Passed' },
      'Edukasyon sa Pagpapakatao':  { q1:90, q2:91, q3:92, q4:91, final:91, remarks:'Passed' },
      'Technology and Livelihood Education': { q1:88, q2:87, q3:89, q4:88, final:88, remarks:'Passed' },
      'MAPEH':                       { q1:86, q2:87, q3:88, q4:87, final:87, remarks:'Passed' }
    }
  },
  's010': { // Maria Santos
    schoolYear: '2025-2026', gradeLevel: 'Grade 7', section: 'Rizal',
    subjects: {
      'Filipino':                    { q1:92, q2:93, q3:91, q4:94, final:93, remarks:'Passed' },
      'English':                     { q1:94, q2:95, q3:93, q4:96, final:95, remarks:'Passed' },
      'Mathematics':                 { q1:88, q2:89, q3:90, q4:91, final:90, remarks:'Passed' },
      'Science':                     { q1:91, q2:92, q3:90, q4:93, final:92, remarks:'Passed' },
      'Araling Panlipunan':         { q1:89, q2:90, q3:91, q4:92, final:91, remarks:'Passed' },
      'Edukasyon sa Pagpapakatao':  { q1:95, q2:94, q3:96, q4:95, final:95, remarks:'Passed' },
      'Technology and Livelihood Education': { q1:90, q2:91, q3:92, q4:91, final:91, remarks:'Passed' },
      'MAPEH':                       { q1:93, q2:92, q3:94, q4:93, final:93, remarks:'Passed' }
    }
  },
  's011': { // Pedro Reyes
    schoolYear: '2025-2026', gradeLevel: 'Grade 7', section: 'Rizal',
    subjects: {
      'Filipino':                    { q1:78, q2:79, q3:80, q4:81, final:80, remarks:'Passed' },
      'English':                     { q1:75, q2:76, q3:74, q4:77, final:76, remarks:'Passed' },
      'Mathematics':                 { q1:82, q2:83, q3:81, q4:84, final:83, remarks:'Passed' },
      'Science':                     { q1:79, q2:78, q3:80, q4:79, final:79, remarks:'Passed' },
      'Araling Panlipunan':         { q1:76, q2:77, q3:78, q4:79, final:78, remarks:'Passed' },
      'Edukasyon sa Pagpapakatao':  { q1:83, q2:84, q3:85, q4:84, final:84, remarks:'Passed' },
      'Technology and Livelihood Education': { q1:80, q2:81, q3:82, q4:81, final:81, remarks:'Passed' },
      'MAPEH':                       { q1:77, q2:78, q3:79, q4:80, final:79, remarks:'Passed' }
    }
  },
  's012': { // Lourdes Fernandez
    schoolYear: '2025-2026', gradeLevel: 'Grade 7', section: 'Rizal',
    subjects: {
      'Filipino':                    { q1:85, q2:86, q3:87, q4:88, final:87, remarks:'Passed' },
      'English':                     { q1:88, q2:89, q3:87, q4:90, final:89, remarks:'Passed' },
      'Mathematics':                 { q1:72, q2:73, q3:71, q4:74, final:73, remarks:'Passed' },
      'Science':                     { q1:83, q2:84, q3:82, q4:85, final:84, remarks:'Passed' },
      'Araling Panlipunan':         { q1:87, q2:88, q3:89, q4:90, final:89, remarks:'Passed' },
      'Edukasyon sa Pagpapakatao':  { q1:90, q2:91, q3:92, q4:91, final:91, remarks:'Passed' },
      'Technology and Livelihood Education': { q1:85, q2:86, q3:87, q4:86, final:86, remarks:'Passed' },
      'MAPEH':                       { q1:89, q2:90, q3:88, q4:91, final:90, remarks:'Passed' }
    }
  },
  's013': { // Ramon Bautista
    schoolYear: '2025-2026', gradeLevel: 'Grade 7', section: 'Rizal',
    subjects: {
      'Filipino':                    { q1:70, q2:71, q3:69, q4:72, final:71, remarks:'Passed' },
      'English':                     { q1:73, q2:72, q3:74, q4:73, final:73, remarks:'Passed' },
      'Mathematics':                 { q1:68, q2:69, q3:67, q4:70, final:69, remarks:'Passed' },
      'Science':                     { q1:72, q2:71, q3:73, q4:72, final:72, remarks:'Passed' },
      'Araling Panlipunan':         { q1:74, q2:75, q3:76, q4:75, final:75, remarks:'Passed' },
      'Edukasyon sa Pagpapakatao':  { q1:78, q2:79, q3:80, q4:79, final:79, remarks:'Passed' },
      'Technology and Livelihood Education': { q1:76, q2:77, q3:78, q4:77, final:77, remarks:'Passed' },
      'MAPEH':                       { q1:74, q2:75, q3:73, q4:76, final:75, remarks:'Passed' }
    }
  },
  // Grade 11 STEM
  's019': { // Lorenzo Miranda
    schoolYear: '2025-2026', gradeLevel: 'Grade 11', section: 'STEM',
    subjects: {
      'Oral Communication':          { q1:90, q2:91, q3:89, q4:92, final:91, remarks:'Passed' },
      'Reading and Writing':         { q1:88, q2:89, q3:87, q4:90, final:89, remarks:'Passed' },
      'Komunikasyon at Pananaliksik':{ q1:85, q2:86, q3:84, q4:87, final:86, remarks:'Passed' },
      '21st Century Literature':     { q1:87, q2:88, q3:86, q4:89, final:88, remarks:'Passed' },
      'General Mathematics':         { q1:92, q2:93, q3:94, q4:95, final:94, remarks:'Passed' },
      'Statistics and Probability':  { q1:90, q2:91, q3:92, q4:93, final:92, remarks:'Passed' },
      'Earth and Life Science':      { q1:88, q2:89, q3:87, q4:90, final:89, remarks:'Passed' },
      'Physical Science':            { q1:91, q2:92, q3:90, q4:93, final:92, remarks:'Passed' }
    }
  },
  's020': { // Michelle Santos
    schoolYear: '2025-2026', gradeLevel: 'Grade 11', section: 'STEM',
    subjects: {
      'Oral Communication':          { q1:95, q2:94, q3:96, q4:95, final:95, remarks:'Passed' },
      'Reading and Writing':         { q1:93, q2:94, q3:92, q4:95, final:94, remarks:'Passed' },
      'Komunikasyon at Pananaliksik':{ q1:91, q2:92, q3:90, q4:93, final:92, remarks:'Passed' },
      '21st Century Literature':     { q1:94, q2:95, q3:93, q4:96, final:95, remarks:'Passed' },
      'General Mathematics':         { q1:89, q2:90, q3:91, q4:92, final:91, remarks:'Passed' },
      'Statistics and Probability':  { q1:87, q2:88, q3:89, q4:90, final:89, remarks:'Passed' },
      'Earth and Life Science':      { q1:92, q2:93, q3:91, q4:94, final:93, remarks:'Passed' },
      'Physical Science':            { q1:88, q2:89, q3:87, q4:90, final:89, remarks:'Passed' }
    }
  }
};

function getGrades() {
  const stored = localStorage.getItem('spsmis_grades');
  if (stored) return JSON.parse(stored);
  localStorage.setItem('spsmis_grades', JSON.stringify(MOCK_GRADES_DEFAULT));
  return { ...MOCK_GRADES_DEFAULT };
}

function saveGrades(grades) {
  localStorage.setItem('spsmis_grades', JSON.stringify(grades));
}

function getStudentGrades(studentId) {
  const grades = getGrades();
  return grades[studentId] || null;
}

function saveStudentGrades(studentId, gradeData) {
  const grades = getGrades();
  grades[studentId] = gradeData;
  saveGrades(grades);
}

function computeFinal(q1, q2, q3, q4) {
  const avg = (Number(q1) + Number(q2) + Number(q3) + Number(q4)) / 4;
  return Math.round(avg);
}

function getRemarks(final) {
  return final >= 75 ? 'Passed' : 'Failed';
}

function getSubjectsForGrade(gradeLevel) {
  const g = parseInt(gradeLevel.replace('Grade ', ''));
  if (g <= 6) return SUBJECTS_ELEM;
  if (g <= 10) return SUBJECTS_JHS;
  return SUBJECTS_SHS_CORE;
}
