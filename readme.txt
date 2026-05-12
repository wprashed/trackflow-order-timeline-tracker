=== TrackFlow - Order Time Tracker for WooCommerce ===
Contributors: wprashed
Tags: woocommerce, order status, order tracking, timeline, my account
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn WooCommerce order status text into a clean visual timeline progress bar on customer-facing order screens.

== Description ==

TrackFlow replaces boring plain status text with a visual progress timeline for WooCommerce orders.

Features:

* Replaces the My Account order status column with a compact progress timeline.
* Displays a full timeline on the order details page.
* Displays a full timeline on the order received (thank-you) page.
* Handles common WooCommerce statuses (pending, on-hold, processing, completed, refunded, failed, cancelled).
* Built with WordPress coding standards in mind (sanitized data, escaped output, translation-ready strings).

== Installation ==

1. Upload the `trackflow-order-time-tracker` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Ensure WooCommerce is installed and active.
4. Visit My Account > Orders to see the timeline UI.

== Frequently Asked Questions ==

= Does this work without WooCommerce? =

No. The plugin requires WooCommerce.

= Can I customize the timeline step labels? =

Yes. Developers can filter the steps using `trackflow_timeline_steps`.

== Changelog ==

= 1.0.0 =
* Initial release.
* Visual order timeline for WooCommerce account/order pages.

== Upgrade Notice ==

= 1.0.0 =
Initial stable release.
