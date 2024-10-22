<?php
/*
Plugin Name: Advanced Steam Widget
Plugin URI: http://www.SnakeByteStudios.com/projects/apps/advanced-steam-widget/
Description: Displays Steam gaming statistics in a widget
Version: 1.7
Author: Snake
Author URI: http://www.SnakeByteStudios.com
*/

class AdvancedSteamWidget extends WP_Widget {
	//preset templates
	private $presets = array(
		"profile" => array(
			"name" => "Profile Only",
			"game_template" => '',
			"template" => '
<style>
.steam-widget-profile .profile {
	background: #f8f8f8;
	box-sizing: content-box;
	height: 72px;
	color: #666666;
	line-height: 18px;
	font-size: 12px;
}
.steam-widget-profile .profile-icon {
	border: 4px solid #CCCCCC;
	border-radius: 2px;
	float: left;
	margin-right: 8px;
	height: 64px;
	width: 64px;
}
.steam-widget-profile .profile-name {
	font-weight: bold;
	font-size: 16px;
	padding-top: 8px;
}
.steam-widget-profile .online {
	border-color: #a7c9e1;
}
.steam-widget-profile .ingame {
	border-color: #B7D282;
}
</style>
<div class="steam-widget steam-widget-profile">
	<div class="profile">
		<img class="profile-icon IF_INGAME{ingame}ELSE{IF_ONLINE{online}}" src="%AVATAR_MEDIUM%" title="%USERNAME% is IF_INGAME{in-game}ELSE{IF_ONLINE{online}ELSE{offline}}">
		<div class="profile-name"><a href="%PROFILE_URL%">%USERNAME%</a></div>
		<div>%HOURS_TWOWEEKS% hrs / 2 wks</div>
		<div><a href="steam://friends/add/%ID64%" rel="nofollow">Add to Friends</a></div>
	</div>
</div>
'
		),
		"profile-small" => array(
			"name" => "Profile Small",
			"game_template" => '',
			"template" => '
<style>
.steam-widget-profile-small .profile {
	background: #f8f8f8;
	box-sizing: content-box;
	color: #666666;
	font-size: 12px;
	height: 40px;
	line-height: 18px;
}
.steam-widget-profile-small .profile-icon {
	border: 4px solid #CCCCCC;
	border-radius: 2px 2px 2px 2px;
	float: left;
	height: 32px;
	margin-right: 8px;
	width: 32px;
}
.steam-widget-profile-small .profile-name {
	font-size: 16px;
	font-weight: bold;
	line-height: 18px;
	padding-top: 3px;
}
.steam-widget-profile-small .online {
	border-color: #a7c9e1;
}
.steam-widget-profile-small .ingame {
	border-color: #B7D282;
}
</style>
<div class="steam-widget steam-widget-profile-small">
	<div class="profile">
		<img class="profile-icon IF_INGAME{ingame}ELSE{IF_ONLINE{online}}" src="%AVATAR_ICON%" title="%USERNAME% is IF_INGAME{in-game}ELSE{IF_ONLINE{online}ELSE{offline}}">
		<div class="profile-name"><a href="%PROFILE_URL%">%USERNAME%</a></div>
		<div>%HOURS_TWOWEEKS% hrs / 2 wks</div>
	</div>
</div>
'
		),
		"profile-games" => array(
			"name" => "Profile + Games",
			"game_template" => '
<div class="game">
	<a href="%GAME_URL%"><img class="game-icon IF_GAME_INGAME{ingame}" src="%GAME_ICON%" /></a>
	<div class="game-name"><a href="%GAME_URL%" title="%GAME_NAME%">%GAME_NAME%</a></div>
	<div class="game-time">IF_GAME_STATS{<a href="%GAME_STATS_URL%">%GAME_HOURS_TWOWEEKS% hrs</a>}ELSE{%GAME_HOURS_TWOWEEKS% hrs} / two weeks</div>
</div>
			',
			"template" => '
<style>
.steam-widget-profile-games {
	margin-bottom: -8px;
	box-sizing: content-box;
}
.steam-widget-profile-games .profile {
	background: #f8f8f8;
	margin-bottom: 12px;
	height: 72px;
	color: #666666;
	line-height: 18px;
	font-size: 12px;
}
.steam-widget-profile-games .profile-icon {
	border: 4px solid #CCCCCC;
	border-radius: 2px;
	float: left;
	margin-right: 8px;
	height: 64px;
	width: 64px;
}
.steam-widget-profile-games .profile-name {
	font-weight: bold;
	font-size: 16px;
	padding-top: 8px;
}
.steam-widget-profile-games .game {
	clear: both;
	height: 40px;
	margin-bottom: 8px;
}
.steam-widget-profile-games .game-icon {
	border: 4px solid #CCCCCC;
	float: left;
	margin-right: 6px;
	border-radius: 2px;
}
.steam-widget-profile-games .online {
	border-color: #a7c9e1;
}
.steam-widget-profile-games .ingame {
	border-color: #B7D282;
}
.steam-widget-profile-games .game-name, .steam-widget-profile-games .game-time {
	margin: 0;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
</style>
<div class="steam-widget steam-widget-profile-games">
	<div class="profile">
	<img class="profile-icon IF_INGAME{ingame}ELSE{IF_ONLINE{online}}" src="%AVATAR_MEDIUM%" title="%USERNAME% is IF_INGAME{in-game}ELSE{IF_ONLINE{online}ELSE{offline}}">
	<div class="profile-name"><a href="%PROFILE_URL%">%USERNAME%</a></div>
	<div>%HOURS_TWOWEEKS% hrs / 2 wks</div>
	<div>IF_INGAME{In-game}ELSE{IF_ONLINE{Online}ELSE{Offline}}</div>
	</div>
	%GAMES_TWOWEEKS%
</div>
			'
		),
		"games" => array(
			"name" => "Games Only",
			"game_template" => '
<div class="game">
	<a href="%GAME_URL%"><img class="game-icon IF_GAME_INGAME{ingame}" src="%GAME_ICON%" /></a>
	<div class="game-name"><a href="%GAME_URL%" title="%GAME_NAME%">%GAME_NAME%</a></div>
	<div class="game-time">IF_GAME_STATS{<a href="%GAME_STATS_URL%">%GAME_HOURS_TWOWEEKS% hrs</a>}ELSE{%GAME_HOURS_TWOWEEKS% hrs} / two weeks</div>
</div>
',
			"template" => '
<style>
.steam-widget-games {
	box-sizing: content-box;
	margin-bottom: -8px;
}
.steam-widget-games .game {
	clear: both;
	height: 40px;
	margin-bottom: 8px;
}
.steam-widget-games .game-icon {
	border: 4px solid #CCCCCC;
	float: left;
	margin-right: 6px;
	border-radius: 2px;
}
.steam-widget-games .ingame {
	border-color: #B7D282;
}
.steam-widget-games .game-name, .steam-widget-games .game-time {
	margin: 0;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
</style>
<div class="steam-widget steam-widget-games">
	%GAMES_TWOWEEKS%
</div>
'
		),
		"grid" => array(
			"name" => "Games Grid",
			"game_template" => '
<a href="IF_GAME_STATS{%GAME_STATS_URL%}ELSE{%GAME_URL%}">
<img class="game IF_GAME_INGAME{ingame}" src="%GAME_ICON%"  title="%GAME_NAME%
%GAME_HOURS_TWOWEEKS% hrs / two weeks"/>
</a>
',
			"template" => '
<style>
.steam-widget-grid {
	margin-bottom: -6px;
	box-sizing: content-box;
}
.steam-widget-grid .game {
	border: 4px solid #CCCCCC;
	float: left;
	margin-right: 6px;
	margin-bottom: 6px;
	border-radius: 2px;
}
.steam-widget-grid .ingame {
	border-color: #B7D282;
}
</style>
<div class="steam-widget steam-widget-grid">
	%GAMES_TWOWEEKS%
	<div style="clear:both"></div>
</div>
'
		),
		"full" => array(
			"name" => "Full-page Profile",
			"game_template" => '
<div class="game">
	<a href="%GAME_URL%"><img class="game-icon IF_GAME_INGAME{ingame}" src="%GAME_LOGO%" /></a>
	<div class="game-name"><a href="%GAME_URL%" title="%GAME_NAME%">%GAME_NAME%</a></div>
	<div>%GAME_HOURS_TWOWEEKS% hours / two weeks</div>
	<div>IF_GAME_STATS{<a href="%GAME_STATS_URL%">View Stats</a>}</div>
</div>
',
			"template" => '
<style>
.steam-widget-full {
	box-sizing: content-box;
}
.steam-widget-full .profile {
	background: #F8F8F8;
	color: #666666;
	font-size: 15px;
	height: 192px;
	line-height: 20px;
	margin-bottom: 16px;
}
.steam-widget-full .profile-icon {
	border: 4px solid #CCCCCC;
	border-radius: 2px 2px 2px 2px;
	float: left;
	height: 184px;
	margin-right: 10px;
	width: 184px;
}
.steam-widget-full .profile-name {
	color: #444444;
	font-size: 24px;
	font-weight: bold;
	line-height: 32px;
	padding-top: 8px;
	text-shadow: 1px 1px 0 #FFFFFF;
}
.steam-widget-full .game {
	clear: both;
	height: 77px;
	margin-bottom: 8px;
	font-size: 14px;
	line-height: 20px;
}
.steam-widget-full .game-icon {
	border: 4px solid #CCCCCC;
	border-radius: 2px 2px 2px 2px;
	float: left;
	margin-right: 8px;
}
.steam-widget-full .game-name {
	font-size: 16px;
	line-height: 22px;
	padding-top: 4px;
}
.steam-widget-full .ingame {
	border-color: #B7D282;
}
.steam-widget-full .online {
	border-color: #A7C9E1;
}
</style>
<div class="steam-widget steam-widget-full">
	<div class="profile">
	<img class="profile-icon IF_INGAME{ingame}ELSE{IF_ONLINE{online}}" src="%AVATAR_LARGE%">
	<div class="profile-name"><a href="%PROFILE_URL%">%USERNAME%</a></div>
	<div>IF_INGAME{In-game}ELSE{IF_ONLINE{Online}ELSE{Offline}}</div>
	<div>%HOURS_TWOWEEKS% hours / two weeks</div>
	<div><a href="steam://friends/add/%ID64%" rel="nofollow">Add to Friends</a></div>
	</div>
	%GAMES_TWOWEEKS%
</div>
'
		)
	);
	
	//these are the widget-wide default settings
	private $default_settings = array(
		"title" => "Currently Playing", 
		"preset" => "games",
		"game_template" => '', //set in constructor
		"template" => '', //set in constructor
		"steam_id" => "", 
		"cache_interval" => 900
	);

	//constructor
	//is not run per-instance, only per-widget-type!
	function __construct() {
		parent::__construct(false, $name = 'Steam Widget', array(
			'classname' => 'advanced_steam_widget',
			'description' => "Displays Steam gaming statistics")
		);
		
		$this->default_settings["game_template"] = $this->presets[$this->default_settings["preset"]]["game_template"];
		$this->default_settings["template"] = $this->presets[$this->default_settings["preset"]]["template"];

		//settings for cron must be delayed until the widget is registered
		add_action('wp_loaded', array(&$this, 'setup_cron'));

		//hook for cleanup on widget deletion
		add_action('sidebar_admin_setup', array(&$this, 'delete_widget'));
	}
	
	//overrides parent function
	//displays the widget on the frontend
	function widget($args, $instance) {
		extract($args);

		if (!isset($instance) || !is_array($instance) || !isset($instance["cache"])) return;

		$steam_array = $instance["cache"];
		print "<!-- Advanced Steam Widget last updated at " . date(DateTime::RFC1123, $instance["last_cached"]) . " -->";
		
		//print the widget title before we get going
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		print $before_widget;
		if (!empty($title)) print $before_title . $title . $after_title;

		if (empty($instance["template"])) {
			$output = $this->presets[$this->default_settings["preset"]]["template"];
		} else $output = $instance["template"];
		
		//replace template patterns with steam data
		//if there are games played and the games pattern is present
		if (isset($steam_array['games']) && count($steam_array['games']) > 0 && stristr($output, "%GAMES_TWOWEEKS%") !== false) {
			$game_output = "";
			foreach ($steam_array['games'] as $game) {
				if (empty($instance["game_template"])) {
					$game_output_tmp = $this->presets[$this->default_settings["preset"]]["game_template"];
				} else $game_output_tmp = $instance["game_template"];
				
				//ingame conditional
				if ($game['ingame']) {
					$game_output_tmp = preg_replace('/IF_GAME_INGAME\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\1', $game_output_tmp);
				} else {
					$game_output_tmp = preg_replace('/IF_GAME_INGAME\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\2', $game_output_tmp);
				}
				
				//stats conditional
				if ($game['stats_url']) {
					$game_output_tmp = preg_replace('/IF_GAME_STATS\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\1', $game_output_tmp);
				} else {
					$game_output_tmp = preg_replace('/IF_GAME_STATS\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\2', $game_output_tmp);
				}
				
				$game_output_tmp = str_ireplace("%GAME_NAME%", $game['name'], $game_output_tmp);
				$game_output_tmp = str_ireplace("%GAME_URL%", $game['url'], $game_output_tmp);
				$game_output_tmp = str_ireplace("%GAME_ICON%", $game['icon'], $game_output_tmp);
				$game_output_tmp = str_ireplace("%GAME_LOGO_SMALL%", $game['logo']['small'], $game_output_tmp);
				$game_output_tmp = str_ireplace("%GAME_LOGO%", $game['logo']['large'], $game_output_tmp);
				$game_output_tmp = str_ireplace("%GAME_HOURS_TWOWEEKS%", $game['hours_twoweeks'], $game_output_tmp);
				$game_output_tmp = str_ireplace("%GAME_HOURS_TOTAL%", $game['hours_total'], $game_output_tmp);
				$game_output_tmp = str_ireplace("%GAME_STATS_URL%", $game['stats_url'], $game_output_tmp);
				$game_output .= $game_output_tmp;
			}
		} else $game_output = "No Steam games played recently";

		$output = str_ireplace("%GAMES_TWOWEEKS%", $game_output, $output);
		
		//status conditionals
		if (($steam_array['online'])) {
			$output = preg_replace('/IF_ONLINE\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\1', $output);
		} else {
			$output = preg_replace('/IF_ONLINE\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\2', $output);
		}
		if (($steam_array['ingame'])) {
			$output = preg_replace('/IF_INGAME\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\1', $output);
		} else {
			$output = preg_replace('/IF_INGAME\{([^}]*)\}(?:ELSE\{([^}]*)\})?/i', '\2', $output);
		}
		
		$output = str_ireplace("%USERNAME%", $steam_array['username'], $output);
		$output = str_ireplace("%ID64%", $steam_array['ID64'], $output);
		$output = str_ireplace("%PROFILE_URL%", $steam_array['profile_url'], $output);
		$output = str_ireplace("%AVATAR_ICON%", $steam_array['avatar']['icon'], $output);
		$output = str_ireplace("%AVATAR_MEDIUM%", $steam_array['avatar']['medium'], $output);
		$output = str_ireplace("%AVATAR_LARGE%", $steam_array['avatar']['large'], $output);
		$output = str_ireplace("%HOURS_TWOWEEKS%", $steam_array['hours_twoweeks'], $output);
		
		print $output . $after_widget;

		//make sure cron is set
		//sets up the cron for plugin-updating users
		if (!wp_next_scheduled('advanced_steam_widget_update', array($this->number))) {
			wp_schedule_event(time(), 'advanced_steam_widget_interval_' . $this->number, 'advanced_steam_widget_update', array($this->number));
		}
	}
	
	//overrides parent function
	//shows the widget settings fields in the widget option page
	function form($instance) {
		$instance = wp_parse_args((array) $instance, $this->default_settings);
		$title = strip_tags($instance['title']);
		$steam_id = esc_attr($instance['steam_id']);
		$cache_interval = $instance['cache_interval'];
		
		$selected_preset = $instance['preset'];
		$game_template = format_to_edit($instance['game_template']);
		$template = format_to_edit($instance['template']);
		
		//backwards compat for 1.5 where preset key was numeric
		if (is_numeric($selected_preset)) $selected_preset = "custom";
		?>
		
		<script type='text/javascript'>
		function advancedSteamWidgetCustomToggle(val, elem) {
			var templates = jQuery("#" + elem);
			if (val == "custom") {
				if (templates.css("display") == "none") templates.fadeIn();
			} else {
				if (templates.css("display") != "none") templates.fadeOut();
			}
		}
		
		function advancedSteamWidgetPatternToggle(elem) {
			jQuery("#" + elem).slideToggle();
		}
		</script>

		<?php if (isset($instance['last_update_status'])) {
			if ($instance['last_update_status'] === true) { ?>
				<p class="notice notice-success">Last Updated: <?php print human_time_diff($instance['last_cached'], current_time('timestamp')); ?> ago</p>
			<?php } else { ?>
				<p class="notice notice-error"><?php print $instance['last_update_status']; ?></p>
				<?php if (!empty($instance['last_cached'])) { ?>
					<p class="notice notice-info">Last Updated: <?php print human_time_diff($instance['last_cached'], current_time('timestamp')); ?></p>
			<?php }
			}
		} ?>

		<?php if (($next_update = wp_next_scheduled('advanced_steam_widget_update', array($this->number))) !== false) { ?>
			<p class="notice notice-info">Next Update: <?php print human_time_diff($next_update, time()); ?></p>
		<?php } ?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('steam_id'); ?>">Steam Profile ID:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('steam_id'); ?>" name="<?php echo $this->get_field_name('steam_id'); ?>" type="text" value="<?php echo $steam_id; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cache_interval'); ?>">Cache Interval (sec):</label> 
			<input class="widefat" id="<?php echo $this->get_field_id('cache_interval'); ?>" name="<?php echo $this->get_field_name('cache_interval'); ?>" type="text" value="<?php echo $cache_interval; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('preset'); ?>">Preset:</label>
			<select name="<?php echo $this->get_field_name('preset'); ?>" id="<?php echo $this->get_field_id('preset'); ?>" class="widefat" onchange="advancedSteamWidgetCustomToggle(this.value, '<?php echo $this->get_field_id('templates'); ?>')">
				<?php foreach ($this->presets as $preset_key => $preset) { ?>
					<option value="<?php print $preset_key; ?>" <?php selected($selected_preset, $preset_key); ?>><?php print $preset["name"]; ?></option>
				<?php } ?>
				<option value="custom" <?php selected($selected_preset, "custom"); ?>>Custom</option>
			</select>
		</p>
		
		<div id="<?php echo $this->get_field_id('templates'); ?>" <?php if ($selected_preset != "custom") { ?>style="display: none;"<?php } ?>>
			<p>
				<label for="<?php echo $this->get_field_id('game_template'); ?>">Game Template:</label> 
				<textarea class="widefat" rows="8" cols="20" id="<?php echo $this->get_field_id('game_template'); ?>" name="<?php echo $this->get_field_name('game_template'); ?>"><?php echo $game_template; ?></textarea>
			</p>
			<div><a id="<?php echo $this->get_field_id('game_template_patterns_toggle'); ?>" href="javascript:void(0)" onclick="advancedSteamWidgetPatternToggle('<?php echo $this->get_field_id('game_template_patterns'); ?>')">Toggle Show Patterns</a></div>
			<div id="<?php echo $this->get_field_id('game_template_patterns'); ?>" style="display: none;">
			%GAME_NAME%<br />
			%GAME_URL%<br />
			%GAME_ICON%<br />
			%GAME_LOGO_SMALL%<br />
			%GAME_LOGO%<br />
			%GAME_HOURS_TWOWEEKS%<br />
			%GAME_HOURS_TOTAL%<br />
			%GAME_STATS_URL%<br />
			IF_GAME_INGAME{}ELSE{}<br />
			IF_GAME_STATS{}ELSE{}<br />
			</div>
			<p style="margin-top: 1em;">
				<label for="<?php echo $this->get_field_id('template'); ?>">Main Template:</label> 
				<textarea class="widefat" rows="8" cols="20" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>"><?php echo $template; ?></textarea>
			</p>
			<div><a id="<?php echo $this->get_field_id('template_patterns_toggle'); ?>" href="javascript:void(0)" onclick="advancedSteamWidgetPatternToggle('<?php echo $this->get_field_id('template_patterns'); ?>')">Toggle Show Patterns</a></div>
			<div id="<?php echo $this->get_field_id('template_patterns'); ?>" style="display: none;">
			%GAMES_TWOWEEKS%<br />
			%HOURS_TWOWEEKS%<br />
			%USERNAME%<br />
			%ID64%<br />
			%PROFILE_URL%<br />
			%AVATAR_ICON%<br />
			%AVATAR_MEDIUM%<br />
			%AVATAR_LARGE%<br />
			IF_INGAME{}ELSE{}<br />
			IF_ONLINE{}ELSE{}<br />
			</div>
		</div>
		<?php if (is_numeric($this->number)) { ?>
			<p style="margin-top: 1em;">Shortcode: [steam id="<?php print $this->number; ?>"]</p>
		<?php }
	}
	
	//overrides parent function
	//saves settings for this widget's instance
	function update($new_instance, $old_instance) {
		if (is_array($old_instance)) $instance = $old_instance; else $instance = array();
		
		if (isset($new_instance['title'])) $instance['title'] = strip_tags($new_instance['title']);
		if (!empty($new_instance['cache_interval'])) {
			$instance['cache_interval'] = $this->get_int_option($new_instance['cache_interval'], $this->default_settings['cache_interval'], 0, 86400);
		}
		
		if (isset($new_instance['steam_id'])) {
			if (preg_match('/\A(?:STEAM_)?\d+:(\d+):(\d+)\Z/i', $new_instance['steam_id'], $matches)) {
				//they used their internal steam id, so we have to convert it
				$new_instance['steam_id'] = ($matches[2] * 2) + 0x0110000100000000 + $matches[1];
			}
			$instance['steam_id'] = $new_instance['steam_id'];
		}
		
		if (isset($new_instance['preset'])) {
			$instance['preset'] = $new_instance['preset'];
			if ($new_instance['preset'] != "custom") {
				$instance['game_template'] = $this->presets[$instance['preset']]["game_template"];
				$instance['template'] = $this->presets[$instance['preset']]["template"];
			} else {
				//if new option is empty, use default
				if (isset($new_instance['game_template'])) $instance['game_template'] = empty($new_instance['game_template']) ? $this->default_settings['game_template'] : $new_instance['game_template'];
				if (isset($new_instance['template'])) $instance['template'] = empty($new_instance['template']) ? $this->default_settings['template'] : $new_instance['template'];
			}
		}
		
		if (isset($new_instance['last_cached'])) $instance['last_cached'] = $new_instance['last_cached'];
		if (isset($new_instance['cache'])) $instance['cache'] = $new_instance['cache'];

		//update steam data
		$instance = $this->update_data($instance);

		//hook for things that need to be done after the instance is updated
		add_filter('widget_form_callback', array(&$this, 'post_update'));

		return $instance;
	}

	//makes sure the requested value is valid and numeric
	//and clamped between an optional minimum and maximum value
	//if not, returns default, min, or max value
	private function get_int_option($request_opt, $default_opt = 0, $min_val = NULL, $max_val = NULL) {
		if ((isset($request_opt)) && (is_numeric($request_opt))) {
			if ((!is_null($min_val)) && ($request_opt < $min_val)) return $min_val;
			if ((!is_null($max_val)) && ($request_opt > $max_val)) return $max_val;
			return $request_opt;
		} else {
			return $default_opt;
		}
	}

	//updates steam data
	//must be passed the widget instance's settings
	//returns widget settings modified with new steam data
	function update_data($instance) {
		try {
			//see if there's any id input
			$steam_id = empty($instance['steam_id']) ? 'slserpent' : $instance['steam_id'];

			//decide whether we're using old or new style profile url
			if (preg_match('/\A\d{17}\Z/', $steam_id)) {
				$profile_url = 'http://steamcommunity.com/profiles/' . $steam_id;
			} else {
				$profile_url = 'http://steamcommunity.com/id/' . $steam_id;
			}
			$xml_url = $profile_url . '?xml=1';

			//get XML from Valve
			//prefer curl, so we can set a timeout
			if (function_exists("curl_init")) {
				//support location redirects to future-proof script
				if (ini_get('safe_mode' == 'On' && ini_get('open_basedir') != '')) {
					$max_redirs = 0;
				} else $max_redirs = 2;

				$ch = curl_init($xml_url);
				curl_setopt_array($ch, array(
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HEADER => false,
					CURLOPT_FOLLOWLOCATION => $max_redirs > 0,
					CURLOPT_ENCODING => "",
					CURLOPT_AUTOREFERER => true,
					CURLOPT_CONNECTTIMEOUT => 15,
					CURLOPT_TIMEOUT => 20,
					CURLOPT_MAXREDIRS => $max_redirs,
					CURLOPT_SSL_VERIFYHOST => 0,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_FRESH_CONNECT => true,
					CURLINFO_HEADER_OUT => true
				));
				$content = curl_exec($ch);
				$err_code = curl_errno($ch);

				//DEBUG
				//$request_info = curl_getinfo($ch);
				//print print_r($request_info);

				curl_close($ch);

				//see if there were no errors
				if ($err_code == 0) {
					if (($steam_xml = @simplexml_load_string($content)) === false) throw new Exception("Error loading XML data.");
				} else throw new Exception("Error downloading XML data. CURL error code: " . $err_code);
			}

			//fallback to simple xml remote open
			if (!$steam_xml) {
				if (($steam_xml = @simplexml_load_file($xml_url)) === false) throw new Exception("Error downloading or parsing XML data.");
			}

			if (isset($steam_xml->error)) throw new Exception("XML Error: " . (string)$steam_xml->error);

			if ((string)$steam_xml->privacyState != "public") throw new Exception("Profile is not public.");

			//parse out some values so they're easier to store / use
			$steam_array = array();
			$steam_array['username'] = (string)$steam_xml->steamID;
			$steam_array['ID64'] = (string)$steam_xml->steamID64;
			$steam_array['profile_url'] = $profile_url;
			$steam_array['avatar']['icon'] = (string)$steam_xml->avatarIcon;
			$steam_array['avatar']['medium'] = (string)$steam_xml->avatarMedium;
			$steam_array['avatar']['large'] = (string)$steam_xml->avatarFull;
			$steam_array['hours_twoweeks'] = (string)$steam_xml->hoursPlayed2Wk;

			if ($steam_xml->onlineState == "in-game") {
				$steam_array['ingame'] = (string)$steam_xml->inGameInfo->gameName;
			} else $steam_array['ingame'] = false;

			if ($steam_xml->onlineState == "online") {
				$steam_array['online'] = true;
			} else $steam_array['online'] = false;

			if (count($steam_xml->mostPlayedGames->mostPlayedGame) > 0) {
				//workaround for steam no 2 wks played bug
				$cumulative_hours_from_games = false;
				if ($steam_array['hours_twoweeks'] == "0.0") {
					$steam_array['hours_twoweeks'] = 0;
					$cumulative_hours_from_games = true;
				}

				$k = 0;
				foreach ($steam_xml->mostPlayedGames->mostPlayedGame as $game) {
					if (strlen($game->gameName) < 1) continue;
					$steam_array['games'][$k]['name'] = (string)$game->gameName;
					$steam_array['games'][$k]['url'] = (string)$game->gameLink;
					$steam_array['games'][$k]['icon'] = (string)$game->gameIcon;
					$steam_array['games'][$k]['logo']['small'] = (string)$game->gameLogoSmall;
					$steam_array['games'][$k]['logo']['large'] = (string)$game->gameLogo;
					$steam_array['games'][$k]['hours_total'] = (string)$game->hoursOnRecord;
					$steam_array['games'][$k]['hours_twoweeks'] = (string)$game->hoursPlayed;

					if ($steam_array['ingame'] && $steam_array['ingame'] == $steam_array['games'][$k]['name']) {
						$steam_array['games'][$k]['ingame'] = true;
					} else $steam_array['games'][$k]['ingame'] = false;

					//see if stats name exists and is valid, i.e. not just the app id
					if (isset($game->statsName) && !preg_match('/\d+/', $game->statsName)) {
						$steam_array['games'][$k]['stats_url'] = $profile_url . "/stats/" . (string)$game->statsName;
					} else $steam_array['games'][$k]['stats_url'] = false;

					if ($cumulative_hours_from_games === true) {
						$steam_array['hours_twoweeks'] += (float)$game->hoursPlayed;
					}

					$k++;
				}
			}

			//write the cache and timestamp
			$instance['cache'] = $steam_array;
			$instance['last_cached'] = current_time('timestamp');
			$instance['last_update_status'] = true;
			return $instance;
		} catch (Exception $err) {
			$instance['last_update_status'] = $err->getMessage();
			return $instance;
		}
	}

	//updates steam data given a specific widget id
	//used for cron target
	function update_and_save_data($id) {
		//get the selected instances of this widget
		$all_instances = $this->get_settings();
		if (!isset($all_instances[$id])) return;

		//call the update
		$all_instances[$id] = $this->update_data($all_instances[$id]);

		//save all instances of this widget
		$this->save_settings($all_instances);
	}

	//called on deletion of a widget from the widget options page
	function delete_widget() {
		if (isset($_POST['delete_widget']) && $_POST['delete_widget'] && isset($_POST['widget_number'])) {
			//remove all crons for this instance
			wp_clear_scheduled_hook('advanced_steam_widget_update', array($_POST['widget_number']));
		}
	}

	//called after update has been saved
	function post_update($instance) {
		//always reset the cron, because we just updated and the interval should start from now
		wp_clear_scheduled_hook('advanced_steam_widget_update', array($this->number));
		wp_reschedule_event(time(), "advanced_steam_widget_interval_" . $this->number, 'advanced_steam_widget_update', array($this->number));

		return $instance;
	}

	//callback for changing the wp cron for the feed update
	function cron_add_interval($schedules) {
		//must be done for each instance so they're always available whenever called
		foreach ($this->get_settings() as $id => $instance) {
			$interval = $this->get_int_option($instance['cache_interval'], $this->default_settings['cache_interval'], 0, 86400);
			$schedules['advanced_steam_widget_interval_' . $id] = array(
				'interval' => $interval,
				'display' => 'Advanced Steam Widget ' . $id
			);
		}
		return $schedules;
	}

	//fired once for all widgets
	function setup_cron() {
		//hook for the custom cron interval
		add_filter('cron_schedules', array(&$this, 'cron_add_interval'));
		//action for cron
		add_action('advanced_steam_widget_update', array(&$this, 'update_and_save_data'));
	}
}

//adds our widget to available ones
function AdvancedSteamWidget_register() {
	register_widget('AdvancedSteamWidget');
}
add_action('widgets_init', 'AdvancedSteamWidget_register');

//cleanup for plugin deactivation
function AdvancedSteamWidget_deactivate($plugin) {
	if ($plugin == "advanced-steam-widget/steam_widget.php") {
		//remove all crons for this plugin
		AdvancedSteamWidget_remove_crons();
	}
}
add_action('deactivated_plugin', 'AdvancedSteamWidget_deactivate');

//cleanup for plugin uninstall
function AdvancedSteamWidget_uninstall() {
	//remove all crons for this plugin
	AdvancedSteamWidget_remove_crons();

	//delete all settings
	delete_option("widget_advancedsteamwidget");

	//no need to remove widgets from sidebars
	//this is done next time widget options are modified
}
register_uninstall_hook("advanced-steam-widget/steam_widget.php", "AdvancedSteamWidget_uninstall");

//removes all crons for this plugin
function AdvancedSteamWidget_remove_crons() {
	$widget_settings = get_option("widget_advancedsteamwidget");
	unset($widget_settings['_multiwidget']);

	if (is_array($widget_settings) && count($widget_settings)) {
		foreach ($widget_settings as $id => $settings) {
			wp_clear_scheduled_hook('advanced_steam_widget_update', array($id));
		}
	}
}

//adds jquery to the widget options page
function AdvancedSteamWidget_admin_scripts($hook) {
	if($hook != 'widget.php') return;

	wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'AdvancedSteamWidget_admin_scripts');

//handler for the shortcode version of our widget
//ex: [steam id=1]
function AdvancedSteamWidget_shortcode($attribs) {
	if (!isset($attribs["id"])) return "No Steam Widget ID!";

	$widget = new AdvancedSteamWidget();

	//set the instance id
	$id = $attribs["id"];
	$widget->_set($id);

	//get instance settings
	$settings = $widget->get_settings();
	if (!isset($settings[$id]) || !is_array($settings[$id])) return "Invalid Steam Widget ID!";
	$instance = $settings[$id];
	
	$args = array('before_widget' => '<div class="advanced_steam_widget">', 'after_widget' => "</div>", 'before_title' => '<h3>', 'after_title' => '</h3>');
	
	ob_start();
	$widget->widget($args, $instance);
	return ob_get_clean();
}
add_shortcode('steam', 'AdvancedSteamWidget_shortcode');