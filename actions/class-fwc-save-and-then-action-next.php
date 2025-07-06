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
 * 'Save and next' action: after saving the post, redirects to the
 * edit screen of the next post (same post type). If no next post exists,
 * (note that the action will be disabled, but just in case) does nothing
 * (the default Wordpress redirect).
 */
class FWC_Save_And_Then_Action_Next extends FWC_Save_And_Then_Action {

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_name() {
		// translators: Action name (used in settings page)
		return _x('Save and Next', 'Action name (used in settings page)', 'really-improved-save-button');
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_id() {
		return 'fastwebcreations.next';
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_description() {
		// translators: Action description (used in settings page)
		return _x('Shows the <strong>next post</strong> edit form after save.', 'Action description (used in settings page)', 'really-improved-save-button');
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_button_label_pattern( $post ) {
		// translators: Button label (used in post edit page). %s = "Publish" or "Update"
		return _x('%s and Next', 'Button label (used in post edit page). %s = "Publish" or "Update"', 'really-improved-save-button');
	}

	/**
	 * Returns true only if there is a next post. Else
	 * returns false.
	 * 
	 * @see FWC_Save_And_Then_Action
	 * @param  WP_Post $post
	 * @return boolean
	 */
	function is_enabled( $post ) {
		return !! FWC_Save_And_Then_Utils::get_adjacent_post( $post, 'next' );
	}

	/**
	 * Returns the HTML title attribute for this action that says
	 * the name of the next post (if there is one), else a message
	 * indicating why the action is disabled.
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  WP_Post $post
	 * @return string
	 */
	function get_button_title( $post ) {
		if( ! $this->is_enabled( $post ) ) {
			// translators: Button title attribute (used in post edit page)
			return _x('You are at the last post.', 'Button title attribute (used in post edit page)', 'really-improved-save-button');
		} else {
			$next_post = FWC_Save_And_Then_Utils::get_adjacent_post( $post, 'next' );
			// translators: Button title attribute (used in post edit page). %s = other post name
			return sprintf( _x('The next post is "%s".', 'Button title attribute (used in post edit page). %s = other post name', 'really-improved-save-button'), $next_post->post_title );
		}
	}

	/**
	 * Returns the URL of the next post's Edit screen. If there
	 * is not a next post, returns null.
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  string $current_url
	 * @param  WP_Post $post
	 * @return string|null
	 */
	function get_redirect_url( $current_url, $post ) {
		$next_post = FWC_Save_And_Then_Utils::get_adjacent_post( $post, 'next' );
		$url = $next_post ? get_edit_post_link( $next_post->ID, 'url' ) : '';
		// error_log('[SaveAndThen] Save and Next get_redirect_url called. Redirecting to: ' . $url);
		return $url;
	}
}