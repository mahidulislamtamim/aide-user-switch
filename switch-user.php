<?php
/*
Plugin Name: Aide::Switch User
Description: Allows administrators to switch to any user account and back.
Version: 1.0
Author: Aide247
Version: 1.0.0
Last Updated : "Oct 08, 2025",
Author URI: https://aide247.com/
Text Domain: aideuserswitch
Domain Path: /languages
Plugin URI: https://aide247.com/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access

class Aide_User_Switch {

    public function __construct() {
        add_filter( 'user_row_actions', [ $this, 'add_switch_link' ], 10, 2 );
        add_action( 'admin_init', [ $this, 'handle_switch' ] );
        add_action( 'admin_notices', [ $this, 'show_switch_notice' ] );
    }

    /**
     * Add "Switch To" link to user list table for admins
     */
    public function add_switch_link( $actions, $user ) {
        if ( current_user_can( 'administrator' ) && get_current_user_id() !== $user->ID ) {
            $switch_url = wp_nonce_url(
                add_query_arg( [ 'aide_switch_user' => $user->ID ], admin_url() ),
                'aide_switch_user_' . $user->ID
            );
            $actions['aide_switch'] = '<a target="_blank" href="' . esc_url( $switch_url ) . '">Switch&nbsp;To</a>';
        }
        return $actions;
    }

    /**
     * Handle switch and switch-back actions
     */
    public function handle_switch() {
        // Switch to another user
        if ( isset( $_GET['aide_switch_user'] ) && current_user_can( 'administrator' ) ) {
            $user_id = absint( $_GET['aide_switch_user'] );
            check_admin_referer( 'aide_switch_user_' . $user_id );

            // Store original admin ID for "switch back"
            update_user_meta( $user_id, '_aide_switched_from', get_current_user_id() );

            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );

            wp_safe_redirect( admin_url() );
            exit;
        }

        // Switch back to original admin
        if ( isset( $_GET['aide_switch_back'] ) ) {
            $current_user = wp_get_current_user();
            $admin_id = get_user_meta( $current_user->ID, '_aide_switched_from', true );

            if ( $admin_id ) {
                delete_user_meta( $current_user->ID, '_aide_switched_from' );

                wp_set_current_user( $admin_id );
                wp_set_auth_cookie( $admin_id );

                wp_safe_redirect( admin_url( 'users.php' ) );
                exit;
            }
        }
    }

    /**
     * Show notice when switched to another user
     */
    public function show_switch_notice() {
        $current_user = wp_get_current_user();
        $admin_id = get_user_meta( $current_user->ID, '_aide_switched_from', true );

        if ( $admin_id ) {
            $admin_data = get_userdata( $admin_id );
            $switch_back_url = add_query_arg( 'aide_switch_back', '1', admin_url() );

            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>You are currently logged in as ' . esc_html( $current_user->user_login ) . '.</strong> ';
            echo '(<a href="' . esc_url( $switch_back_url ) . '">Switch back to ' . esc_html( $admin_data->user_login ) . '</a>)</p>';
            echo '</div>';
        }
    }
}

new Aide_User_Switch();
