<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="mri-siteoptimizer-wrap">
  <div class="so-header">
    <div class="so-logo">
      <div class="so-logo-icon"><svg viewBox="0 0 24 24" fill="white"><path d="M13 2L4.09 12.26c-.36.43-.14 1.07.39 1.22L11 15l-2 7 8.91-10.26c.36-.43.14-1.07-.39-1.22L11 9l2-7z"/></svg></div>
      <h1>Files</h1>
    </div>
  </div>

  <div class="so-tabs">
    <button class="so-tab active" data-tab="tab-orphans">Orphaned files</button>
    <button class="so-tab" data-tab="tab-junk">Temp &amp; junk files</button>
    <button class="so-tab" data-tab="tab-sizes">Unused image sizes</button>
  </div>

  <!-- Orphaned files -->
  <div class="so-tab-content active" id="tab-orphans">
    <div class="so-card" style="margin-bottom:16px">
      <div class="so-card-title">
        <div class="icon icon-amber"><svg viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg></div>
        Orphaned file scanner
      </div>
      <p style="font-size:13px;color:#6B7280;margin-bottom:14px">
        Finds files in <code>/wp-content/uploads/</code> that have no matching attachment record in the database. These are safe to remove.
      </p>
      <button class="so-btn so-btn-primary" id="so-btn-scan-orphans">🔍 Scan for orphaned files</button>
    </div>
    <div id="so-orphan-results"></div>
  </div>

  <!-- Junk files -->
  <div class="so-tab-content" id="tab-junk">
    <div class="so-card" style="margin-bottom:16px">
      <div class="so-card-title">
        <div class="icon icon-red"><svg viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg></div>
        Temp &amp; junk file scanner
      </div>
      <p style="font-size:13px;color:#6B7280;margin-bottom:14px">
        Finds <code>.tmp</code>, <code>.log</code>, <code>.bak</code>, <code>.old</code>, and similar junk files left behind by plugins, themes, or server operations.
      </p>
      <button class="so-btn so-btn-primary" id="so-btn-scan-junk">🔍 Scan for junk files</button>
    </div>
    <div id="so-junk-results"></div>
  </div>

  <!-- Unused image sizes -->
  <div class="so-tab-content" id="tab-sizes">
    <div class="so-card">
      <div class="so-card-title">
        <div class="icon icon-blue"><svg viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
        Unused image sizes
      </div>
      <p style="font-size:13px;color:#6B7280;margin-bottom:14px">
        WordPress generates multiple thumbnail sizes for every image you upload. This removes thumbnail files for image sizes that are no longer registered by your theme or plugins.
      </p>
      <button class="so-btn so-btn-danger" id="so-btn-remove-sizes">Remove unused size files</button>
      <div id="so-sizes-result" style="margin-top:12px"></div>
    </div>
  </div>
</div>
