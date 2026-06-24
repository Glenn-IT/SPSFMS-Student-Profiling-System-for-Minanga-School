# Version Control — SPSFMS Rollout Schedule

## Rollout Schedule

| Version | Feature Unlocked | Pages Unlocked | Pages Still Gated |
|---------|-----------------|----------------|-------------------|
| v1.00 | Login / Forgot Password | `views/auth/login.php`, `views/auth/forgot-password.php` | All admin, teacher, student pages |
| v1.01 | Admin: Dashboard | `views/admin/dashboard.php` | analytics, students, accounts, reports, settings (admin); all teacher; all student |
| v1.02 | Admin: Analytics + Manage Students | `views/admin/analytics.php`, `views/admin/students.php` | accounts, reports, settings (admin); all teacher; all student |
| v1.03 | Admin: Manage Accounts + Reports | `views/admin/accounts.php`, `views/admin/reports.php` | settings (admin); all teacher; all student |
| v1.04 | Admin: Settings | `views/admin/settings.php` | all teacher; all student |
| v1.05 | Teacher: Dashboard | `views/teacher/dashboard.php` | grades, student-profiles, reports, settings (teacher); all student |
| v1.06 | Teacher: Grades + Student Profiles | `views/teacher/grades.php`, `views/teacher/student-profiles.php` | reports, settings (teacher); all student |
| v1.07 | Teacher: Reports + Settings | `views/teacher/reports.php`, `views/teacher/settings.php` | all student |
| v1.08 | Student: Dashboard | `views/student/dashboard.php` | profile, settings (student) |
| v1.09 | Student: Profile | `views/student/profile.php` | settings (student) |
| v1.10 | Student: Settings — **Full System** | `views/student/settings.php` | none |

---

## Under Construction Strategy

- `components/under-construction.php` holds a single constant `CURRENT_VERSION`.
- Every page not yet unlocked includes `require_once '../../components/under-construction.php'` as its **very first line**.
- Because the file ends with `exit`, PHP stops and renders the Under Construction card instead of the real page.
- To unlock a page: **remove** that `require_once` gate line and bump `CURRENT_VERSION` in `components/under-construction.php`.

---

## Git Commands Per Version

```bash
# 1. Remove the gate line(s) from the page(s) being unlocked
# 2. Update CURRENT_VERSION in components/under-construction.php
# 3. Stage, commit, tag, and push

git add <unlocked-page(s)> components/under-construction.php
git commit -m "feat: implement vX.XX - unlock [Feature Name]"
git tag vX.XX
git push origin main
git push origin vX.XX
```

---

## How Git Tags Work

Each version is a **permanent snapshot** in Git history.

- `git tag vX.XX` marks the current commit with that label forever.
- `git push origin vX.XX` uploads the tag to GitHub so it shows as a Release on the repo.
- To view a specific version later: `git checkout vX.XX` (read-only) or compare with `git diff vX.XX vX.YY`.
- Tags survive branch changes — they always point to the exact commit they were created on.

---

## GitHub Release Tags

| Version | Tag Name | Commit Hash |
|---------|----------|-------------|
| v1.00 | v1.00 | |
| v1.01 | v1.01 | |
| v1.02 | v1.02 | |
| v1.03 | v1.03 | |
| v1.04 | v1.04 | |
| v1.05 | v1.05 | |
| v1.06 | v1.06 | |
| v1.07 | v1.07 | |
| v1.08 | v1.08 | |
| v1.09 | v1.09 | |
| v1.10 | v1.10 | |

> Fill commit hashes after all versions are tagged:
> ```bash
> git tag | sort | xargs -I{} git log -1 --format="{} %H" {}
> ```

---

## When a Prof or Client Requests Changes After a Presentation

```bash
# 1. Fix the issue on main
git checkout main
git add <changed-files>
git commit -m "feat: update [page] per feedback"
git push origin main

# 2. Move the tag to the new commit
git tag -d vX.XX                        # delete local tag
git push origin :refs/tags/vX.XX        # delete remote tag
git tag vX.XX                           # re-create pointing to latest commit
git push origin vX.XX                   # push updated tag
```

This keeps the GitHub Release pointing to the corrected version without creating a new version number.
