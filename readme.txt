=== MRI SiteOptimizer ===
Contributors: mdredoyislam
Tags: optimize, image compression, database cleaner, lazy load, performance
Requires at least: 5.6
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean unlinked images, remove unnecessary files, compress images, lazy load, and optimize your WordPress database — all in one lightweight plugin.

== Description ==

MRI SiteOptimizer is a comprehensive WordPress optimization plugin designed to help you keep your site fast and clean.

**Key Features:**

* **Unlinked Image Cleaner** - Automatically detect and safely remove images that aren't used in posts, pages, widgets, or theme files
* **Image Compression** - Automatically compress images on upload with configurable quality levels (lossy or lossless)
* **Lazy Loading** - Add native lazy loading to all images in your content for faster page loads
* **File Cleaner** - Remove orphaned files and temporary junk files (.tmp, .log, .bak) from your uploads folder
* **Database Optimizer** - Clean up post revisions, auto-drafts, spam comments, expired transients, and more
* **Scheduled Tasks** - Set automatic daily, weekly, or monthly scans and cleanup
* **Activity Log** - Track all optimization actions with timestamps and space saved
* **One-Click Operations** - Run full scans or individual cleaners on demand

== Installation ==

1. Upload the `mri-siteoptimizer` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to MRI SiteOptimizer menu to access the dashboard and configure settings

== Usage ==

1. Navigate to **MRI SiteOptimizer** in the WordPress admin menu
2. Review the dashboard to see optimization opportunities
3. Use individual modules (Images, Files, Database) to scan and clean
4. Configure automatic schedules in Settings for hands-off optimization

== Configuration ==

The plugin comes with sensible defaults but can be customized:

* **Image Compression Type** - Choose between lossy (smaller files) or lossless (better quality)
* **Compression Quality** - Set JPEG/WebP quality from 1-100 (recommended: 75-85)
* **Auto-Compress on Upload** - Enable or disable automatic compression of new uploads
* **Lazy Loading** - Enable native HTML lazy loading on images
* **Database Cleanup Options** - Choose which types of database clutter to remove
* **Schedule** - Set optimization frequency (daily, weekly, or monthly)

== Frequently Asked Questions ==

= Is it safe to delete unlinked images? =
Yes. The plugin identifies images that aren't referenced anywhere in your posts, pages, widgets, or theme files. However, we recommend reviewing the list before deletion, just in case.

= Will compression hurt my image quality? =
With lossy compression, you may see minimal quality loss while saving significant space. Lossless compression preserves quality but saves less space. Test on a few images first to find your preferred setting.

= Can I undo deletions? =
Yes, by default deleted items are moved to trash first, allowing you to recover them if needed. You can enable permanent deletion in settings for more aggressive cleanup.

= Does this work with my images? =
MRI SiteOptimizer works with all standard WordPress image formats (JPEG, PNG, WebP). GIF and SVG optimization requires additional tools.

== Support ==

For support, documentation, and feature requests:
* GitHub Issues: https://github.com/mdredoyislam/mri-siteoptimizer/issues
* Documentation: https://github.com/mdredoyislam/mri-siteoptimizer
* WordPress.org Support Forums: Coming soon

== Changelog ==

= 1.0.1 =
* Compatibility tested with WordPress 7.0

= 1.0.0 =
* Initial release with core optimization features
* Unlinked image cleaner
* Image compression with quality controls
* Lazy loading support
* File cleaner for orphaned files
* Database optimization
* Scheduled cleanup tasks
* Activity logging

See CHANGELOG.md in the plugin folder for a detailed changelog.

== Upgrade Notice ==

= 1.0.0 =
Initial release - No upgrades needed.

== License ==

This plugin is released under the GPLv2 or later license.
See the LICENSE file included with the plugin for full details.

== Credits ==

Built with care for WordPress performance optimization.
