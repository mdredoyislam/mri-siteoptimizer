<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="mri-siteoptimizer-wrap">
  <div class="so-header">
    <div class="so-logo">
      <div class="so-logo-icon"><svg viewBox="0 0 24 24" fill="white"><path d="M13 2L4.09 12.26c-.36.43-.14 1.07.39 1.22L11 15l-2 7 8.91-10.26c.36-.43.14-1.07-.39-1.22L11 9l2-7z"/></svg></div>
      <h1>Images</h1>
    </div>
  </div>

  <div class="so-tabs">
    <button class="so-tab active" data-tab="tab-unlinked">Unlinked images</button>
    <button class="so-tab" data-tab="tab-compress">Compression</button>
  </div>

  <!-- Tab: Unlinked -->
  <div class="so-tab-content active" id="tab-unlinked">
    <div class="so-card" style="margin-bottom:16px">
      <div class="so-card-title">
        <div class="icon icon-red"><svg viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="15" y2="21" stroke="#DC2626"/></svg></div>
        Unlinked image scanner
      </div>
      <p style="font-size:13px;color:#6B7280;margin-bottom:14px">
        Scans your entire media library for images not referenced in any post, page, widget, or theme file. Review before deleting — you can preview each image first.
      </p>
      <div style="display:flex;align-items:center;gap:10px">
        <button class="so-btn so-btn-primary" id="so-btn-scan-images">🔍 Scan for unlinked images</button>
        <label style="font-size:13px;color:#6B7280;display:flex;align-items:center;gap:6px">
          <input type="checkbox" <?php checked( get_option( 'mri_siteoptimizer_trash_before_delete', '1' ), '1' ); ?> id="so-trash-before-delete" class="so-checkbox">
          Move to trash instead of permanent delete
        </label>
      </div>
    </div>
    <div id="so-images-results"></div>
  </div>

  <!-- Tab: Compression -->
  <div class="so-tab-content" id="tab-compress">
    <div class="so-card" style="margin-bottom:16px">
      <div class="so-card-title">
        <div class="icon icon-green"><svg viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
        Bulk image compression
      </div>
      <p style="font-size:13px;color:#6B7280;margin-bottom:14px">
        Compress all unprocessed images in your media library. Quality setting is applied from your <a href="<?php echo esc_url( admin_url( 'admin.php?page=mri-siteoptimizer-settings' ) ); ?>">Settings</a>.
      </p>

      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <button class="so-btn so-btn-primary" id="so-btn-bulk-compress">⚡ Bulk compress now</button>
      </div>
    </div>

    <div class="so-card">
      <div class="so-card-title">Compression options</div>
      <div class="so-toggle-row">
        <label class="row-label">Auto-compress on upload</label>
        <label class="so-switch">
          <input type="checkbox" data-setting="auto_compress_upload" <?php checked( get_option( 'mri_siteoptimizer_auto_compress_upload', '1' ), '1' ); ?>>
          <span class="track"></span>
        </label>
      </div>
      <div class="so-toggle-row">
        <label class="row-label">Lazy load all images</label>
        <label class="so-switch">
          <input type="checkbox" data-setting="lazy_load" <?php checked( get_option( 'mri_siteoptimizer_lazy_load', '1' ), '1' ); ?>>
          <span class="track"></span>
        </label>
      </div>
      <div class="so-toggle-row">
        <label class="row-label">Compression type</label>
        <select class="so-select" data-setting="compression_type">
          <option value="lossy"    <?php selected( get_option( 'mri_siteoptimizer_compression_type', 'lossy' ), 'lossy' ); ?>>Lossy (smaller files)</option>
          <option value="lossless" <?php selected( get_option( 'mri_siteoptimizer_compression_type', 'lossy' ), 'lossless' ); ?>>Lossless (best quality)</option>
        </select>
      </div>
      <div class="so-toggle-row">
        <label class="row-label">JPEG / WebP quality (1–100)</label>
        <input type="number" min="1" max="100" class="so-input" data-setting="compression_quality" value="<?php echo esc_attr( get_option( 'mri_siteoptimizer_compression_quality', 82 ) ); ?>">
      </div>
      <div style="margin-top:14px">
        <button class="so-btn so-btn-primary" id="so-btn-save-settings">Save settings</button>
      </div>
    </div>
  </div>
</div>
