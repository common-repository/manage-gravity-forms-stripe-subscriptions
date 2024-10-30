<?php

use  Gravity_Forms\Gravity_Forms\Orders\Summaries\GF_Order_Summary ;
use  Gravity_Forms\Gravity_Forms\Orders\Factories\GF_Order_Factory ;
/*
Plugin Name: GravityStripe Subscription Management
description: An easy way for people to manage their stripe subscriptions made through GravityForms. Subscriptions are shown using a shortcode. Even includes an admin shortcode to manage ALL subscriptions and see any overdue subscription payments that failed to process in Stripe.com. Make sure your use the gravity forms registration addon so subscribers can log in and manage their subscriptions.
Author: ConcurrentEQ
Author URI: https://www.gravitystripe.com/
Version: 4.3.2
Tested up to: 6.5.5
*/
define( 'ZZD_GSS_DIR', plugin_dir_path( __FILE__ ) );
define( 'ZZD_GSS_URL', plugin_dir_url( __FILE__ ) );
define( 'ZZD_GSS_VER', '4.3.2' );
if ( !defined( 'IS_DEMO_SITE' ) ) {
    define( 'IS_DEMO_SITE', false );
}
define( 'GSS_LOADER_IMAGE', ZZD_GSS_URL . 'images/loader.gif' );

