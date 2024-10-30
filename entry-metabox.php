<?php

$users = get_users();

$created_by = $entry['created_by'];
$current_user = get_userdata( $created_by );

$show_user_info = false;
if( ! is_wp_error($current_user) && $current_user ) {
	
	$show_user_info = true;
}
?>

<table class="widefat fixed entry-detail-notes" cellspacing="0">
	<tbody id="user-list">
		<tr>
			
			<?php if( $show_user_info ) { ?>
				<td style="padding:10px;vertical-align:middle;" class="lastrow">
					<h3 style="font-size: 16px;margin: 10px 0;"> Currently Assigned User </h3>
					User ID : <?php echo $current_user->id; ?><br>
					Username : <?php echo $current_user->user_login; ?><br>
					User Email : <?php echo $current_user->user_email; ?><br>
					Display Name : <?php echo $current_user->display_name; ?><br>
					<a href="<?php echo get_edit_user_link($current_user->id); ?>" style="margin-top: 10px;display: inline-block;font-size: 14px;"> View User </a>
				</td>
			<?php } 

			else { ?>
				<td style="padding:10px;vertical-align:middle;" class="lastrow">
					<h3 style="font-size: 16px;margin: 10px 0;"> No User Assigned to This Entry </h3>
				</td>
			<?php } ?>
			
			
			
			<td style="padding:10px;vertical-align: middle;" class="lastrow">
				<input type="hidden" class="entry_id" name="entry_id" value="<?php echo $entry['id'];  ?>">
				<h3 style="font-size: 16px;margin: 10px 0;"> Change Assigned User for Entry </h3>
				<select name="assign_created_by" class="assign_created_by">
					<option>Select User</option>
				<?php 
										
					foreach( $users as $user ) {
						$selected = $created_by ==$user->id  ? "selected" : "";
						echo "<option $selected value='".$user->id."'>".$user->user_email."</option>";
					}				
				?>
				</select>
				
				<input type="button" name="add_link" value="Assign User" class="button assign_user" style="width:auto;padding-bottom:2px;">
			</td>
		</tr>
	</tbody>
</table>