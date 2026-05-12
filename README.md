# TrackFlow - Order Time Tracker for WooCommerce

TrackFlow is a WooCommerce plugin that replaces plain order status text with a visual timeline/progress bar.

## Features

- Compact order timeline in **My Account > Orders** status column.
- Full timeline card on the **View Order** page.
- Full timeline card on the **Order Received (Thank You)** page.
- Status-aware visual states for processing, completed, on-hold, refunded, failed, and cancelled orders.
- Translation-ready strings using the `trackflow-order-time-tracker` text domain.

## Requirements

- WordPress 6.0+
- WooCommerce (active)
- PHP 7.4+

## Installation

1. Copy the `trackflow-order-time-tracker` folder to `wp-content/plugins/`.
2. Activate **TrackFlow - Order Time Tracker for WooCommerce** in WordPress admin.
3. Open your WooCommerce customer account pages to verify timeline rendering.

## Developer Notes

- Hook used for orders table status replacement:
  - `woocommerce_my_account_my_orders_column_order-status`
- Hooks used for full timeline rendering:
  - `woocommerce_view_order`
  - `woocommerce_thankyou`
- Filter for timeline labels:
  - `trackflow_timeline_steps`

## Security and Standards

This plugin follows WordPress plugin coding standards by:

- Sanitizing dynamic values before use.
- Escaping all frontend output.
- Keeping user-facing strings translation-ready.

## Changelog

### 1.0.0
- Initial release under the name TrackFlow.
