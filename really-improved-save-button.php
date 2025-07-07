<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
Plugin Name: Really Improved Save Button
Description: Adds a new "Save" button to the Post Edit screen that saves the post and immediately takes you to your next action: the previous page, the next/previous post, the posts list, the post's frontend, etc.
Author: FastWebCreations LLC
Version: 1.0.1
Author URI: http://www.fastwebcreations.com
Domain Path: /
Text Domain: really-improved-save-button
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
 * Copyright 2025 FastWebCreations LLC (https://fastwebcreations.com)
 *
 * This file is part of the "Really Improved Save Button"
 * Wordpress plugin.
 *
 * The "Really Improved Save Button" Wordpress plugin
 * is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * All the PHP files of the plugin
 * @var array
 */
$lib_files_to_include = array(
	'class-fwc-save-and-then-utils.php',
	'class-fwc-save-and-then-settings.php',
	'class-fwc-save-and-then-post-edit.php',
	'class-fwc-save-and-then-post-save.php',
	'class-fwc-save-and-then-messages.php',
	'class-fwc-save-and-then-actions.php',
	'class-fwc-save-and-then-action.php',
);

// Include all the PHP files of the plugin
foreach ( $lib_files_to_include as $file_name ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib' . DIRECTORY_SEPARATOR . $file_name );
}

/**
 * PHP files of the actions that come with the plugin
 * @var array
 */
$actions_files_to_include = array(
	'class-fwc-save-and-then-action-new.php',
	'class-fwc-save-and-then-action-list.php',
	'class-fwc-save-and-then-action-view.php',
	'class-fwc-save-and-then-action-view-popup.php',
	'class-fwc-save-and-then-action-next.php',
	'class-fwc-save-and-then-action-previous.php',
	'class-fwc-save-and-then-action-duplicate.php',
	'class-fwc-save-and-then-action-return.php',
);

// Include all the actions php files
foreach ( $actions_files_to_include as $file_name ) {
	require_once( plugin_dir_path( __FILE__ ) . 'actions' . DIRECTORY_SEPARATOR . $file_name );
}

if( !class_exists( 'FWC_Save_And_Then' ) ) {

/**
 * Main class. Mainly calls the setup function of other classes and
 * define the list of 'actions'.
 */
class FWC_Save_And_Then {

	/**
	 * Main entry point of the plugin. Calls the setup function
	 * of the other classes.
	 */
	static function setup() {
		FWC_Save_And_Then_Settings::setup();
		FWC_Save_And_Then_Post_Edit::setup();
		FWC_Save_And_Then_Post_Save::setup();
		FWC_Save_And_Then_Messages::setup();
		FWC_Save_And_Then_Actions::setup();

		if( self::requires_language_loading() ) {
			// Priority 1, because the settings page is also on
			// admin_init and uses translations
			add_action( 'admin_init', array( get_called_class(), 'load_languages' ), 1 );
		}

		add_action( 'fwc-sat_load_actions', array( get_called_class(), 'load_default_actions' ) );

		// Enqueue Gutenberg integration script
		add_action('enqueue_block_editor_assets', function() {
			wp_enqueue_script(
				'fwc-sat-gutenberg',
				plugins_url('js/build/gutenberg.build.js', __FILE__),
				array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-data'),
				filemtime(__DIR__ . '/js/build/gutenberg.build.js'),
				true
			);
		});
	}

	/**
	 * Returns the localized name of the plugin
	 * @return string
	 */
	static function get_localized_name() {
		$plugin_data = get_plugin_data( __FILE__, false, true );
		return $plugin_data['Name'];
	}

	/**
	 * Called by the fwc-sat_load_actions filter. Loads all the
	 * actions that come by default with the plugin.
	 */
	static function load_default_actions( $actions ) {
		$default_actions_classes = array(
			'FWC_Save_And_Then_Action_New',
			'FWC_Save_And_Then_Action_Duplicate',
			'FWC_Save_And_Then_Action_List',
			'FWC_Save_And_Then_Action_Return',
			'FWC_Save_And_Then_Action_Next',
			'FWC_Save_And_Then_Action_Previous',
			'FWC_Save_And_Then_Action_View',
			'FWC_Save_And_Then_Action_View_Popup',
		);

		foreach ( $default_actions_classes as $class_name ) {
			$actions[] = new $class_name();
		}

		return $actions;
	}

	/**
	 * Loads the language file for the admin. Must be called in the
	 * 'admin_init' hook, since it uses get_plugin_data() and this
	 * function is loaded once all admin files are included.
	 */
	static function load_languages() {
		$plugin_data = get_plugin_data( __FILE__, false, true );
		$path = dirname( FWC_Save_And_Then_Utils::plugin_main_file_basename() );
		$path .= $plugin_data['DomainPath'];
		load_plugin_textdomain( $plugin_data['TextDomain'], false, $path );
	}

	/**
	 * Returns the full path of the plugin's main file (this file).
	 * Used in the utils
	 *
	 * @return string
	 */
	static function get_main_file_path() {
		return __FILE__;
	}

	/**
	 * Returns true if this Wordpress version requires loading
	 * of language files. It is not required since version 4.6
	 *
	 * @return boolean
	 */
	static function requires_language_loading() {
		global $wp_version;

		if( ! isset( $wp_version ) ) {
			return true;
		}

		list( $version ) = explode( '-', $wp_version );

		if( version_compare( $version, '4.6', '>=') ) {
			return false;
		}

		return true;
	}

} // end class

} // end if( class_exists() )

FWC_Save_And_Then::setup();
