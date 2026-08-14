<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="mri-siteoptimizer-wrap">
  <div class="so-header">
    <div class="so-logo">
      <div class="so-logo-icon"><svg viewBox="0 0 24 24" fill="white"><path d="M13 2L4.09 12.26c-.36.43-.14 1.07.39 1.22L11 15l-2 7 8.91-10.26c.36-.43.14-1.07-.39-1.22L11 9l2-7z"/></svg></div>
      <h1>Settings</h1>
    </div>
    <div class="so-header-actions">
      <button class="so-btn so-btn-primary" id="so-btn-save-settings">💾 Save all settings</button>
    </div>
  </div>

  <div class="so-two-col">
    <div>

      <!-- Images -->
      <div class="so-card">
        <div class="so-card-title">
          <div class="icon icon-green"><svg viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
          Image settings
        </div>

        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Auto-compress on upload</div>
            <div class="so-setting-desc">Compress every image as it's uploaded</div>
          </div>
          <label class="so-switch">
            <input type="checkbox" data-setting="auto_compress_upload" <?php checked( get_option( 'mri_siteoptimizer_auto_compress_upload', '1' ), '1' ); ?>>
            <span class="track"></span>
          </label>
        </div>

        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Lazy load images</div>
            <div class="so-setting-desc">Add loading="lazy" to all images in content</div>
          </div>
          <label class="so-switch">
            <input type="checkbox" data-setting="lazy_load" <?php checked( get_option( 'mri_siteoptimizer_lazy_load', '1' ), '1' ); ?>>
            <span class="track"></span>
          </label>
        </div>

        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Compression type</div>
            <div class="so-setting-desc">Lossy = smaller files, lossless = best quality</div>
          </div>
          <select class="so-select" data-setting="compression_type">
            <option value="lossy"    <?php selected( get_option( 'mri_siteoptimizer_compression_type', 'lossy' ), 'lossy' ); ?>>Lossy</option>
            <option value="lossless" <?php selected( get_option( 'mri_siteoptimizer_compression_type', 'lossy' ), 'lossless' ); ?> >Lossless</option>
          </select>
        </div>

        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">JPEG / WebP quality</div>
            <div class="so-setting-desc">1 (lowest) — 100 (original). Recommended: 75–85</div>
          </div>
          <input type="number" min="1" max="100" class="so-input" data-setting="compression_quality" value="<?php echo esc_attr( get_option( 'mri_siteoptimizer_compression_quality', 82 ) ); ?>">
        </div>
      </div>

      <!-- File cleaner -->
      <div class="so-card">
        <div class="so-card-title">
          <div class="icon icon-amber"><svg viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg></div>
          File cleaner settings
        </div>

        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Move to trash before delete</div>
            <div class="so-setting-desc">Safer: sends unlinked images to trash first</div>
          </div>
          <label class="so-switch">
            <input type="checkbox" data-setting="trash_before_delete" <?php checked( get_option( 'mri_siteoptimizer_trash_before_delete', '1' ), '1' ); ?>>
            <span class="track"></span>
          </label>
        </div>

        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Remove temp &amp; log files</div>
            <div class="so-setting-desc">Auto-remove .tmp, .log, .bak files from uploads</div>
          </div>
          <label class="so-switch">
            <input type="checkbox" data-setting="remove_temp_files" <?php checked( get_option( 'mri_siteoptimizer_remove_temp_files', '1' ), '1' ); ?>>
            <span class="track"></span>
          </label>
        </div>

        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Remove unused image sizes</div>
            <div class="so-setting-desc">Delete thumbnail files for unregistered sizes</div>
          </div>
          <label class="so-switch">
            <input type="checkbox" data-setting="remove_unused_sizes" <?php checked( get_option( 'mri_siteoptimizer_remove_unused_sizes', '1' ), '1' ); ?>>
            <span class="track"></span>
          </label>
        </div>
      </div>

      <!-- Database -->
      <div class="so-card">
        <div class="so-card-title">
          <div class="icon icon-blue"><svg viewBox="0 0 24 24" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div>
          Database settings
        </div>

        <?php
        $mri_siteoptimizer_db_settings = [
          [ 'db_revisions',      'Delete post revisions'     ],
          [ 'db_auto_drafts',    'Delete auto-draft posts'   ],
          [ 'db_trash_posts',    'Delete trashed posts'      ],
          [ 'db_spam_comments',  'Delete spam comments'      ],
          [ 'db_trash_comments', 'Delete trashed comments'   ],
          [ 'db_transients',     'Delete expired transients' ],
        ];
        foreach ( $mri_siteoptimizer_db_settings as $s ) : ?>
        <div class="so-setting-row">
          <div class="so-setting-label"><?php echo esc_html( $s[1] ); ?></div>
          <label class="so-switch">
            <input type="checkbox" data-setting="<?php echo esc_attr( $s[0] ); ?>" <?php checked( get_option( 'mri_siteoptimizer_' . $s[0], '1' ), '1' ); ?>>
            <span class="track"></span>
          </label>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <div>
      <!-- Schedule -->
      <div class="so-card">
        <div class="so-card-title">Auto-scan schedule</div>
        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Scan frequency</div>
            <div class="so-setting-desc">How often MRI SiteOptimizer scans for issues</div>
          </div>
          <select class="so-select" data-setting="scan_schedule">
            <option value="daily"   <?php selected( get_option( 'mri_siteoptimizer_scan_schedule', 'weekly' ), 'daily' ); ?>>Daily</option>
            <option value="weekly"  <?php selected( get_option( 'mri_siteoptimizer_scan_schedule', 'weekly' ), 'weekly' ); ?>>Weekly</option>
            <option value="monthly" <?php selected( get_option( 'mri_siteoptimizer_scan_schedule', 'weekly' ), 'monthly' ); ?>>Monthly</option>
          </select>
        </div>
      </div>

      <!-- Info -->
      <div class="so-card">
        <div class="so-card-title">Plugin info</div>
        <table style="font-size:13px;width:100%;border-collapse:collapse">
          <tr><td style="color:#6B7280;padding:5px 0">Version</td><td><?php echo esc_html( MRI_SITEOPTIMIZER_VERSION ); ?></td></tr>
          <tr><td style="color:#6B7280;padding:5px 0">Activated</td><td><?php echo esc_html( get_option( 'mri_siteoptimizer_activated_at', '—' ) ); ?></td></tr>
          <tr><td style="color:#6B7280;padding:5px 0">WP version</td><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
          <tr><td style="color:#6B7280;padding:5px 0">PHP version</td><td><?php echo esc_html( phpversion() ); ?></td></tr>
        </table>
      </div>

      <div class="so-card">
        <div class="so-card-title" style="color:#DC2626">⚠ Danger zone</div>
        <p style="font-size:13px;color:#6B7280;margin-bottom:12px">Reset all MRI SiteOptimizer settings to defaults. This does not delete any files.</p>
        <button class="so-btn so-btn-danger so-btn-sm" onclick="if(confirm('Reset all settings?')){window.location.href='<?php echo esc_url( add_query_arg( [ 'mri_siteoptimizer_reset' => '1', '_wpnonce' => wp_create_nonce( 'mri_siteoptimizer_reset' ) ], admin_url( 'admin.php?page=mri-siteoptimizer-settings' ) ) ); ?>'}">Reset settings</button>
      </div>
    </div>
  </div>

  <div style="margin-top:8px">
    <button class="so-btn so-btn-primary" id="so-btn-save-settings">💾 Save all settings</button>
  </div>
</div>
<?php
// Handle reset
if ( isset( $_GET['mri_siteoptimizer_reset'] ) && check_admin_referer( 'mri_siteoptimizer_reset' ) ) {
  \MRISiteOptimizer\Installer::activate();
  wp_safe_redirect( admin_url( 'admin.php?page=mri-siteoptimizer-settings&reset=1' ) );
  exit;
  exit;
}
if ( isset( $_GET['reset'] ) ) {
  echo '<div class="so-notice so-notice-success" style="max-width:400px;margin-top:12px">Settings reset to defaults.</div>';
}
?>
