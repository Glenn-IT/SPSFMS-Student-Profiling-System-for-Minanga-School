/**
 * SF9 OFFICIAL DEPED REPORT CARD RENDERER
 * Minanga Integrated School (SPSMIS) — DepEd Form 9 Component
 * Replicated exactly from official DepEd SF9-TVL-ICT12 layout
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

  const rawGradeLevel = student.grade_level || 'Grade 12';
  const gradeNum = parseInt(rawGradeLevel.replace(/\D/g, '')) || 12;
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
  const sy = data.school_year || student.school_year || '2026-2027';

  let trackStrand = student.track_strand || '';
  if (!trackStrand) {
    if (isShs) {
      trackStrand = 'Technical-Vocational-Livelihood (TVL) - ICT';
    } else if (isJhs) {
      trackStrand = 'Junior High School (K to 12 Curriculum)';
    } else {
      trackStrand = 'Elementary Education Curriculum (K to 12)';
    }
  }

  const nextGrade = isShs && gradeNum === 12 
    ? 'Graduated / Higher Education (Tertiary)' 
    : `Grade ${gradeNum + 1}`;

  const adviser = escapeHtml(options.adviser !== undefined && options.adviser !== null && options.adviser !== ''
    ? options.adviser 
    : (data.adviser_name || student.adviser_name || 'JOSEPH M. BATUYONG'));
  const schoolHead = escapeHtml(options.schoolHead !== undefined && options.schoolHead !== null && options.schoolHead !== ''
    ? options.schoolHead 
    : (data.school_head || 'School Head'));

  const baseUrl = options.baseUrl || '/SPSFMS-Student-Profiling-System-for-Minanga-School';
  const depedLogo = `${baseUrl}/assets/img/deped_logo.png`;
  const schoolLogo = `${baseUrl}/assets/img/MIS-Logo.jpg`;

  const selectedPeriod = parseInt(options.period ?? data.selected_term ?? 0);
  const periodBadge = selectedPeriod > 0 ? `Term ${selectedPeriod} Progress` : 'All Terms';

  const fmtG = val => (val !== null && val !== undefined && val !== '' && !isNaN(Number(val))) ? Math.round(Number(val)) : '—';

  let subjectRowsHtml = '';
  const allFinals = [];

  if (isShs) {
    // Senior High School: Categorize into Core, Applied, and Specialized
    const coreList = [];
    const appliedList = [];
    const specList = [];

    subjects.forEach((sub, idx) => {
      const sName = (sub.name || '').toLowerCase();
      if (sName.includes('filipino sa piling') || sName.includes('practical research') || sName.includes('entrepreneurship') || sName.includes('inquiries')) {
        appliedList.push(sub);
      } else if (sName.includes('computer systems') || sName.includes('work immersion') || sName.includes('empowerment') || sName.includes('programming')) {
        specList.push(sub);
      } else if (idx < 5 && appliedList.length === 0 && specList.length === 0) {
        coreList.push(sub);
      } else if (idx < 9 && specList.length === 0) {
        appliedList.push(sub);
      } else {
        specList.push(sub);
      }
    });

    const groups = [
      { name: 'Core Subjects', list: coreList },
      { name: 'Applied Subjects', list: appliedList },
      { name: 'Specialized Subjects', list: specList }
    ];

    groups.forEach(grp => {
      if (grp.list.length > 0) {
        subjectRowsHtml += `
          <tr>
            <td colspan="6" class="sf9-group-hdr">${grp.name}</td>
          </tr>
        `;
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
            <tr class="sf9-subject-row" data-subject="${escapeHtml(sub.name)}">
              <td class="sf9-subj-name editable-field" contenteditable="false">${escapeHtml(sub.name)}</td>
              <td class="text-center grade-cell grade-t1 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${t1}</td>
              <td class="text-center grade-cell grade-t2 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${t2}</td>
              <td class="text-center grade-cell grade-t3 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${t3}</td>
              <td class="text-center grade-final fw-bold">${finalG}</td>
              <td class="text-center grade-remarks fw-bold" style="color:${remarkColor}">${remark}</td>
            </tr>
          `;
        });
      }
    });

  } else {
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
        <tr class="sf9-subject-row" data-subject="${escapeHtml(sub.name)}">
          <td class="sf9-subj-name editable-field" contenteditable="false">${escapeHtml(sub.name)}</td>
          <td class="text-center grade-cell grade-t1 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${t1}</td>
          <td class="text-center grade-cell grade-t2 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${t2}</td>
          <td class="text-center grade-cell grade-t3 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${t3}</td>
          <td class="text-center grade-final fw-bold">${finalG}</td>
          <td class="text-center grade-remarks fw-bold" style="color:${remarkColor}">${remark}</td>
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
            <tr class="sub-row sf9-subject-row" data-subject="${m.name}">
              <td class="sf9-subj-name ps-4 editable-field" contenteditable="false">${m.name}</td>
              <td class="text-center grade-cell grade-t1 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${m.grades[0]}</td>
              <td class="text-center grade-cell grade-t2 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${m.grades[1]}</td>
              <td class="text-center grade-cell grade-t3 editable-field" contenteditable="false" oninput="recalcSf9Row(this)">${m.grades[2]}</td>
              <td class="text-center grade-final fw-bold">${subFinal}</td>
              <td class="text-center grade-remarks fw-bold" style="color:${subRemarkColor}">${subRemark}</td>
            </tr>
          `;
        });
      }
    });
  }

  const finalGenAvg = (allFinals.length > 0)
    ? Math.round(allFinals.reduce((a, b) => a + b, 0) / allFinals.length)
    : (data.general_average ? Math.round(parseFloat(data.general_average)) : '—');
  const finalGenRemark = (finalGenAvg !== '—')
    ? (Number(finalGenAvg) >= 75 ? 'Passed' : 'Failed')
    : '—';

  return `
    <!-- SF9 Screen Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded no-print" style="background:#0f172a;color:#fff;">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success px-2 py-1"><i class="fas fa-file-invoice me-1"></i>Official DepEd SF9 &bull; ${periodBadge}</span>
        <span class="small text-white-50">US Letter Landscape (11" &times; 8.5") &bull; Minanga Integrated School</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-light btn-sm py-1 px-2" id="sf9EditBtn" onclick="toggleSf9InlineEdit(this)">
          <i class="fas fa-edit me-1"></i><span>Edit Sheet</span>
        </button>
        <button class="btn btn-success btn-sm py-1 px-3 fw-bold text-white shadow-sm" onclick="printSf9Official()">
          <i class="fas fa-print me-1"></i>Print SF9 (Landscape)
        </button>
      </div>
    </div>

    <!-- SF9 Sheet Container -->
    <div class="sf9-wrapper">
      <div class="sf9-sheet" id="sf9PrintContainer">

        <!-- =======================================================
             LEFT PANEL: ACADEMIC PERFORMANCE & EVALUATION
             ======================================================= -->
        <div class="sf9-panel">
          
          <!-- Header with Official Seals -->
          <div class="sf9-header-layout">
            <img src="${depedLogo}" alt="DepEd Seal" class="sf9-header-logo" onerror="this.style.visibility='hidden'">
            
            <div class="sf9-header-text">
              <div class="gov">Republic of the Philippines</div>
              <div class="gov">Department of Education</div>
              <div>Region 02</div>
              <div class="division">SCHOOLS DIVISION OF CAGAYAN</div>
              <div>Piat District</div>
              <div>Minanga, Piat, Cagayan</div>
              <div class="school-name">MINANGA INTEGRATED SCHOOL</div>
            </div>

            <img src="${schoolLogo}" alt="MIS Seal" class="sf9-header-logo" onerror="this.style.visibility='hidden'">
          </div>

          <!-- Report Title -->
          <div class="sf9-title-block">
            <div class="main-title">LEARNER’S PERFORMANCE REPORT</div>
            <div class="sy-title">School Year <span contenteditable="false" class="editable-field" id="sf9SyText">${escapeHtml(sy)}</span></div>
          </div>

          <!-- Student Personal Details -->
          <div class="sf9-info-grid">
            <div class="sf9-info-row">
              <span style="min-width: 45px;">Name:</span>
              <span class="sf9-underline editable-field" contenteditable="false" style="margin-right: 15px;">${escapeHtml(fullName.toUpperCase())}</span>
              <span style="min-width: 32px;">Age:</span>
              <span class="sf9-underline editable-field text-center" contenteditable="false" style="max-width: 45px; margin-right: 15px;">${escapeHtml(String(age))}</span>
              <span style="min-width: 30px;">Sex:</span>
              <span class="sf9-underline editable-field text-center" contenteditable="false" style="max-width: 70px;">${escapeHtml(sex)}</span>
            </div>
            <div class="sf9-info-row">
              <span style="min-width: 45px;">LRN:</span>
              <span class="sf9-underline editable-field" contenteditable="false" style="margin-right: 15px; font-family: monospace;">${escapeHtml(lrn)}</span>
              <span style="min-width: 42px;">Grade:</span>
              <span class="sf9-underline editable-field text-center" contenteditable="false" style="max-width: 65px; margin-right: 15px;">${escapeHtml(rawGradeLevel)}</span>
              <span style="min-width: 50px;">Section:</span>
              <span class="sf9-underline editable-field" contenteditable="false">${escapeHtml(section)}</span>
            </div>
            <div class="sf9-info-row">
              <span style="min-width: 45px;">Track:</span>
              <span class="sf9-underline editable-field" contenteditable="false">${escapeHtml(trackStrand)}</span>
            </div>
          </div>

          <!-- Dear Parents Message -->
          <div class="sf9-dear-parents">
            <div><strong>Dear Parents,</strong></div>
            <div style="text-indent: 14px;">
              This Performance Report shows the ability and progress your child has made in the different learning areas as well as his/her core values.
            </div>
            <div>The school welcomes you should you desire to know more about your child’s progress.</div>
          </div>

          <div class="sf9-section-heading">LEARNING PROGRESS AND ACHIEVEMENT</div>

          <!-- Grades Table -->
          <table class="sf9-grades-table" id="sf9GradesTable">
            <thead>
              <tr>
                <th rowspan="2" style="width: 54%; text-align: center;">Learning Areas</th>
                <th colspan="3" style="width: 24%; text-align: center;">TERM</th>
                <th rowspan="2" style="width: 11%; text-align: center;">Final<br>Grade</th>
                <th rowspan="2" style="width: 11%; text-align: center;">Remarks</th>
              </tr>
              <tr>
                <th style="width: 8%; text-align: center;">1</th>
                <th style="width: 8%; text-align: center;">2</th>
                <th style="width: 8%; text-align: center;">3</th>
              </tr>
            </thead>
            <tbody>
              ${subjectRowsHtml || '<tr><td colspan="6" class="text-center text-muted py-3">No subjects enrolled.</td></tr>'}
              <tr class="sf9-genavg-row">
                <td class="fw-bold" style="text-align: center;">General Average</td>
                <td class="text-center fw-bold" id="sf9AvgT1"></td>
                <td class="text-center fw-bold" id="sf9AvgT2"></td>
                <td class="text-center fw-bold" id="sf9AvgT3"></td>
                <td class="text-center fw-bold fs-6" id="sf9FinalAvg">${finalGenAvg}</td>
                <td class="text-center fw-bold" id="sf9FinalRemarks" style="color:${finalGenRemark==='Failed'?'#dc2626':'#000000'}">${finalGenRemark}</td>
              </tr>
            </tbody>
          </table>

          <!-- Performance Descriptors Table (Exact match from SF9-TVL-ICT12.pdf) -->
          <div class="sf9-descriptors-block">
            <div class="sf9-descriptors-title">PERFORMANCE DESCRIPTORS</div>
            <table class="sf9-descriptors-table">
              <thead>
                <tr>
                  <th style="width: 33%;">Grading Scale</th>
                  <th style="width: 37%;">Description</th>
                  <th style="width: 30%;">Remarks</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>90-100</td><td>Advancing</td><td>Passed</td></tr>
                <tr><td>80-89</td><td>Benchmarking</td><td>Passed</td></tr>
                <tr><td>75-79</td><td>Connecting</td><td>Passed</td></tr>
                <tr><td>65-74</td><td>Developing</td><td>Failed</td></tr>
                <tr><td>0-64</td><td>Emerging</td><td>Failed</td></tr>
              </tbody>
            </table>
          </div>

        </div><!-- /.sf9-panel (Left) -->


        <!-- =======================================================
             RIGHT PANEL: ATTENDANCE, COMMENTS, TRANSFER
             ======================================================= -->
        <div class="sf9-panel">

          <!-- Attendance Record -->
          <table class="sf9-attendance-table">
            <thead>
              <tr>
                <th colspan="12" class="sf9-attendance-title">ATTENDANCE RECORD</th>
              </tr>
              <tr>
                <th style="width: 25%; text-align: left; padding-left: 4px;">Month</th>
                <th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th>
                <th style="width: 10%;">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="text-align: left; padding-left: 4px;">No. of Class Days</td>
                <td class="editable-field" contenteditable="false">21</td>
                <td class="editable-field" contenteditable="false">22</td>
                <td class="editable-field" contenteditable="false">21</td>
                <td class="editable-field" contenteditable="false">20</td>
                <td class="editable-field" contenteditable="false">15</td>
                <td class="editable-field" contenteditable="false">21</td>
                <td class="editable-field" contenteditable="false">20</td>
                <td class="editable-field" contenteditable="false">22</td>
                <td class="editable-field" contenteditable="false">18</td>
                <td class="editable-field" contenteditable="false">20</td>
                <td class="fw-bold editable-field" contenteditable="false">200</td>
              </tr>
              <tr>
                <td style="text-align: left; padding-left: 4px;">No. of Days Present</td>
                <td class="editable-field" contenteditable="false">21</td>
                <td class="editable-field" contenteditable="false">22</td>
                <td class="editable-field" contenteditable="false">20</td>
                <td class="editable-field" contenteditable="false">20</td>
                <td class="editable-field" contenteditable="false">15</td>
                <td class="editable-field" contenteditable="false">21</td>
                <td class="editable-field" contenteditable="false">20</td>
                <td class="editable-field" contenteditable="false">21</td>
                <td class="editable-field" contenteditable="false">18</td>
                <td class="editable-field" contenteditable="false">19</td>
                <td class="fw-bold editable-field" contenteditable="false">197</td>
              </tr>
              <tr>
                <td style="text-align: left; padding-left: 4px;">No. of Days Absent</td>
                <td class="editable-field" contenteditable="false">0</td>
                <td class="editable-field" contenteditable="false">0</td>
                <td class="editable-field" contenteditable="false">1</td>
                <td class="editable-field" contenteditable="false">0</td>
                <td class="editable-field" contenteditable="false">0</td>
                <td class="editable-field" contenteditable="false">0</td>
                <td class="editable-field" contenteditable="false">0</td>
                <td class="editable-field" contenteditable="false">1</td>
                <td class="editable-field" contenteditable="false">0</td>
                <td class="editable-field" contenteditable="false">1</td>
                <td class="fw-bold editable-field" contenteditable="false">3</td>
              </tr>
            </tbody>
          </table>

          <!-- Teacher's Comments / Remarks Box -->
          <div class="sf9-comments-box">
            <div class="sf9-comments-header">TEACHER’S COMMENTS/REMARKS</div>
            <div class="sf9-comment-term">
              <div class="sf9-comment-term-label">Term 1</div>
              <div class="editable-field" contenteditable="false" style="min-height: 28px;">Demonstrates exceptional focus and diligence in all core and applied learning tasks.</div>
            </div>
            <div class="sf9-comment-term">
              <div class="sf9-comment-term-label">Term 2</div>
              <div class="editable-field" contenteditable="false" style="min-height: 28px;">Shows outstanding technical problem-solving skills and teamwork.</div>
            </div>
            <div class="sf9-comment-term">
              <div class="sf9-comment-term-label">Term 3</div>
              <div class="editable-field" contenteditable="false" style="min-height: 28px;">Successfully completed the curriculum requirements with high commendation.</div>
            </div>
          </div>

          <!-- Parents / Guardian's Signature -->
          <div class="sf9-signatures-block">
            <div class="sf9-signatures-title">PARENTS/GUARDIAN’S SIGNATURE</div>
            <div class="sf9-sig-row">
              <label>Term 1</label>
              <div class="sf9-sig-line"></div>
            </div>
            <div class="sf9-sig-row">
              <label>Term 2</label>
              <div class="sf9-sig-line"></div>
            </div>
            <div class="sf9-sig-row">
              <label>Term 3</label>
              <div class="sf9-sig-line"></div>
            </div>
          </div>

          <!-- Certificate of Transfer -->
          <div class="sf9-cert-transfer">
            <div class="sf9-cert-title">CERTIFICATE OF TRANSFER</div>
            <div>This is to certify that the above-named learner has satisfactorily completed the requirements for the grade level indicated.</div>
            
            <div class="sf9-cert-lines">
              <div class="sf9-cert-row">
                <span style="min-width: 110px;">Admitted to Grade:</span>
                <span class="sf9-underline editable-field" contenteditable="false">${nextGrade}</span>
              </div>
              <div class="sf9-cert-row">
                <span style="min-width: 165px;">Eligible for Admission to Grade:</span>
                <span class="sf9-underline editable-field" contenteditable="false">${nextGrade}</span>
              </div>
            </div>

            <div class="sf9-cert-signatures">
              <div style="width: 48%; text-align: left;">
                <div style="font-size: 8pt; margin-bottom: 14px;">Approved:</div>
                <div style="width: 85%; text-align: center;">
                  <div class="sf9-cert-sig-line editable-field" id="sf9SchoolHeadName" contenteditable="false">${schoolHead}</div>
                  <div style="font-size: 7.5pt;">School Head</div>
                </div>
              </div>
              <div style="width: 48%; text-align: center; margin-top: 4px;">
                <div class="sf9-cert-sig-line editable-field" id="sf9AdviserName" contenteditable="false" style="font-weight: bold;">${adviser}</div>
                <div style="font-size: 7.5pt;">Adviser</div>
              </div>
            </div>
          </div>

          <!-- Cancellation of Eligibility to Transfer -->
          <div class="sf9-cancel-block">
            <div class="sf9-cancel-title">CANCELLATION OF ELIGIBILITY TO TRANSFER</div>
            <div class="sf9-cancel-row">
              <span style="min-width: 75px;">Admitted in:</span>
              <span class="sf9-underline editable-field" contenteditable="false" style="flex-grow: 1;"></span>
              <span style="min-width: 35px;">Date:</span>
              <span class="sf9-underline editable-field" contenteditable="false" style="width: 120px;"></span>
            </div>

            <div style="width: 50%; margin: 8px auto 0; text-align: center;">
              <div class="sf9-cert-sig-line editable-field" contenteditable="false"></div>
              <div style="font-size: 7.5pt;">School Head</div>
            </div>
          </div>

        </div><!-- /.sf9-panel (Right) -->

      </div><!-- /.sf9-sheet -->
    </div><!-- /.sf9-wrapper -->
  `;
}

/**
 * Toggle inline editing mode for the rendered SF9 sheet
 */
