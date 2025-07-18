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
 * 'Save and duplicate' action: after saving the post,
 * duplicates it (as a draft) and redirects to the new post's
 * edit page.
 */
class FWC_Save_And_Then_Action_Duplicate extends FWC_Save_And_Then_Action {
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_name() {
		// translators: Action name (used in settings page)
		return _x('Save and Duplicate', 'Action name (used in settings page)', 'really-improved-save-button');
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_id() {
		return 'fastwebcreations.duplicate';
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_description() {
		// translators: Action description (used in settings page)
		return _x('<strong>Duplicates the current post</strong> (as a draft) after save and shows the duplicated post\'s edit page.', 'Action description (used in settings page)', 'really-improved-save-button');
	}
	
	/**
	 * @see FWC_Save_And_Then_Action
	 */
	function get_button_label_pattern( $post ) {
		// translators: Button label (used in post edit page). %s = "Publish" or "Update"
		return _x('%s and Duplicate', 'Button label (used in post edit page). %s = "Publish" or "Update"', 'really-improved-save-button');
	}

	/**
	 * Duplicates the current post (as a draft) and returns
	 * the URL of the new post's edit page.
	 *
	 * Inspired by https://rudrastyh.com/wordpress/duplicate-post.html
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  string $current_url
	 * @param  WP_Post $post
	 * @return string
	 */
	function get_redirect_url( $current_url, $post ) {
		$new_post = self::copy_post( $post );

		if( is_wp_error( $new_post ) ) {
			return $new_post;
		}

		self::copy_thumbnail( $post, $new_post );
		self::copy_taxonomies( $post, $new_post );
		self::copy_metas( $post, $new_post );

		$url_parts = FWC_Save_And_Then_Utils::parse_url( $current_url );
		$params = $url_parts['query'];

		// Query params to add
		$params['post'] = $new_post->ID;
		$params['action'] = 'edit';
		$params[ FWC_Save_And_Then_Messages::HTTP_PARAM_UPDATED_POST_ID ] = $post->ID;

		// Standard query params that are kept:
		// - message

		return FWC_Save_And_Then_Utils::admin_url( 'post.php', $params );
	}

	/**
	 * Inserts a new post with the same values as the passed
	 * one. On success, returns the new post. If an error
	 * occured, a WP_Error is returned.
	 * 
	 * @param  WP_Post $post The post to copy
	 * @return WP_Post|WP_Error
	 */
	protected static function copy_post( $post ) {
		$insert_post_args = array(
			'post_content' => $post->post_content,
			// translators: Text added to the duplicated post's title (notice the space at the beginning).
			'post_title' => $post->post_title . _x(' (copy)', 'Text added to the duplicated post\'s title (notice the space at the beginning).', 'really-improved-save-button'),
			'post_excerpt' => $post->post_excerpt,
			'post_status' => 'draft',
			'post_type' => $post->post_type,
			'comment_status' => $post->comment_status,
			'ping_status' => $post->ping_status,
			'post_password' => $post->post_password,
			'post_name' => '', // empty value allowed for draft posts
			'post_parent' => $post->post_parent,
			'menu_order' => $post->menu_order,
			'to_ping' => $post->to_ping,
		);

		$new_post_id = wp_insert_post( $insert_post_args, true );

		if( is_wp_error( $new_post_id) ) {
			return $new_post_id;
		}

		return get_post( $new_post_id );
	}

	/**
	 * Sets the thumbnail of the second post to the same as
	 * the first post.
	 *
	 * @param WP_Post $from_post
	 * @param WP_Post $to_post
	 */
	protected static function copy_thumbnail( $from_post, $to_post ) {
		// Set post thumbnail
		$post_thumbail_id = get_post_thumbnail_id( $from_post->ID );
		if( false != $post_thumbail_id && ! empty( $post_thumbail_id ) ) {
			$res = set_post_thumbnail( $to_post->ID, $post_thumbail_id );
		}
	}

	/**
	 * Copies all taxonomy terms from the first post to
	 * the second.
	 *
	 * @param WP_Post $from_post
	 * @param WP_Post $to_post
	 */
	protected static function copy_taxonomies( $from_post, $to_post ) {
		// wp_insert_post didn't allow to pass taxonomy terms
		// for custom post types, so we do it this way
		$taxonomies = get_object_taxonomies($from_post->post_type);
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($from_post->ID, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($to_post->ID, $post_terms, $taxonomy, false);
		}
	}

	/**
	 * Copies all meta pairs from the first post to
	 * the second.
	 *
	 * @param WP_Post $from_post
	 * @param WP_Post $to_post
	 */
	protected static function copy_metas( $from_post, $to_post ) {
		global $wpdb;

		// Direct database queries are used here for efficient bulk meta copying.
		// This is a one-time admin operation, only triggered by a user action in the admin panel.
		// Caching is not needed because this is not a repeated or performance-critical operation.
		// There is no WordPress API for bulk meta copy; using $wpdb is the only efficient way.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $from_post->ID ) );
		if ( count( $post_meta_infos ) != 0 ) {
			$values_sql = array();
			foreach ( $post_meta_infos as $meta_info ) {
				$values_sql[] = $wpdb->prepare(
					"(%d, %s, %s)",
					$to_post->ID,
					$meta_info->meta_key,
					$meta_info->meta_value
				);
			}
			if ( ! empty( $values_sql ) ) {
				$query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES " . implode( ', ', $values_sql );
				$wpdb->query( $query );
			}
		}
	}
}