<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="mri-siteoptimizer-wrap" id="so-db-stats-wrap">
  <div class="so-header">
    <div class="so-logo">
      <div class="so-logo-icon"><svg viewBox="0 0 24 24" fill="white"><path d="M13 2L4.09 12.26c-.36.43-.14 1.07.39 1.22L11 15l-2 7 8.91-10.26c.36-.43.14-1.07-.39-1.22L11 9l2-7z"/></svg></div>
      <h1>Database</h1>
    </div>
    <div class="so-header-actions">
      <button class="so-btn so-btn-primary" id="so-btn-optimize-db">⚡ Optimize database now</button>
    </div>
  </div>

  <div class="so-two-col">
    <div>
      <div class="so-card">
        <div class="so-card-title">
          <div class="icon icon-blue"><svg viewBox="0 0 24 24" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div>
          Items to clean
        </div>

        <table class="so-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Count</th>
              <th>Enabled</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $mri_siteoptimizer_rows = [
              [ 'db_revisions',      'Post revisions',       'so-db-revisions'     ],
              [ 'db_auto_drafts',    'Auto-draft posts',     'so-db-auto-drafts'   ],
              [ 'db_trash_posts',    'Trashed posts',        'so-db-trash-posts'   ],
              [ 'db_spam_comments',  'Spam comments',        'so-db-spam'          ],
              [ 'db_trash_comments', 'Trashed comments',     'so-db-trash-comments'],
              [ 'db_transients',     'Expired transients',   'so-db-transients'    ],
            ];
            foreach ( $mri_siteoptimizer_rows as $mri_siteoptimizer_row ) : ?>
            <tr>
              <td><?php echo esc_html( $mri_siteoptimizer_row[1] ); ?></td>
              <td><strong id="<?php echo esc_attr( $mri_siteoptimizer_row[2] ); ?>">—</strong></td>
              <td>
                <label class="so-switch">
                  <input type="checkbox" data-setting="<?php echo esc_attr( $mri_siteoptimizer_row[0] ); ?>" <?php checked( get_option( 'mri_siteoptimizer_' . $mri_siteoptimizer_row[0], '1' ), '1' ); ?>>
                  <span class="track"></span>
                </label>
              </td>
            </tr>
            <?php endforeach; ?>
            <tr>
              <td>Orphaned post meta</td>
              <td><strong id="so-db-orphan-meta">—</strong></td>
              <td><span class="so-badge so-badge-info">Auto</span></td>
            </tr>
          </tbody>
        </table>

        <div style="margin-top:16px;display:flex;gap:8px">
          <button class="so-btn so-btn-primary" id="so-btn-optimize-db">Clean &amp; optimize</button>
          <button class="so-btn so-btn-outline" id="so-btn-save-settings">Save settings</button>
        </div>
      </div>
    </div>

    <div>
      <div class="so-card">
        <div class="so-card-title">Schedule</div>
        <div class="so-setting-row">
          <div>
            <div class="so-setting-label">Cleanup frequency</div>
            <div class="so-setting-desc">How often to auto-optimize</div>
          </div>
          <select class="so-select" data-setting="scan_schedule">
            <option value="daily"   <?php selected( get_option( 'mri_siteoptimizer_scan_schedule', 'weekly' ), 'daily' ); ?>>Daily</option>
            <option value="weekly"  <?php selected( get_option( 'mri_siteoptimizer_scan_schedule', 'weekly' ), 'weekly' ); ?>>Weekly</option>
            <option value="monthly" <?php selected( get_option( 'mri_siteoptimizer_scan_schedule', 'weekly' ), 'monthly' ); ?>>Monthly</option>
          </select>
        </div>
        <div style="margin-top:12px">
          <button class="so-btn so-btn-primary so-btn-sm" id="so-btn-save-settings">Save</button>
        </div>
      </div>

      <div class="so-card">
        <div class="so-card-title">What gets optimized</div>
        <ul style="font-size:13px;color:#6B7280;line-height:1.8;padding-left:18px">
          <li>Post revisions older than the latest N</li>
          <li>Auto-draft posts never published</li>
          <li>Posts &amp; comments in trash</li>
          <li>Expired transients (session data)</li>
          <li>Orphaned post meta records</li>
          <li>MySQL table optimization (OPTIMIZE TABLE)</li>
        </ul>
      </div>
    </div>
  </div>
</div>
