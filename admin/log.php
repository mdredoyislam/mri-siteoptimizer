<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="mri-siteoptimizer-wrap">
  <div class="so-header">
    <div class="so-logo">
      <div class="so-logo-icon"><svg viewBox="0 0 24 24" fill="white"><path d="M13 2L4.09 12.26c-.36.43-.14 1.07.39 1.22L11 15l-2 7 8.91-10.26c.36-.43.14-1.07-.39-1.22L11 9l2-7z"/></svg></div>
      <h1>Activity log</h1>
    </div>
  </div>

  <div class="so-card">
    <div class="so-table-wrap">
      <table class="so-table">
        <thead>
          <tr>
            <th>Type</th>
            <th>Message</th>
            <th>Space saved</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody id="so-log-table-body">
          <tr><td colspan="4" class="so-loading-row"><span class="so-spinner"></span></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
