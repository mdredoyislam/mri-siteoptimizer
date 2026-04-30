<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="mri-siteoptimizer-wrap" id="so-dashboard-stats">
  <div class="so-header">
    <div class="so-logo">
      <div class="so-logo-icon">
        <svg viewBox="0 0 24 24"><path d="M13 2L4.09 12.26c-.36.43-.14 1.07.39 1.22L11 15l-2 7 8.91-10.26c.36-.43.14-1.07-.39-1.22L11 9l2-7z"/></svg>
      </div>
      <h1>MRI SiteOptimizer <span class="version">v<?php echo esc_html( MRI_SITEOPTIMIZER_VERSION ); ?></span></h1>
    </div>
    <div class="so-header-actions">
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-settings' ) ); ?>" class="so-btn so-btn-outline">⚙ Settings</a>
      <button class="so-btn so-btn-primary" id="so-quick-scan">⚡ Run full scan</button>
    </div>
  </div>

  <div class="so-stat-grid">
    <div class="so-stat">
      <div class="so-stat-label">Unlinked images</div>
      <div class="so-stat-value" id="so-stat-unlinked">—</div>
      <div class="so-stat-sub" id="so-stat-unlinked-sub">Scanning…</div>
    </div>
    <div class="so-stat">
      <div class="so-stat-label">Orphaned files</div>
      <div class="so-stat-value" id="so-stat-orphans">—</div>
      <div class="so-stat-sub" id="so-stat-orphans-sub">Scanning…</div>
    </div>
    <div class="so-stat">
      <div class="so-stat-label">DB items to clean</div>
      <div class="so-stat-value" id="so-stat-db">—</div>
      <div class="so-stat-sub" id="so-stat-db-sub">Scanning…</div>
    </div>
    <div class="so-stat">
      <div class="so-stat-label">Total space saved</div>
      <div class="so-stat-value" id="so-stat-saved">—</div>
      <div class="so-stat-sub" id="so-stat-saved-sub">Since install</div>
    </div>
  </div>

  <div class="so-two-col">
    <div>
      <!-- Quick links to modules -->
      <div class="so-card">
        <div class="so-card-title">
          <div class="icon icon-red"><svg viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg></div>
          Unlinked image cleaner
        </div>
        <p style="font-size:13px;color:#6B7280;margin-bottom:12px">Find and safely remove images not used in any post, page, widget, or theme file.</p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-images' ) ); ?>" class="so-btn so-btn-primary so-btn-sm">View images →</a>
      </div>

      <div class="so-card">
        <div class="so-card-title">
          <div class="icon icon-amber"><svg viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></div>
          File &amp; database cleaner
        </div>
        <p style="font-size:13px;color:#6B7280;margin-bottom:12px">Remove orphaned uploads, temp/log files, unused image sizes, and clean your WordPress database.</p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-files' ) ); ?>" class="so-btn so-btn-outline so-btn-sm">Files →</a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-database' ) ); ?>" class="so-btn so-btn-outline so-btn-sm" style="margin-left:6px">Database →</a>
      </div>

      <div class="so-card">
        <div class="so-card-title">
          <div class="icon icon-green"><svg viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
          Image compression
        </div>
        <p style="font-size:13px;color:#6B7280;margin-bottom:12px">Auto-compress on upload, bulk compress existing images, and enable lazy loading across the site.</p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-images' ) ); ?>" class="so-btn so-btn-green so-btn-sm">Manage →</a>
      </div>
    </div>

    <div>
      <!-- Site health -->
      <div class="so-card">
        <div class="so-card-title">Site health</div>
        <div class="so-health-score">
          <div class="so-health-num" id="so-health-num">—</div>
          <div class="so-health-label">out of 100</div>
        </div>
        <div class="so-perf-label"><span>Images</span><span id="pb-images-pct"></span></div>
        <div class="so-progress-wrap"><div class="so-progress-bar pb-amber" id="pb-images" style="width:0%"></div></div>
        <div class="so-perf-label"><span>Database</span></div>
        <div class="so-progress-wrap"><div class="so-progress-bar pb-red" id="pb-db" style="width:0%"></div></div>
        <div class="so-perf-label"><span>Files</span></div>
        <div class="so-progress-wrap"><div class="so-progress-bar pb-green" id="pb-files" style="width:0%"></div></div>
      </div>

      <!-- Quick actions -->
      <div class="so-card">
        <div class="so-card-title">Quick actions</div>
        <button class="so-quick-btn" id="so-quick-scan">
          <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Scan site now
        </button>
        <button class="so-quick-btn" id="so-quick-compress">
          <svg viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          Bulk compress images
        </button>
        <button class="so-quick-btn" id="so-quick-db">
          <svg viewBox="0 0 24 24" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
          Optimize database
        </button>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-settings' ) ); ?>" class="so-quick-btn">
          <svg viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
          Settings
        </a>
      </div>

      <!-- Activity log -->
      <div class="so-card">
        <div class="so-card-title">Recent activity <small id="so-last-scan" style="font-weight:400;font-size:11px;color:#9CA3AF"></small></div>
        <div id="so-activity-log"><div class="so-loading-row"><span class="so-spinner"></span></div></div>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-log' ) ); ?>" style="font-size:12px;color:#4F46E5;display:block;margin-top:8px">View full log →</a>
      </div>
    </div>
  </div>
</div>