function toggleSf9InlineEdit(btn) {
  const container = document.getElementById('sf9PrintContainer');
  if (!container) return;

  const isEditing = container.classList.toggle('is-editing');
  const editableNodes = container.querySelectorAll('.editable-field, .sf9-grade-cell');

  editableNodes.forEach(node => {
    node.setAttribute('contenteditable', isEditing ? 'true' : 'false');
  });

  if (btn) {
    if (isEditing) {
      btn.classList.remove('btn-outline-light');
      btn.classList.add('btn-warning');
      btn.innerHTML = '<i class="fas fa-check me-1"></i><span>Finish Editing</span>';
    } else {
      btn.classList.remove('btn-warning');
      btn.classList.add('btn-outline-light');
      btn.innerHTML = '<i class="fas fa-edit me-1"></i><span>Edit Sheet</span>';
      recalculateSf9Averages();
    }
  }
}

/**
 * Realtime recalculation for a single edited grade row
 */
function recalcSf9Row(cell) {
  const row = cell.closest('tr');
  if (!row) return;

  const t1Cell = row.querySelector('.grade-t1');
  const t2Cell = row.querySelector('.grade-t2');
  const t3Cell = row.querySelector('.grade-t3');
  const finalCell = row.querySelector('.grade-final');
  const remarksCell = row.querySelector('.grade-remarks');

  const v1 = parseFloat(t1Cell ? t1Cell.textContent.trim() : '');
  const v2 = parseFloat(t2Cell ? t2Cell.textContent.trim() : '');
  const v3 = parseFloat(t3Cell ? t3Cell.textContent.trim() : '');

  const validVals = [v1, v2, v3].filter(v => !isNaN(v));
  if (validVals.length > 0) {
    const avg = Math.round(validVals.reduce((a, b) => a + b, 0) / validVals.length);
    if (finalCell) finalCell.textContent = avg;
    if (remarksCell) {
      remarksCell.textContent = avg >= 75 ? 'Passed' : 'Failed';
      remarksCell.style.color = avg < 75 ? '#dc2626' : '#000000';
    }
  } else {
    if (finalCell) finalCell.textContent = '—';
    if (remarksCell) {
      remarksCell.textContent = '—';
      remarksCell.style.color = 'inherit';
    }
  }

  recalculateSf9Averages();
}

