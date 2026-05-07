<?php
/*
Plugin Name: Aide :: Switch User
Description: Allows administrators to switch to any user account and back.
Version: 1.0.0
Requires at least: 5.8
Requires PHP: 7.4
Author: Aide247
Author URI: https://aide247.com/
Text Domain: aide-user-switch
Domain Path: /languages
Plugin URI: https://aide247.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Main plugin class.
 */
final class Aide_User_Switch {
	/**
	 * Query arg used to switch to a user.
	 */
	private const QUERY_SWITCH = 'aide_switch_user';

	/**
	 * Query arg used to switch back to the original user.
	 */
	private const QUERY_BACK = 'aide_switch_back';

	/**
	 * Meta key stored on the switched-to user indicating the original user ID.
	 */
	private const META_SWITCHED_FROM = '_aide_switched_from';

    public function __construct() {
        add_filter( 'user_row_actions', [ $this, 'add_switch_link' ], 10, 2 );
        add_action( 'admin_init', [ $this, 'handle_switch' ] );
        add_action( 'admin_notices', [ $this, 'show_switch_notice' ] );
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
    }

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'aide-user-switch', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

    /**
     * Add "Switch To" link to user list table for admins
     */
    public function add_switch_link( $actions, $user ) {
		if ( ! is_admin() || ! ( $user instanceof WP_User ) ) {
			return $actions;
		}

		if ( get_current_user_id() === (int) $user->ID ) {
			return $actions;
		}

		// Limit to users who can manage users (admins on single-site; configurable on multisite).
		if ( ! current_user_can( 'list_users' ) || ! current_user_can( 'edit_user', $user->ID ) ) {
			return $actions;
		}

		$switch_url = wp_nonce_url(
			add_query_arg( [ self::QUERY_SWITCH => (int) $user->ID ], admin_url( 'users.php' ) ),
			'aide_switch_user_' . (int) $user->ID
		);

		$actions['aide_switch'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $switch_url ),
			esc_html__( 'Switch to', 'aide-user-switch' )
		);
        return $actions;
    }

    /**
     * Handle switch and switch-back actions
     */
    public function handle_switch() {
		if ( ! is_admin() ) {
			return;
		}

		// Switch to another user.
		if ( isset( $_GET[ self::QUERY_SWITCH ] ) ) {
			if ( ! current_user_can( 'list_users' ) ) {
				return;
			}

			$user_id = absint( wp_unslash( $_GET[ self::QUERY_SWITCH ] ) );
			if ( ! $user_id ) {
				return;
			}

			check_admin_referer( 'aide_switch_user_' . $user_id );

			if ( get_current_user_id() === $user_id ) {
				return;
			}

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return;
			}

			$target_user = get_userdata( $user_id );
			if ( ! $target_user ) {
				return;
			}

			// Store original user ID on the switched-to account for "switch back".
			update_user_meta( $user_id, self::META_SWITCHED_FROM, get_current_user_id() );

			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );

			wp_safe_redirect( admin_url() );
			exit;
		}

		// Switch back to the original user.
		if ( isset( $_GET[ self::QUERY_BACK ] ) ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'aide_switch_back' ) ) {
				return;
			}

			$current_user = wp_get_current_user();
			if ( ! ( $current_user instanceof WP_User ) || ! $current_user->ID ) {
				return;
			}

			$original_user_id = absint( get_user_meta( $current_user->ID, self::META_SWITCHED_FROM, true ) );
			if ( ! $original_user_id ) {
				return;
			}

			// Only allow switching back to a valid user.
			if ( ! get_userdata( $original_user_id ) ) {
				delete_user_meta( $current_user->ID, self::META_SWITCHED_FROM );
				return;
			}

			delete_user_meta( $current_user->ID, self::META_SWITCHED_FROM );

			wp_set_current_user( $original_user_id );
			wp_set_auth_cookie( $original_user_id );

			wp_safe_redirect( admin_url( 'users.php' ) );
			exit;
		}
    }

    /**
     * Show notice when switched to another user
     */
    public function show_switch_notice() {
		if ( ! is_admin() ) {
			return;
		}

		$current_user = wp_get_current_user();
		if ( ! ( $current_user instanceof WP_User ) || ! $current_user->ID ) {
			return;
		}

		$original_user_id = absint( get_user_meta( $current_user->ID, self::META_SWITCHED_FROM, true ) );
		if ( ! $original_user_id ) {
			return;
		}

		$original_user = get_userdata( $original_user_id );
		if ( ! $original_user ) {
			delete_user_meta( $current_user->ID, self::META_SWITCHED_FROM );
			return;
		}

		$switch_back_url = wp_nonce_url(
			add_query_arg( self::QUERY_BACK, '1', admin_url() ),
			'aide_switch_back'
		);

		printf(
			'<div class="notice notice-warning is-dismissible"><p><strong>%s</strong> %s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s is the current username */
					__( 'You are currently logged in as %s.', 'aide-user-switch' ),
					$current_user->user_login
				)
			),
			wp_kses(
				sprintf(
					/* translators: %s is the original username */
					__( '(<a href="%1$s">Switch back to %2$s</a>)', 'aide-user-switch' ),
					esc_url( $switch_back_url ),
					esc_html( $original_user->user_login )
				),
				[ 'a' => [ 'href' => [] ] ]
			)
		);
    }
}

new Aide_User_Switch();
