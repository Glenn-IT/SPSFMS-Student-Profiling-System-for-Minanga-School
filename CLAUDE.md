# SPSMIS SYSTEM RULES & SYNCHRONIZATION DIRECTIVE

Always read and adhere to `SYSTEM_MEMORY.md` before making any code modifications or refactorings in this project.

## Mandatory Rules:
1. **Consult the Dependency Matrix in `SYSTEM_MEMORY.md`**:
   - Before modifying any database table, column, API endpoint, view, constant, or authentication logic, identify all dependent files across the system.
2. **Synchronize All Dependent Files Contiguously**:
   - Never update a database column without updating `database/schema.sql`, `database/setup.php`, the relevant `api/` scripts, and the corresponding `views/` forms/tables.
   - Never modify an API endpoint without updating all client-side JavaScript `fetch()` calls in the views that interact with it.
   - When adding or renaming admin or teacher views, ensure `includes/admin-sidebar.php` or `includes/teacher-sidebar.php` is updated and `$activePage` is properly configured.
   - Ensure every view maintains `requireAuth(...)` and the desktop warning overlay (for Admin/Teacher).
3. **Execute the System Memory Checker After Changes**:
   - After completing any modification or refactoring, ALWAYS execute:
     ```bash
     php system_memory_check.php
     ```
   - Verify that 0 errors and 0 critical discrepancies are reported.
