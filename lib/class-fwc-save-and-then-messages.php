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

if( ! class_exists( 'FWC_Save_And_Then_Messages' ) ) {

/**
 * Manages message display in the administation header after a redirect.
 */

class FWC_Save_And_Then_Messages {

	/**
	 * URL parameter defining the id of the post that was being modified
	 * before the redirect.
	 */
	const HTTP_PARAM_UPDATED_POST_ID = 'fwc-sat-updated-post-id';

	/**
	 * Main entry point. Setups all the Wordpress hooks.
	 */
	static function setup() {
		add_filter( 'post_updated_messages', array( get_called_class(), 'post_updated_messages' ), 99 );
		add_filter( 'removable_query_args', array( get_called_class(), 'removable_query_args' ), 99 );
		// Add nonce field to the post edit form for this action
		add_action('edit_form_after_title', function() {
			wp_nonce_field('fwc_sat_messages_action', '_fwc_sat_messages_nonce');
		});
	}

	/**
	 * Adds the URL param containing the last modified post id to
	 * the list of URL params that can be removed after being used once.
	 * 
	 * @param  array $removable_query_args An array of parameters to remove from the URL.
	 * @return array                       The array with the added param.
	 */
	static function removable_query_args( $removable_query_args ) {
		$removable_query_args[] = self::HTTP_PARAM_UPDATED_POST_ID;
		return $removable_query_args;
	}

	/**
	 * If the plugin did a redirect, we update the success messages.
	 * The regular messages always contain links and date of the
	 * currently show post, but, if we did a redirect, we want to
	 * change them to reflect the post where we were.
	 *
	 * Called by the post_updated_messages filter. The message is
	 * only shown when the redirected page (the page shown after
	 * saving) is the 'edit' or 'new' post page. It doesn't show
	 * on a 'list posts' page.
	 *
	 * @see          wp-admin/edit-form-advanced.php ($messages variable)
	 * @param  array $messages Associative array of messages per post type
	 * @return array The modified messages array
	 */
	static function post_updated_messages( $messages ) {

		// Only modify the messages if this plugin did a redirect
		if( ! isset( $_REQUEST[ self::HTTP_PARAM_UPDATED_POST_ID ] ) ) {
			return $messages;
		}
		$nonce = isset( $_REQUEST['_fwc_sat_messages_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_fwc_sat_messages_nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'fwc_sat_messages_action' ) ) {
			return $messages;
		}
		// In the default messages, the post URL references the
		// current post. With our plugin, we need to change
		// those URLs to the ones of the post that was just edited.

		$current_post = get_post();
		$post_ID = $current_post->ID;
		$previous_post_ID = intval( sanitize_text_field( wp_unslash( $_REQUEST[ self::HTTP_PARAM_UPDATED_POST_ID ] ) ) );

		// Check if the post exists
		if (false === get_post_status($previous_post_ID)) {
			return $messages;
		}
		
		$current_permalink_url = get_permalink( $post_ID );
		$current_preview_url = get_preview_post_link( $post_ID );
		$previous_permalink_url = get_permalink( $previous_post_ID );
		$previous_preview_url = get_preview_post_link( $previous_post_ID );

		foreach ($messages as $post_type => $post_messages) {
			foreach ($post_messages as $code => $message) {
				$message_changed = false;
				$new_message = '';

				// We replaces URLs (normal and escaped versions)
				switch ($code) {
					case 1:
					case 6:
					case 9:
						$new_message = str_replace($current_permalink_url, $previous_permalink_url, $message);
						$new_message = str_replace(esc_url($current_permalink_url), esc_url($previous_permalink_url), $new_message);
						$message_changed = true;
						break;

					case 8:
					case 10:
						$new_message = str_replace($current_preview_url, $previous_preview_url, $message);
						$new_message = str_replace(esc_url($current_preview_url), esc_url($previous_preview_url), $new_message);
						$message_changed = true;
						break;
				}

				// We update the published date
				if( 9 == $code ) {
					$date_format = _x('M j, Y @ H:i', 'Date format used to find and replace the date in success message for scheduled posts. Important: translate with *exactly* the official Wordpress translation for this string.', 'really-improved-save-button');
					$previous_post = get_post( $previous_post_ID );
					$current_date = date_i18n( $date_format, strtotime( $current_post->post_date ) );
					$previous_date = date_i18n( $date_format, strtotime( $previous_post->post_date ) );

					$new_message = str_replace($current_date, $previous_date, $message);
					$message_changed = true;
				}

				if( $message_changed ) {
					$messages[$post_type][$code] = $new_message;
				}
			}
		}

		return $messages;
	}
} // end class

} // end if( class_exists() )