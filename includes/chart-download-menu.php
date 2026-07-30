<?php
// $chartId must be set before including this file — used to build unique button ids
// (e.g. "trend" -> #trend-csv-btn / #trend-pdf-btn), wired up in the including page's JS.
?>
<div class="dropdown">
  <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Download data">
    <i class="fas fa-download"></i>
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="#" id="<?= $chartId ?>-csv-btn"><i class="fas fa-file-excel me-2 text-success"></i>Export as Excel (CSV)</a></li>
    <li><a class="dropdown-item" href="#" id="<?= $chartId ?>-pdf-btn"><i class="fas fa-file-pdf me-2 text-danger"></i>Export as PDF</a></li>
  </ul>
</div>
