=== KKSR Data Faker ===
Contributors: maisydat
Tags: ratings, reviews, woocommerce, kk-star-ratings, traffic, sales
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 4.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Traffic-based ratings and sales counter. Automatically increments KK Star Ratings and WooCommerce sales based on unique visitor views.

== Description ==

**KKSR Data Faker** is a powerful WordPress plugin that automatically increments ratings and sales counts based on real traffic. Perfect for building social proof naturally as visitors browse your site.

= Key Features =

* **Traffic-Based Counting**: Increments based on unique visitors, not random numbers
* **Dual Functionality**: Separate modules for Ratings and Sales
* **Smart Visitor Tracking**: Uses IP + User Agent hash to identify unique visitors
* **Configurable Cool down**: Set how long before same visitor can increment again (default: 7 days)
* **Session Protection**: F5 refresh won't increment counts
* **Random Star Ratings**: Each rating gets random stars (configurable 1-5 range)
* **Protection Threshold**: Stop incrementing when reaching set limits
* **Tabbed Interface**: Separate settings for Ratings and Sales
* **Zero Fake Data**: All increments are tracked in database, no random generation

= How It Works =

**Ratings Module:**
1. Unique visitor views a post/product
2. Plugin adds +1 rating with random stars (e.g., 4-5 stars)
3. Average rating calculated automatically
4. Cooldown prevents same visitor incrementing for X days

**Sales Module:**
1. Unique visitor views a product
2. Plugin adds +1 to total sales
3. WooCommerce displays updated count
4. Cooldown prevents duplicate counting

= When Does It Stop? =

The plugin automatically stops incrementing when:
- Rating count reaches your threshold (default: 100)
- OR sales count reaches your threshold (default: 50)

This ensures controlled growth and prevents unrealistic numbers.

= Configuration =

**Ratings Tab:**
- Auto Increment: Enable/disable
- Cooldown Period: Days before same visitor can rate again
- Protection Threshold: Stop at X ratings
- Min Stars: Minimum stars for fake votes (1-5)
- Max Stars: Maximum stars for fake votes (1-5)

**Sales Tab:**
- Auto Increment: Enable/disable
- Cooldown Period: Days before same visitor counts again
- Protection Threshold: Stop at X sales

= Important Notes =

⚠️ **For demonstration and testing purposes.** Use responsibly and in compliance with applicable laws.

✅ **Database Safe**: Creates separate tracking table, doesn't modify your real data.

✅ **Uninstall Clean**: Removes tracking data but keeps your real ratings and sales.

== Installation ==

= Automatic Installation =

1. Log in to WordPress admin
2. Go to Plugins → Add New
3. Search for "KKSR Data Faker"
4. Click Install → Activate

= Manual Installation =

1. Download plugin ZIP
2. Upload via Plugins → Add New → Upload
3. Activate plugin

= Setup =

1. Go to **KKSR Faker** menu
2. Configure **Ratings** tab settings
3. Configure **Sales** tab settings
4. Save and test!

== Frequently Asked Questions ==

= Does this modify my database? =

Only creates one tracking table `wp_kksr_visitor_log`. Your real ratings and sales data remain untouched.

= What happens when I uninstall =

Plugin settings and tracking table are deleted. Real ratings and sales data are preserved.

= How does visitor tracking work? =

Uses MD5 hash of (IP Address + User Agent) to identify unique visitors.

= Can same visitor increment multiple products? =

Yes! Each product has separate tracking. Same visitor can increment different products.

= Does F5 refresh increment the count? =

No. Session-based prevention blocks same-session increments.

= Is this compatible with caching plugins? =

Yes, works with most caching plugins as counting happens server-side.

== Screenshots ==

1. Tabbed admin interface - Ratings and Sales settings
2. Ratings tab - Configure star ranges and cooldown
3. Sales tab - Configure sales increment behavior
4. Product page showing incremented ratings
5. WooCommerce sales count incrementing with traffic

== Changelog ==

= 4.0.0 (2026-01-16) =
* **MAJOR UPDATE**: Complete rewrite to traffic-based system
* Added: Visitor tracking with IP + User Agent hash
* Added: Cooldown period (default 7 days)
* Added: Session-based F5 prevention
* Added: Tabbed admin interface (Ratings | Sales)
* Added: Random stars (min/max configurable)
* Added: Full KKSR meta compatibility
* Added: Automatic average calculation
* Added: Database table for visitor logs
* Added: Clean uninstall (preserves real data)
* Changed: No more random fake numbers
* Changed: Increments based on real traffic
* Improved: Protection thresholds
* Improved: Better compatibility with KKSR plugin
* Removed: Static fake number generation
* Removed: Min/max sales/votes settings

= 3.0.0 (2026-01-15) =
* Complete rewrite with Settings API
* Admin settings page
* Configurable ranges and thresholds

= 2.0.0 (2025-10-01) =
* Initial release

== Upgrade Notice ==

= 4.0.0 =
MAJOR UPDATE: Plugin now uses traffic-based counting instead of random numbers. Database table will be created on activation. Previous settings will be reset to defaults.

== Privacy Policy ==

This plugin:
- Stores visitor hashes (MD5 of IP + User Agent) for tracking
- Does not collect personally identifiable information
- Data stored locally on your server
- Uninstall removes all tracking data

== Support ==

GitHub: https://github.com/MaiSyDat/kksr-data-faker

== Credits ==

Developed by MaiSyDat | https://hupuna.com