/**
 * Recalculate term averages, final average, and remarks across the entire SF9 sheet
 */
function recalculateSf9Averages() {
  const rows = document.querySelectorAll('.sf9-subject-row');
  const finals = [];
  const t1Vals = [];
  const t2Vals = [];
  const t3Vals = [];

  rows.forEach(r => {
    const fg = parseFloat(r.querySelector('.grade-final')?.textContent.trim());
    if (!isNaN(fg)) finals.push(fg);

    const v1 = parseFloat(r.querySelector('.grade-t1')?.textContent.trim());
    if (!isNaN(v1)) t1Vals.push(v1);

    const v2 = parseFloat(r.querySelector('.grade-t2')?.textContent.trim());
    if (!isNaN(v2)) t2Vals.push(v2);

    const v3 = parseFloat(r.querySelector('.grade-t3')?.textContent.trim());
    if (!isNaN(v3)) t3Vals.push(v3);
  });

  const avgT1El = document.getElementById('sf9AvgT1');
  const avgT2El = document.getElementById('sf9AvgT2');
  const avgT3El = document.getElementById('sf9AvgT3');
  const finalAvgEl = document.getElementById('sf9FinalAvg');
  const finalRemEl = document.getElementById('sf9FinalRemarks');

  if (avgT1El && t1Vals.length) avgT1El.textContent = Math.round(t1Vals.reduce((a, b) => a + b, 0) / t1Vals.length);
  if (avgT2El && t2Vals.length) avgT2El.textContent = Math.round(t2Vals.reduce((a, b) => a + b, 0) / t2Vals.length);
  if (avgT3El && t3Vals.length) avgT3El.textContent = Math.round(t3Vals.reduce((a, b) => a + b, 0) / t3Vals.length);

  if (finals.length > 0) {
    const genAvg = Math.round(finals.reduce((a, b) => a + b, 0) / finals.length);
    if (finalAvgEl) finalAvgEl.textContent = genAvg;
    if (finalRemEl) {
      finalRemEl.textContent = genAvg >= 75 ? 'Passed' : 'Failed';
      finalRemEl.style.color = genAvg < 75 ? '#dc2626' : '#000000';
    }
  } else {
    if (finalAvgEl) finalAvgEl.textContent = '—';
    if (finalRemEl) {
      finalRemEl.textContent = '—';
      finalRemEl.style.color = 'inherit';
    }
  }
}

/**
 * Triggers native landscape printing
 */
function printSf9Official() {
  window.print();
}

/**
 * Helper to escape HTML characters
 */
function escapeHtml(str) {
  return String(str || '').replace(/[&<>"']/g, m => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[m]);
}
