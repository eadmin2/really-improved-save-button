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

/**
 * 'Save and Return' action: after saving the post,
 * returns to the referer page, no mater which page it was.
 */
class FWC_Save_And_Then_Action_Return extends FWC_Save_And_Then_Action {

	/**
	 * Name of the cookie that will contain the URL of the 
	 * referer page.
	 */
	const COOKIE_REFERER_URL = 'fwc-sat_return_referer';

	/**
	 * Constructor, adds a Wordpress hook to 'current_screen' action.
	 */
	function __construct() {
		parent::__construct();
		add_action('current_screen', array( $this, 'save_referer' ) );
	}

	/**
	 * If we are in a post edit page (so this action could be
	 * called), we save in a cookie the referer. If this
	 * action is used, we will redirect to this saved URL.
	 * 
	 * @param  WP_Screen $wp_screen WP_Screen returned by the current_screen action
	 */
	function save_referer( $wp_screen ) {
		if( $wp_screen->base == 'post' ) {
			// Only execute this function in GET
			if( ! isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET' ) {
				return;
			}

			$referer_url = '';

			if( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$referer_url = esc_url_raw( $_SERVER['HTTP_REFERER'] );
			}

			// If the referer is also the same as this post 
			// edit screen, we don't save its URL. This will
			// allow to use the regular "Update" button at
			// least once without losing where we were before
			// editing.
			$is_same_url = false;
			if( isset( $_GET['post'] ) ) {
				$is_same_url = FWC_Save_And_Then_Utils::url_is_post_edit( $referer_url, intval($_GET['post']) );
			}

			if( ! $is_same_url ) {
				setcookie( self::COOKIE_REFERER_URL, $referer_url );
			}
		}
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_name() {
		return _x('Save and Return', 'Action name (used in settings page)', 'improved-save-button');
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_id() {
		return 'fastwebcreations.return';
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_description() {
		return _x('Returns to the <strong>previous page</strong> (no matter which page) after save.', 'Action description (used in settings page)', 'improved-save-button');
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_button_label_pattern( $post ) {
		return _x('%s and Return', 'Button label (used in post edit page). %s = "Publish" or "Update"', 'improved-save-button');
	}

	/**
	 * Returns the URL of the page we were before editing
	 * the post. If, for any reason, we cannot determine
	 * the referer page, returns the $current_url.
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  string $current_url
	 * @param  WP_Post $post
	 * @return string
	 */
	function get_redirect_url( $current_url, $post ) {
		$referer = isset( $_REQUEST['_fwc-sat_return_referer'] ) ? esc_url_raw( $_REQUEST['_fwc-sat_return_referer'] ) : '';
		error_log('[SaveAndThen] Save and Return get_redirect_url called. Redirecting to: ' . $referer);
		return $referer;
	}
}