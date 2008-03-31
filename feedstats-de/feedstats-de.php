<?php
/*
Plugin Name: FeedStats
Plugin URI: http://bueltge.de/wp-feedstats-de-plugin/171/
Description: Simple statistictool for feeds.
Version: 3.6
Author: <a href="http://www.anieto2k.com">Andres Nieto Porras</a> and <a href="http://bueltge.de">Frank Bueltge</a>
*/

define('FEEDSTATS_VERSION', '3.6');
define('fs_DAY', 60*60*24);

/*
------------------------------------------------------
 ACKNOWLEDGEMENTS
------------------------------------------------------
Thx to Thomas R. Koll - http://blog.tomk32.de
for many improvements for a better code and performance

Thx to Frank Bueltge - http://bueltge.de
Statistic, multilingualism and improvements

Thx to Neil - http://wmfield.idv.tw
for traditional Chinese (zh_TW) translation

Thx to burningHat - http://blog.burninghat.net
for french (fr_FR) translation

Thx to Baris Unver - http://beyn.org
for turkish (tr_TR) translation

FeedReaderButton (gif) by http://www.nasendackel.de
FeedReaderButton (gif - traditional Chinese (zh_TW))
 by http://www.wmfield.idv.tw/485
------------------------------------------------------
*/

/*
------------------------------------------------------
 FEED-READER BUTTON
------------------------------------------------------
Function for button with reader:
------------------------------------------------------
fs_getfeeds_button()

Example:
------------------------------------------------------
<div id="feeds_button"><?php fs_getfeeds_button(); ?></div>


Example for style-css:
------------------------------------------------------
#feeds_button {
	width: 74px;
	height: 14px;
	text-align: left;
	font-size: 10px;
	padding: 1px 15px 15px 3px;
	color: #fff;
	background: url(wp-content/plugins/feedstats-de/feedstats-de.gif) no-repeat;
	margin-bottom: 2px;
}

Example for style-css in traditional Chinese (zh_TW) translation:
------------------------------------------------------
	background: url(wp-content/plugins/feedstats-de/feedstats-de-zh_TW.gif) no-repeat;
*/

if(function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain('feedstats', 'wp-content/plugins/feedstats-de');
}

$location = get_option('siteurl') . '/wp-admin/options-general.php?page=feedstats-de/feedstats-de.php'; // Form Action URI

// Installation functions
function fs_generateDB() {
	global $wpdb;
	
	$fs_data_query = "CREATE TABLE " . $wpdb->prefix . "fs_data (
					time_install int(11) NOT NULL default '0',
					max_visits mediumint(8) unsigned NOT NULL default '0',
					max_visits_time int(11) NOT NULL default '0',
					max_online mediumint(8) unsigned NOT NULL default '0',
					max_online_time int(11) NOT NULL default '0'
				) TYPE=MyISAM;";
	$fs_visits_query = "CREATE TABLE " . $wpdb->prefix . "fs_visits (
					visit_id mediumint(8) unsigned NOT NULL auto_increment,
					ip varchar(20) NOT NULL default '',
					url varchar(255) NOT NULL default '',
					time_begin int(11) NOT NULL default '0',
					time_last int(11) NOT NULL default '0',
					PRIMARY KEY (visit_id)
				) TYPE=MyISAM;";
	
	if (file_exists(ABSPATH . '/wp-admin/includes/upgrade.php')) {
		@require_once (ABSPATH . '/wp-admin/includes/upgrade.php');
	} elseif (file_exists(ABSPATH . WPINC . '/upgrade-functions.php')) {
		@require_once (ABSPATH . WPINC . '/upgrade-functions.php');
	} elseif (file_exists(ABSPATH . '/wp-admin/upgrades.php')) {
		@require_once (ABSPATH . '/wp-admin/upgrades.php');
	} else {
		die (__('Error in file: ' . __FILE__ . ' on line: ' . __LINE__ . '.<br />The Wordpress file "upgrade-functions.php" or "upgrade.php" could not be included.'));
	}
	
	maybe_create_table($wpdb->prefix . 'fs_data', $fs_data_query);
	maybe_create_table($wpdb->prefix . 'fs_visits', $fs_visits_query);

	$time = time();
	
	$count_data = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_data');
	if ($count_data==0) {
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "fs_data (time_install, max_visits, max_visits_time) VALUES (".$time.",0,".$time.")");
	}
}

