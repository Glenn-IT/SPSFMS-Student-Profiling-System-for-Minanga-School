/**
 * SF9 OFFICIAL DEPED REPORT CARD RENDERER
 * Minanga Integrated School (SPSMIS) — DepEd Form 9 Component
 * Ported & adapted from DepEd Standard SF9
 */

function renderSf9ReportCard(data, options = {}) {
  const student = data.student || {};
  let subjects = data.subjects || [];

  // Normalize subjects if passed as string names with data.grades map
  if (subjects.length > 0 && typeof subjects[0] === 'string') {
    const gradesMap = data.grades || {};
    subjects = subjects.map(subjName => {
      const g = gradesMap[subjName] || {};
      return {
        name: subjName,
        t1: g.t1 ?? g.q1 ?? null,
        t2: g.t2 ?? g.q2 ?? null,
        t3: g.t3 ?? g.q3 ?? null,
        final_grade: g.final_grade ?? null,
        remarks: g.remarks ?? ''
      };
    });
  }

  const rawGradeLevel = student.grade_level || 'Grade 7';
  const gradeNum = parseInt(rawGradeLevel.replace(/\D/g, '')) || 7;
  const isElementary = gradeNum <= 6;
  const isJhs = gradeNum >= 7 && gradeNum <= 10;
  const isShs = gradeNum >= 11;

  const fullName = student.full_name 
    || `${student.last_name || ''}, ${student.first_name || ''} ${student.middle_name || ''}`.trim() 
    || '—';
  const lrn = student.lrn || '—';
  const age = student.age || (gradeNum + 6);
  const sex = student.sex || student.gender || 'Male';
  const section = student.section || '—';
  const sy = data.school_year || student.school_year || '2025-2026';

  let curriculum = 'Basic Education Curriculum (K to 12)';
  if (isElementary) curriculum = 'Elementary Education Curriculum (K to 12)';
  else if (isJhs) curriculum = 'Junior High School (K to 12 Curriculum)';
  else if (isShs) curriculum = student.track_strand || 'Senior High School (Academic / TVL Track)';

  const nextGrade = isShs && gradeNum === 12 
    ? 'Graduated / Higher Education (Tertiary)' 
    : `Grade ${gradeNum + 1}`;

  const adviser = escapeHtml(options.adviser !== undefined && options.adviser !== null && options.adviser !== ''
    ? options.adviser 
    : (data.adviser_name || student.adviser_name || 'Class Adviser'));
  const schoolHead = escapeHtml(options.schoolHead !== undefined && options.schoolHead !== null && options.schoolHead !== ''
    ? options.schoolHead 
    : (data.school_head || 'Principal / School Head'));

  const baseUrl = options.baseUrl || '/SPSFMS-Student-Profiling-System-for-Minanga-School';
  const depedLogo = `${baseUrl}/assets/img/deped_logo.png`;
  const schoolLogo = `${baseUrl}/img/MIS-Logo.jpg`;

  const selectedPeriod = parseInt(options.period ?? data.selected_term ?? 0);
  const periodBadge = selectedPeriod > 0 ? `Term ${selectedPeriod} Progress` : 'All Terms';

  const fmtG = val => (val !== null && val !== undefined && val !== '' && !isNaN(Number(val))) ? Math.round(Number(val)) : '—';

  let subjectRowsHtml = '';
  const allFinals = [];

  if (!isShs) {
    // Elementary and JHS
    subjects.forEach(sub => {
      let rawT1 = sub.t1 ?? sub.q1;
      let rawT2 = sub.t2 ?? sub.q2;
      let rawT3 = sub.t3 ?? sub.q3;

      if (selectedPeriod > 0) {
        if (selectedPeriod < 1) rawT1 = null;
        if (selectedPeriod < 2) rawT2 = null;
        if (selectedPeriod < 3) rawT3 = null;
      }

      const t1 = fmtG(rawT1);
      const t2 = fmtG(rawT2);
      const t3 = fmtG(rawT3);

      const validTerms = [t1, t2, t3].filter(v => typeof v === 'number');
      let finalG = '—';
      let remark = '—';
      let remarkColor = '#000000';

      if (validTerms.length === 3 || (selectedPeriod === 0 && validTerms.length > 0)) {
        finalG = Math.round(validTerms.reduce((a, b) => a + b, 0) / validTerms.length);
        remark = finalG >= 75 ? 'Passed' : 'Failed';
        remarkColor = remark === 'Failed' ? '#dc2626' : '#000000';
        allFinals.push(finalG);
      } else if (sub.final_grade !== null && sub.final_grade !== undefined && sub.final_grade !== '') {
        finalG = Math.round(Number(sub.final_grade));
        remark = finalG >= 75 ? 'Passed' : 'Failed';
        remarkColor = remark === 'Failed' ? '#dc2626' : '#000000';
        allFinals.push(finalG);
      }

      subjectRowsHtml += `
        <tr data-subject="${escapeHtml(sub.name)}">
          <td class="subj-name">${escapeHtml(sub.name)}</td>
          <td class="text-center sf9-grade-cell" contenteditable="false">${t1}</td>
          <td class="text-center sf9-grade-cell" contenteditable="false">${t2}</td>
          <td class="text-center sf9-grade-cell" contenteditable="false">${t3}</td>
          <td class="text-center final-cell fw-bold">${finalG}</td>
          <td class="text-center remark-cell" style="color:${remarkColor}">${remark}</td>
        </tr>
      `;

      // If MAPEH, render standard sub-components Music, Arts, PE, Health
      if (sub.name.toUpperCase().includes('MAPEH')) {
        const mapehSubs = [
          { name: 'Music', grades: [t1, t2, t3] },
          { name: 'Arts', grades: [t1, t2, t3] },
          { name: 'Physical Education (PE)', grades: [t1, t2, t3] },
          { name: 'Health', grades: [t1, t2, t3] }
        ];
        mapehSubs.forEach(m => {
          const subValid = m.grades.filter(v => typeof v === 'number');
          const subFinal = subValid.length > 0 ? Math.round(subValid.reduce((a, b) => a + b, 0) / subValid.length) : '—';
          const subRemark = subFinal !== '—' ? (subFinal >= 75 ? 'Passed' : 'Failed') : '—';
          const subRemarkColor = subRemark === 'Failed' ? '#dc2626' : '#000000';

          subjectRowsHtml += `
            <tr class="sub-row" data-subject="${m.name}">
              <td class="subj-name">${m.name}</td>
              <td class="text-center sf9-grade-cell" contenteditable="false">${m.grades[0]}</td>
              <td class="text-center sf9-grade-cell" contenteditable="false">${m.grades[1]}</td>
              <td class="text-center sf9-grade-cell" contenteditable="false">${m.grades[2]}</td>
              <td class="text-center final-cell fw-bold">${subFinal}</td>
              <td class="text-center remark-cell" style="color:${subRemarkColor}">${subRemark}</td>
            </tr>
          `;
        });
      }
    });

  } else {
    // Senior High School (Core, Applied, Specialized)
    const coreList = [];
    const appliedList = [];
    const specList = [];

    subjects.forEach((sub, idx) => {
      if (idx < 5) coreList.push(sub);
      else if (idx < 9) appliedList.push(sub);
      else specList.push(sub);
    });

    const groups = [
      { name: 'Core Subjects', list: coreList },
      { name: 'Applied Subjects', list: appliedList },
      { name: 'Specialized Subjects', list: specList }
    ];

    groups.forEach(grp => {
      if (grp.list.length > 0) {
        subjectRowsHtml += `<tr class="group-row"><td colspan="6">${grp.name}</td></tr>`;
        grp.list.forEach(sub => {
          let rawT1 = sub.t1 ?? sub.q1;
          let rawT2 = sub.t2 ?? sub.q2;
          let rawT3 = sub.t3 ?? sub.q3;

          if (selectedPeriod > 0) {
            if (selectedPeriod < 1) rawT1 = null;
            if (selectedPeriod < 2) rawT2 = null;
            if (selectedPeriod < 3) rawT3 = null;
          }

          const t1 = fmtG(rawT1);
          const t2 = fmtG(rawT2);
          const t3 = fmtG(rawT3);

          const validTerms = [t1, t2, t3].filter(v => typeof v === 'number');
          let finalG = '—';
          let remark = '—';
          let remarkColor = '#000000';

          if (validTerms.length === 3 || (selectedPeriod === 0 && validTerms.length > 0)) {
            finalG = Math.round(validTerms.reduce((a, b) => a + b, 0) / validTerms.length);
            remark = finalG >= 75 ? 'Passed' : 'Failed';
            remarkColor = remark === 'Failed' ? '#dc2626' : '#000000';
            allFinals.push(finalG);
          } else if (sub.final_grade !== null && sub.final_grade !== undefined && sub.final_grade !== '') {
            finalG = Math.round(Number(sub.final_grade));
            remark = finalG >= 75 ? 'Passed' : 'Failed';
            remarkColor = remark === 'Failed' ? '#dc2626' : '#000000';
            allFinals.push(finalG);
          }

          subjectRowsHtml += `
            <tr data-subject="${escapeHtml(sub.name)}">
              <td class="subj-name">${escapeHtml(sub.name)}</td>
              <td class="text-center sf9-grade-cell" contenteditable="false">${t1}</td>
              <td class="text-center sf9-grade-cell" contenteditable="false">${t2}</td>
              <td class="text-center sf9-grade-cell" contenteditable="false">${t3}</td>
              <td class="text-center final-cell fw-bold">${finalG}</td>
              <td class="text-center remark-cell" style="color:${remarkColor}">${remark}</td>
            </tr>
          `;
        });
      }
    });
  }

  const finalGenAvg = (allFinals.length > 0)
    ? (allFinals.reduce((a, b) => a + b, 0) / allFinals.length).toFixed(2)
    : (data.general_average ? parseFloat(data.general_average).toFixed(2) : '—');
  const finalGenRemark = (finalGenAvg !== '—')
    ? (parseFloat(finalGenAvg) >= 75 ? 'Passed' : 'Failed')
    : '—';

  return `
    <!-- SF9 Screen Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded no-print" style="background:#1e3a2f;color:#fff;">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success px-2 py-1"><i class="fas fa-file-invoice me-1"></i>Official DepEd SF9 &bull; ${periodBadge}</span>
        <span class="small text-white-50">US Letter Landscape (11" &times; 8.5") &bull; Minanga Integrated School</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-light btn-sm py-1 px-2" onclick="toggleSf9InlineEdit(this)">
          <i class="fas fa-edit me-1"></i><span>Edit Sheet</span>
        </button>
        <button class="btn btn-warning btn-sm py-1 px-3 text-dark fw-bold" onclick="printSf9Official()">
          <i class="fas fa-print me-1"></i>Print / Save SF9 PDF
        </button>
      </div>
    </div>

    <!-- SF9 Sheet Container -->
    <div class="sf9-wrapper">
      <div class="sf9-sheet" id="sf9PrintContainer">

        <!-- =======================================================
             LEFT PANEL: ACADEMIC PERFORMANCE & EVALUATION
             ======================================================= -->
        <section class="sf9-panel">
          
          <!-- Header with Official Seals -->
          <table class="sf9-header-table">
            <tr>
              <td style="width: 54px; text-align: left;">
                <img src="${depedLogo}" alt="DepEd Seal" class="sf9-logo" onerror="this.style.visibility='hidden'">
              </td>
              <td class="sf9-header-center">
                <div class="sf9-dept">Republic of the Philippines</div>
                <div class="sf9-dept">Department of Education</div>
                <div class="sf9-dept">Region II – Cagayan Valley</div>
                <div class="sf9-division">SCHOOLS DIVISION OF CAGAYAN</div>
                <div class="sf9-dept">Piat District</div>
                <div class="sf9-dept">Minanga, Piat, Cagayan</div>
                <div class="sf9-school">MINANGA INTEGRATED SCHOOL</div>
              </td>
              <td style="width: 54px; text-align: right;">
                <img src="${schoolLogo}" alt="MIS Seal" class="sf9-logo" onerror="this.style.visibility='hidden'">
              </td>
            </tr>
          </table>

          <!-- School Year -->
          <div class="sf9-sy">School Year ${sy}</div>

          <!-- Form Title -->
          <div class="sf9-title">LEARNER’S PROGRESS REPORT CARD (SF9)</div>

          <!-- Student Personal Details -->
          <div class="sf9-info-grid">
            <div class="sf9-info-item">
              <span class="sf9-info-label">Name:</span>
              <span class="sf9-info-line" contenteditable="false">${fullName.toUpperCase()}</span>
            </div>
            <div class="sf9-info-item">
              <span class="sf9-info-label">Age:</span>
              <span class="sf9-info-line text-center" contenteditable="false">${age}</span>
            </div>
            <div class="sf9-info-item">
              <span class="sf9-info-label">Sex:</span>
              <span class="sf9-info-line text-center" contenteditable="false">${sex}</span>
            </div>

            <div class="sf9-info-item">
              <span class="sf9-info-label">LRN:</span>
              <span class="sf9-info-line" contenteditable="false">${lrn}</span>
            </div>
            <div class="sf9-info-item">
              <span class="sf9-info-label">Grade:</span>
              <span class="sf9-info-line text-center" contenteditable="false">${rawGradeLevel}</span>
            </div>
            <div class="sf9-info-item">
              <span class="sf9-info-label">Section:</span>
              <span class="sf9-info-line text-center" contenteditable="false">${section}</span>
            </div>

            <div class="sf9-info-item" style="grid-column: span 3;">
              <span class="sf9-info-label">${isShs ? 'Track / Strand:' : 'Curriculum:'}</span>
              <span class="sf9-info-line" contenteditable="false">${curriculum}</span>
            </div>
          </div>

          <!-- Dear Parents Message -->
          <div class="sf9-parents-msg">
            <strong>Dear Parents:</strong>
            This Performance Report shows the ability and progress your child has made in the different learning areas as well as his/her core values. The school welcomes you should you desire to know more about your child’s progress.
          </div>

          <!-- Learning Progress Header -->
          <div class="sf9-section-title">LEARNING PROGRESS AND ACHIEVEMENT</div>

          <!-- Grades Table -->
          <table class="sf9-table" id="sf9GradesTable">
            <thead>
              <tr>
                <th rowspan="2" style="width: 48%;">Learning Areas</th>
                <th colspan="3" style="width: 26%;">TERM</th>
                <th rowspan="2" style="width: 13%;">Final Grade</th>
                <th rowspan="2" style="width: 13%;">Remarks</th>
              </tr>
              <tr>
                <th style="width: 8.6%;" class="${selectedPeriod === 1 ? 'selected-term-header' : ''}">1</th>
                <th style="width: 8.6%;" class="${selectedPeriod === 2 ? 'selected-term-header' : ''}">2</th>
                <th style="width: 8.6%;" class="${selectedPeriod === 3 ? 'selected-term-header' : ''}">3</th>
              </tr>
            </thead>
            <tbody>
              ${subjectRowsHtml || '<tr><td colspan="6" class="text-center text-muted py-3">No subjects enrolled.</td></tr>'}
              <tr class="gen-avg-row">
                <td colspan="4" class="text-right fw-bold" style="padding-right: 8px;">General Average</td>
                <td class="text-center fw-bold" id="sf9GenAvgVal">${finalGenAvg}</td>
                <td class="text-center fw-bold" id="sf9GenAvgRemark" style="color:${finalGenRemark==='Failed'?'#dc2626':'#000000'}">${finalGenRemark}</td>
              </tr>
            </tbody>
          </table>

          <!-- Performance Descriptors Table -->
          <div class="sf9-section-title">PERFORMANCE DESCRIPTORS</div>
          <table class="sf9-desc-table">
            <thead>
              <tr>
                <th style="width: 32%;">Grading Scale</th>
                <th style="width: 42%;">Description</th>
                <th style="width: 26%;">Remarks</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-center">90 – 100</td>
                <td class="text-center">Outstanding</td>
                <td class="text-center">Passed</td>
              </tr>
              <tr>
                <td class="text-center">85 – 89</td>
                <td class="text-center">Very Satisfactory</td>
                <td class="text-center">Passed</td>
              </tr>
              <tr>
                <td class="text-center">80 – 84</td>
                <td class="text-center">Satisfactory</td>
                <td class="text-center">Passed</td>
              </tr>
              <tr>
                <td class="text-center">75 – 79</td>
                <td class="text-center">Fairly Satisfactory</td>
                <td class="text-center">Passed</td>
              </tr>
              <tr>
                <td class="text-center">Below 75</td>
                <td class="text-center">Did Not Meet Expectations</td>
                <td class="text-center">Failed</td>
              </tr>
            </tbody>
          </table>

        </section>


        <!-- =======================================================
             RIGHT PANEL: ATTENDANCE, SIGNATURES & TRANSFER
             ======================================================= -->
        <section class="sf9-panel">

          <!-- Attendance Record Title -->
          <div class="sf9-section-title">ATTENDANCE RECORD</div>

          <!-- Attendance Table -->
          <table class="sf9-attendance-table">
            <thead>
              <tr>
                <th style="width: 23%;" class="text-start ps-1">Month</th>
                <th>Aug</th>
                <th>Sep</th>
                <th>Oct</th>
                <th>Nov</th>
                <th>Dec</th>
                <th>Jan</th>
                <th>Feb</th>
                <th>Mar</th>
                <th>Apr</th>
                <th>May</th>
                <th style="width: 9%;">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="row-label">No. of Class Days</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td class="fw-bold">-</td>
              </tr>
              <tr>
                <td class="row-label">No. of Days Present</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td class="fw-bold">-</td>
              </tr>
              <tr>
                <td class="row-label">No. of Days Absent</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td contenteditable="false">-</td>
                <td class="fw-bold">-</td>
              </tr>
            </tbody>
          </table>

          <!-- Parent / Guardian Signatures -->
          <div class="sf9-section-title">PARENT / GUARDIAN'S SIGNATURE</div>
          <div class="sf9-signatures-block">
            <div class="sf9-sig-row">
              <span class="sf9-sig-label">1st Term:</span>
              <div class="sf9-sig-line"></div>
            </div>
            <div class="sf9-sig-row">
              <span class="sf9-sig-label">2nd Term:</span>
              <div class="sf9-sig-line"></div>
            </div>
            <div class="sf9-sig-row">
              <span class="sf9-sig-label">3rd Term:</span>
              <div class="sf9-sig-line"></div>
            </div>
          </div>

          <!-- Certificate of Transfer -->
          <div class="sf9-section-title">CERTIFICATE OF TRANSFER</div>
          <div class="sf9-transfer-cert">
            <p>
              This is to certify that the above-named learner has satisfactorily completed the requirements for the grade level indicated.
            </p>
            <div class="sf9-transfer-field">
              <span>Admitted to Grade:</span>
              <span class="t-line" contenteditable="false">${nextGrade}</span>
            </div>
            <div class="sf9-transfer-field">
              <span>Eligible for Admission to Grade:</span>
              <span class="t-line" contenteditable="false">${nextGrade}</span>
            </div>

            <div class="sf9-transfer-signers">
              <div class="sf9-signer-box">
                <span class="text-muted small" style="font-size: 7.0pt; margin-bottom: 12px;">Approved by:</span>
                <div class="sf9-signer-name" id="sf9SchoolHeadName" contenteditable="false">${schoolHead}</div>
                <div class="sf9-signer-role">School Head / Principal</div>
              </div>
              <div class="sf9-signer-box">
                <span class="text-muted small" style="font-size: 7.0pt; margin-bottom: 12px;">Prepared by:</span>
                <div class="sf9-signer-name" id="sf9AdviserName" contenteditable="false">${adviser}</div>
                <div class="sf9-signer-role">Class Adviser</div>
              </div>
            </div>
          </div>

          <!-- Cancellation of Eligibility to Transfer -->
          <div class="sf9-section-title">CANCELLATION OF ELIGIBILITY TO TRANSFER</div>
          <div class="sf9-cancellation-cert">
            <div class="sf9-cancel-grid">
              <div class="sf9-transfer-field" style="margin-bottom: 0;">
                <span style="white-space: nowrap;">Admitted in:</span>
                <span class="t-line" contenteditable="false"></span>
              </div>
              <div class="sf9-transfer-field" style="margin-bottom: 0;">
                <span style="white-space: nowrap;">Date:</span>
                <span class="t-line" contenteditable="false"></span>
              </div>
            </div>

            <div class="d-flex justify-content-center mt-2">
              <div class="sf9-signer-box" style="width: 55%;">
                <div class="sf9-signer-name" contenteditable="false">&nbsp;</div>
                <div class="sf9-signer-role">School Head</div>
              </div>
            </div>
          </div>

        </section>

      </div>
    </div>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g,
    c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function printSf9Official() {
  document.body.classList.add('printing-sf9');
  let styleTag = document.getElementById('sf9-print-page-override');
  if (!styleTag) {
    styleTag = document.createElement('style');
    styleTag.id = 'sf9-print-page-override';
    styleTag.textContent = '@page { size: letter landscape !important; margin: 0.22in 0.28in 0.18in 0.28in !important; }';
    document.head.appendChild(styleTag);
  }
  window.print();
}

if (typeof window !== 'undefined') {
  window.addEventListener('afterprint', () => {
    document.body.classList.remove('printing-sf9');
    const styleTag = document.getElementById('sf9-print-page-override');
    if (styleTag) styleTag.remove();
  });
}

function recalculateSf9Sheet() {
  const container = document.getElementById('sf9PrintContainer');
  if (!container) return;
  const rows = container.querySelectorAll('#sf9GradesTable tbody tr[data-subject]');
  const allFinals = [];

  rows.forEach(r => {
    const isSubRow = r.classList.contains('sub-row');
    const cells = r.querySelectorAll('.sf9-grade-cell');
    const vals = [];
    cells.forEach(c => {
      const txt = c.textContent.trim();
      const num = parseFloat(txt);
      if (!isNaN(num) && txt !== '' && txt !== '—' && txt !== '-') {
        vals.push(num);
      }
    });

    const finalCell = r.querySelector('.final-cell');
    const remarkCell = r.querySelector('.remark-cell');

    if (vals.length > 0) {
      const avg = vals.reduce((a, b) => a + b, 0) / vals.length;
      const rounded = Math.round(avg);
      if (finalCell) finalCell.textContent = rounded;
      if (!isSubRow) allFinals.push(rounded);

      if (remarkCell) {
        if (rounded >= 75) {
          remarkCell.textContent = 'Passed';
          remarkCell.style.color = '#000000';
        } else {
          remarkCell.textContent = 'Failed';
          remarkCell.style.color = '#dc2626';
        }
      }
    } else {
      if (finalCell) finalCell.textContent = '—';
      if (remarkCell) {
        remarkCell.textContent = '—';
        remarkCell.style.color = '#000000';
      }
    }
  });

  const genAvgEl = container.querySelector('#sf9GenAvgVal');
  const genRemarkEl = container.querySelector('#sf9GenAvgRemark');

  if (allFinals.length > 0) {
    const genAvg = (allFinals.reduce((a, b) => a + b, 0) / allFinals.length).toFixed(2);
    if (genAvgEl) genAvgEl.textContent = genAvg;
    if (genRemarkEl) {
      if (parseFloat(genAvg) >= 75) {
        genRemarkEl.textContent = 'Passed';
        genRemarkEl.style.color = '#000000';
      } else {
        genRemarkEl.textContent = 'Failed';
        genRemarkEl.style.color = '#dc2626';
      }
    }
  } else {
    if (genAvgEl) genAvgEl.textContent = '—';
    if (genRemarkEl) {
      genRemarkEl.textContent = '—';
      genRemarkEl.style.color = '#000000';
    }
  }
}

function recalculateSf9Attendance() {
  const container = document.getElementById('sf9PrintContainer');
  if (!container) return;
  const rows = container.querySelectorAll('.sf9-attendance-table tbody tr');
  rows.forEach(r => {
    const cells = r.querySelectorAll('td:not(.row-label):not(.sf9-att-label)');
    if (cells.length > 1) {
      let sum = 0;
      let hasVal = false;
      for (let i = 0; i < cells.length - 1; i++) {
        const txt = cells[i].textContent.trim();
        const val = parseFloat(txt);
        if (!isNaN(val) && txt !== '' && txt !== '-' && txt !== '—') {
          sum += val;
          hasVal = true;
        }
      }
      cells[cells.length - 1].textContent = hasVal ? sum : '-';
    }
  });
}

function toggleSf9InlineEdit(btn) {
  const container = document.getElementById('sf9PrintContainer');
  if (!container) return;
  const isEditing = container.classList.toggle('editable-active');
  const editables = container.querySelectorAll('[contenteditable]');
  editables.forEach(el => {
    el.setAttribute('contenteditable', isEditing ? 'true' : 'false');
  });

  if (isEditing) {
    btn.classList.remove('btn-outline-light');
    btn.classList.add('btn-light');
    btn.innerHTML = '<i class="fas fa-check me-1"></i><span>Done Editing</span>';

    const table = container.querySelector('#sf9GradesTable');
    if (table && !table.dataset.boundLive) {
      table.dataset.boundLive = '1';
      table.addEventListener('input', recalculateSf9Sheet);
    }
    const attTable = container.querySelector('.sf9-attendance-table');
    if (attTable && !attTable.dataset.boundLive) {
      attTable.dataset.boundLive = '1';
      attTable.addEventListener('input', recalculateSf9Attendance);
    }
  } else {
    btn.classList.remove('btn-light');
    btn.classList.add('btn-outline-light');
    btn.innerHTML = '<i class="fas fa-edit me-1"></i><span>Edit Sheet</span>';

    const gradeCells = container.querySelectorAll('.sf9-grade-cell');
    gradeCells.forEach(c => {
      const txt = c.textContent.trim();
      if (txt === '' || txt === '-') {
        c.textContent = '—';
      }
    });

    const attCells = container.querySelectorAll('.sf9-attendance-table tbody td:not(.row-label):not(.sf9-att-label)');
    attCells.forEach(c => {
      const txt = c.textContent.trim();
      if (txt === '' || txt === '—') {
        c.textContent = '-';
      }
    });

    recalculateSf9Sheet();
    recalculateSf9Attendance();
  }
}
