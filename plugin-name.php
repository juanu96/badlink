<?php

/**
 *
 * The plugin bootstrap file
 *
 * This file is responsible for starting the plugin using the main plugin class file.
 *
 * @since 0.0.1
 * @package Plugin_Name
 *
 * @wordpress-plugin
 * Plugin Name:     BadLink
 * Description:     Broken link tracking.
 * Version:         0.0.1
 * Author:          Juan Ubau
 * Author URI:      https://www.example.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     plugin-name
 * Domain Path:     /lang
 */

if (!defined('ABSPATH')) {
	die('Direct access not permitted.');
}

if (!class_exists('plugin_name')) {

	/*
	 * main plugin_name class
	 *
	 * @class plugin_name
	 * @since 0.0.1
	 */
	class plugin_name
	{

		/*
		 * plugin_name plugin version
		 *
		 * @var string
		 */
		public $version = '4.7.5';

		/**
		 * The single instance of the class.
		 *
		 * @var plugin_name
		 * @since 0.0.1
		 */
		protected static $instance = null;

		/**
		 * Main plugin_name instance.
		 *
		 * @since 0.0.1
		 * @static
		 * @return plugin_name - main instance.
		 */
		public static function instance()
		{
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * plugin_name class constructor.
		 */
		public function __construct()
		{
			$this->load_plugin_textdomain();
			$this->define_constants();
			$this->includes();
			$this->define_actions();
			$this->define_menus();
			//add cron job to check broken links every day
			$this->cron_jobs();
		}

		public function load_plugin_textdomain()
		{
			load_plugin_textdomain('plugin-name', false, basename(dirname(__FILE__)) . '/lang/');
		}

		/**
		 * Include required core files
		 */
		public function includes()
		{
			// Loading table class
			if (!class_exists('WP_List_Table')) {
				require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
			}

			// Example
			require_once __DIR__ . '/includes/badlink.php';

			// Load custom functions and hooks
			require_once __DIR__ . '/includes/includes.php';
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path()
		{
			return untrailingslashit(plugin_dir_path(__FILE__));
		}


		/**
		 * Define plugin_name constants
		 */
		private function define_constants()
		{
			define('PLUGIN_NAME_PLUGIN_FILE', __FILE__);
			define('PLUGIN_NAME_PLUGIN_BASENAME', plugin_basename(__FILE__));
			define('PLUGIN_NAME_VERSION', $this->version);
			define('PLUGIN_NAME_PATH', $this->plugin_path());
		}

		/**
		 * Define plugin_name actions
		 */
		public function define_actions()
		{
			//
		}

		/**
		 * Define plugin_name menus
		 */
		public function define_menus()
		{
			add_action('admin_menu', 'bad_link_tracking');
		}

		public function cron_jobs(){
			//add function bad badlinkstracking to action hook
			add_action( 'bad_link_cron_job', 'badlinkstracking' );
			if (!function_exists('prefix_add_scheduled_event')) :
				function prefix_add_scheduled_event()
				{
					// Schedule the event if it is not scheduled.
					if (!wp_next_scheduled('bad_link_cron_job')) {
						//programmatically schedule the event to run every day 
						wp_schedule_event(time(), 'daily', 'bad_link_cron_job');
					}
				}
				add_action('admin_init', 'prefix_add_scheduled_event');
			endif;
		}
	}

	$plugin_name = new plugin_name();
}
