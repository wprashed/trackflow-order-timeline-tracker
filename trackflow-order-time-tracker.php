<?php
/**
 * Plugin Name:       TrackFlow - Order Time Tracker for WooCommerce
 * Plugin URI:        https://github.com/wprashed/trackflow-order-timeline-tracker
 * Description:       Adds a visual WooCommerce order timeline progress bar in place of plain status text.
 * Version:           1.0.0
 * Author:            Rashed Hossain
 * Author URI:        https://rashed.im/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:  woocommerce
 * Text Domain:       trackflow-order-time-tracker
 * Domain Path:       /languages
 *
 * @package TrackFlow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main TrackFlow Plugin Class.
 */
final class TrackFlow_Order_Time_Tracker {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of this class.
	 *
	 * @var TrackFlow_Order_Time_Tracker|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return TrackFlow_Order_Time_Tracker
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'bootstrap' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
	}

	/**
	 * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}

	/**
	 * Bootstrap plugin and register integrations.
	 */
	public function bootstrap() {
		load_plugin_textdomain( 'trackflow-order-time-tracker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'woocommerce_my_account_my_orders_column_order-status', array( $this, 'render_account_orders_timeline' ), 10 );
		add_action( 'woocommerce_view_order', array( $this, 'render_timeline_by_order_id' ), 8 );
		add_action( 'woocommerce_thankyou', array( $this, 'render_timeline_by_order_id' ), 8 );
	}

	/**
	 * Load frontend CSS only on relevant WooCommerce pages.
	 */
	public function enqueue_assets() {
		if ( ! function_exists( 'is_account_page' ) || ! function_exists( 'is_order_received_page' ) ) {
			return;
		}

		if ( ! is_account_page() && ! is_order_received_page() ) {
			return;
		}

		wp_enqueue_style(
			'trackflow-order-time-tracker',
			plugin_dir_url( __FILE__ ) . 'assets/css/trackflow-order-time-tracker.css',
			array(),
			self::VERSION
		);
	}

	/**
	 * Render compact timeline in My Account order list table.
	 *
	 * @param WC_Order $order WooCommerce order.
	 */
	public function render_account_orders_timeline( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML generated in get_timeline_markup is safely escaped.
		echo $this->get_timeline_markup( $order, true );
	}

	/**
	 * Render full timeline on order details and thank-you pages.
	 *
	 * @param int $order_id Order ID.
	 */
	public function render_timeline_by_order_id( $order_id ) {
		$order = wc_get_order( absint( $order_id ) );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML generated in get_timeline_markup is safely escaped.
		echo $this->get_timeline_markup( $order, false );
	}

	/**
	 * Build timeline HTML.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @param bool     $compact Whether to render compact markup.
	 * @return string
	 */
	private function get_timeline_markup( WC_Order $order, $compact = false ) {
		$steps         = $this->get_steps();
		$meta          = $this->map_order_to_timeline_state( $order );
		$current_index = $this->normalize_step_index( $meta['index'], count( $steps ) );
		$compact       = (bool) $compact;

		$progress_percent = 0;
		if ( count( $steps ) > 1 ) {
			$progress_percent = ( $current_index / ( count( $steps ) - 1 ) ) * 100;
		}

		$wrapper_classes = array(
			'trackflow-timeline',
			'trackflow-state-' . sanitize_html_class( $meta['state'] ),
		);

		if ( $compact ) {
			$wrapper_classes[] = 'trackflow-timeline-compact';
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" role="group" aria-label="<?php esc_attr_e( 'Order timeline', 'trackflow-order-time-tracker' ); ?>">
			<div class="trackflow-track" aria-hidden="true">
				<span class="trackflow-track-fill" style="width: <?php echo esc_attr( (string) round( $progress_percent, 2 ) ); ?>%;"></span>
			</div>
			<ol class="trackflow-steps" aria-hidden="true">
				<?php foreach ( $steps as $step_index => $label ) : ?>
					<?php
					$item_classes = array( 'trackflow-step' );
					if ( $step_index < $current_index ) {
						$item_classes[] = 'is-complete';
					} elseif ( $step_index === $current_index ) {
						$item_classes[] = 'is-current';
					}
					?>
					<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
						<span class="trackflow-dot"></span>
						<span class="trackflow-label"><?php echo esc_html( $label ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
			<div class="trackflow-status-row">
				<span class="trackflow-status-badge"><?php echo esc_html( $meta['badge'] ); ?></span>
				<?php if ( ! $compact ) : ?>
					<span class="trackflow-status-text"><?php echo esc_html( $meta['description'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Get the steps for the timeline.
	 *
	 * @return array<int, string>
	 */
	private function get_steps() {
		$steps = array(
			__( 'Order Placed', 'trackflow-order-time-tracker' ),
			__( 'In Progress', 'trackflow-order-time-tracker' ),
			__( 'Delivered', 'trackflow-order-time-tracker' ),
		);

		$steps = apply_filters( 'trackflow_timeline_steps', $steps );

		if ( ! is_array( $steps ) || empty( $steps ) ) {
			$steps = array(
				__( 'Order Placed', 'trackflow-order-time-tracker' ),
				__( 'In Progress', 'trackflow-order-time-tracker' ),
				__( 'Delivered', 'trackflow-order-time-tracker' ),
			);
		}

		$steps = array_map( 'sanitize_text_field', $steps );

		return array_values( array_filter( $steps ) );
	}

	/**
	 * Map order status to timeline position and presentation state.
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return array<string, mixed>
	 */
	private function map_order_to_timeline_state( WC_Order $order ) {
		$status = sanitize_key( $order->get_status() );
		$label  = wc_get_order_status_name( $status );

		$state = 'active';
		$index = 0;
		$note  = __( 'We have received your order.', 'trackflow-order-time-tracker' );

		switch ( $status ) {
			case 'on-hold':
				$index = 1;
				$state = 'paused';
				$note  = __( 'Your order is waiting for confirmation.', 'trackflow-order-time-tracker' );
				break;
			case 'processing':
				$index = 1;
				$note  = __( 'Your order is being prepared.', 'trackflow-order-time-tracker' );
				break;
			case 'completed':
				$index = 2;
				$state = 'complete';
				$note  = __( 'Your order has been delivered.', 'trackflow-order-time-tracker' );
				break;
			case 'refunded':
				$index = 2;
				$state = 'refunded';
				$note  = __( 'This order was refunded.', 'trackflow-order-time-tracker' );
				break;
			case 'cancelled':
			case 'failed':
				$index = 0;
				$state = 'stopped';
				$note  = __( 'This order is no longer in progress.', 'trackflow-order-time-tracker' );
				break;
			default:
				$index = 0;
				$state = 'active';
				$note  = __( 'We have received your order.', 'trackflow-order-time-tracker' );
				break;
		}

		return array(
			'index'       => $index,
			'state'       => sanitize_key( $state ),
			'badge'       => sprintf(
				/* translators: %s: order status label */
				__( 'Status: %s', 'trackflow-order-time-tracker' ),
				sanitize_text_field( wp_strip_all_tags( $label ) )
			),
			'description' => sanitize_text_field( wp_strip_all_tags( $note ) ),
		);
	}

	/**
	 * Keep step index in valid bounds.
	 *
	 * @param int $index Raw step index.
	 * @param int $steps_count Number of available steps.
	 * @return int
	 */
	private function normalize_step_index( $index, $steps_count ) {
		$index      = absint( $index );
		$max_index  = max( 0, absint( $steps_count ) - 1 );

		return min( $index, $max_index );
	}
}

/**
 * Initialize the plugin.
 */
function trackflow_order_time_tracker_init() {
	TrackFlow_Order_Time_Tracker::instance();
}
add_action( 'plugins_loaded', 'trackflow_order_time_tracker_init' );
