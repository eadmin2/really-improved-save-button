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
 * 'Save and view' action: after saving the post, redirects to the post
 * page, on the frontend.
 */
class FWC_Save_And_Then_Action_View extends FWC_Save_And_Then_Action {

	/**
	 * @see FWC_Save_And_Then_Action
	 */	
	function get_name() {
		// translators: Action name (used in settings page)
		return _x('Save and View', 'Action name (used in settings page)', 'really-improved-save-button');
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_id() {
		return 'fastwebcreations.view';
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_description() {
		// translators: Action description (used in settings page)
		return _x('Shows the <strong>post itself</strong> after save. The same window is used.', 'Action description (used in settings page)', 'really-improved-save-button');
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_button_label_pattern( $post ) {
		// translators: Button label (used in post edit page). %s = "Publish" or "Update"
		return _x('%s and View', 'Button label (used in post edit page). %s = "Publish" or "Update"', 'really-improved-save-button');
	}

	/**
	 * Returns a title attribute that simply informs the
	 * user the post will open in the same window.
	 * 
	 * @see FWC_Save_And_Then_Action
	 * @param WP_Post $post
	 */	
	function get_button_title( $post ) {
		// translators: Button title attribute (used in post edit page)
		return _x('The post will be shown in this window.', 'Button title attribute (used in post edit page)', 'really-improved-save-button');
	}

	/**
	 * Returns the URL of the post's page on the frontend.
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  string $current_url
	 * @param  WP_Post $post
	 * @return string
	 */
	function get_redirect_url( $current_url, $post ) {
		$url = get_permalink( $post );
		// error_log('[SaveAndThen] Save and View get_redirect_url called. Redirecting to: ' . $url);
		return $url;
	}
}