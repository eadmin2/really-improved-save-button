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
 * 'Save and list' action: after saving the post, redirects to the
 * post listing page.
 */
class FWC_Save_And_Then_Action_List extends FWC_Save_And_Then_Action {

	/**
	 * Cookie name that contains the URL of the last 'post list'
	 * page that was visited.
	 */
	const COOKIE_LAST_EDIT_URL = 'lbsat_last_edit_url';

	/**
	 * Constructor, adds a Wordpress hook to 'current_screen' action.
	 */
	function __construct() {
		parent::__construct();
		add_action('current_screen', array( $this, 'check_post_list_page' ) );
	}

	/**
	 * If we are on an post listing page, we save the current
	 * URL in a cookie, including all filtering and paginating
	 * parameters.
	 * 
	 * When The user uses this action, we check
	 * if the last visited post listing page (in the cookie) is
	 * the listing page of this post type. If so, we redirect
	 * to this page.
	 * 
	 * @param  WP_Screen $wp_screen WP_Screen returned by the current_screen action
	 */
	function check_post_list_page( $wp_screen ) {
		if( $wp_screen->base == 'edit' ) {
			$url = admin_url('edit.php');

			if( isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ) {
				// Only allow safe query strings
				$url .= '?' . esc_url_raw( $_SERVER['QUERY_STRING'] );
			}
			setcookie( self::COOKIE_LAST_EDIT_URL, $url );
		}
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_name() {
		return _x('Save and List', 'Action name (used in settings page)', 'improved-save-button');
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_id() {
		return 'fastwebcreations.list';
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_description() {
		return _x('Shows the <strong>posts list</strong> after save.', 'Action description (used in settings page)', 'improved-save-button');
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_button_label_pattern( $post ) {
		return _x('%s and List', 'Button label (used in post edit page). %s = "Publish" or "Update"', 'improved-save-button');
	}

	/**
	 * Returns the post listing page URL for this post type.
	 * If the last post listing page visited was the one
	 * for this post, we try to return the URL with the
	 * same filtering and paging parameters that were used.
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  string $current_url
	 * @param  WP_Post $post
	 * @return string
	 */
	function get_redirect_url( $current_url, $post ) {
		$post_type = get_post_type( $post );
		$url_parts = FWC_Save_And_Then_Utils::parse_url( $current_url );
		$params = $url_parts['query'];
		unset( $params['post'] );
		unset( $params['action'] );
		if( $post_type && 'post' != $post_type ) {
			$params['post_type'] = $post_type;
		}
		$params[ FWC_Save_And_Then_Messages::HTTP_PARAM_UPDATED_POST_ID ] = $post->ID;
		$redirect_url = FWC_Save_And_Then_Utils::admin_url( 'edit.php', $params );
		error_log('[SaveAndThen] Save and List get_redirect_url called. Redirecting to: ' . $redirect_url);
		return $redirect_url;
	}
}