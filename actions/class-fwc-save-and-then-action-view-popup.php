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
 * 'Save and view (popup)' action: after saving the post, the same post
 * editing page is shown, but a popup is opened with the post's frontend
 * page.
 */
class FWC_Save_And_Then_Action_View_Popup extends FWC_Save_And_Then_Action {

	/**
	 * HTTP param added to the URL when we re-show the post editing
	 * page (after saving the post with this action) that triggers
	 * the JavaScrip that reloads the popup containing the post's
	 * frontend page.
	 */
	const HTTP_PARAM_RELOAD_POPUP = 'fwc-sat-reload-popup';
	const HTML_ICON = '<span class="dashicons dashicons-external"></span>';

	/**
	 * Constructor. Adds some hooks.
	 */
	function __construct() {
		parent::__construct();
		add_action( 'post_submitbox_start', array( $this, 'post_submitbox_start' ) );
		add_filter( 'removable_query_args', array( get_called_class(), 'removable_query_args' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_view_popup_script' ) );
	}

	/**
	 * Adds the URL param HTTP_PARAM_RELOAD_POPUP to the list of
	 * URL params that can be removed after being used once.
	 * Called by the removable_query_args filter in the constructor.
	 * 
	 * @param  array $removable_query_args An array of parameters to remove from the URL.
	 * @return array                       The array with the added param.
	 */
	static function removable_query_args( $removable_query_args ) {
		$removable_query_args[] = self::HTTP_PARAM_RELOAD_POPUP;
		return $removable_query_args;
	}

	/**
	 * Enqueue the JS for the Save and View (popup) action and localize variables.
	 */
	public function enqueue_view_popup_script($hook) {
		if ($hook !== 'post.php' && $hook !== 'post-new.php') {
			return;
		}

		wp_enqueue_script(
			'fwc-view-popup',
			plugin_dir_url(__FILE__) . '../js/view-popup.js',
			array('jquery'),
			'1.0',
			true
		);

		$reload_popup = isset($_GET[self::HTTP_PARAM_RELOAD_POPUP]) && $_GET[self::HTTP_PARAM_RELOAD_POPUP] === '1';
		$permalink = $reload_popup ? get_permalink() : '';

		wp_localize_script('fwc-view-popup', 'FWCViewPopupData', array(
			'actionId'    => $this->get_id(),
			'windowName'  => 'fwc-save-and-then-post-preview',
			'waitMessage' => esc_html( _x('Please wait while the post is being saved. This window will refresh automatically.', 'Message shown in the new window when "Save and view (new window)" is used.', 'really-improved-save-button') ),
			'reloadPopup' => $reload_popup,
			'permalink'   => $permalink,
		));
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */		
	function get_name() {
		// translators: Action name (used in settings page). %s = new window icon
		return sprintf( _x('Save and View %s (new window)', 'Action name (used in settings page). %s = new window icon', 'really-improved-save-button'), self::HTML_ICON );
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */	
	function get_id() {
		return 'fastwebcreations.viewPopup';
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */	
	function get_description() {
		// translators: Action description (used in settings page)
		return _x('Shows the <strong>post itself in a new window</strong> after save.', 'Action description (used in settings page)', 'really-improved-save-button');
	}

	/**
	 * @see FWC_Save_And_Then_Action
	 */	
	function get_button_label_pattern( $post ) {
		// translators: Button label (used in post edit page). %%s = "Publish" or "Update"; %s = new window icon
		// translators: The first %s must be escaped, because it is not replaced by this sprintf. The second %s is replaced by the new window icon HTML.
		return sprintf( _x('%%s and View %s', 'Button label (used in post edit page). %%s = "Publish" or "Update"; %s = new window icon', 'really-improved-save-button'), self::HTML_ICON );
	}

	/**
	 * Returns a title attribute that simply informs the
	 * user the post will open in a new window.
	 * 
	 * @see FWC_Save_And_Then_Action
	 * @param WP_Post $post
	 */	
	function get_button_title( $post ) {
		// translators: Button title attribute (used in post edit page)
		return _x('The post will be shown in a new window.', 'Button title attribute (used in post edit page)', 'really-improved-save-button');
	}

	/**
	 * Returns the current redirect url, but adds the parameter to
	 * trigger the JavaScript popup reload. Verifies nonce before processing.
	 *
	 * @see FWC_Save_And_Then_Action
	 * @param  string $current_url
	 * @param  WP_Post $post
	 * @return string
	 */
	function get_redirect_url( $current_url, $post ) {
		$nonce = isset( $_REQUEST['_fwc_sat_view_popup_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_fwc_sat_view_popup_nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'fwc_sat_view_popup_action' ) ) {
			return $current_url;
		}
		$url = get_permalink( $post );
		return $url;
	}

	/**
	 * Outputs content at the start of the post submit box. Placeholder to prevent fatal error.
	 */
	public function post_submitbox_start() {
	    // No output needed for now. Implement if needed.
	}
}