// Upgrade functions
function FeedStats_versionControl() {
	global $wpdb;
	
	// Version 1.6.0 to 1.7.0
	// Added 4 more fields to visits table: referer, platform, browser and version
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD referer varchar(255) NOT NULL default '' AFTER ip";
	maybe_add_column($wpdb->prefix . 'fs_visits','referer',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD platform varchar(50) NOT NULL default '' AFTER referer";
	maybe_add_column($wpdb->prefix . 'fs_visits','platform',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD browser varchar(50) NOT NULL default '' AFTER platform";
	maybe_add_column($wpdb->prefix . 'fs_visits','browser',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD version varchar(15) NOT NULL default '' AFTER browser";
	maybe_add_column($wpdb->prefix . 'fs_visits','version',$create_dll);
	// Added 2 more fields to data table: max_online and max_online_time
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_data ADD max_online mediumint(8) unsigned NOT NULL default '0' AFTER max_hits_time";
	maybe_add_column($wpdb->prefix . 'fs_data','max_online',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_data ADD max_online_time int(11) NOT NULL default '0' AFTER max_online";
	maybe_add_column($wpdb->prefix . 'fs_data','max_online_time',$create_dll);
	
	// Version 1.7.0 to 1.7.1
	// Fixed search engine user agent which should return no version
	$wpdb->query("UPDATE " . $wpdb->prefix . "fs_visits SET version='' WHERE browser='Crawler/Search Engine'");
	// Added 1 more field to visits table: search_terms
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD search_terms varchar(255) NOT NULL default '' AFTER version";
	maybe_add_column($wpdb->prefix . 'fs_visits','search_terms',$create_dll);
	
	// Version 1.7.4 to 1.8.0
	// Added 1 more field to visits table: url
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD url varchar(255) NOT NULL default '' AFTER ip";
	maybe_add_column($wpdb->prefix . 'fs_visits','url',$create_dll);
}

// Global set of functions
function FeedStats_tr($s) {
	global $FeedStats_tr;

	$return = ($FeedStats_tr[$s]!='') ? $FeedStats_tr[$s] : $s;
	if (get_bloginfo('charset') == 'UTF-8') {
		$return = utf8_encode($return);
	}
	return $return;
}

function FeedStats_resetDB() {
	global $wpdb;
	
	$wpdb->get_var("DROP TABLE " . $wpdb->prefix . "fs_visits," . $wpdb->prefix . 'fs_data');
	fs_generateDB();
}

function fs_getIP() {
	global $_SERVER;
	
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		return($_SERVER['HTTP_CLIENT_IP']);
	} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return($_SERVER['HTTP_X_FORWARDED_FOR']);
	} else if (isset($_SERVER['REMOTE_ADDR'])) {
		return($_SERVER['REMOTE_ADDR']);
	} else {
		return($_SERVER['REMOTE_HOST']);
	}
}

function fs_getMidnight($time) {
	return date('U', mktime(0, 0, 0, 1, date('z', $time)+1, date('y',$time)));
}

// Main/System functions
function FeedStats_track($title = '', $more_link_text = '', $stripteaser = '', $more_file = '', $cut = '', $encode_html = '') {
	if (!is_feed()) {
		return;
	}
	
	global $wpdb, $_SERVER;

	$time = time();
	$url  = $_SERVER['REQUEST_URI'];


	if ($url == get_bloginfo('rdf_url')) {
		$url = "RDF";
	} else if ($url == get_bloginfo('rss_url')) {
		$url = "RSS";
	} else if ($url == get_bloginfo('rss2_url')) {
		$url = "RSS2";
	} else if ($url == get_bloginfo('atom_url')) {
		$url = "ATOM";
	} else if ($url == get_bloginfo('comments_rss2_url')) {
		$url = "COMMENT RSS";
	} else if ($url == get_bloginfo('comments_atom_url')) {
		$url = "COMMENT ATOM";
	}

	$time_delete = fs_getMidnight($time-(fs_DAY*get_option('fs_days')));	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "fs_visits WHERE time_begin < ".$time_delete);
	
	if(in_array(fs_getIP(), array(get_option("fs_ifs_not_tracked")))) {
		return $title;
		return $more_link_text;
		return $stripteaser;
		return $more_file;
		return $cut;
		return $encode_html;
	}
		
	$time_insert_visit = $time - get_option('fs_session_timeout');
	
	if ($wpdb->is_admin || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
		$sessions = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE ip='" . fs_getIP() . "' AND time_last > " . $time_insert_visit);
		if ($sessions>0) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "fs_visits SET time_last=" . $time . ",url='" . $url . "' WHERE ip='" . fs_getIP() . "' AND time_last > " . $time_insert_visit);
		}
		return;
	}
	
	$ip_time_query = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE ip='".fs_getIP()."' AND time_last > " . $time_insert_visit);
	
	if ($ip_time_query==0) {
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "fs_visits (ip, url, time_begin, time_last) VALUES ('" . fs_getIP() . "','" . $url . "'," . $time . "," . $time . ")");
	} else {
		$wpdb->query("UPDATE " . $wpdb->prefix . "fs_visits SET time_last=" . $time . ",url='" . $url . "' WHERE ip='" . fs_getIP() . "' AND time_last > " . $time_insert_visit);
	}
	
	$time_start       = fs_getMidnight($time);
	$time_end         = $time_start + fs_DAY;
	$count_visits_day = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE time_begin >= " . $time_start . " AND time_begin < " . $time_end);
	$max_visits       = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');

	if ($count_visits_day>=$max_visits) {
		$wpdb->query("UPDATE " . $wpdb->prefix . "fs_data SET max_visits = " . $count_visits_day . ", max_visits_time = " . $time);		
	}
	
	$time_visits_online = $time - get_option('fs_visits_online');
	$count_online = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "fs_visits WHERE time_last > " . $time_visits_online);
	$max_online = $wpdb->get_var("SELECT max_online FROM " . $wpdb->prefix . 'fs_data');
	
	if ($count_online>=$max_online) {
		$wpdb->query("UPDATE " . $wpdb->prefix . "fs_data SET max_online = " . $count_online . ", max_online_time = " . $time);
	}
	
	return $title;
	return $more_link_text;
	return $stripteaser;
	return $more_file;
	return $cut;
	return $encode_html;
}

