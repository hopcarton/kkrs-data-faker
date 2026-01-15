=== KKSR Data Faker ===
Contributors: maisydat
Tags: ratings, reviews, woocommerce, kk-star-ratings, fake-data, simulation
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced data simulation tool for KK Star Ratings and WooCommerce. Boost product credibility with configurable fake ratings and sales data.

== Description ==

**KKSR Data Faker** is a powerful WordPress plugin that helps you simulate ratings, reviews, and sales data for your posts and WooCommerce products. Perfect for new stores that need to build social proof while waiting for real customer reviews.

= Key Features =

* **Fake Star Ratings**: Add simulated star ratings (configurable from 1-5 stars)
* **Fake Vote Counts**: Show fake review/vote counts for products and posts
* **Fake Sales Numbers**: Display simulated sales counts for WooCommerce products
* **Fully Configurable**: Set custom min/max ranges for all fake data via admin panel
* **Smart Thresholds**: Automatically disables fake data when real sales/reviews exceed your threshold
* **Consistent Data**: Same post always gets the same fake numbers (uses post ID as seed)
* **Visual Fixes**: Fixes the 0-star display issue in KK Star Ratings
* **Real + Fake Combination**: Adds fake numbers to your real sales (e.g., 5 real + 200 fake = 205 displayed)
* **Zero Database Impact**: No database modifications, all data is filtered on-the-fly
* **Compatible**: Works seamlessly with KK Star Ratings 5.4+ and WooCommerce

= How It Works =

1. Install and activate the plugin
2. Go to Settings → KKSR Faker
3. Configure your desired ranges:
   - Sales count (default: 100-1000)
   - Vote/review count (default: 100-500)
   - Star ratings (default: 4.0-5.0)
4. Set thresholds for when to stop faking
5. Enable for posts and/or products
6. Done! Visit your products/posts to see the simulated data

= When Does It Stop Faking? =

The plugin automatically stops showing fake data when:
- Real sales count exceeds your threshold (default: 100)
- OR real review count exceeds your threshold (default: 100)

This ensures that once you get genuine traction, the plugin gets out of the way.

= Use Cases =

* New WooCommerce stores building initial credibility
* Migrating from another platform and need placeholder data
* Testing how ratings affect conversion rates
* Demonstrating products before launch

= Important Notes =

⚠️ **This plugin is for testing and demonstration purposes.** Use responsibly and in compliance with applicable laws and platform policies.

⚠️ **Not recommended for production stores** if you want to maintain 100% transparency with customers.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "KKSR Data Faker"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins → Add New → Upload Plugin
4. Choose the downloaded ZIP file and click "Install Now"
5. After installation, click "Activate Plugin"

= Configuration =

1. Go to Settings → KKSR Faker
2. Configure your ranges and thresholds
3. Enable for posts and/or products
4. Save changes

== Frequently Asked Questions ==

= Does this plugin modify my database? =

No! All fake data is filtered on-the-fly using WordPress hooks. Your real data remains untouched.

= What happens to fake data when I get real sales? =

The fake and real numbers are combined. For example: 5 real sales + 200 fake = 205 displayed.

= When does fake data stop showing? =

When real sales OR real reviews exceed your configured thresholds (default: 100 each).

= Is this compatible with the latest WordPress? =

Yes, tested up to WordPress 6.7 and WooCommerce 9.x.

= Does it work with KK Star Ratings? =

Yes! Fully compatible with KK Star Ratings version 5.4 and above.

= Can I set different ranges for different products? =

Currently, the plugin uses global settings for all posts/products. Per-item customization is planned for a future release.

= Will this affect my SEO? =

The plugin uses JavaScript to update some visual elements. Structured data (schema.org) is also filtered, so search engines may see the fake data. Use with caution.

= Is this legal? =

The legality depends on your jurisdiction and use case. Some countries have strict regulations about fake reviews. Always consult local laws and platform policies.

== Screenshots ==

1. Admin settings page - Configure all ranges and thresholds
2. Product page showing fake ratings and sales count
3. Post page with fake KK Star Ratings data
4. Settings sections: Sales, Ratings, and General options

== Changelog ==

= 3.0.0 (2026-01-15) =
* **Major Update**: Complete rewrite with Settings API
* Added: Admin settings page with full customization
* Added: Configurable min/max ranges for all data types
* Added: Enable/disable for posts and products separately
* Added: Configurable thresholds for real data
* Added: Settings validation and sanitization
* Added: Admin CSS styling
* Added: Better i18n support
* Improved: Code structure following WordPress coding standards
* Improved: Security with nonce and input sanitization
* Improved: More meta keys supported for better compatibility
* Fixed: Better handling of edge cases

= 2.2.0 (2025-12-10) =
* Added threshold checking for real sales and votes
* Improved: Better legend text replacement
* Fixed: WooCommerce review count fallback

= 2.1.0 (2025-11-05) =
* Fixed: 0-star display issue in KKSR
* Added: Total score calculation
* Improved: JavaScript injection for better visual consistency

= 2.0.0 (2025-10-01) =
* Initial public release
* Core functionality for fake ratings and sales
* Support for KK Star Ratings and WooCommerce

== Upgrade Notice ==

= 3.0.0 =
Major update with admin settings panel. All previous settings will use default values. Please reconfigure your preferences after updating.

== Privacy Policy ==

This plugin does not collect, store, or transmit any user data. All fake data generation happens locally on your server.

== Support ==

For support, bug reports, or feature requests, please visit:
https://github.com/hupuna/kksr-data-faker

== Credits ==

Developed by MaiSyDat | https://hupuna.com

