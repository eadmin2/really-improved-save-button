<?php

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

if( ! class_exists( 'FWC_Save_And_Then_Post_Edit' ) ) {

/**
 * Management of the "edit post" and "new post" admin pages.
 */

class FWC_Save_And_Then_Post_Edit {

	/**
	 * URL parameter defining the action to do after saving.
	 */
	const HTTP_PARAM_ACTION = 'fwc-sat-action';

	/**
	 * Main entry point. Setups all the Wordpress hooks.
	 */
	static function setup() {
		add_action( 'admin_enqueue_scripts', array( get_called_class(), 'add_admin_scripts' ) );
		add_action( 'post_submitbox_start', array( get_called_class(), 'post_submitbox_start' ), 100 );
	}

	/**
	 * Adds JavaScript and CSS files on the "edit post" or "new post"
	 * page.
	 * 
	 * @param string  $page_id  Page id where we are.
	 */
	static function add_admin_scripts( $page_id ) {

		if( $page_id != 'post.php' && $page_id != 'post-new.php' ) {
			return;
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
		// Adds post-edit.js	
		wp_enqueue_script(
			'fwc-save-and-then-post-edit',
			FWC_Save_And_Then_Utils::plugins_url( "js/post-edit{$min}.js" ),
			array('jquery', 'utils'),
			'1.0.1',
			true
		);

		// If Wordpress version < 4.2, we include the backward-compatibility
		// script.
		$wp_version = get_bloginfo('version');

		if( version_compare( $wp_version, '4.2', '<' ) ) {
			wp_enqueue_script(
				'fwc-save-and-then-post-edit-pre-4.2',
				FWC_Save_And_Then_Utils::plugins_url( "js/backward-compatibility/post-edit.pre-4.2{$min}.js" ),
				array('fwc-save-and-then-post-edit'),
				'1.0',
				true
			);
		}

		// Adds post-edit.css
		wp_enqueue_style(
			'fwc-save-and-then-post-edit',
			FWC_Save_And_Then_Utils::plugins_url( 'css/post-edit.css' ),
			array(),
			'1.0'
		);

		// Adds rtl for post-edit.css
		if( function_exists('wp_style_add_data') ) {
			wp_style_add_data( 'fwc-save-and-then-post-edit', 'rtl', 'replace' );
		}
	}


	/**
	 * Adds JavaScript and some HTML to the 'post submit box' in the
	 * edit page.
	 *
	 * Mainly outputs the JavaScript object containing all the enabled
	 * actions and some settings set in Wordpress. Also create
	 * a hidden input containing the referer (used when doing the
	 * redirection).
	 */
	static function post_submitbox_start() {
		error_log('FWC_Save_And_Then_Post_Edit::post_submitbox_start fired');
		echo '<!-- ' . esc_html('FWC_Save_And_Then_Post_Edit::post_submitbox_start fired') . ' -->';
		$options = FWC_Save_And_Then_Settings::get_options();
		$enabled_actions = FWC_Save_And_Then_Settings::get_enabled_actions();
		$current_post = get_post();

		if( ! count( $enabled_actions ) ) {
			error_log('FWC_Save_And_Then_Post_Edit::post_submitbox_start: No enabled actions');
			echo '<!-- ' . esc_html('FWC_Save_And_Then_Post_Edit::post_submitbox_start: No enabled actions') . ' -->';
			return;
		}

		$js_object = array(
			'setAsDefault' => $options['set-as-default'],
			'actions' => array(),
			'defaultActionId' => $options['default-action'],
		);
		foreach ( $enabled_actions as $action ) {
			$new_js_action = array(
				'id' => $action->get_id(),
				'buttonLabelPattern' => $action->get_button_label_pattern( $current_post ),
				'enabled' => $action->is_enabled( $current_post ),
			);
			if( $button_title = $action->get_button_title( $current_post ) ) {
				$new_js_action['title'] = $button_title;
			}
			$js_object['actions'][] = $new_js_action;
		}
		// Output JS config only - no HTML
		echo '<script type="text/javascript">';
		echo 'window.FastWebCreations = window.FastWebCreations || {};';
		echo 'window.FastWebCreations.SaveAndThen = window.FastWebCreations.SaveAndThen || {};';
		echo 'window.FastWebCreations.SaveAndThen.ACTION_LAST_ID = "' . esc_js( FWC_Save_And_Then_Actions::ACTION_LAST ) . '";';
		echo 'window.FastWebCreations.SaveAndThen.HTTP_PARAM_ACTION = "' . esc_js( self::HTTP_PARAM_ACTION ) . '";';
		echo 'window.FastWebCreations.SaveAndThen.config = ' . wp_json_encode( $js_object ) . ';';
		echo '</script>';
	}
} // end class

} // end if( class_exists() )