# Presentation Retag Guide — SPSFMS

A reference for applying post-presentation fixes to a version tag **without** breaking the gated/unlocked state of that version.

---

## The Problem This Solves

When you fix a bug on a feature that belongs to an older version (e.g., v1.00 owns Forgot Password), you cannot just move the tag to `main` — because `main` is already at the latest version (e.g., v1.10, fully unlocked). Moving the tag to `main` would make `git checkout v1.00` show the full system instead of the gated presentation state.

**Wrong approach (what NOT to do):**
```bash
# This moves v1.00 to the fully-unlocked HEAD — WRONG
git tag -d v1.00
git tag v1.00          # points to main/HEAD = all pages unlocked
git push origin v1.00
```

**Correct approach:** cherry-pick only the fix commits onto the original version base, then retag there.

---

## Correct Retag Process (Step by Step)

### 1. Identify the original version commit

```bash
git log --oneline
# Find the original vX.XX commit hash, e.g.:
# e3dc81c feat: implement v1.00 - login/auth only, gate all other pages
```

Or look it up in `docs/Version-Control.md` under **GitHub Release Tags**.

---

### 2. Create a temporary branch from that original commit

```bash
git checkout -b vX.XX-presentation <original-commit-hash>
# Example:
git checkout -b v1.00-presentation e3dc81c
```

This puts you on a branch that has the correct gated/unlocked state for that version.

---

### 3. Cherry-pick only the fix commits

Find the commit hashes of the fixes you made on `main`:

```bash
git log main --oneline
# e.g.:
# 7a40e51 feat: update security question flow in forgot password process
# 12bd651 fix: replace Go Back with Logout button on Under Construction page
```

Cherry-pick them in order (oldest first):

```bash
git cherry-pick <fix-commit-1>
git cherry-pick <fix-commit-2>
```

**If there is a conflict** (common in `components/under-construction.php` because the version number differs):

- Open the conflicting file and resolve manually
- Keep the version number from HEAD (e.g., `v1.00`) — do NOT use the one from `main`
- Keep any new lines added by the fix commit (e.g., `require_once`, new button HTML)
- Then continue:

```bash
git add components/under-construction.php
git cherry-pick --continue --no-edit
```

---

### 4. Retag the version to this new commit

```bash
git tag -d vX.XX                        # delete old local tag
git push origin :refs/tags/vX.XX        # delete old remote tag
git tag vX.XX                           # create tag at current HEAD (cherry-picked state)
git push origin vX.XX                   # push new tag to GitHub
```

---

### 5. Update the commit hash in Version-Control.md

```bash
git checkout main
# Edit docs/Version-Control.md — update the hash in the GitHub Release Tags table
git add docs/Version-Control.md
git commit -m "docs: retag vX.XX with updated commit hash after <fix description>"
git push origin main
```

---

### 6. Switch working directory for presentation

```bash
git checkout vX.XX
# XAMPP now serves the correct gated presentation state
```

After the presentation, return to full system:

```bash
git checkout main
```

---

## Real Example — v1.00 Retag (June 28, 2026)

**Fixes applied after original v1.00 tag:**
1. Forgot Password — changed security question from auto-revealed text to a dropdown; user must select the question they set + enter the answer. Both question and answer are verified on the backend.
2. Under Construction page — replaced "← Go Back" button with a **Logout** button that destroys the session and redirects to login, preventing session trapping on gated pages.

**Files changed by the fixes:**
| File | What Changed |
|------|-------------|
| `views/auth/forgot-password.php` | Step 2 now shows a `<select>` dropdown of all 4 security questions instead of displaying the user's question as plain text |
| `api/auth/forgot-step1.php` | No longer returns `question` in the response — just confirms the user exists |
| `api/auth/forgot-step2.php` | Now receives and verifies both `question` and `answer`; rejects if either is wrong |
| `components/under-construction.php` | Added `require_once` for constants (to get `BASE_URL`); replaced Go Back `<a>` with a Logout `<a>` pointing to `api/auth/logout.php` |

**Commands used:**
```bash
git checkout -b v1.00-presentation e3dc81c
git cherry-pick 7a40e51   # forgot-password fix
git cherry-pick 12bd651   # logout button fix (had conflict — kept v1.00 version number)
git tag -d v1.00
git push origin :refs/tags/v1.00
git tag v1.00
git push origin v1.00
git checkout main
# updated docs/Version-Control.md hash to cf45f9f
git checkout v1.00        # switched XAMPP to presentation state
```

---

## Quick Reference — Switching States

| Goal | Command |
|------|---------|
| Present vX.XX | `git checkout vX.XX` |
| Return to full system | `git checkout main` |
| Check which state XAMPP is serving | `git status` (shows branch or `HEAD detached at vX.XX`) |
| Verify gate lines are active | `head -1 views/admin/dashboard.php` (should show `require_once`) |

---

## Security Questions Reference

The same 4 questions are used across all settings pages and the Forgot Password dropdown. If you ever add more questions, update all four files:

- `views/admin/settings.php` — `$secQuestions` array
- `views/teacher/settings.php` — `$secQuestions` array
- `views/student/settings.php` — equivalent list
- `views/auth/forgot-password.php` — `<select>` options in Step 2
