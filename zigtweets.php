<?php
/*
Plugin Name: ZigTweets
Plugin URI: https://wordpress.org/plugins/zigtweets/
Description: Plugin to fetch tweets from an account and display them in a widget. It uses the Twitter API v1.1 and stores tweets in the cache. It means that it will read status messages from your database and it doesn't query Twitter.com for every page load so you won't be rate limited. You can set how often you want to update the cache.
Version: 1.0
Author: ZigPress
Requires at least: 4.5
Tested up to: 5.4
Requires PHP: 5.3
Author URI: https://www.zigpress.com/
License: GPLv2
*/


/*
Copyright (c) 2015-2020 ZigPress

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation Inc, 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/


require_once dirname(__FILE__) . '/widget.php';


if (!class_exists('zigtweets')) {


	final class zigtweets {


		private static $_instance = null;


		public static function getinstance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}


		private function __clone() {}


		private function __wakeup() {}


		private function __construct() {
			$this->protocol = (strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== false) ? 'https://' : 'http://';
			$this->server = $_SERVER['SERVER_NAME'];
			$this->callback_url = $this->protocol . $this->server . preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
			$this->plugin_folder = get_bloginfo('url') . '/' . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)); # no final slash
			$this->plugin_directory = WP_PLUGIN_DIR . '/zigtweets/';
			$this->plugin_path = str_replace('plugin.php', 'zigtweets.php', __FILE__);
			$this->options = get_option('zigtweets');
			add_action('plugins_loaded', array($this, 'action_plugins_loaded'));
			add_action('widgets_init', array($this, 'action_widgets_init'));
			add_action('wp_enqueue_scripts', array($this, 'action_wp_enqueue_scripts'));
			add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));
			add_action('admin_menu', array($this, 'action_admin_menu'));
			#add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'filter_plugin_action_links'));
			add_filter('plugin_row_meta', array($this, 'filter_plugin_row_meta'), 10, 2 );
			/* That which can be added without discussion, can be removed without discussion. */
			remove_filter( 'the_title', 'capital_P_dangit', 11 );
			remove_filter( 'the_content', 'capital_P_dangit', 11 );
			remove_filter( 'comment_text', 'capital_P_dangit', 31 );
		}


		public function activate() {
			if (!$this->options = get_option('zigtweets')) {
				$this->options = array();
				$this->options['last_cache_time'] = '0';
				add_option('zigtweets', $this->options);
			}
		}


		public function autodeactivate($requirement) {
			if (!function_exists( 'get_plugins')) require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			$plugin = plugin_basename($this->plugin_path);
			$plugindata = get_plugin_data($this->plugin_path, false);
			if (is_plugin_active($plugin)) {
				deactivate_plugins($plugin);
				wp_die($plugindata['Name'] . ' requires ' . $requirement . ' and has been deactivated. <a href="' . admin_url('plugins.php') . '">Click here to go back.</a>');
			}
		}


		# ACTIONS


		public function action_plugins_loaded() {
			global $wp_version;
			if (version_compare(phpversion(), '5.3.0', '<')) $this->autodeactivate('PHP 5.3.0');
			if (version_compare($wp_version, '4.5', '<')) $this->autodeactivate('WordPress 4.5');
		}


		public function action_widgets_init() {
			register_widget('widget_zigtweets');
		}


		public function action_wp_enqueue_scripts() {
			wp_enqueue_style('zigtweets', $this->plugin_folder . '/css/zigtweets.css', false, rand());
		}


		public function action_admin_enqueue_scripts() {
			wp_enqueue_style('zigtweets-admin', $this->plugin_folder . '/css/admin.css', false, date('Ymd'));
		}


		public function action_admin_menu() {
			add_options_page('ZigTweets Options', 'ZigTweets', 'manage_options', 'zigtweets-options', array($this, 'admin_page_options'));
		}


		# FILTERS


		public function filter_plugin_action_links($links) {
			$newlinks = array(
				'<a href="' . get_admin_url() . 'options-general.php?page=zigtweets-options">Settings</a>',
			);
			return array_merge( $links, $newlinks );
		}


		public function filter_plugin_row_meta($links, $file) {
			$plugin = plugin_basename(__FILE__);
			$newlinks = array(
				'<a target="_blank" href="http://www.zigpress.com/donations/">Donate</a>',
				'<a href="' . get_admin_url() . 'options-general.php?page=zigtweets-options">Settings</a>',
			);
			if ($file == $plugin) return array_merge($links, $newlinks);
			return $links;
		}


		# ADMIN CONTENT


		public function admin_page_options() {
			if (!current_user_can('manage_options')) { wp_die('You are not allowed to do this.'); }
			?>
			<div class="wrap zigtweets-admin">
				<h2>ZigTweets</h2>
				<div class="wrap-left">
					<div class="col-pad">

						<p>ZigTweets is a plugin to fetch tweets from an account and display them in a widget. It uses the Twitter API v1.1 and stores tweets in the cache. It means that it will read status messages from your database and it doesn't query Twitter.com for every page load so you won't be rate limited. You can set how often you want to update the cache.</p>
						<p>NOTE: saving the widget control panel options clears the tweet cache so don't do it too many times per hour.</p>

						<h3>Adding the Widget</h3>
						<ol>
							<li><a href="<?php echo admin_url('widgets.php'); ?>" target="_blank">Go to your Widgets menu</a>, add the <code>ZigTweets</code> widget to a widget area.</li>
							<li>Visit <a target="_blank" href="https://apps.twitter.com/">https://apps.twitter.com/</a>, sign in with your account, click on <code>Create New App</code> and create your own keys if you haven't already.</li>
							<li>Fill all your widget settings.</li>
							<li>Enjoy your new Twitter feed! :)</li>
						</ol>

					</div><!--col-pad-->
				</div><!--wrap-left-->
				<div class="wrap-right">
					<table class="widefat donate" cellspacing="0">
						<thead>
						<tr><th>Support this plugin!</th></tr>
						</thead>
						<tr><td>
								<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
									<input type="hidden" name="cmd" value="_s-xclick">
									<input type="hidden" name="hosted_button_id" value="GT252NPAFY8NN">
									<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>
								<p>If you find ZigTweets useful, please keep it free and actively developed by making a donation.</p>
								<p>Suggested donation: &euro;10 or an amount of your choice. Thanks!</p>
							</td></tr>
					</table>
					<table class="widefat donate" cellspacing="0">
						<thead>
						<tr><th>Brought to you by ZigPress</th></tr>
						</thead>
						<tr><td>
								<p><a href="https://www.zigpress.com/">ZigPress</a> is engaged in WordPress consultancy, solutions and research. We have also released a number of free and commercial plugins to support the WordPress community.</p>
								<p><a target="_blank" href="https://www.zigpress.com/plugins/zigtweets/">ZigTweets page</a></p>
								<p><a target="_blank" href="https://www.zigpress.com/wordpress-plugins/">Other ZigPress plugins</a></p>
								<p><a target="_blank" href="https://www.facebook.com/zigpress">ZigPress on Facebook</a></p>
								<p><a target="_blank" href="https://twitter.com/ZigPress">ZigPress on Twitter</a></p>
							</td></tr>
					</table>
				</div><!--wrap-right-->
				<div class="clearer">&nbsp;</div>
			</div><!--/wrap-->
		<?php
		}


		# UTILITIES


		public function is_classicpress() {
			return function_exists('classicpress_version');
		}


		function get_relative_time($a) {
			//get current timestampt
			$b = strtotime('now');
			//get timestamp when tweet created
			$c = strtotime($a);
			//get difference
			$d = $b - $c;
			//calculate different time values
			$minute = 60;
			$hour = $minute * 60;
			$day = $hour * 24;
			$week = $day * 7;

			if(is_numeric($d) && $d > 0) {
				//if less then 3 seconds
				if($d < 3) return 'right now';
				//if less then minute
				if($d < $minute) return floor($d) . ' seconds ago';
				//if less then 2 minutes
				if($d < $minute * 2) return 'about 1 minute ago';
				//if less then hour
				if($d < $hour) return floor($d / $minute) . ' minutes ago';
				//if less then 2 hours
				if($d < $hour * 2) return 'about 1 hour ago';
				//if less then day
				if($d < $day) return floor($d / $hour) .' hours ago';
				//if more then day, but less then 2 days
				if($d > $day && $d < $day * 2) return'yesterday';
				//if less then year
				if($d < $day * 365) return floor($d / $day) . ' days ago';
				//else return more than a year
				return 'over a year ago';
			}
		}


		function convert_links($status,$targetBlank=true,$linkMaxLen=250){

			// the target
			$target=$targetBlank ? " target=\"_blank\" " : "";

			// convert link to url
			$status = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[A-Z0-9+&@#\/%=~_|]/i', '<a href="\0" target="_blank">\0</a>', $status);

			// convert @ to follow
			$status = preg_replace("/(@([_a-z0-9\-]+))/i","<a href=\"http://twitter.com/$2\" title=\"Follow $2\" $target >$1</a>",$status);

			// convert # to search
			$status = preg_replace("/(#([_a-z0-9\-]+))/i","<a href=\"https://twitter.com/search?q=$2\" title=\"Search $1\" $target >$1</a>",$status);

			// return the status
			return $status;
		}


	}


} else {
	wp_die('Namespace clash! Class zigtweets already exists.');
}


$zigtweets = zigtweets::getinstance();
register_activation_hook(__FILE__, array(&$zigtweets, 'activate'));


# EOF
