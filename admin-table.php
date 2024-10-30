<?php 


function gss_fn_stripe_subscriptions_page() {
	
	if( !gss_fn_validate_required_plugins()) {
		$html = '<div class="wrap">
				<h1>'.__("Stripe Subscriptions", 'gravitystripe').'</h1>
				
				<div style="margin: 20px 0"> 
					<p> '.__("To Access this page, Gravityforms and gravityforms Stripe need to be active.", 'gravitystripe').' </p>
				</div>';
				
		$html .= '</div>';
	}
	else {
		$form_html = gss_fn_admin_table_subscriptions();
		$html = '<div class="wrap">
				<h1>'.__("Stripe Subscriptions", 'gravitystripe').'</h1>
				
				'.$form_html.'
				
				<div style="margin: 20px 0"> 
					<p> '.__("Let user manage their subscription by adding this shortcode to a page:", 'gravitystripe').' <strong> [user-subscriptions form_ids=1,2,3] </strong> </p>
				</div>
				<div> 
					<p> '.__("Display all subscriptions on a page (admin view only) with this shortcode:", 'gravitystripe').'<strong> [subscription-list form_ids=1,2,3] </strong> </p>
				</div>
				
				'.gss_fn_trial_cancel_html().'
				';
				
				
				
		$html .= '</div>';
	}
	echo $html;
}

function gss_fn_trial_cancel_html() {
	
	if( ! gss_valid() ) {
		return "";
	}
	
	$html = '<div id="dialog-confirm" title="Cancel Subscription ?" style="display: none;">
			<h5> '.__("Please select an option below", 'gravitystripe').' </h5>
			<p>
				<label>
					<input type="radio" name="gss_cancel_at_end" value="0" checked>
					'.__("Cancel immediately", 'gravitystripe').'
				</label>
				<label>
					<input type="radio" name="gss_cancel_at_end" value="1">
					'.__("Cancel at the end of the cycle", 'gravitystripe').'
				</label>
			</p>
		
	</div>';
	return $html;
}

add_filter( "gform_addon_navigation", "gss_fn_gform_addon_navigation", 100, 1 );
function gss_fn_gform_addon_navigation($addon_menus) {
	
	/*if( !gss_fn_validate_required_plugins()) {
		return $addon_menus;
	}*/
	
	
	$addon_menus[] = array(
		'label' => __('Stripe Subscriptions', 'gravitystripe'),
		'permission' => 'update_plugins',
		'name' => 'zzd_stripe_subscriptions',
		'callback' => 'gss_fn_stripe_subscriptions_page'
	
	);
	
	wp_enqueue_style( 'dt-css-1', ZZD_GSS_URL . '/css/jquery.dataTables.min.css' ); 
	wp_enqueue_style( 'dt-css-2', ZZD_GSS_URL . '/css/responsive.dataTables.min.css' ); 
	
	if( isset($_GET['page']) && $_GET['page'] == "zzd_stripe_subscriptions" ) {
		wp_enqueue_style( 'gss-jquery-ui-1', ZZD_GSS_URL . '/css/jquery-ui.css' ); 
		wp_enqueue_style( 'gss-jquery-ui-2', ZZD_GSS_URL . '/css/jquery-ui.theme.css' ); 
	}
	if( isset($_GET['subview']) && $_GET['subview'] == "gravitystripe" ) {
		wp_enqueue_style( 'gss-jquery-ui-1', ZZD_GSS_URL . '/css/jquery-ui.css' ); 
		wp_enqueue_style( 'gss-jquery-ui-2', ZZD_GSS_URL . '/css/jquery-ui.theme.css' ); 
	}
	
	
	
	wp_enqueue_style( 'boots', ZZD_GSS_URL . '/css/dataTables.bootstrap.css' ); 
	wp_enqueue_style( 'dt-custom', ZZD_GSS_URL . '/css/custom.css' ); 
	
	wp_enqueue_script( 'gss_pro_bs', ZZD_GSS_URL . 'js/bootstrap.min.js', array('jquery', 'script_zzd') );	
		wp_enqueue_style( 'main-boots', ZZD_GSS_URL . 'css/bootstrap.min.css' ); 
	
	wp_enqueue_script( 'dt-js-1', ZZD_GSS_URL . '/js/jquery.dataTables.min.js', array('jquery') );
	wp_enqueue_script( 'dt-js-2', ZZD_GSS_URL . '/js/dataTables.responsive.min.js', array('jquery') );
	wp_enqueue_script( 'dt-js-3', ZZD_GSS_URL . '/js/jquery-ui.js', array('jquery') );
	wp_enqueue_script( 'script_zzd', ZZD_GSS_URL . '/js/custom.js', array('jquery') );
	wp_localize_script('script_zzd', 'script_zzd', array(
		'admin_url_zzd' => admin_url('admin-ajax.php')
	));
	wp_localize_script('script_zzd', 'gss_variables', array(
		'confirm_cancel' => __('Are you sure you want to cancel the subscription?', 'gravitystripe'),
		'canceled_label' => __('Canceled', 'gravitystripe'),
		'refunded_cancelled_label' => __('Refunded/Canceled', 'gravitystripe'),
		'confirm_refund' => __('Are you sure you want to cancel and refund the last subscription payment?', 'gravitystripe'),
		'wrong_card' => __('Wrong card number', 'gravitystripe'),
		'wrong_cvv' => __('Wrong CVV', 'gravitystripe'),
		'select_expiry_month' => __('Please Select Expiry Month', 'gravitystripe'),
		'select_expiry_year' => __('Please Select Expiry Year', 'gravitystripe'),
		'please_wait' => __('Please wait', 'gravitystripe'),
		'please_wait_dots' => __('Please wait...', 'gravitystripe'),
		'card_updated' => __('Credit Card Updated Successfully', 'gravitystripe'),
	));
	if(function_exists('load_rest_assets')) {
		load_rest_assets();
	}
	return $addon_menus;
}


