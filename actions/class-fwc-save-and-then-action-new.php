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
 * 'Save and new' action: after saving the post, redirects to the
 * new post screen.
 */
class FWC_Save_And_Then_Action_New extends FWC_Save_And_Then_Action {
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_name() {
		// translators: Action name (used in settings page)
		return _x('Save and New', 'Action name (used in settings page)', 'really-improved-save-button');
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_id() {
		return 'fastwebcreations.new';
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_description() {
		// translators: Action description (used in settings page)
		return _x('Shows the <strong>new post</strong> form after save.', 'Action description (used in settings page)', 'really-improved-save-button');
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_button_label_pattern( $post ) {
		// translators: Button label (used in post edit page). %s = "Publish" or "Update"
		return _x('%s and New', 'Button label (used in post edit page). %s = "Publish" or "Update"', 'really-improved-save-button');
	}

	/**
	 * Returns the URL of the New Post screen for this post type.
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  string $current_url
	 * @param  WP_Post $post
	 * @return string
	 */
	function get_redirect_url( $current_url, $post ) {
		$post_type = get_post_type( $post );
		// Start with a clean parameter array
		$params = array();
		// Only add post_type if it's not the default 'post' type
		if( $post_type && 'post' != $post_type ) {
			$params['post_type'] = $post_type;
		}
		// Add the updated post ID for messaging
		$params[ FWC_Save_And_Then_Messages::HTTP_PARAM_UPDATED_POST_ID ] = $post->ID;
		return FWC_Save_And_Then_Utils::admin_url( 'post-new.php', $params );
	}
}