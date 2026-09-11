---
trigger: always_on
---

# SPSMIS System Memory & Cross-File Synchronization Rule

Whenever changing, refactoring, or adding code and files in this repository:
1. Always maintain complete system synchronization across all layers (Database, API, View, Sidebar, Auth, Constants).
2. Follow the inter-file dependency matrix defined in `SYSTEM_MEMORY.md`.
3. Whenever an edit is performed, run `php system_memory_check.php` to verify that all files remain 100% synchronized and free of broken links or syntax errors.
