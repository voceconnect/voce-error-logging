<?php
/*
Plugin Name: Voce Error Logging
Plugin URI: http://plugins.voceconnect.com
Description: Allows error logging as a post type, for VIP sites and developers that don't have access to log files.
Version: 0.1
Author: jeffstieler
License: A "Slug" license name e.g. GPL2
*/

// TODO: Auto-clean-up of old error logs, either by age or max total errors
// TODO: Collapsable post rows on edit.php
// TODO: Highlight 'tools > logged errors' menu item on ?post_type=error

class Voce_Error_Logging {

	const POST_TYPE = 'error';

	public static function init() {
		add_action('init', array(__CLASS__, 'create_post_type'));
		add_action('init', array(__CLASS__, 'redirect_to_error_listing'));
		add_action('admin_menu', array(__CLASS__, 'add_menu_item'));
		add_filter('manage_error_posts_columns', array(__CLASS__, 'set_error_columns'));
		add_action('manage_error_posts_custom_column', array(__CLASS__, 'display_error_columns'), 10, 2);
	}

	public static function create_post_type() {
		register_post_type(self::POST_TYPE, array(
			'labels' => array(
				'name' => 'Logged Errors',
				'singular_name' => 'Logged Error',
				'view_item' => 'View',
				'search_items' => 'Search Errors',
				'not_found' => 'No logged errors found.',
				'not_found_in_trash' => 'No logged errors in trash.'
			),
			'show_ui' => true,
			'show_in_menu' => false,
		));
	}

	public static function add_menu_item() {
		add_submenu_page('tools.php', 'Logged Errors', 'Error Log', 'manage_options', 'error_log', array(__CLASS__, 'logged_errors_page'));
	}

	public static function redirect_to_error_listing() {
		if (isset($_GET['page']) && ('error_log' == $_GET['page'])) {
			wp_redirect(admin_url('edit.php?post_type='.self::POST_TYPE));
			die();
		}
	}

	public static function error_log($post_title, $error) {

		$post_content = (is_string($error) ? $error : print_r($error, true)) . "\n\n";
		$backtrace = array_slice(debug_backtrace(), 1); // slice removes call to this function
		$post_content .= "<hr>\n";
		
		foreach ($backtrace as $call) {
			if (isset($call['file']) && isset($call['line'])) {
				$post_content .= sprintf("%s - (%s:%d)\n", $call['function'], $call['file'], $call['line']);
			} else {
				$post_content .= $call['function'] . "\n";
				break; // stop when we get to the function containing the voce_error_log() call
			}
		}

		$postarr = compact('post_title', 'post_content');
		$postarr = array_merge($postarr, array('post_type' => self::POST_TYPE, 'post_status' => 'publish'));

		wp_insert_post($postarr);

	}

	public static function set_error_columns($columns) {
		return array(
			'cb' => $columns['cb'],
			'error' => 'Error',
			'date' => $columns['date']
		);
	}

	public static function display_error_columns($column_name, $post_id) {
		switch($column_name) {
			case 'error':
				$post = get_post($post_id);
				?>
				<strong><?php echo esc_html($post->post_title); ?></strong>
				<pre><?php echo wpautop($post->post_content); ?></pre>
				<?php
				break;
		}
	}

}

Voce_Error_Logging::init();

// create convenience function for logging
if (!function_exists('voce_error_log')) {
	function voce_error_log($title, $error) {
		return Voce_Error_Logging::error_log($title, $error);
	}
}


