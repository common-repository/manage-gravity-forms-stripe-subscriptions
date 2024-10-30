var $ = jQuery;
function gss_fn_process_cancellation_subscription($this) {
	
	var type = 0;
	
	if( $this.hasClass( "after_end" ) ) {
		type = 1;
	}
	
	if(typeof script_zzd_options != "undefined" && script_zzd_options.gss_admin && script_zzd_options.gss_is_admin && script_zzd_options.gss_valid )  { 
		$( "#dialog-confirm" ).dialog({
			resizable: false,
			height: "auto",
			width: 400,
			modal: true,
			buttons: {
				"Yes": function() {
					var $force = false;
					if( $("input[name=gss_cancel_at_end]:checked").val() == 1 ) {
						$force = true;
					}
					gss_fn_do_cancellation($this, $force);
					$( this ).dialog( "close" );
				},
				"Cancel": function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
	else if( type == 1  && script_zzd_validate && script_zzd_validate.gss_valid ) {
		if( confirm(gss_variables.confirm_cancel) ) { 			
			gss_fn_do_cancellation($this, true);
		}
		
	}
	else {
		if( confirm(gss_variables.confirm_cancel) ) { 			
			process_cancellation($this);
		}
		
	}
}

function gss_fn_do_cancellation($this, $force) {
	
		
	if( $this.hasClass("cancelled") ) {
		return;
	}
	
	var $status_col = $this.parents(".gss_item").find(".status");
	var $status_col2 = $this.parents("tr.child").find(".status");
	var $status_col3 = $this.parents("tr.child").prev().find(".status");
	
	var $parent = $this.parent();
	//$parent.html("Please wait...");
	var $parents = $this.parents(".gss_item");
	$parents.find(".loader-image").addClass("show");
	$parent.find(".loader-image").addClass("show");
	
	var data = {
		action: 'gss_cancel_subscription_pro',
		eid: $this.attr('data-eid'),
		cancel_at_end : 0
	};

	if( $force ) {
		data.cancel_at_end = 1;
	}
	
	
	$.post(script_zzd.admin_url_zzd, data, function(response) {
		if( response == 1 ) {
			$parent.html("");
			$status_col.html(gss_variables.canceled_label).addClass("cancelled").removeClass("active");
			$status_col2.html(gss_variables.canceled_label).addClass("cancelled").removeClass("active");
			$status_col3.html(gss_variables.canceled_label).addClass("cancelled").removeClass("active");
			$parents.find('td.action').html('');
		}
		else {
			$parent.html("");
			$status_col.html(response).addClass("cancelled").removeClass("active");
			$status_col2.html(response).addClass("cancelled").removeClass("active");
			$status_col3.html(response).addClass("cancelled").removeClass("active");
			$parents.find('td.action').html('');
		}
		$(".loader-image").removeClass("show");
	});
}

function gss_fn_do_refund($this) {
	
	var $status_col = $this.parents(".gss_item").find(".status");
	var $parent = $this.parent();
	//$parent.html("Please wait...");
	
	var data = {
		action: 'gss_do_refund',
		eid: $this.attr('data-eid'),
		cancel_at_end : 0
	};
	var $parents = $this.parents(".gss_item");
	$parents.find(".loader-image").addClass("show");
	
	$.post(script_zzd.admin_url_zzd, data, function(response) {
		if( response == 1 ) {
			$parent.html("");
			$status_col.html(gss_variables.refunded_cancelled_label).addClass("refund").removeClass("active").removeClass("cancelled");
			$parents.find('td.action').html('');
		}
		$(".loader-image").removeClass("show");
	});
}

jQuery(document).ready(function($) {
	
	if(jQuery('.gss_selectx').length > 0) {
		
		jQuery('.gss_select2').select2();
		
	}
	
	$(document).on("click", '.refund_link', function(){
		
		var $this = jQuery(this); c = false;
		
		if( confirm(gss_variables.confirm_refund) ) { 			
			
			gss_fn_do_refund($this);
			
		}
		
		
	});
	
	
	
	$(document).on("click", '.cc_info_change_link', function(){
		$("#gss_existing_card span").hide();
		$("#gssModalCCUpdate").modal();
		$("#gss_entry_id").val($(this).data('eid'));
		$("#gss_field_key").val($(this).data('field_key'));
		current_card = $(this).data('current_card');
		if( typeof current_card != "undefined" && current_card != "" ) {
			current_card = gss_add_space(current_card);
			$("#gss_existing_card span").html(current_card).show();
		}
		
		$("#gss_response_msg").html("");
	});
	
	$(document).mouseup(function(e) {
		var container = $(".dropdown");

		// if the target of the click isn't the container nor a descendant of the container
		if ( ! container.is(e.target) && container.has(e.target).length == 0) {
			$(".dropdown-content").removeClass("show");
		}
	});
	
	
	if( $("#gaddon-setting-row-fail_count").length > 0 ) {
		
		if( jQuery("#enable_cancel_fail:checked").val() == "on" ) {
			$("#gaddon-setting-row-fail_count").show();
		}
		else {
			$("#gaddon-setting-row-fail_count").hide();
		}		
	}
	
	jQuery("#enable_cancel_fail").change(function() {
		var v = jQuery(this).prop("checked");
		if( v == true ) {
			$("#gaddon-setting-row-fail_count").show();
		}
		else {
			$("#gaddon-setting-row-fail_count").hide();
		}
	});
	
	if( $("#gaddon-setting-row-upgrade_subscription_page").length > 0 ) {
		
		if( jQuery("#enable_subscription_upgrade:checked").val() == "on" ) {
			$("#gaddon-setting-row-upgrade_subscription_page").show();
		}
		else {
			$("#gaddon-setting-row-upgrade_subscription_page").hide();
		}		
	}
	
	jQuery("#enable_subscription_upgrade").change(function() {
		var v = jQuery(this).prop("checked");
		if( v == true ) {
			$("#gaddon-setting-row-upgrade_subscription_page").show();
		}
		else {
			$("#gaddon-setting-row-upgrade_subscription_page").hide();
		}
	});
	
	if( $("#gaddon-setting-row-downgrade_subscription_page").length > 0 ) {
		
		if( jQuery("#enable_subscription_downgrade:checked").val() == "on" ) {
			$("#gaddon-setting-row-downgrade_subscription_page").show();
		}
		else {
			$("#gaddon-setting-row-downgrade_subscription_page").hide();
		}		
	}
	
	jQuery("#enable_subscription_downgrade").change(function() {
		var v = jQuery(this).prop("checked");
		if( v == true ) {
			$("#gaddon-setting-row-downgrade_subscription_page").show();
		}
		else {
			$("#gaddon-setting-row-downgrade_subscription_page").hide();
		}
	});
	
	if( $("#gaddon-setting-row-downgrade_role").length > 0 ) {
		
		if( jQuery("#enable_downgrade:checked").val() == "on" ) {
			$("#gaddon-setting-row-downgrade_role").show();
		}
		else {
			$("#gaddon-setting-row-downgrade_role").hide();
		}		
	}
	
	jQuery("#enable_downgrade").change(function() {
		var v = jQuery(this).prop("checked");
		if( v == true ) {
			$("#gaddon-setting-row-downgrade_role").show();
		}
		else {
			$("#gaddon-setting-row-downgrade_role").hide();
		}
	});
	
	if( $("#gaddon-setting-row-cancel_ability").length > 0 ) {
		
		var v = $("#cancel_ability").val();
		if( v == "contact_admin" ) {			
			$("#gaddon-setting-row-redirection_page").show();
		}
		else {
			$("#gaddon-setting-row-redirection_page").hide();
		}	
	}
	jQuery("#cancel_ability").change(function() {
		var v = jQuery(this).val();
		if( v == "contact_admin" ) {			
			$("#gaddon-setting-row-redirection_page").show();
		}
		else {
			$("#gaddon-setting-row-redirection_page").hide();
		}
	});
	/*if( $("#gaddon-setting-row-enable_cancellation").length > 0 ) {
		
		if( jQuery("#enable_cancellation:checked").val() == "on" ) {
			$("#gaddon-setting-row-cancel_type").show();
			
			if( jQuery("#cancel_type").val() == "redirect_link" ) {
				$("#gaddon-setting-row-redirection_page").show();
			}
			else {
				$("#gaddon-setting-row-redirection_page").hide();
			}
		}
		else {
			$("#gaddon-setting-row-cancel_type").hide();
			$("#gaddon-setting-row-redirection_page").hide();
		}		
	}
	jQuery("#enable_cancellation").change(function() {
		var v = jQuery(this).prop("checked");
		if( v == true ) {
			$("#gaddon-setting-row-cancel_type").show();
		}
		else {
			$("#gaddon-setting-row-cancel_type").hide();
			$("#gaddon-setting-row-redirection_page").hide();
		}
	});
	jQuery("#cancel_type").change(function() {
		var v = jQuery(this).val();
		if( v == "redirect_link" ) {			
			$("#gaddon-setting-row-redirection_page").show();
		}
		else {
			$("#gaddon-setting-row-redirection_page").hide();
		}
	});*/
	
	
});

$(function() {
	var $ = jQuery;
	if( $("#gss_update_cc").length > 0 ) {
		
		var cardNumber = $('#gss_cardNumber');
		var cardNumberField = $('#gss_card-number-field');
		var CVV = $("#gss_cvv");
		var confirmButton = $('#gss_confirm-purchase');
	   
		// Use the payform library to format and validate
		// the payment fields.

		cardNumber.payform('formatCardNumber');
		CVV.payform('formatCardCVC');


		cardNumber.keyup(function() {

		   
			if ($.payform.validateCardNumber(cardNumber.val()) == false) {
				cardNumberField.addClass('has-error');
			} else {
				cardNumberField.removeClass('has-error');
				cardNumberField.addClass('has-success');
			}
			
			if ($.payform.validateCardCVC(CVV.val()) == false) {
				CVV.parent().addClass('has-error');
			} else {
				CVV.parent().removeClass('has-error');
				CVV.parent().addClass('has-success');
			}
			
			
		});

		confirmButton.click(function(e) {

			e.preventDefault();

			var isCardValid = $.payform.validateCardNumber(cardNumber.val());
			var isCvvValid = $.payform.validateCardCVC(CVV.val());

			if (!isCardValid) {
				alert(gss_variables.wrong_card);
			} else if (!isCvvValid) {
				alert(gss_variables.wrong_cvv);
			} else if ($("#gss_exp_month").val() == "" ) {
				alert(gss_variables.select_expiry_month);
			} else if ($("#gss_exp_year").val() == "" ) {
				alert(gss_variables.select_expiry_year);
			} else {
				var data = {
					action: 'gss_do_cc_update',
					eid: $("#gss_entry_id").val(),
					gss_cc_number: $("#gss_cardNumber").val(),
					gss_exp_month: $("#gss_exp_month").val(),
					gss_exp_year: $("#gss_exp_year").val(),
					gss_cvv: $("#gss_cvv").val(),
					gss_field_key: $("#gss_field_key").val(),
				};
				$("#gss_response_msg").html(gss_variables.please_wait_dots);
				$("#gss_confirm-purchase").attr("disabled", "disabled");
				$.post(script_zzd.admin_url_zzd, data, function(response) {
					if( response == 1 ) {
						alert(gss_variables.card_updated);
						$("#gss_confirm-purchase").removeAttr("disabled");
						jQuery("#gss_card-number-field").parent().trigger("reset");
						$("#gssModalCCUpdate").modal('hide');
						$("#gss_existing_card span").html("");
						location.reload(true);
					}
					else {
						$("#gss_response_msg").html(response);
						$("#gss_confirm-purchase").removeAttr("disabled");
						$("#gss_existing_card span").html("");
					}
				});
			}
		});
		
		
	}
});

function gss_add_space (ele) {
	ele = ele.split(' ').join('');    // Remove dash (-) if mistakenly entered.

	var finalVal = ele.match(/.{1,4}/g).join(' ');
	return finalVal;
}