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

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

require_once('lib/class-fwc-save-and-then-settings.php');

if( ! is_multisite() ) {
	delete_option( FWC_Save_And_Then_Settings::MAIN_SETTING_NAME );
} else {
	global $wpdb;
	
	$blog_ids = wp_cache_get( 'fwc_all_blog_ids', 'fwc_save_and_then' );
	if ( false === $blog_ids ) {
		// Direct database query is used here to retrieve all blog IDs for multisite uninstall.
		// This is necessary because WordPress does not provide a built-in function for this.
		// Result is cached to avoid repeated queries and minimize DB load during uninstall.
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		wp_cache_set( 'fwc_all_blog_ids', $blog_ids, 'fwc_save_and_then', 300 );
	}
	$original_blog_id = get_current_blog_id();

	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		delete_option( FWC_Save_And_Then_Settings::MAIN_SETTING_NAME );    
	}

	switch_to_blog( $original_blog_id );
}