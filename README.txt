=== Wordapp ===
Contributors: Wordapp
Requires at least: 3.5
Tested up to: 4.9.4
Stable tag: 4.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Wordapp Plugin connects your site to the Wordapp Platform.

== Description ==

Wordapp is a free word-processing and publishing platform for e-commerce that lets you manage and control your content creation with a crowd of writers and editors suited to your job.

The Wordapp Plugin connects your site to the Wordapp Platform to allow you to create, translate and optimize online content easily and seamlessly.

For more information please visit http://www.wordapp.io

== Installation ==

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area.
2. Search for 'Wordapp'.
3. Click 'Install Now' and activate the plugin.

For a manual installation via FTP:

1. Upload the `wordapp` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' screen in your WordPress admin area

To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.

Note: This plugin does not have a settings page within WordPress. All settings and configuration are done directly from within the Wordapp Platform.
After installation and activation of plugin in your site, please contact wordapp support to configure and activate plugin in wordapp.

== Changelog ==

= 1.0.0 (2017-05-29): =
* Initial Public Release

= 1.0.1 (2017-06-01): =
* PDX protocol modified
* Added support for custom post types
* Added support for post authors

= 1.0.2 (2017-06-01): =
* Added support for post templates

= 1.0.3 (2017-06-06): =
* Added support for post schedule
* Added support for post category
* Added support for post tags
* Bug fixes

= 1.1.0 (2017-06-12): =
* Added support for HEAD / custom headers restricted environments

= 1.1.1 (2017-06-14): =
* Added support for PHP versions older than 5.6 (removed const string concatenations)

= 1.2.0 (2017-06-14): =
* Added Server IP based option to verify sender
* Security bug fixes

= 1.2.1 (2017-06-21): =
* Support for dual (html / element node based) contents. html has a precedence
* Bug fix with post schedule

= 1.2.2 (2017-06-28): =
* Support for featured image

= 1.2.3 (2017-06-29): =
* Multi-site installation related configuration improvement.

= 1.2.4 (2017-06-29): =
* Removed configuration clean-up after uninstalling, activating and deactivating of plugin

= 1.2.5 (2017-07-05): =
* Bug fix: ensure of content items to be rebuilt by sequence number
* An experimental WP Slug support disabled

= 1.2.6 (2017-07-05): =
* Bug fix: title corrupted

= 1.2.7 (2018-02-26): =
* Settings panel added

= 1.2.8 (2018-03-21): =
* Short code rendered html content is available in content_html field

= 1.2.9 (2018-03-21): =
* Short code rendering support for Visual Composer Plugin
