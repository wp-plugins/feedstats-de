<?php
// Option Page
function feedstats_admin_option_page() {
	global $wpdb, $wp_version;
?>
<div class="wrap">
	<h2><?php _e('FeedStats Options', 'feedstats'); ?></h2>
<?php
	if ( isset($_POST['action']) && ($_POST['action'] == 'add_index') && $_POST['feedstats_add_index'] ) {
		
		if ( function_exists('current_user_can') && current_user_can('edit_plugins') ) {
			check_admin_referer('FeedStats_nonce');
			feedstats_genereta_tables();
		
			echo '<div class="updated fade"><p>' . __('Allready update the tables!', 'feedstats') . '</p></div>';
		} else {
			wp_die('<p>'.__('You do not have sufficient permissions to edit plugins for this blog.').'</p>');
		}
	}

	if ( isset($_POST['action']) && ($_POST['action'] == 'insert') && $_POST['fs_ifs_save'] ) {
	
		if ( function_exists('current_user_can') && current_user_can('edit_plugins') ) {
			check_admin_referer('FeedStats_nonce');

			// for a smaller database
			function feedstats_get_update($option) {
				
				if ( ! isset($_POST[$option]) || ($_POST[$option] == '0') || $_POST[$option] == '') {
					delete_option($option);
				} else {
					update_option($option , esc_attr( $_POST[$option] ) );
				}
			}
			
			feedstats_get_update('fs_view_days');	
			feedstats_get_update('fs_days');
			feedstats_get_update('fs_user_level');
			feedstats_get_update('fs_session_timeout');
			feedstats_get_update('fs_visits_online');
			feedstats_get_update('fs_ifs_not_tracked');
			feedstats_get_update('fs_ifs_dashboardinfo');
			
			echo '<div class="updated fade"><p>' . __('The options have been saved!', 'feedstats') . '</p></div>';
		} else {
			wp_die('<p>'.__('You do not have sufficient permissions to edit plugins for this blog.').'</p>');
		}
	}
	
	if ( isset($_POST['action']) && ($_POST['action'] == 'deactivate') && $_POST['feedstats_ifs_deactivate'] ) {

		if ( function_exists('current_user_can') && current_user_can('edit_plugins') ) {
			check_admin_referer('FeedStats_nonce');
			
			$wpdb->query ("DROP TABLE {$wpdb->prefix}fs_data");
			$wpdb->query ("DROP TABLE {$wpdb->prefix}fs_visits");
			
			delete_option('fs_days');
			delete_option('fs_view_days');
			delete_option('fs_user_level');
			delete_option('fs_session_timeout');
			delete_option('fs_visits_online');
			delete_option('fs_ifs_not_tracked');
			delete_option('fs_ifs_dashboardinfo');

			echo '<div class="updated fade"><p>' . __('The options have been deleted!', 'feedstats') . '</p></div>';
		} else {
			wp_die('<p>' . __('You do not have sufficient permissions to edit plugins for this blog.') . '</p>');
		}
	}
?>

	<br class="clear" />
	
		<div id="poststuff" class="ui-sortable">
			<div class="postbox" >
				<h3><?php _e('FeedStats settings', 'feedstats'); ?></h3>
				<div class="inside">
					<form name="form1" method="post" action="">
						<?php if ( feedstats_nonce_field('FeedStats_nonce') ); ?>
						
						<table summary="feedstats options" class="form-table">
							<tr valign="top">
								<th><?php _e('Days', 'feedstats'); ?></th>
								<td><input name="fs_days" value="<?php echo get_option('fs_days'); ?>" type="text" /><br /><?php _e('Amount of days that is supposed to be saved in the statistics.', 'feedstats'); ?></td>
							</tr>

							<tr valign="top">
								<th scope="row"><?php _e('Days View', 'feedstats'); ?></th>
								<td><input name="fs_view_days" value="<?php echo get_option('fs_view_days'); ?>" type="text" /><br /><?php _e('Amount of days that is supposed to be viewed in the statistics.', 'feedstats'); ?></td>
							</tr>

							<tr valign="top">
								<th scope="row"><?php _e('User Level', 'feedstats'); ?></th>
								<td>
									<?php $fs_user_level = get_option('fs_user_level'); ?>
									<select name="fs_user_level">
										<option value="0"<?php if ($fs_user_level == '0') { echo ' selected="selected"'; } ?>>0 <?php _e('Subscriber', 'feedstats'); ?></option>
										<option value="1"<?php if ($fs_user_level == '1') { echo ' selected="selected"'; } ?>>1 <?php _e('Contributor', 'feedstats'); ?></option>
										<option value="2"<?php if ($fs_user_level == '2') { echo ' selected="selected"'; } ?>>2 <?php _e('Author', 'feedstats'); ?></option>
										<option value="5"<?php if ($fs_user_level == '5') { echo ' selected="selected"'; } ?>>5 <?php _e('Editor', 'feedstats'); ?></option>
										<option value="9"<?php if ($fs_user_level == '9') { echo ' selected="selected"'; } ?>>9 <?php _e('Admin', 'feedstats'); ?></option>
									</select>
									<br /><?php _e('Minimum level of WordPress-user, who is allowed to see the statistics.', 'feedstats'); ?></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Sesssion Timeout', 'feedstats'); ?></th>
								<td><input name="fs_session_timeout" value="<?php echo get_option('fs_session_timeout'); ?>" type="text" /><br /><?php _e('Time of a stay/visit (1hour values 3600seconds is common but might be changed)','feedstats'); ?></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Visit Online', 'feedstats'); ?></th>
								<td><input name="fs_visits_online" value="<?php echo get_option('fs_visits_online'); ?>" type="text" /><br /><?php _e('Visitors onlinetime (5minutes value 300s is a recommendation)', 'feedstats'); ?></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Not tracked', 'feedstats'); ?></th>
								<td><input name="fs_ifs_not_tracked" value="<?php echo get_option('fs_ifs_not_tracked'); ?>" type="text" /><br /><?php _e('IP, that is supposed not to be saved, ex.: your own IP', 'feedstats'); echo '<code> ' . esc_attr( $_SERVER['REMOTE_ADDR'] ) . '</code>'; ?></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e('Dashboardinfo', 'feedstats'); ?></th>
								<td><input name="fs_ifs_dashboardinfo" value='1' <?php if (get_option('fs_ifs_dashboardinfo') === '1') { echo "checked='checked'";  } ?> type="checkbox" /><br /><?php _e('Statistics can be shown on the dashboard ?', 'feedstats'); ?></td>
							</tr>
						</table>
						<p class="submit">
							<input type="hidden" name="action" value="insert" />
							<input class="button-primary" type="submit" name="fs_ifs_save" value="<?php _e('Update Options'); ?> &raquo;" />
						</p>
					</form>
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable">
			<div class="postbox" >
				<h3><?php _e('Add Index', 'feedstats'); ?></h3>
				<div class="inside">
					<p><?php _e('The follow button add index to the table of thsi plugin for a better performance. Do you have install the plugin new at version 3.6.4? Then is this not necessary.', 'feedstats'); ?></p>
					<form name="form2" method="post" action="">
						<?php feedstats_nonce_field('FeedStats_nonce'); ?>
						<p class="submit">
							<input type="hidden" name="action" value="add_index" />
							<input class="button" type="submit" name="feedstats_add_index" value="<?php _e('Add Index', 'feedstats'); ?> &raquo;" />
						</p>
					</form>
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable">
			<div class="postbox" >
				<h3><?php _e('Delete Options', 'feedstats'); ?></h3>
				<div class="inside">
					<p><?php _e('The follow button delete all tables and options for the FeedStats plugin. <strong>Attention: </strong>You <strong>cannot</strong> undo any changes made by this plugin.', 'feedstats'); ?></p>
					<form name="form2" method="post" action="">
						<?php feedstats_nonce_field('FeedStats_nonce'); ?>
						<p class="submit">
							<input type="hidden" name="action" value="deactivate" />
							<input class="button" type="submit" name="feedstats_ifs_deactivate" value="<?php _e('Delete Options', 'feedstats'); ?> &raquo;" />
						</p>
					</form>
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable">
			<div class="postbox" >
				<h3><?php _e('Information on the plugin', 'feedstats') ?></h3>
				<div class="inside">
					<p><?php _e('Plugin created by <a href="http://www.anieto2k.com">Andr&eacute;s Nieto</a>, in cooperation/base with plugin <a href="http://www.deltablog.com/">PopStats</a>. German and english adjustments, little extensions and new coding by <a href="http://bueltge.de">Frank Bueltge</a>. Thx to <a href="http://blog.tomk32.de">Thomas R. Koll</a> for many improvements for a better code and performance.', 'feedstats'); ?></p>
					<p><?php _e('Further information: Visit the <a href="http://bueltge.de/wp-feedstats-de-plugin/171/">plugin homepage</a> for further information or to grab the latest version of this plugin.', 'feedstats'); ?><br />&copy; Copyright 2007 - <?php echo date("Y"); ?> <a href="http://bueltge.de">Frank B&uuml;ltge</a> | <?php _e('You want to thank me? Visit my <a href=\'http://bueltge.de/wunschliste\'>wishlist</a>.', 'feedstats'); ?></p>
					<!-- <?php echo get_num_queries(); ?> queries. <?php timer_stop(1); ?> seconds. -->
				</div>
			</div>
		</div>

		<script type="text/javascript">
		<!--
		<?php if ( version_compare( $wp_version, '2.6.999', '<' ) ) { ?>
		jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
		<?php } ?>
		jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
		jQuery('.postbox.close-me').each(function(){
			jQuery(this).addClass("closed");
		});
		//-->
		</script>
		
	</div>

<?php } //End Options-Page ?>