function FeedStats_displayStats() {
	global $wpdb;

	if ($_GET['fs_action'] == 'reset')
		FeedStats_resetDB();
		
	$time = time();
	
	$time_begin = fs_getMidnight($wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data'));
	$num_days   = ceil(($time-$time_begin)/fs_DAY);
	$num_days   = htmlspecialchars($num_days, ENT_QUOTES);
	if ($num_days>get_option('fs_days')) {
		$num_days = get_option('fs_days') + 1;
	}
	$visits = array();
	$count_visits_total  = 0;
	
	for ($i=0; $i<$num_days; $i++) {
		$day_time          = $time - ($i * fs_DAY);
		$time_start        = fs_getMidnight($day_time);
		$time_end          = $time_start + fs_DAY;
		$count_visits_day  = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE time_begin >= " . $time_start . " AND time_begin < " . $time_end);
		$visits[$day_time] = $count_visits_day;
		
		if ($i!=0 && $time_start!=$time_begin) {
			$count_visits_total += $count_visits_day;
		}
		
	}
	
	$total_visits    = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits    = htmlspecialchars($total_visits, ENT_QUOTES);
	
	$average_visits  = ($num_days) ? round($total_visits/($num_days)) : '0';
	$average_visits  = htmlspecialchars($average_visits, ENT_QUOTES);

	$max_visits      = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');
	$max_visits      = htmlspecialchars($max_visits, ENT_QUOTES);
	
	$max_visits_time = date(get_option('date_format'), $wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_visits_time = htmlspecialchars($max_visits_time, ENT_QUOTES);
	
	$max_online      = $wpdb->get_var("SELECT max_online FROM " . $wpdb->prefix . 'fs_data');
	$max_online      = htmlspecialchars($max_online, ENT_QUOTES);
	
	$max_online_time = date(get_option('date_format') . " " . get_option('time_format'), $wpdb->get_var("SELECT max_online_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_online_time = htmlspecialchars($max_online_time, ENT_QUOTES);

	$referers_query  = $wpdb->get_results("SELECT DISTINCT url FROM " . $wpdb->prefix . "fs_visits WHERE LEFT(url, '" . strlen(get_settings('home')) . "') != '" . get_settings('home') . "' AND url <> '' ORDER BY url DESC");
	
	$referers        = array();
	
	if ($referers_query) {
		foreach ($referers_query as $r) {
			$refer['cont']  = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE url = '" . $r->url . "';");
			$refer['cont']  = htmlspecialchars($refer['cont']);
			$refer['title'] = $r->url;
			$refer['title'] = htmlspecialchars($refer['title']);
			array_push($referers,$refer);
		}
	}

	$time_visits_online = $time - get_option('fs_visits_online');
	
	$online_query       = $wpdb->get_results("SELECT ip,url FROM " . $wpdb->prefix . "fs_visits WHERE time_last > ".$time_visits_online);
	
	$online             = array();
	
	if ($online_query) {
		foreach ($online_query as $visit) {
			$o['ip']  = $visit->ip;
			$o['url'] = $visit->url;
			array_push($online,$o);
		}
	}
	
	$people_online = count($online);	
	
?>
	
	<div class="wrap">
		<p id="feeds_button"><?php fs_getfeeds_button(); ?></p>
		<h2>FeedStats</h2>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" summary="feedstast view">
			<tr valign="top">
				<td colspan="3" align="center" valign="top" style="height:160px;">
	
					<table align="center" style="height: 140px; border: 1px solid #CCC;" summary="feedstast view two">
						<tr valign="top">
							<th colspan="<?php echo count($visits); ?>" align="center"><?php echo FeedStats_tr(__('Visits', 'feedstats')); ?></th>
						</tr>
						<tr>
							<?php ksort($visits); foreach ($visits as $day=>$num) { ?>
							<td align="center" style="padding-left: 5px; font-size: 10px; color:#A3A3A3;"><?php echo $num; ?></td>
							<?php } ?>
							<td align="center" style="padding-left: 5px; font-size: 10px; color: #CCC"><?php echo $average_visits; ?></td>
							<td align="center" style="padding-left: 5px; font-size: 10px; color:#FF0000"><?php echo $max_visits; ?></td>
						</tr>
						<tr>
							<?php foreach ($visits as $day=>$num) { ?>
							<td valign="bottom" align="center"><div title="<?php echo date('j. M',$day), ": ", $num; ?>" style="width: 16px; height: <?php echo round(100*($num/$max_visits)); ?>px; background-color: #A3A3A3; border-bottom: 1px solid #A3A3A3;">&nbsp;</div></td>
							<?php } ?>
							<td valign="bottom" style="padding-left: 5px;"><div title="<?php echo _e('Average', 'feedstats').": ".$average_visits; ?>" style="width: 16px; height: <?php echo round(100*($average_visits/$max_visits)); ?>px; background-color: #CCC; border-bottom: 1px solid #CCC;">&nbsp;</div></td>
							<td valign="bottom" style="padding-left: 5px;"><div title="<?php echo _e('Maximum', 'feedstats').": ".$max_visits; ?>" style="width: 16px; height: <?php echo round(100*($max_visits/$max_visits)); ?>px; background-color:#FFCC66; border-bottom: 1px solid #CCC;">&nbsp;</div></td>
						</tr>
						<tr>
							<?php foreach ($visits as $day=>$num) { ?>
							<td align="center" style="font-size: 10px;"><?php echo date('j',$day); ?></td>
							<?php } ?>
							<td align="center" style="padding-left: 5px; font-size: 10px;">&Oslash;</td>
							<td align="center" style="padding-left: 5px; font-size: 10px; color:#FF0000">Max</td>
						</tr>
					</table>
	
				</td>
			</tr>

			<tr valign="top"> 
				<td width="6%" rowspan="2" valign="top">
					
					<table summary="feedstast view one" class="widefat" style="margin: 5px 5px 0 0;">
						<thead>
						<tr valign="top">
							<th><?php echo _e('Day', 'feedstats'); ?></th>
							<th><?php echo _e('Visits', 'feedstats'); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php
							krsort($visits);
							$class = '';
							foreach ($visits as $day=>$num) {
								$class = ($class=='form-invalid') ? '' : 'form-invalid';
								if (date('j M Y',$day)==date('j M Y',time())) {
									$day_s = __('Today', 'feedstats');
								} else if (date(get_option('date_format'),$day)==date(get_option('date_format'),$time_begin)) {
									$day_s = __('First Day', 'feedstats');
								} else {
									$day_s = date('j.M',$day);
								}
						?>
						<tr class="<?php echo $class; ?>">
							<th><?php echo $day_s; ?></th>
							<td><?php echo $num; ?></td>
						</tr>
						<?php } //end foreach ?>
					</tbody>
					</table> 
					
				</td>
			</tr>
			<tr valign="top"> 
				<td width="45%" align="right" valign="top" >
					
					<table style="margin: 5px 5px 0 0;" summary="feedstast view three" class="widefat">
						<thead>
							<tr valign="top"> 
							<th width="278" colspan="2" scope="col"><?php echo str_replace('%N%', get_option('fs_num_referers'), __('Last %N% Referer', 'feedstats')); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($referers) {
								arsort($referers);
								$class = '';
								foreach ($referers as $r) {
									$class = ($class=='form-invalid') ? '' : 'form-invalid';
							?>
							<tr class="<?php echo $class; ?>"> 
								<td><?php echo htmlspecialchars($r['cont']); ?></td>
								<td><?php echo htmlspecialchars((strlen($r['title'])>50) ? substr_replace($r['title'],"...",50) : $r['title']); ?></td>
							</tr>
							<?php } //end foreach
							} //end if ?>
						</tbody>
					</table>
					
				</td>
				<td width="35%" align="right" valign="top">
					
					<table style="margin: 5px 0 0 0;" summary="feedstast view four" class="widefat">
						<thead>
							<tr> 
								<th><?php echo _e('Statistic', 'feedstats'); ?></th>
								<th><?php echo _e('Visits', 'feedstats'); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr class="form-invalid"> 
								<th><?php echo _e('Average', 'feedstats'); ?></th>
								<td><?php echo ($average_visits!=0) ? $average_visits : '-' ?></td>
							</tr>
							<tr> 
								<th><?php echo _e('Maximum', 'feedstats'); ?></th>
								<td><?php echo $max_visits ?> (<?php echo $max_visits_time ?>)</td>
							</tr>
							<tr class="form-invalid"> 
								<th><?php echo _e('Total', 'feedstats'); ?> <?php echo _e(' (Last ', 'feedstats') . $num_days . __(' Days)', 'feedstats'); ?></th>
								<td><?php echo $total_visits ?></td>
							</tr>
							<tr> 
								<th><?php echo _e('Maximum online', 'feedstats'); ?></th>
								<td><?php echo $max_online ?> (<?php echo $max_online_time ?>)</td>
							</tr>
							</tbody>
					</table>
					
				</td>
			</tr>
		</table>
	
		<h3><?php echo _e('Reset Statistic', 'feedstats'); ?></h3>
		<p><a href="index.php?page=feedstats-de/feedstats-de.php&amp;fs_action=reset" onclick="return confirm('<?php echo _e('You are about to delete all data and reset stats. OK to delete, Cancel to stop', 'feedstats'); ?>');">&raquo;&raquo; <?php echo _e('Reset Statistic', 'feedstats'); ?> &laquo;&laquo;</a></p>
	</div>

<?php
}

function FeedStats_addAdminMenu() {
	add_submenu_page('index.php', 'FeedStats', 'FeedStats', get_option('fs_user_level'), __FILE__, 'FeedStats_displayStats');
	add_options_page(__('Konfiguration FeedStats', 'feedstats'), 'FeedStats', 9, __FILE__, 'fb_admin_feedstats_option_page');
}

function fs_getfeeds() {
	global $wpdb;
	
	$total_visits = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits = htmlspecialchars($total_visits, ENT_QUOTES);

	$time       = time();
	$time_begin = fs_getMidnight($wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data'));
	$num_days   = ceil(($time-$time_begin)/fs_DAY);
	$num_days   = htmlspecialchars($num_days, ENT_QUOTES);
	
	if ($num_days>get_option('fs_days')) {
		$num_days = get_option('fs_days') + 1;
	}
		
	$average_visits  = ($num_days) ? round($total_visits/($num_days)) : '0';
	$average_visits  = htmlspecialchars($average_visits, ENT_QUOTES);
	
	$max_visits      = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');
	$max_visits      = htmlspecialchars($max_visits, ENT_QUOTES);
	
	$max_visits_time = date(get_option('date_format'), $wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_visits_time = htmlspecialchars($max_visits_time, ENT_QUOTES);
	?>
	<div id="feeds_readers">
		<h3><?php echo FeedStats_tr(__('FeedReaders', 'feedstats')); ?></h3>
		<ul>
			<li><?php echo _e('Total', 'feedstats'), ": ", attribute_escape($total_visits); ?><small><?php echo __(' (Last ', 'feedstats') . $num_days . __(' Days)', 'feedstats'); ?></small></li>
			<li><?php echo _e('Maximum', 'feedstats'), ": ", attribute_escape($max_visits); ?> <small>(<?php echo $max_visits_time; ?>)</small></li>
			<li><?php echo _e('Average', 'feedstats'), ": ", attribute_escape($average_visits); ?></li>
		</ul>
	</div>
	<?php
}

// feedstats-button
function fs_getfeeds_button() {
	global $wpdb;
	
	$total_visits = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits = htmlspecialchars($total_visits, ENT_QUOTES);
	
	$time       = time();
	$time_begin = fs_getMidnight($wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data'));
	$num_days   = ceil(($time-$time_begin)/fs_DAY);
	
	if ($num_days>get_option('fs_days')) {
		$num_days = get_option('fs_days') + 1;
	}
		
	$average_visits = ($num_days) ? round($total_visits/($num_days)) : '0';
	$average_visits = htmlspecialchars($average_visits, ENT_QUOTES);
	
	echo $average_visits;
}

// style im header
function FeedStats_Admin_Header() {
	$fs_feed_button_style = '<style type="text/css" media="screen">';
	$fs_feed_button_style.= '#feeds_button {
		float: right;
		width: 74px;
		height: 14px;
		text-align: left;
		font-size: 10px;
		padding: 1px 15px 15px 3px;
		color: #fff;
		background: url('.get_settings('home').'/wp-content/plugins/feedstats-de/feedstats-de.gif) no-repeat 0 1px;
		margin: 0;
	}';
	$fs_feed_button_style.= '</style>';
	$fs_feed_button_style.= "\n";
	
	print($fs_feed_button_style);
}

// wp-dashboard (Tellerrand) information
function FeedStats_Admin_Footer() {
	global $wpdb;

	$total_visits = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits = htmlspecialchars($total_visits, ENT_QUOTES);

	$time        = time();
	$time_begin  = fs_getMidnight($wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data'));
	$num_days    = ceil(($time-$time_begin)/fs_DAY);
	
	if ($num_days>get_option('fs_days')) {
		$num_days = get_option('fs_days') + 1;
	}
		
	$average_visits = ($num_days) ? round($total_visits/($num_days)) : '0';
	$average_visits = htmlspecialchars($average_visits, ENT_QUOTES);
	
	$max_visits = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');
	$max_visits = htmlspecialchars($max_visits, ENT_QUOTES);
	
	//$max_visits_time = date('j. F Y',$wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_visits_time = htmlspecialchars(strftime('%d. %B %Y',$wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data')), ENT_QUOTES);
	
	$content = '<h3>' . __('FeedStats', 'feedstats') . ' <a href="admin.php?page=feedstats-de/feedstats-de.php">&raquo;</a></h3>';
	$content .= '<ul><li>' . __('Total', 'feedstats') . ': ' . attribute_escape($total_visits) . __(' (Last ', 'feedstats') . $num_days . __(' Days)', 'feedstats') . '</li>';
	$content.= '<li>' . __('Maximum', 'feedstats') . ': ' . attribute_escape($max_visits) . ' (' . attribute_escape($max_visits_time) . ')</li>';
	$content.= '<li>' . __('Average', 'feedstats') . ': ' . attribute_escape($average_visits) . '</li>';
	$content.= "</ul>";
		
	print $content;
}


// Program flow
function FeedStats_activate() {
	add_option("fs_days", "15");
	add_option("fs_user_level", "1");
	add_option("fs_session_timeout", "3600");
	add_option("fs_visits_online", "300");
}

if ( function_exists('register_activation_hook') ) {
	register_activation_hook(__FILE__, 'fs_generateDB');
	register_activation_hook(__FILE__, 'FeedStats_activate');
	register_activation_hook(__FILE__, 'FeedStats_versionControl');
}


if ( function_exists('add_action') ) {
	add_action('the_title_rss', 'FeedStats_track');
	add_action('the_content_rss', 'FeedStats_track');
	add_action('admin_menu', 'FeedStats_addAdminMenu');
	
	if (strpos($_SERVER['REQUEST_URI'], 'page=wp-feedstats/feedstats-de') !== false) {
		add_action('admin_head', 'FeedStats_Admin_Header');
	}
	
	$admin = dirname($_SERVER['SCRIPT_FILENAME']);
	$admin = substr($admin, strrpos($admin, '/')+1);
	
	if ($admin == 'wp-admin' && basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' && get_option('fs_ifs_dashboardinfo') == '1') {
		add_action('activity_box_end', 'FeedStats_Admin_Footer');
	}
}


// some basic security with nonce
if ( !function_exists('wp_nonce_field') ) {
	function FeedStats_nonce_field($action = -1) { return; }
	$FeedStats_nonce = -1;
} else {
	function FeedStats_nonce_field($action = -1) { return wp_nonce_field($action); }
	$FeedStats_nonce = 'FeedStats-update-key';
}


// Option Page
function fb_admin_feedstats_option_page() {
	global $wpdb;

	if ( ($_POST['action'] == 'insert') && $_POST['fs_ifs_save'] ) {
	
		if ( function_exists('current_user_can') && current_user_can('edit_plugins') ) {
			check_admin_referer($FeedStats_nonce);

			update_option("fs_days", $_POST['fs_days']);	
			update_option("fs_user_level", $_POST['fs_user_level']);	
			update_option("fs_session_timeout", $_POST['fs_session_timeout']);		
			update_option("fs_visits_online", $_POST['fs_visits_online']);		
			update_option("fs_ifs_not_tracked", $_POST['fs_ifs_not_tracked']);
			update_option("fs_ifs_dashboardinfo", $_POST['fs_ifs_dashboardinfo']);
			
			echo '<div class="updated"><p>' . __('The options have been saved!', 'feedstats') . '</p></div>';
		} else {
			wp_die('<p>'.__('You do not have sufficient permissions to edit plugins for this blog.').'</p>');
		}
	}
	
	if ( ($_POST['action'] == 'deactivate') && $_POST['fs_ifs_deactivate'] ) {

		if ( function_exists('current_user_can') && current_user_can('edit_plugins') ) {
			check_admin_referer($FeedStats_nonce);
			
			$wpdb->query ("DROP TABLE {$wpdb->prefix}fs_data");
			$wpdb->query ("DROP TABLE {$wpdb->prefix}fs_visits");
			
			delete_option("fs_days", $_POST['fs_days']);	
			delete_option("fs_user_level", $_POST['fs_user_level']);	
			delete_option("fs_session_timeout", $_POST['fs_session_timeout']);		
			delete_option("fs_visits_online", $_POST['fs_visits_online']);		
			delete_option("fs_ifs_not_tracked", $_POST['fs_ifs_not_tracked']);
			delete_option("fs_ifs_dashboardinfo", $_POST['fs_ifs_dashboardinfo']);

			echo '<div class="updated"><p>' . __('The options have been deleted!', 'feedstats') . '</p></div>';
		} else {
			wp_die('<p>'.__('You do not have sufficient permissions to edit plugins for this blog.').'</p>');
		}
	}
?>

<div class="wrap">
	<h2><?php echo _e('FeedStats settings', 'feedstats'); ?></h2>
	<h3><?php _e('FeedStats settings', 'feedstats'); ?></h3>
	<form name="form1" method="post" action="<?php echo $location; ?>">
		<?php FeedStats_nonce_field($FeedStats_nonce); ?>

		<table summary="feedstats options" class="form-table">
			<tr valign="top">
				<th><?php echo _e('Days', 'feedstats'); ?></th>
				<td><input name="fs_days" value="<?php echo get_option("fs_days"); ?>" type="text" /><br /><?php echo _e('Amount of days that is supposed to be saved in the statistics.', 'feedstats'); ?></td>
			</tr>
			<tr valign="top">
				<th><?php echo _e('User Level', 'feedstats'); ?></th>
				<td><input name="fs_user_level" value="<?php echo get_option("fs_user_level"); ?>" type="text" /><br /><?php echo _e('Minimum level of WordPress-user, who is allowed to see the statistics.', 'feedstats'); ?></td>
			</tr>
			<tr valign="top">
				<th><?php echo _e('Sesssion Timeout', 'feedstats'); ?></th>
				<td><input name="fs_session_timeout" value="<?php echo get_option("fs_session_timeout"); ?>" type="text" /><br /><?php echo _e('Time of a stay/visit (1hour values 3600seconds is common but might be changed)','feedstats'); ?></td>
			</tr>
			<tr valign="top">
				<th><?php echo _e('Visit Online', 'feedstats'); ?></th>
				<td><input name="fs_visits_online" value="<?php echo get_option("fs_visits_online"); ?>" type="text" /><br /><?php echo _e('Visitors onlinetime (5minutes value 300s is a recommendation)', 'feedstats'); ?></td>
			</tr>
			<tr valign="top">
				<th><?php echo _e('Not tracked', 'feedstats'); ?></th>
				<td><input name="fs_ifs_not_tracked" value="<?php echo get_option("fs_ifs_not_tracked"); ?>"  type="text" /><br /><?php echo _e('IP, that is supposed not to be saved, ex.: your own IP', 'feedstats'); echo '<small> ' . $_SERVER['REMOTE_ADDR'] . '</small>' ;?></td>
			</tr>
			<tr valign="top">
				<th><?php echo _e('Dashboardinfo', 'feedstats'); ?></th>
				<td><input name="fs_ifs_dashboardinfo" value='1' <?php if(get_option('fs_ifs_dashboardinfo')=='1') { echo "checked='checked'";  } ?> type="checkbox" /><br /><?php echo _e('Statistics can be shown on the dashboard ?', 'feedstats'); ?></td>
			</tr>
		</table>
		<p class="submit">
			<input type="hidden" name="action" value="insert" />
			<input type="submit" name="fs_ifs_save" value="<?php _e('Update Options'); ?> &raquo;" />
		</p>
	</form>

	<h3><?php _e('Delete Options', 'feedstats'); ?></h3>
	<p><?php _e('The follow button delete all tables and options for the FeedStats plugin. Please use it, <strong>before</strong> deactivate the plugin.<br /><strong>Attention: </strong>You <strong>cannot</strong> undo any changes made by this plugin.', 'feedstats'); ?></p>
	<form name="form2" method="post" action="<?php echo $location; ?>">
		<?php FeedStats_nonce_field($FeedStats_nonce); ?>
		<p class="submit">
			<input type="hidden" name="action" value="deactivate" />
			<input type="submit" name="fs_ifs_deactivate" value="<?php _e('Delete Options'); ?> &raquo;" />
		</p>
	</form>

	<h3><?php _e('Information on the plugin', 'feedstats') ?></h3>
	<p><?php echo _e('Plugin created by <a href="http://www.anieto2k.com">Andr&eacute;s Nieto</a>, in cooperation/base with plugin <a href="http://www.deltablog.com/">PopStats</a>. German and english adjustments, little extensions and new coding by <a href="http://bueltge.de">Frank Bueltge</a>. Thx to <a href="http://blog.tomk32.de">Thomas R. Koll</a> for many improvements for a better code and performance.', 'feedstats'); ?></p>
	<p><?php _e('Further information: Visit the <a href="http://bueltge.de/wp-feedstats-de-plugin/171/">plugin homepage</a> for further information or to grab the latest version of this plugin.', 'feedstats'); ?><br />&copy; Copyright 2007 - <?php echo date("Y"); ?> <a href="http://bueltge.de">Frank B&uuml;ltge</a> | <?php _e('You want to thank me? Visit my <a href=\'http://bueltge.de/wunschliste\'>wishlist</a>.', 'feedstats'); ?></p>
</div>

<?php } //End Options-Page ?>