function gss_fn_admin_table_subscriptions() {
	
	
	$forms = GFAPI::get_forms(true);
	if($forms && is_array($forms) ) {
		$dropdown = "<select name='' id='zzd_sort_forms' class='zzd_sort_forms'><option value=''>-- Select Form --</option>";
		foreach( $forms as $form ) {
			$dropdown .= "<option value='".trim($form['title'])."'>".$form['title']."</option>";
		}
		$dropdown .= "</select>";
	}
	
	$html = "<div class='table-responsive list-gss'>";
		$html .= "<div class='zzd_sort_forms_wrap'>$dropdown</div>";
		$html .= "<table id='wpadmin-list' class='table dt-responsive' cellspacing='0' width='100%' data-not_found_message='No Active Subscriptions'>";
		$html .= ' <thead>
			<tr class="gss_item">
				<th data-priority="8">'.__('ID', 'gravitystripe').'</th>
				<th data-priority="9">'.__('Form ID', 'gravitystripe').'</th>
				<th data-priority="1" class="subscriber ">'.__('Subscriber', 'gravitystripe').'</th>
				<th data-priority="3" class="email min-tablet-p">'.__('Email', 'gravitystripe').'</th>
				<th data-priority="2" class="name">'.__('Subscription Name', 'gravitystripe').'</th>
				<th data-priority="2" class="amount min-tablet-p">'.__('Amount / Term', 'gravitystripe').'</th>
				<th data-priority="4" class="status">'.__('Status', 'gravitystripe').'</th>
				<th data-priority="5" class="action min-tablet-p">'.__('Action', 'gravitystripe').'</th>
			</tr>
		</thead>
		<tbody>';
	
	
	$count = 0;

	$search_criteria = array();
	$sorting = array();
	
	$entry_results = gss_fn_get_entries( 0, true  );
	
	if($entry_results && is_array($entry_results) ) {
		
		foreach($entry_results as $result) {
			extract($result);
			$gss_action .= "<img src='".GSS_LOADER_IMAGE."' class='loader-image' style='width: 15px'>";
			$html .= "<tr  class='gss_item'>";				
				$html .= "<td>".$gss_entry_id."</td>";
				$html .= "<td>".$gss_form_title."</td>";
				$html .= "<td class='subscriber'>".gss_fn_trim_values_to_print($gss_username, 13)."</td>";
				$html .= "<td class='email'><a href='javascript:void(0);' data-tooltip='$gss_email' data-tooltip-location='right'> ".gss_fn_trim_values_to_print($gss_email, 23)."	 </a></td>";
				$html .= "<td class='name'><a href='javascript:void(0);' data-tooltip='$gss_subscription_name' data-tooltip-location='right'> ".gss_fn_trim_values_to_print($gss_subscription_name, 16)." </a></td>";
				$html .= "<td class='amount'>".$gss_amount."</td>";
				
				$html .= "<td class='status $gss_status'>".$gss_status_value."</td>";
				$html .= "<td class='action'>$gss_action</td>";
			$html .= "</tr>";
			$count++;
			
		}
	}
	else {
		///$html .= "No Entries Found";
		$count = 1;
	}

	if( $count == 0 ) {
		///$html .= "No Entries Found";
	}
	
	$html .= "</tbody></table></div>";
	
	do_action("gss_action_after_shortcode_rendered");
	
	return $html;
}