if ( !function_exists( 'mgfss_fs' ) ) {
    // Create a helper function for easy SDK access.
    function mgfss_fs()
    {
        global  $mgfss_fs ;
        
        if ( !isset( $mgfss_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $mgfss_fs = fs_dynamic_init( array(
                'id'               => '5784',
                'slug'             => 'manage-gravity-forms-stripe-subscriptions',
                'premium_slug'     => 'manage-gravity-forms-stripe-subscriptions-pro',
                'type'             => 'plugin',
                'public_key'       => 'pk_24c3f6e4c971afbf79dc4092c80d2',
                'is_premium'       => false,
                'has_addons'       => true,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'has_affiliation'  => 'all',
                'menu'             => array(
                'first-path' => 'plugins.php',
                'support'    => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $mgfss_fs;
    }
    
    // Init Freemius.
    mgfss_fs();
    // Signal that SDK was initiated.
    do_action( 'mgfss_fs_loaded' );
    function mgfss_fs_settings_url()
    {
        return admin_url( 'admin.php?page=zzd_stripe_subscriptions' );
    }
    
    mgfss_fs()->add_filter( 'connect_url', 'mgfss_fs_settings_url' );
    mgfss_fs()->add_filter( 'after_skip_url', 'mgfss_fs_settings_url' );
    mgfss_fs()->add_filter( 'after_connect_url', 'mgfss_fs_settings_url' );
    mgfss_fs()->add_filter( 'after_pending_connect_url', 'mgfss_fs_settings_url' );
    mgfss_fs()->add_filter( 'pricing_url', 'gss_upgrade_url' );
    function gss_upgrade_url( $url )
    {
        $modified_url = "https://gravitystripe.com";
        return $modified_url;
    }

}

add_filter(
    'plugin_action_links_' . plugin_basename( __FILE__ ),
    'gss_plugin_action_links',
    1,
    2
);
function gss_plugin_action_links( $links )
{
    $url = mgfss_fs()->get_upgrade_url();
    $settings_link = "<a href='{$url}'>" . __( 'Upgrade', 'gravitystripe' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
}

add_action( 'plugins_loaded', 'gss_fn_plugins_loaded', 0 );
function gss_fn_plugins_loaded()
{
    load_plugin_textdomain( 'gravitystripe', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function gss_allowed_access_roles()
{
    $allowed_roles = apply_filters( "gss_allowed_admin_level_roles", array( 'administrator' ) );
    return $allowed_roles;
}

function gss_user_has_access()
{
    $user = $user_roles = array();
    
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        $user_roles = $user->roles;
    } else {
        return false;
    }
    
    if ( array_intersect( gss_allowed_access_roles(), $user_roles ) ) {
        return true;
    }
    return false;
}

add_action( 'wp_enqueue_scripts', 'gss_fn_register_assets', 10 );
add_action( 'admin_enqueue_scripts', 'gss_fn_register_assets', 10 );
function gss_fn_register_assets()
{
    $ver = ZZD_GSS_VER;
    $ver = 4.0 * 1 * rand( 0, 100 );
    wp_register_style(
        'gss-dataTables',
        ZZD_GSS_URL . 'css/jquery.dataTables.min.css',
        array(),
        $ver
    );
    wp_register_style(
        'gss-dataTables-responsive',
        ZZD_GSS_URL . 'css/responsive.dataTables.min.css',
        array(),
        $ver
    );
    wp_register_style(
        'gss-datatables-bootstrap',
        ZZD_GSS_URL . 'css/dataTables.bootstrap.css',
        array(),
        $ver
    );
    wp_register_style(
        'gss-bootstrap-modal',
        ZZD_GSS_URL . 'css/bootstrap.min.css',
        array(),
        $ver
    );
    wp_register_style(
        'gss-custom',
        ZZD_GSS_URL . 'css/custom.css',
        array(),
        $ver
    );
    // wp_register_style( 'gss-jquery-ui', ZZD_GSS_URL . 'css/jquery-ui.css', array(), $ver );
    // wp_register_style( 'gss-jquery-ui-theme', ZZD_GSS_URL . 'css/jquery-ui.theme.css', array(), $ver );
    wp_register_script(
        'gss-dataTables',
        ZZD_GSS_URL . 'js/jquery.dataTables.min.js',
        array( 'jquery' ),
        $ver
    );
    wp_register_script(
        'gss-dataTables-responsive',
        ZZD_GSS_URL . 'js/dataTables.responsive.min.js',
        array( 'jquery' ),
        $ver
    );
    wp_register_script(
        'gss-bootstrap-modal',
        ZZD_GSS_URL . 'js/bootstrap.min.js',
        array( 'jquery' ),
        $ver
    );
    wp_register_script(
        'script_zzd',
        ZZD_GSS_URL . 'js/custom.js',
        array( 'jquery' ),
        $ver
    );
    wp_register_script(
        'gss_pro_payform',
        ZZD_GSS_URL . 'js/jquery.payform.min.js',
        array( 'jquery', 'script_zzd' ),
        $ver
    );
    // wp_register_script( 'gss_pro_sel2', ZZD_GSS_URL . 'js/select2.min.js', array('jquery', 'script_zzd'), $ver );
    wp_register_script(
        'gss-jquery-ui',
        ZZD_GSS_URL . 'js/jquery-ui.js',
        array( 'jquery' ),
        $ver
    );
    wp_localize_script( 'script_zzd', 'gss_variables', array(
        'confirm_cancel'           => __( 'Are you sure you want to cancel the subscription?', 'gravitystripe' ),
        'canceled_label'           => __( 'Canceled', 'gravitystripe' ),
        'refunded_cancelled_label' => __( 'Refunded/Canceled', 'gravitystripe' ),
        'no_amount_refund_warning' => __( 'Please select amount to refund', 'gravitystripe' ),
        'confirm_refund'           => __( 'Are you sure you want to cancel and refund the last subscription payment?', 'gravitystripe' ),
        'wrong_card'               => __( 'Wrong card number', 'gravitystripe' ),
        'wrong_cvv'                => __( 'Wrong CVV', 'gravitystripe' ),
        'select_expiry_month'      => __( 'Please Select Expiry Month', 'gravitystripe' ),
        'select_expiry_year'       => __( 'Please Select Expiry Year', 'gravitystripe' ),
        'please_wait'              => __( 'Please wait', 'gravitystripe' ),
        'please_wait_dots'         => __( 'Please wait...', 'gravitystripe' ),
        'card_updated'             => __( 'Credit Card Updated Successfully', 'gravitystripe' ),
        'admin_url'                => admin_url( 'admin-ajax.php' ),
        'gss_is_admin'             => ( is_admin() ? true : false ),
    ) );
    if ( gss_user_has_access() ) {
        wp_localize_script( 'script_zzd', 'script_zzd_options', array(
            'gss_admin'    => true,
            'gss_is_admin' => ( is_admin() ? true : false ),
        ) );
    }
}

add_action( 'wp_enqueue_scripts', 'gss_fn_load_frontend_aseets' );
function gss_fn_load_frontend_aseets()
{
    wp_enqueue_style( 'gss-dataTables' );
    wp_enqueue_style( 'gss-dataTables-responsive' );
    wp_enqueue_style( 'gss-bootstrap-modal' );
    wp_enqueue_style( 'gss-datatables-bootstrap' );
    wp_enqueue_style( 'gss-jquery-ui' );
    wp_enqueue_style( 'gss-jquery-ui-theme' );
    wp_enqueue_style( 'gss-custom' );
    wp_enqueue_script( 'gss-dataTables' );
    wp_enqueue_script( 'gss-dataTables-responsive' );
    wp_enqueue_script( 'gss-bootstrap-modal' );
    wp_enqueue_script( 'gss-jquery-ui' );
    wp_enqueue_script( 'script_zzd' );
    wp_enqueue_script( 'gss_pro_payform' );
}

add_action( "wp", "gss_fn_validate_required_plugins" );
function gss_fn_validate_required_plugins()
{
    if ( !method_exists( 'GFForms', 'include_payment_addon_framework' ) ) {
        return false;
    }
    if ( !class_exists( 'GF_Stripe_Bootstrap' ) ) {
        return false;
    }
    if ( !function_exists( 'gf_stripe' ) ) {
        return false;
    }
    $gf_stripe = gf_stripe();
    $gs = $gf_stripe->get_plugin_settings();
    if ( !$gs ) {
        return false;
    }
    return true;
}

add_action( 'gform_loaded', 'gss_fn_load_gf_addon', 100 );
function gss_fn_load_gf_addon()
{
    if ( file_exists( ZZD_GSS_DIR . "gravitystripe-transaction-manager.php" ) ) {
        require_once ZZD_GSS_DIR . 'gravitystripe-transaction-manager.php';
    }
    if ( file_exists( ZZD_GSS_DIR . "functions.php" ) ) {
        require_once ZZD_GSS_DIR . 'functions.php';
    }
    if ( !method_exists( 'GFForms', 'include_addon_framework' ) ) {
        return;
    }
    
    if ( class_exists( 'GSS_Functions' ) ) {
        GFAddOn::register( 'GSS_Functions' );
        GSS_Functions::get_instance();
    }

}

add_action( 'wp_ajax_gss_cancel_subscription', 'gss_ajax_fn_cancel_subscription' );
function gss_ajax_fn_cancel_subscription()
{
    
    if ( !isset( $_POST ) && !isset( $_POST['eid'] ) ) {
        echo  0 ;
        exit;
    }
    
    $eid = sanitize_text_field( $_POST['eid'] );
    $cancel_opion = -1;
    if ( isset( $_POST['cancel_opion'] ) && gss_user_has_access() ) {
        $cancel_opion = sanitize_text_field( $_POST['cancel_opion'] );
    }
    
    if ( class_exists( 'GSS_Functions' ) ) {
        $object = GSS_Functions::get_instance();
        $return_value = $object->gss_ajax_fn_cancel_subscription_pro( $eid, $cancel_opion );
        echo  $return_value ;
        exit;
    }
    
    $lead = GFAPI::get_entry( $eid );
    $feed = ( is_wp_error( $lead ) || !function_exists( 'gf_stripe' ) ? false : gf_stripe()->get_payment_feed( $lead ) );
    
    if ( is_array( $feed ) && rgar( $feed, 'addon_slug' ) == 'gravityformsstripe' && gf_stripe()->cancel( $lead, $feed ) ) {
        gf_stripe()->cancel_subscription( $lead, $feed );
        $return_value = "1";
    }
    
    echo  $return_value ;
    exit;
}

add_action(
    "gform_subscription_payment_failed",
    "gss_fn_gform_subscription_payment_failed",
    10,
    2
);
function gss_fn_gform_subscription_payment_failed( $entry, $subscription_id )
{
    $meta_value = "";
    $meta_key = 'fail_count';
    $subscription_id = $entry['transaction_id'];
    $entry_id = $entry['id'];
    $meta_value = gform_get_meta( $entry_id, $meta_key );
    
    if ( !$meta_value ) {
        $meta_value = 1;
        gform_update_meta( $entry_id, $meta_key, $meta_value );
    } else {
        $meta_value++;
        gform_update_meta( $entry_id, $meta_key, $meta_value );
    }
    
    do_action(
        "gss_after_fail_attempt",
        $meta_value,
        $entry,
        $subscription_id
    );
}

add_action(
    "gform_subscription_canceled",
    "gss_fn_gform_subscription_canceled",
    10,
    3
);
function gss_fn_gform_subscription_canceled( $entry, $feed, $subscription_id )
{
    $meta_value = "";
    $meta_key = 'canceled_at';
    $entry_id = $entry['id'];
    $meta_value = date( 'm/d/Y' );
    gform_update_meta( $entry_id, $meta_key, $meta_value );
    $form = GFAPI::get_form( $entry['form_id'] );
    do_action( "gss_after_subscription_cancelled", $entry, $form );
}

function gss_fn_trim_values_to_print( $value, $length = 13 )
{
    if ( strlen( $value ) <= $length ) {
        return $value;
    }
    return substr( $value, 0, $length ) . "...";
}

add_action(
    'gform_stripe_customer_after_create',
    'gss_fn_add_stripe_customer_id',
    10,
    4
);
function gss_fn_add_stripe_customer_id(
    $customer,
    $feed,
    $entry,
    $form
)
{
    if ( is_user_logged_in() ) {
        gform_update_meta( $entry['id'], '_stripe_customer_id', $customer->id );
    }
}

add_action( 'admin_notices', 'gss_fn_admin_notice' );
function gss_fn_admin_notice()
{
    $show = false;
    if ( mgfss_fs()->is_not_paying() ) {
        $show = true;
    }
    
    if ( isset( $_GET['show_notices'] ) ) {
        delete_transient( 'gss-notice' );
        $show = true;
    }
    
    
    if ( !gss_fn_validate_required_plugins() ) {
        ?>
		<div id="gss-notice-error" class="gss-notice-error notice notice-error">
			<div class="notice-container">
				<span> <?php 
        echo  __( "GravityStripe Subscription Management Needs GravityForms and GravityForms Stripe Active and Configured.", 'gravitystripe' ) ;
        ?></span>
			</div>
		</div>
		<?php 
    } else {
        
        if ( gss_fn_validate_required_plugins() && $show && false == get_transient( 'gss-notice' ) && current_user_can( 'install_plugins' ) ) {
            ?>
    <div id="gss-notice" class="gss-notice notice is-dismissible ">
		<div class="notice-container">
			<div class="notice-image">
				<img src="<?php 
            echo  ZZD_GSS_URL ;
            ?>/images/icon.png" class="custom-logo" alt="GSS">
			</div> 
			<div class="notice-content">
				<div class="notice-heading">
					<?php 
            echo  __( 'Hello! Seems like you have used GravityStripe Subscription Management on this website — Thanks a ton!', 'gravitystripe' ) ;
            ?>
				</div>
				<?php 
            echo  __( 'Please checkout pro version for additional features like refund, upgrade, downgrade, Update card, Auto cancel etc.', 'gravitystripe' ) ;
            ?><br>
				<div class="gss-review-notice-container">
					<a href="<?php 
            echo  mgfss_fs()->get_upgrade_url() ;
            ?>" class="gss-review-notice button-primary" target="_blank">
					<?php 
            echo  __( "Upgrade to Pro", 'gravitystripe' ) ;
            ?>
					</a>
					<a href="https://gformsdemo.com/gravitystripe-demo/" class="gss-review-notice button-primary" style="margin-left: 10px;" target="_blank">
					<?php 
            echo  __( "See The Demo", 'gravitystripe' ) ;
            ?> 
					</a>
				<span class="dashicons dashicons-smiley"></span>
					<a href="#" class="gss-notice-close notice- gss-review-notice">
					<?php 
            echo  __( "Dismiss", 'gravitystripe' ) ;
            ?>
					</a>
				</div>
			</div>				
		</div>
	</div>
    <?php 
        }
    
    }
    
    echo  '<style>.notice-container{padding-top:10px;padding-bottom:10px;display:flex;justify-content:left;align-items:center;}.notice-image img{max-width:90px;}.notice-content{margin-left:15px;}.notice-content.notice-heading{padding-bottom:5px;}.gss-review-notice-container a{padding-left:5px;text-decoration:none;}.gss-review-notice-container{display:flex;align-items:center;padding-top:10px;}.gss-review-notice-container.dashicons{font-size:1.4em;padding-left:10px;}</style>' ;
}

add_action( 'wp_ajax_gss-notice-dismiss', 'gss_ajax_fn_dismiss_notice' );
function gss_ajax_fn_dismiss_notice()
{
    $notice_id = ( isset( $_POST['notice_id'] ) ? sanitize_key( $_POST['notice_id'] ) : '' );
    $repeat_notice_after = 60 * 60 * 24 * 30;
    
    if ( !empty($notice_id) ) {
        if ( !empty($repeat_notice_after) ) {
            set_transient( $notice_id, true, $repeat_notice_after );
        }
        wp_send_json_success();
    }

}

function gss_next_subscription_renewal_date( $subscription_id, $entry )
{
    $gf_stripe = gf_stripe();
    $date_format = get_option( 'date_format' );
    $date_format = ( $date_format != "" ? $date_format : 'm/d/y' );
    $entry_id = $entry['id'];
    $gss_next_period_start = gform_get_meta( $entry_id, "gss_next_period_start" );
    $fetch_and_update = true;
    if ( is_numeric( $gss_next_period_start ) && time() <= $gss_next_period_start ) {
        $fetch_and_update = false;
    }
    if ( $fetch_and_update && $gss_next_period_start != "none" ) {
        try {
            $subscription = $gf_stripe->get_subscription( $subscription_id );
            $gss_next_period_start = ( $subscription->current_period_end ? $subscription->current_period_end : "none" );
            gform_update_meta( $entry_id, "gss_next_period_start", $gss_next_period_start );
        } catch ( \Exception $e ) {
            $gss_next_period_start = $e->getMessage();
        }
    }
    if ( is_numeric( $gss_next_period_start ) ) {
        $gss_next_period_start = date( $date_format, $gss_next_period_start );
    }
    return $gss_next_period_start;
}

function gss_subscription_start_date( $subscription_id, $entry )
{
    $gf_stripe = gf_stripe();
    $date_format = get_option( 'date_format' );
    $date_format = ( $date_format != "" ? $date_format : 'm/d/y' );
    $entry_id = $entry['id'];
    $gss_trial_end_time = gform_get_meta( $entry_id, "gss_trial_end_time" );
    if ( $gss_trial_end_time != "none" ) {
        try {
            $subscription = $gf_stripe->get_subscription( $subscription_id );
            $gss_trial_end_time = ( $subscription->trial_end ? $subscription->trial_end : "none" );
            gform_update_meta( $entry_id, "gss_trial_end_time", $gss_trial_end_time );
        } catch ( \Exception $e ) {
            echo  $e->getMessage() ;
            exit;
        }
    }
    
    if ( is_numeric( $gss_trial_end_time ) ) {
        $gss_trial_end = date( $date_format, $gss_trial_end_time );
    } else {
        $old_date_timestamp = strtotime( $entry['date_created'] );
        $gss_trial_end = date( $date_format, $old_date_timestamp );
    }
    
    return $gss_trial_end;
}

function gss_fn_get_total_entries_count( $form_ids = 0, $is_admin = false, $search_value = '' )
{
    $search_criteria = array();
    if ( !$is_admin || IS_DEMO_SITE ) {
        $search_criteria['field_filters'][] = array(
            'key'   => 'created_by',
            'value' => get_current_user_id(),
        );
    }
    $search_criteria['status'] = 'active';
    $search_criteria['field_filters'][] = array(
        'key'      => 'transaction_id',
        'value'    => 'sub_',
        'operator' => 'CONTAINS',
    );
    $search_criteria['field_filters'][] = array(
        'key'   => 'transaction_type',
        'value' => '2',
    );
    if ( $search_value ) {
        $search_criteria['field_filters'][] = array(
            'key'      => '0',
            'value'    => $search_value,
            'operator' => 'contains',
        );
    }
    $form_entries = GFAPI::count_entries( $form_ids, $search_criteria );
    return $form_entries;
}

function gss_fn_get_entries(
    $form_ids = 0,
    $is_admin = false,
    $limit = 10,
    $offset = 0,
    $search_value = ""
)
{
    $date_format = get_option( 'date_format' );
    $date_format = ( $date_format != "" ? $date_format : 'm/d/y' );
    $gf_stripe = gf_stripe();
    $gf_stripe->include_stripe_api();
    if ( !class_exists( '\\Stripe\\Stripe' ) ) {
        require_once $gf_stripe->get_base_path() . '/includes/autoload.php';
    }
    
    if ( gss_fn_validate_required_plugins() ) {
        $results = array();
        
        if ( is_array( $form_ids ) || $form_ids == 0 ) {
            $search_criteria = array();
            $sorting = array();
            $paging = array(
                'offset'    => $offset,
                'page_size' => $limit,
            );
            if ( !$is_admin || IS_DEMO_SITE ) {
                $search_criteria['field_filters'][] = array(
                    'key'   => 'created_by',
                    'value' => get_current_user_id(),
                );
            }
            $search_criteria['status'] = 'active';
            $search_criteria['field_filters'][] = array(
                'key'      => 'transaction_id',
                'value'    => 'sub_',
                'operator' => 'CONTAINS',
            );
            $search_criteria['field_filters'][] = array(
                'key'   => 'transaction_type',
                'value' => '2',
            );
            if ( $search_value ) {
                $search_criteria['field_filters'][] = array(
                    'key'      => '0',
                    'value'    => $search_value,
                    'operator' => 'contains',
                );
            }
            // echo "<Pre>"; print_r($search_criteria); echo "</pre>";
            $form_entries = GFAPI::get_entries(
                $form_ids,
                $search_criteria,
                $sorting,
                $paging
            );
            foreach ( $form_entries as $entry ) {
                
                if ( isset( $entry['transaction_id'] ) && $entry['transaction_id'] != "" && strpos( $entry['transaction_id'], 'sub_' ) !== false ) {
                    $result = array();
                    $form = GFAPI::get_form( $entry['form_id'] );
                    $entry_id = $entry['id'];
                    $additional_info = "";
                    $meta_key = 'canceled_at';
                    
                    if ( strtolower( $entry['payment_status'] ) == "cancelled" ) {
                        $additional_info = gform_get_meta( $entry_id, $meta_key );
                        if ( !$additional_info ) {
                            $additional_info = "";
                        }
                    }
                    
                    $feed = gf_stripe()->get_payment_feed( $entry );
                    $order_data = gf_stripe()->get_order_data( $feed, $form, $entry );
                    $user_id = $entry['created_by'];
                    $username = __( 'Anonymous', 'gravitystripe' );
                    $email = "";
                    if ( isset( $feed['meta']['customerInformation_email'] ) && $feed['meta']['customerInformation_email'] != "" ) {
                        $email = $entry[$feed['meta']['customerInformation_email']];
                    }
                    
                    if ( $user_id ) {
                        $userdata = get_user_by( "id", $user_id );
                        $username = $userdata->user_nicename;
                        $name = get_user_meta( $user_id, 'first_name', true );
                        $name .= " " . get_user_meta( $user_id, 'last_name', true );
                        if ( trim( $name ) != "" ) {
                            $username = $name;
                        }
                        $email = ( !$email ? $userdata->user_email : $email );
                    }
                    
                    
                    if ( isset( $feed['meta'] ) && isset( $feed['meta']['metaData'] ) && is_array( $feed['meta']['metaData'] ) ) {
                        $create_fname = $create_lname = $create_name = "";
                        foreach ( $feed['meta']['metaData'] as $metadata ) {
                            
                            if ( strtolower( $metadata['custom_key'] ) == "first_name" || strtolower( $metadata['custom_key'] ) == "first name" ) {
                                if ( isset( $entry[$metadata['value']] ) ) {
                                    $create_fname = $entry[$metadata['value']];
                                }
                            } else {
                                
                                if ( strtolower( $metadata['custom_key'] ) == "last_name" || strtolower( $metadata['custom_key'] ) == "last name" ) {
                                    if ( isset( $entry[$metadata['value']] ) ) {
                                        $create_lname = $entry[$metadata['value']];
                                    }
                                } else {
                                    if ( strtolower( $metadata['custom_key'] ) == "full_name" || strtolower( $metadata['custom_key'] ) == "full name" || strtolower( $metadata['custom_key'] ) == "name" ) {
                                        if ( isset( $entry[$metadata['value']] ) ) {
                                            $create_name = $entry[$metadata['value']];
                                        }
                                    }
                                }
                            
                            }
                        
                        }
                        if ( $create_name == "" ) {
                            $create_name = $create_fname . " " . $create_lname;
                        }
                        if ( trim( $create_name ) != "" ) {
                            $username = trim( $create_name );
                        }
                    }
                    
                    $subscription_name = GFCommon::replace_variables(
                        $feed['meta']['subscription_name'],
                        $form,
                        $entry,
                        false,
                        true,
                        true,
                        'text'
                    );
                    $length = $feed['meta']['billingCycle_length'] . " " . $feed['meta']['billingCycle_unit'];
                    
                    if ( $feed['meta']['billingCycle_length'] > 1 ) {
                        $length = $feed['meta']['billingCycle_length'] . " " . $feed['meta']['billingCycle_unit'] . "s";
                    } else {
                        $length = $feed['meta']['billingCycle_unit'];
                    }
                    
                    $old_date_timestamp = strtotime( $entry['date_created'] );
                    $new_date = date( $date_format, $old_date_timestamp );
                    $gss_renewal_date = "-";
                    $amount = "\$" . number_format( floatval( $order_data['payment_amount'] ), 2 );
                    $status = strtolower( $entry['payment_status'] );
                    $additional_info = ( $additional_info != "" && $additional_info ? "(" . $additional_info . ")" : "" );
                    
                    if ( $status == "active" ) {
                        $new_date = gss_subscription_start_date( $entry['transaction_id'], $entry );
                        $gss_renewal_date = gss_next_subscription_renewal_date( $entry['transaction_id'], $entry );
                    }
                    
                    $action = array(
                        'cancel_subscription'         => array(),
                        'upgrade_subscription'        => array(),
                        'downgrade_subscription'      => array(),
                        'update_card_of_subscription' => array(),
                        'refund_subscription'         => array(),
                    );
                    
                    if ( $status == "active" ) {
                        $data = array(
                            "eid" => $entry['id'],
                        );
                        $action['cancel_subscription'] = array(
                            "class"     => "cancel_subscription",
                            "url"       => 'javascript:void(0);',
                            "link_text" => __( "Cancel", "gravitystripe" ),
                            "data"      => $data,
                        );
                    } else {
                        $action['cancel_subscription'] = "";
                    }
                    
                    
                    if ( true ) {
                        $currency = $entry['currency'];
                        $currency = new RGCurrency( $currency );
                        $amount = $currency->to_money( $order_data['payment_amount'] );
                    }
                    
                    
                    if ( $status == 'failed' ) {
                        $failed_times = gform_get_meta( $entry_id, "fail_count" );
                        if ( $failed_times ) {
                            $additional_info = "(" . $failed_times . ")";
                        }
                    }
                    
                    $amount = $amount . " " . __( "per", 'gravitystripe' ) . " " . $length;
                    $result['gss_entry_id'] = $entry_id;
                    $result['gss_username'] = $username;
                    $result['gss_email'] = $email;
                    $result['gss_subscription_name'] = $subscription_name;
                    $result['gss_new_date'] = $new_date;
                    $result['gss_renewal_date'] = $gss_renewal_date;
                    $result['gss_amount'] = $amount;
                    $result['gss_status'] = strtolower( $entry['payment_status'] );
                    $result['gss_status_value'] = $entry['payment_status'] . " " . $additional_info;
                    $result['gss_additional_info'] = $additional_info;
                    $result['gss_action'] = $action;
                    $result['gss_form_title'] = $form['title'];
                    $result['original_entry'] = $entry;
                    $options = apply_filters(
                        "gss_additional_options_to_values",
                        array(),
                        $result,
                        $entry,
                        $form,
                        $is_admin
                    );
                    $result = apply_filters(
                        "gss_set_result_array_values",
                        $result,
                        $entry,
                        $form,
                        $is_admin,
                        $feed,
                        $options
                    );
                    $results[$entry_id] = $result;
                }
            
            }
        }
        
        $results = apply_filters( "gss_subscription_entries", $results );
        return $results;
    }
    
    return false;
}

add_shortcode( "user-subscriptions", "gss_fn_user_subscriptions" );
function gss_fn_user_subscriptions( $atts, $content = null )
{
    ob_start();
    $html = "";
    $args = shortcode_atts( array(
        'form_ids'          => 0,
        'per_page'          => 10,
        'not_found_message' => __( "No Active Subscriptions", 'gravitystripe' ),
    ), $atts );
    if ( !$args['form_ids'] ) {
        $args['form_ids'] = 0;
    }
    $columns = gss_user_table_columns();
    $per_page = apply_filters( "gss_user_list_per_page", $args['per_page'], $args );
    $not_found_message = apply_filters( "gss_user_list_not_found_message", $args['not_found_message'], $args );
    
    if ( !gss_fn_validate_required_plugins() ) {
        $html = __( "GravityForm and Stripe Addon Both plugin need to be active to view list.", 'gravitystripe' );
    } else {
        
        if ( ($args['form_ids'] != "" || $args['form_ids'] == 0) && is_user_logged_in() ) {
            
            if ( $args['form_ids'] != 0 ) {
                $form_ids = explode( ",", $args['form_ids'] );
            } else {
                $form_ids = 0;
            }
            
            
            if ( is_array( $form_ids ) || $form_ids == 0 ) {
                // $entry_results = gss_fn_get_entries( $form_ids, false  );
                $entry_results = array();
                $args = array(
                    "per_page"          => $per_page,
                    "not_found_message" => $not_found_message,
                    "feed"              => $form_ids,
                    "table"             => "user-list",
                );
                $html = "<div class='table-responsive  list-gss'>";
                $table_header = gss_fn_create_table_header(
                    $columns,
                    $entry_results,
                    "frontend_user",
                    $args
                );
                $table_body = "";
                //gss_fn_create_table_body( $columns, $entry_results, "frontend_user", $args );
                $table_footer = gss_fn_create_table_footer(
                    $columns,
                    $entry_results,
                    "frontend_user",
                    $args
                );
                $html .= $table_header . $table_body . $table_footer;
                $html .= "</div>";
            }
        
        } else {
            
            if ( !is_user_logged_in() ) {
                global  $wp ;
                $login_message = __( 'Please Login to see your subscriptions.' );
                $login_message = apply_filters( "gss_not_loggedin_message", $login_message );
                $login_link = wp_login_url( home_url( add_query_arg( array(), $wp->request ) ) );
                $html = "<div class='nothing-found'><a href='{$login_link}' class='login-link'>" . $login_message . "</a></div>";
            } else {
                $html = "<div class='nothing-found'>" . __( 'No Form ID Specified' ) . "</div>";
            }
        
        }
    
    }
    
    echo  $html ;
    do_action( "gss_action_after_shortcode_rendered" );
    return ob_get_clean();
}

add_shortcode( "subscription-list", "gss_fn_subscriptions_list" );
function gss_fn_subscriptions_list( $atts, $content = null )
{
    ob_start();
    $html = "";
    $args = shortcode_atts( array(
        'form_ids'          => 0,
        'per_page'          => 10,
        'not_found_message' => __( "No Active Subscriptions", 'gravitystripe' ),
    ), $atts );
    if ( !$args['form_ids'] ) {
        $args['form_ids'] = 0;
    }
    $per_page = apply_filters( "gss_user_list_per_page", $args['per_page'], $args );
    $not_found_message = apply_filters( "gss_user_list_not_found_message", $args['not_found_message'], $args );
    $columns = gss_admin_frontend_table_columns();
    
    if ( !gss_fn_validate_required_plugins() ) {
        $html = __( "GravityForm and Stripe Addon Both plugin need to be active to view list.", 'gravitystripe' );
    } else {
        
        if ( ($args['form_ids'] != "" || $args['form_ids'] == 0) && is_user_logged_in() && gss_user_has_access() ) {
            
            if ( $args['form_ids'] != 0 ) {
                $form_ids = explode( ",", $args['form_ids'] );
            } else {
                $form_ids = 0;
            }
            
            
            if ( is_array( $form_ids ) || $form_ids == 0 ) {
                // $entry_results = gss_fn_get_entries( $form_ids, false  );
                $entry_results = array();
                $args = array(
                    "per_page"          => $per_page,
                    "not_found_message" => $not_found_message,
                    "feed"              => $form_ids,
                    "table"             => "admin-list",
                );
                $html = "<div class='table-responsive  list-gss'>";
                $table_header = gss_fn_create_table_header(
                    $columns,
                    $entry_results,
                    "frontend_admin",
                    $args
                );
                $table_body = "";
                //gss_fn_create_table_body( $columns, $entry_results, "frontend_admin", $args );
                $table_footer = gss_fn_create_table_footer(
                    $columns,
                    $entry_results,
                    "frontend_admin",
                    $args
                );
                $html .= $table_header . $table_body . $table_footer;
                $html .= "</div>";
            }
        
        } else {
            
            if ( is_user_logged_in() && !gss_user_has_access() ) {
                $html = "<div class='nothing-found'> " . __( "You do not have sufficient permission to access this area.", 'gravitystripe' ) . "</div>";
            } else {
                
                if ( !is_user_logged_in() ) {
                    global  $wp ;
                    $login_message = __( 'Please Login to see your subscriptions.' );
                    $login_message = apply_filters( "gss_not_loggedin_message", $login_message );
                    $login_link = wp_login_url( home_url( add_query_arg( array(), $wp->request ) ) );
                    $html = "<div class='nothing-found'><a href='{$login_link}' class='login-link'>" . $login_message . "</a></div>";
                } else {
                    $html = "<div class='nothing-found'>" . __( 'No Form ID Specified' ) . "</div>";
                }
            
            }
        
        }
    
    }
    
    echo  $html ;
    do_action( "gss_action_after_shortcode_rendered" );
    return ob_get_clean();
}

function gss_fn_create_table_properties( $type )
{
    $properties = array(
        "classes" => "table dt-responsive user-subscriptions",
        "id"      => "user-subscriptions",
    );
    
    if ( $type == "frontend_admin" ) {
        $properties = array(
            "classes" => "table dt-responsive admin-list",
            "id"      => "admin-list",
        );
    } else {
        if ( $type == "backend_admin" ) {
            $properties = array(
                "classes" => "table dt-responsive wp-admin-list",
                "id"      => "wpadmin-list",
            );
        }
    }
    
    return apply_filters( "gss_table_properties", $properties, $type );
}

function gss_fn_create_table_header(
    $columns,
    $entry_results,
    $type,
    $args
)
{
    $content = "";
    $table_properties = gss_fn_create_table_properties( $type );
    $table_class = $table_properties['classes'];
    $table_id = $table_properties['id'];
    extract( $args );
    $data_attributes = " data-columns = '" . json_encode( $columns ) . "' ";
    foreach ( $args as $key => $arg ) {
        
        if ( $key == 'feed' ) {
            
            if ( is_array( $arg ) ) {
                $data_attributes .= " data-" . $key . " = '" . implode( ',', $arg ) . "'";
            } else {
                $data_attributes .= " data-" . $key . " = '" . $arg . "'";
            }
        
        } else {
            $data_attributes .= " data-" . $key . " = '" . $arg . "'";
        }
    
    }
    $suffix_class = "";
    if ( $type == "backend_admin" ) {
        // $suffix_class = "_no_ajax";
    }
    $content .= "<table id='{$table_id}' class='{$table_class} gss_table{$suffix_class}' cellspacing='0' width='100%' {$data_attributes} >";
    $content .= '<thead>';
    $content .= '<tr>';
    foreach ( $columns as $index_key => $column ) {
        extract( $column );
        $content .= "<th class='{$col_class}'>{$col_label}</th>";
    }
    $content .= '</tr>';
    $content .= '</thead>';
    return $content;
}

function gss_fn_create_table_footer(
    $columns,
    $entry_results,
    $type,
    $args
)
{
    $content = "</table>";
    return $content;
}

function gss_fn_create_table_body(
    $columns,
    $entry_results,
    $type,
    $args
)
{
    $content = "";
    extract( $args );
    $content .= '<tbody>';
    foreach ( $entry_results as $result ) {
        $content .= '<tr class="gss_item">';
        foreach ( $columns as $index_key => $column ) {
            extract( $column );
            $cell_class = $col_class . " " . $index_key;
            if ( $index_key == "gss_status" ) {
                $cell_class .= " " . $result[$index_key];
            }
            $cell_value = $result[$col_key];
            $data_tooltip_attribute = "";
            
            if ( $allow_trim ) {
                $data_tooltip_attribute = " data-tooltip='{$cell_value}' ";
                $cell_value = gss_fn_trim_values_to_print( $cell_value, $trim_length );
                $cell_value = "<a href='javascript:void(0);' {$data_tooltip_attribute} data-tooltip-location='right'> " . $cell_value . "\t </a>";
            }
            
            $cell_value = apply_filters(
                "gss_result_value",
                $cell_value,
                $col_key,
                $result
            );
            $content .= "<td class='{$cell_class}'>{$cell_value}</td>";
        }
        $content .= '</tr>';
    }
    $content .= '</tbody>';
    return $content;
}

function gss_fn_stripe_subscriptions_page()
{
    
    if ( !gss_fn_validate_required_plugins() ) {
        $html = '<div class="wrap">
				<h1>' . __( "Subscriptions", 'gravitystripe' ) . '</h1>
				
				<h3>' . __( "Getting started with GravityStripe", 'gravitystripe' ) . '</h3>
				<div style="margin: 20px 0"> 
                <p> ' . __( "GravityStripe requires both GravityForms and the GravityForms Stripe Add-on in order to function. Links to those are below: 
                </br></br>
                <a href='https://www.gravityforms.com/pricing/' target='_blank'>GravityForms (Pro or Elite plans) </a></br>
                <a href='https://www.gravityforms.com/add-ons/stripe/' target='_blank'>Stripe Add on (Free) </a></br>
                <p>To access this page, both of the above plugins must be setup. For instructions on how to set these plugins up and more, go to 
                <a href='https://gravitystripe.com/how-to-set-up-a-subscription-form-in-wordpress-so-users-can-self-manage-their-subscriptions/' target='_blank'>gravitystripe.com</a>
                </p>
                ", 'gravitystripe' ) . ' </p>
                <insert video>
                <insert instructions?>


                    </div>';
        $html .= '</div>';
    } else {
        $form_html = gss_fn_admin_table_subscriptions();
        $html = '<div class="wrap">
				<h1>' . __( "Stripe Subscriptions", 'gravitystripe' ) . '</h1>
				
				' . $form_html . '
				
				
				<div style="margin: 20px 0"> 
					<p> ' . __( "Let user manage their subscription by adding this shortcode to a page:", 'gravitystripe' ) . ' <strong> <br>[user-subscriptions form_ids=1,2,3] </strong> </p>
				</div>
				
				<div> 
					<p> ' . __( "Display all subscriptions on a page (admin view only) with this shortcode:", 'gravitystripe' ) . '<strong><br> [subscription-list form_ids=1,2,3] </strong> </p>
				</div>
				
				<div> 
					<p> ' . __( "*Note: Replace the id numbers with the Gravity Form's form id or multiple form id's. Not sure how to find it?", 'gravitystripe' ) . '<strong><br> <a href="https://youtu.be/aI9Iyjz8afQ" target="_blank">Watch this video</a></strong> </p>
				</div>
				
				';
        $html .= '</div>';
    }
    
    echo  $html ;
}

add_action( 'admin_menu', 'gss_fn_wp_admin_navigation' );
function gss_fn_wp_admin_navigation()
{
    add_menu_page(
        __( 'GravityStripe', 'gravitystripe' ),
        __( 'GravityStripe', 'gravitystripe' ),
        'manage_options',
        'zzd_stripe_subscriptions',
        'gss_fn_stripe_subscriptions_page',
        'dashicons-gs',
        19
    );
    $call_back = 'gss_fn_upgrade_page_transaction';
    add_submenu_page(
        'zzd_stripe_subscriptions',
        __( 'Subscriptions', 'gravitystripe' ),
        __( 'Subscriptions', 'gravitystripe' ),
        'manage_options',
        'zzd_stripe_subscriptions',
        'gss_fn_stripe_subscriptions_page'
    );
    add_submenu_page(
        'zzd_stripe_subscriptions',
        __( 'Single Transactions', 'gravitystripe' ),
        __( 'Single Transactions', 'gravitystripe' ),
        'manage_options',
        'zzd_stripe_transactions',
        $call_back
    );
    global  $submenu ;
    $menu_slug = 'zzd_stripe_subscriptions';
    $submenu[$menu_slug][PHP_INT_MAX] = array(
        'Upgrade to pro',
        'manage_options',
        mgfss_fs()->get_upgrade_url(),
        'Upgrade to pro',
        'mgfs-upgrade-to-pro'
    );
}

add_action( 'admin_head', 'gss_icon_css' );
function gss_icon_css()
{
    echo  '
		<style>
			.dashicons-gs {
				background-image: url("' . ZZD_GSS_URL . 'images/gravity-stripe-wp-menu_logo.png");
				background-repeat: no-repeat;
				background-position: center; 
				background-size: 20px;
				opacity: 1;
			}
			.wp-menu-open .dashicons-flame, #adminmenu li:hover .dashicons-flame{
				opacity: 1;
			}
		</style>
		
		<style>a.sd-upgrade-to-pro { background-color: #00a32a !important; color: #fff !important; font-weight: 600 !important; }</style>
	' ;
}

// add_filter( "gform_addon_navigation", "gss_fn_gform_addon_navigation", 100, 1 );
function gss_fn_gform_addon_navigation( $addon_menus )
{
    $addon_menus[] = array(
        'label'      => __( 'Stripe Subscriptions', 'gravitystripe' ),
        'permission' => 'update_plugins',
        'name'       => 'zzd_stripe_subscriptions',
        'callback'   => 'gss_fn_stripe_subscriptions_page',
    );
    return $addon_menus;
}

add_action( 'admin_enqueue_scripts', 'gss_fn_load_admin_assets' );
function gss_fn_load_admin_assets()
{
    wp_enqueue_style( 'gss-dataTables' );
    wp_enqueue_style( 'gss-dataTables-responsive' );
    wp_enqueue_style( 'gss-bootstrap-modal' );
    wp_enqueue_style( 'gss-datatables-bootstrap' );
    wp_enqueue_style( 'gss-custom' );
    wp_enqueue_script( 'gss-dataTables' );
    wp_enqueue_script( 'gss-dataTables-responsive' );
    wp_enqueue_script( 'gss-bootstrap-modal' );
    wp_enqueue_script( 'gss-jquery-ui' );
    wp_enqueue_script( 'script_zzd' );
    
    if ( isset( $_GET['page'] ) && $_GET['page'] == "zzd_stripe_subscriptions" ) {
        wp_enqueue_style( 'gss-jquery-ui' );
        wp_enqueue_style( 'gss-jquery-ui-theme' );
    }
    
    
    if ( isset( $_GET['page'] ) && $_GET['page'] == "zzd_stripe_transactions" ) {
        wp_enqueue_style( 'gss-jquery-ui' );
        wp_enqueue_style( 'gss-jquery-ui-theme' );
    }
    
    
    if ( isset( $_GET['subview'] ) && $_GET['subview'] == "gravitystripe" ) {
        wp_enqueue_style( 'gss-jquery-ui' );
        wp_enqueue_style( 'gss-jquery-ui-theme' );
    }
    
    wp_enqueue_script( 'gss_pro_payform' );
    // wp_enqueue_script( 'gss_pro_sel2' );
}

add_filter( 'gform_noconflict_styles', 'gss_gform_noconflict_styles' );
function gss_gform_noconflict_styles( $styles )
{
    $styles[] = 'gss-dataTables';
    $styles[] = 'gss-dataTables-responsive';
    $styles[] = 'gss-bootstrap-modal';
    $styles[] = 'gss-datatables-bootstrap';
    $styles[] = 'gss-jquery-ui';
    $styles[] = 'gss-jquery-ui-theme';
    $styles[] = 'gss-custom';
    return $styles;
}

add_filter( 'gform_noconflict_scripts', 'gss_gform_noconflict_scripts' );
function gss_gform_noconflict_scripts( $scripts )
{
    $scripts[] = 'gss-dataTables';
    $scripts[] = 'gss-dataTables-responsive';
    $scripts[] = 'gss-bootstrap-modal';
    $scripts[] = 'gss-jquery-ui';
    $scripts[] = 'script_zzd';
    $scripts[] = 'gss_pro_payform';
    $scripts[] = 'gss_pro_sel2';
    return $scripts;
}

function gss_fn_admin_table_subscriptions()
{
    $forms = GFAPI::get_forms( true );
    
    if ( $forms && is_array( $forms ) ) {
        $dropdown = "<select name='' id='zzd_sort_forms' class='zzd_sort_forms'><option value=0>-- Select Form --</option>";
        foreach ( $forms as $form ) {
            $dropdown .= "<option value='" . trim( $form['id'] ) . "'>" . $form['title'] . "</option>";
        }
        $dropdown .= "</select>";
    }
    
    $columns = gss_admin_backend_table_columns();
    $admin_forms = apply_filters( "gss_admin_side_form_ids", 0 );
    $per_page = 5000;
    // apply_filters("gss_user_list_per_page", 20, $args);
    $per_page = apply_filters( "gss_user_list_per_page", 20, $admin_forms );
    $not_found_message = apply_filters( "gss_user_list_not_found_message", __( "No Active Subscriptions", 'gravitystripe' ), $admin_forms );
    $args = array(
        "per_page"          => $per_page,
        "not_found_message" => $not_found_message,
        "feed"              => $admin_forms,
        "table"             => "wp-admin-list",
    );
    $count = 0;
    $search_criteria = array();
    $sorting = array();
    // $entry_results = gss_fn_get_entries( $form_ids, false  );
    $entry_results = array();
    // echo "<pre>"; print_r($entry_results); echo "</pre>";
    $html = "<div class='table-responsive list-gss'>";
    $html .= "<div class='zzd_sort_forms_wrap'>{$dropdown}</div>";
    $table_header = gss_fn_create_table_header(
        $columns,
        $entry_results,
        "backend_admin",
        $args
    );
    // $table_body = gss_fn_create_table_body( $columns, $entry_results, "backend_admin", $args );
    $table_body = "";
    $table_footer = gss_fn_create_table_footer(
        $columns,
        $entry_results,
        "backend_admin",
        $args
    );
    $html .= $table_header . $table_body . $table_footer;
    $html .= "</div>";
    do_action( "gss_action_after_shortcode_rendered" );
    return $html;
}

add_action( 'wp_ajax_get_subscription_rows', 'gss_ajax_fn_get_subscription_rows' );
function gss_ajax_fn_get_subscription_rows()
{
    $form_ids = "";
    $is_admin = false;
    $limit = 10;
    $offset = 0;
    $type = "subscriptions";
    if ( isset( $_POST["type"] ) ) {
        $type = sanitize_text_field( $_POST["type"] );
    }
    if ( isset( $_POST["start"] ) ) {
        $offset = sanitize_text_field( $_POST["start"] );
    }
    if ( isset( $_POST["length"] ) ) {
        $limit = sanitize_text_field( $_POST["length"] );
    }
    
    if ( isset( $_POST["feed"] ) ) {
        $form_ids = sanitize_text_field( $_POST["feed"] );
        
        if ( $form_ids == 0 ) {
            $form_ids = 0;
        } else {
            $form_ids = explode( ",", $form_ids );
        }
    
    }
    
    
    if ( isset( $_POST["table"] ) ) {
        $table = sanitize_text_field( $_POST["table"] );
        if ( $table == "wp-admin-list" || $table == "admin-list" ) {
            $is_admin = true;
        }
    }
    
    $search_value = "";
    
    if ( isset( $_POST["search"] ) && isset( $_POST["search"]["value"] ) ) {
        $search_form = sanitize_text_field( $_POST["search"]["value"] );
        if ( $search_form != "" ) {
            $search_value = $search_form;
        }
    }
    
    
    if ( $type == 'subscriptions' ) {
        $entry_results = gss_fn_get_entries(
            $form_ids,
            $is_admin,
            $limit,
            $offset,
            $search_value
        );
    } else {
        $entry_results = gss_fn_get_transaction_entries(
            $form_ids,
            $is_admin,
            $limit,
            $offset,
            $search_value
        );
    }
    
    $total_results = gss_fn_get_total_entries_count( $form_ids, $is_admin, $search_value );
    
    if ( $type == 'subscriptions' ) {
        $columns = gss_user_table_columns();
        if ( $table == "admin-list" ) {
            $columns = gss_admin_frontend_table_columns();
        }
        if ( $table == "wp-admin-list" ) {
            $columns = gss_admin_backend_table_columns();
        }
    } else {
        $columns = gss_user_table_transaction_columns();
        if ( $table == "admin-list" ) {
            $columns = gss_admin_frontend_transaction_columns();
        }
        if ( $table == "wp-admin-list" ) {
            $columns = gss_admin_backend_transaction_columns();
        }
    }
    
    $print_result = array();
    foreach ( $entry_results as $result ) {
        $row_data = array();
        foreach ( $columns as $index_key => $column ) {
            extract( $column );
            $cell_class = $col_class . " " . $index_key;
            if ( $index_key == "gss_status" ) {
                $cell_class .= " " . $result[$index_key];
            }
            $cell_value = $result[$col_key];
            $data_tooltip_attribute = "";
            if ( is_array( $cell_value ) ) {
                // continue;
            }
            
            if ( $allow_trim ) {
                $data_tooltip_attribute = " data-tooltip='{$cell_value}' ";
                $cell_value = gss_fn_trim_values_to_print( $cell_value, $trim_length );
                $cell_value = "<a href='javascript:void(0);' {$data_tooltip_attribute} data-tooltip-location='right'> " . $cell_value . "\t </a>";
            }
            
            // error_log( print_r($cell_value,1) );
            // error_log( print_r($col_key,1 ) );
            // $content .= "<td class='$cell_class.'>$cell_value</td>";
            $cell_value = apply_filters(
                "gss_result_value",
                $cell_value,
                $col_key,
                $result
            );
            $row_data[$col_key] = $cell_value;
        }
        $row_data['gss_status'] = $result['gss_status'];
        $print_result[] = $row_data;
    }
    $response = array(
        "draw"                 => intval( $_POST['draw'] ),
        "iTotalRecords"        => $total_results,
        "iTotalDisplayRecords" => $total_results,
        "aaData"               => $print_result,
    );
    echo  json_encode( $response ) ;
    exit;
}

function gss_user_table_columns()
{
    $columns = array(
        "gss_entry_id"     => array(
        "col_key"      => "gss_entry_id",
        "col_label"    => __( "ID", 'gravitystripe' ),
        "col_class"    => "",
        "allow_trim"   => false,
        "col_prioriry" => 500,
        "col_visible"  => false,
    ),
        "name"             => array(
        "col_key"      => "gss_subscription_name",
        "col_label"    => __( "Subscription", 'gravitystripe' ),
        "col_class"    => "name",
        "col_prioriry" => 60,
        "allow_trim"   => true,
        "trim_length"  => 16,
    ),
        "gss_new_date"     => array(
        "col_key"      => "gss_new_date",
        "col_label"    => __( "Start Date", 'gravitystripe' ),
        "col_class"    => "date",
        "allow_trim"   => false,
        "col_prioriry" => 80,
    ),
        "gss_renewal_date" => array(
        "col_key"      => "gss_renewal_date",
        "col_label"    => __( "Renewal Date", 'gravitystripe' ),
        "col_class"    => "renewal_date",
        "col_prioriry" => 70,
        "allow_trim"   => false,
    ),
        "gss_amount"       => array(
        "col_key"      => "gss_amount",
        "col_label"    => __( "Amount / Term", 'gravitystripe' ),
        "col_class"    => "amount",
        "allow_trim"   => false,
        "col_prioriry" => 40,
    ),
        "gss_status"       => array(
        "col_key"      => "gss_status_value",
        "col_label"    => __( "Status", 'gravitystripe' ),
        "col_class"    => "status",
        "allow_trim"   => false,
        "col_prioriry" => 20,
    ),
        "action"           => array(
        "col_key"      => "gss_action",
        "col_label"    => __( "Action", 'gravitystripe' ),
        "col_class"    => "action",
        "allow_trim"   => false,
        "col_prioriry" => 100,
    ),
    );
    return apply_filters( "gss_user_table_columns", $columns );
}

function gss_admin_frontend_table_columns()
{
    $columns = array(
        "gss_entry_id" => array(
        "col_key"      => "gss_entry_id",
        "col_label"    => __( "ID", 'gravitystripe' ),
        "col_class"    => "",
        "col_prioriry" => 500,
        "allow_trim"   => false,
        "col_visible"  => false,
    ),
        "subscriber"   => array(
        "col_key"      => "gss_username",
        "col_label"    => __( "Subscriber", 'gravitystripe' ),
        "col_class"    => "subscriber",
        "col_prioriry" => 10,
        "allow_trim"   => true,
        "trim_length"  => 16,
        "col_visible"  => true,
    ),
        "email"        => array(
        "col_key"      => "gss_email",
        "col_label"    => __( "Email", 'gravitystripe' ),
        "col_class"    => "email",
        "col_prioriry" => 50,
        "allow_trim"   => true,
        "trim_length"  => 16,
        "col_visible"  => true,
    ),
        "name"         => array(
        "col_key"      => "gss_subscription_name",
        "col_label"    => __( "Subscription", 'gravitystripe' ),
        "col_class"    => "name",
        "col_prioriry" => 60,
        "allow_trim"   => true,
        "trim_length"  => 16,
        "col_visible"  => true,
    ),
        "date"         => array(
        "col_key"      => "gss_new_date",
        "col_label"    => __( "Start Date", 'gravitystripe' ),
        "col_class"    => "date",
        "col_prioriry" => 80,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
        "renewal_date" => array(
        "col_key"      => "gss_renewal_date",
        "col_label"    => __( "Renewal Date", 'gravitystripe' ),
        "col_class"    => "renewal_date",
        "col_prioriry" => 70,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
        "amount"       => array(
        "col_key"      => "gss_amount",
        "col_label"    => __( "Amount / Term", 'gravitystripe' ),
        "col_class"    => "amount",
        "col_prioriry" => 40,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
        "gss_status"   => array(
        "col_key"      => "gss_status_value",
        "col_label"    => __( "Status", 'gravitystripe' ),
        "col_class"    => "status",
        "col_prioriry" => 20,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
        "action"       => array(
        "col_key"      => "gss_action",
        "col_label"    => __( "Action", 'gravitystripe' ),
        "col_class"    => "action",
        "col_prioriry" => 100,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
    );
    return apply_filters( "gss_admin_frontend_table_columns", $columns );
}

function gss_admin_backend_table_columns()
{
    $columns = array(
        "gss_entry_id"   => array(
        "col_key"      => "gss_entry_id",
        "col_label"    => __( "ID", 'gravitystripe' ),
        "col_class"    => "",
        "col_prioriry" => 500,
        "allow_trim"   => false,
        "col_visible"  => false,
    ),
        "gss_form_title" => array(
        "col_key"        => "gss_form_title",
        "col_label"      => __( "Form ID", 'gravitystripe' ),
        "col_class"      => "",
        "col_prioriry"   => 400,
        "allow_trim"     => false,
        "col_visible"    => false,
        "col_searchable" => true,
    ),
        "subscriber"     => array(
        "col_key"      => "gss_username",
        "col_label"    => __( "Subscriber", 'gravitystripe' ),
        "col_class"    => "subscriber",
        "col_prioriry" => 10,
        "allow_trim"   => true,
        "trim_length"  => 16,
        "col_visible"  => true,
    ),
        "email"          => array(
        "col_key"      => "gss_email",
        "col_label"    => __( "Email", 'gravitystripe' ),
        "col_class"    => "email",
        "col_prioriry" => 50,
        "allow_trim"   => true,
        "trim_length"  => 16,
        "col_visible"  => true,
    ),
        "name"           => array(
        "col_key"      => "gss_subscription_name",
        "col_label"    => __( "Subscription", 'gravitystripe' ),
        "col_class"    => "name",
        "col_prioriry" => 60,
        "allow_trim"   => true,
        "trim_length"  => 16,
        "col_visible"  => true,
    ),
        "amount"         => array(
        "col_key"      => "gss_amount",
        "col_label"    => __( "Amount / Term", 'gravitystripe' ),
        "col_class"    => "amount",
        "col_prioriry" => 40,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
        "gss_status"     => array(
        "col_key"      => "gss_status_value",
        "col_label"    => __( "Status", 'gravitystripe' ),
        "col_class"    => "status",
        "col_prioriry" => 20,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
        "gss_action"     => array(
        "col_key"      => "gss_action",
        "col_label"    => __( "Action", 'gravitystripe' ),
        "col_class"    => "action",
        "col_prioriry" => 100,
        "allow_trim"   => false,
        "col_visible"  => true,
    ),
    );
    return apply_filters( "gss_admin_backend_table_columns", $columns );
}

add_filter(
    "gss_result_value",
    "gss_fn_append_loader_image",
    999,
    3
);
function gss_fn_append_loader_image( $cell_value, $col_key, $result )
{
    
    if ( $col_key == "gss_action" ) {
        // $cell_value .= "<img src='".GSS_LOADER_IMAGE."' class='loader-image' style='width: 15px'>";
        $create_action_html = "";
        $create_action_mobile_html = "";
        $links = count( $cell_value );
        
        if ( $links == 1 ) {
            foreach ( $cell_value as $link ) {
                
                if ( !is_array( $link ) ) {
                    $create_action_html .= $link;
                    $one_link_found = true;
                } else {
                    if ( !$link || empty($link) ) {
                        continue;
                    }
                    $one_link_found = true;
                    $class = $link['class'];
                    $url = $link['url'];
                    $link_text = $link['link_text'];
                    $data = ( isset( $link['data'] ) ? $link['data'] : array() );
                    $data_attrs = "";
                    if ( $data ) {
                        foreach ( $data as $key => $attr ) {
                            $data_attrs .= " data-{$key}='" . $attr . "'";
                        }
                    }
                    if ( isset( $link['target'] ) ) {
                        $data_attrs .= " target='_blank' ";
                    }
                    $create_action_html .= "<a class='{$class}' href='{$url}' {$data_attrs}> {$link_text} </a>";
                    $create_action_mobile_html .= "<a class='{$class} mobile_link' href='{$url}' {$data_attrs}> {$link_text} </a>";
                }
            
            }
        } else {
            
            if ( $links > 1 ) {
                $one_link_found = false;
                $create_action_html .= '<div class="dropdown">';
                $create_action_html .= '<span class="select-action">';
                $create_action_html .= __( "Select Action", 'gravitystripe' );
                $create_action_html .= '  <span>▼</span> </span>';
                $create_action_html .= '<div class="dropdown-content">';
                foreach ( $cell_value as $link ) {
                    if ( !$link || empty($link) ) {
                        continue;
                    }
                    $one_link_found = true;
                    $class = $link['class'];
                    $url = $link['url'];
                    $link_text = $link['link_text'];
                    $data = ( isset( $link['data'] ) ? $link['data'] : array() );
                    $data_attrs = "";
                    if ( $data ) {
                        foreach ( $data as $key => $attr ) {
                            $data_attrs .= " data-{$key}='" . $attr . "'";
                        }
                    }
                    if ( isset( $link['target'] ) ) {
                        $data_attrs .= " target='_blank' ";
                    }
                    $create_action_html .= "<a class='{$class}' href='{$url}' {$data_attrs}> {$link_text} </a>";
                    $create_action_mobile_html .= "<a class='{$class} mobile_link' href='{$url}' {$data_attrs}> {$link_text} </a>";
                }
                $create_action_html .= '</div>';
                $create_action_html .= '</div>';
            }
        
        }
        
        $cell_value = $create_action_html . $create_action_mobile_html;
        $cell_value .= "<img src='" . GSS_LOADER_IMAGE . "' class='loader-image' style='width: 15px'>";
        // echo "<pre>"; print_r($cell_value); echo "</pre>";
        if ( !$one_link_found ) {
            $cell_value = "";
        }
    }
    
    if ( $col_key == "gss_username" ) {
        //$cell_value .= " ".$result['gss_entry_id'];
    }
    return $cell_value;
}

add_action( "gss_action_after_shortcode_rendered", "gss_fn_add_popups" );
function gss_fn_add_popups()
{
    $html = '';
    
    if ( mgfss_fs()->is__premium_only() && is_admin() ) {
        ?>
			<div id="gssModalCCUpdate" class="modal fade" role="dialog">
				<div class="modal-dialog">
				<!-- Modal content-->
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h4 class="modal-title">Update Credit Card</h4>
						</div>
					<div class="modal-body">
						<div id="gss_update_cc" class="creditCardForm">
							
							<div class="payment">
								<form>
									
									<div class="form-group" id="gss_card-number-field">
										<h4 id="gss_existing_card">Current Card Used: <span></span></h4>
									</div>
									<div class="form-group" id="gss_card-number-field">
										<label for="gss_cardNumber">New Card Number</label>
										<input type="text" class="form-control" id="gss_cardNumber">
									</div>
									<div class="form-group" id="gss_expiration-date">
										<label>Expiration Date</label>
										<select id="gss_exp_month">
											<option value="01">January</option>
											<option value="02">February </option>
											<option value="03">March</option>
											<option value="04">April</option>
											<option value="05">May</option>
											<option value="06">June</option>
											<option value="07">July</option>
											<option value="08">August</option>
											<option value="09">September</option>
											<option value="10">October</option>
											<option value="11">November</option>
											<option value="12">December</option>
										</select>
										<select id="gss_exp_year">
											<?php 
        $year = date( "Y" );
        for ( $i = 0 ;  $i <= 30 ;  $i++ ) {
            echo  '<option value="' . $year . '"> ' . $year . '</option>' ;
            $year++;
        }
        ?>						
										</select>
									</div>    
									<div class="form-group CVV">
										<label for="gss_cvv">CVV</label>
										<input type="text" class="form-control" id="gss_cvv">
									</div>					
									<div class="form-group" id="gss_pay-now">
										<input type="hidden" id="gss_entry_id">
										<input type="hidden" id="gss_field_key">
										<button type="submit" class="btn btn-default" id="gss_confirm-purchase">Update Card Info</button>
									</div>
									<div class="form-group" id="gss_response_msg"> </div>
								</form>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
					</div>
				</div>
			</div>
			
			
			<?php 
    }
    
    $title = __( 'Order id: ' );
    $title = apply_filters( "gss_order_modal_title", $title );
    ?>
	<div id="viewEntryDialog" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><?php 
    echo  $title ;
    ?> <span></span></h4>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<?php 
    remove_action( "gss_action_after_shortcode_rendered", "gss_fn_add_popups" );
}

/* Backend Entry Metabox by Rashid Bhura */
add_filter(
    "gform_entry_detail_meta_boxes",
    "gss_fn_add_metabox_to_assign_entry",
    100,
    3
);
function gss_fn_add_metabox_to_assign_entry( $meta_boxes, $entry, $form )
{
    $meta_boxes['gss_metabox'] = array(
        'title'    => esc_html__( 'Assign User to Entry', 'gravityforms' ),
        'callback' => 'gss_fn_add_metabox_content',
        'context'  => 'normal',
    );
    return $meta_boxes;
}

/* Entry MetaBox */
function gss_fn_add_metabox_content( $args, $metabox )
{
    $form = $args['form'];
    $entry = $args['entry'];
    $mode = $args['mode'];
    include ZZD_GSS_DIR . 'entry-metabox.php';
}

add_action( "wp_ajax_save_created_by", "gss_fn_save_created_by_fn" );
function gss_fn_save_created_by_fn( $entry_id = false, $providers = false )
{
    
    if ( isset( $_POST['entry_id'] ) && $_POST['entry_id'] != "" && isset( $_POST['assign_created_by'] ) ) {
        $entry_id = sanitize_text_field( $_POST['entry_id'] );
        $assign_created_by = sanitize_text_field( $_POST['assign_created_by'] );
        echo  $result = GFAPI::update_entry_property( $entry_id, 'created_by', $assign_created_by ) ;
    }
    
    exit;
}

add_action( "wp_ajax_gss_view_order", "gss_view_transaction_ajax" );
function gss_view_transaction_ajax( $entry_id = false, $providers = false )
{
    $message = __( 'Invalid Request ID Passed' );
    
    if ( isset( $_POST['eid'] ) && $_POST['eid'] != "" ) {
        $entry_id = base64_decode( $_POST['eid'] );
        $entry = GFAPI::get_entry( $entry_id );
        
        if ( !$entry ) {
            $message = __( 'Invalid Request ID Passed' );
        } else {
            
            if ( !gss_user_has_access() && $entry['created_by'] != get_current_user_id() ) {
                $message = __( 'You are not authorized to view this order' );
            } else {
                $display_view = 'order-summary';
                $display_view = apply_filters( "gss_order_modal_content", $display_view );
                $form = GFAPI::get_form( $entry['form_id'] );
                $products = GFCommon::get_product_fields(
                    $form,
                    $entry,
                    false,
                    true
                );
                $order_summary_markup = $order_summary_markup = GF_Order_Summary::render(
                    $form,
                    $entry,
                    $display_view,
                    false,
                    true
                );
                $message = gf_apply_filters(
                    array( 'gform_order_summary', $form['id'] ),
                    trim( $order_summary_markup ),
                    $form,
                    $entry,
                    $products,
                    'html'
                );
                $message = '<table class="gss_order_table" border="0">' . $message . '</table>';
            }
        
        }
    
    }
    
    $response = array(
        "transaction_id" => ( $entry['transaction_id'] ? $entry['transaction_id'] : $entry['id'] ),
        "html"           => $message,
    );
    echo  json_encode( $response ) ;
    exit;
}

function gss_fn_upgrade_page_transaction()
{
    $url = mgfss_fs()->get_upgrade_url();
    $html = '<div class="wrap">
			<h1>' . __( "Single Transactions", 'gravitystripe' ) . '</h1>	

			<div style="margin: 20px auto;text-align: center;line-height:1.5;font-size: 18px"> 
				<h4>' . __( "It looks like you are using GravityStripe Basic.", 'gravitystripe' ) . '<br>' . __( "Upgrade to GravityStripe Pro for the Single Transaction feature and more.", 'gravitystripe' ) . '</h4>
			</div>

            <div style="margin: 20px auto;text-align: center;line-height:1.5;font-size: 16px"> 
            <a href="' . $url . '" target="_blank" style="text-decoration: none;padding: 10px 30px 12px;background: #a7b9fe;border-radius: 5px;color: #000;">' . __( "Upgrade Now", 'gravitystripe' ) . '</a>
            </div>		

			<div style="margin: 20px 0"> 
				<p style="text-align: center;"> <iframe width="560" height="315" src="https://www.youtube.com/embed/yFBzycNqw4A" title="Gravity Stripe" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> </p>
			</div>

			';
    $html .= '</div>';
    echo  $html ;
}