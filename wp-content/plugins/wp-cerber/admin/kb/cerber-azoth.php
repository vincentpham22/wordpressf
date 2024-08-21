<?php
/*
	Copyright (C) 2015-24 CERBER TECH INC., https://wpcerber.com

    Licenced under the GNU GPL.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/*

*========================================================================*
|                                                                        |
|	       ATTENTION!  Do not change or edit this file!                  |
|                                                                        |
*========================================================================*

*/

final class CRB_Explainer {
	private static $counter = 0;
	private static $user_id = 0;
	private static $activity;
	private static $closing_html = '';
	private static $ip = '';

	/* Prevents duplicates when including settings in the explainer */

	private static $done_sts = array();

	/**
	 * Generates UI HTML elements for displaying extended information on event as a popup
	 *
	 * @param int $activity Activity ID
	 * @param int $status Status ID
	 * @param int $user_id User ID
	 * @param string $set_list Comma-separated list of settings from the log entry
	 * @param string $ip IP address
	 * @param string $control Link text to open the popup
	 * @param string $closing_html To be displayed bellow the explainer text, no block-level HTML tags are allowed
	 * @param string $footer To be displayed at the bottom of the explainer element
	 *
	 * @return string  Sanitized HTML
	 *
	 * @since 9.6.1.3
	 */
	static function create_popup( $activity, $status, $user_id, $set_list, $ip = '', $control = '', $closing_html = '', $footer = '' ) {

		if ( $set_list ) {
			$set_list = explode( ',', $set_list );
		}

		self::$activity = $activity;
		self::$closing_html = $closing_html;
		self::$ip = $ip;

		if ( ! $explainer = self::create( $activity, $status, $user_id, $set_list ) ) {
			return '';
		}

		self::$counter++;
		$dom_id = 'crb-expl-' . self::$counter;

		CRB_Globals::to_admin_footer( '<div class="crb-popup-dialog mfp-hide" id="' . $dom_id . '">' . $explainer . $footer . '</div>' );

		if ( ! $control ) {
			$control = '<svg height="2em" viewBox="-5 -5 28 28" preserveAspectRatio="xMidYMid meet" focusable="false"><path d="M9 6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 5a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 5a2 2 0 1 1 0-4 2 2 0 0 1 0 4z" fill-rule="evenodd"></path></svg>';
		}

		return '<div class="crb-act-context-menu"><a href="#" data-popup_element_id="' . $dom_id . '" class="crb-popup-dialog-open">' . $control . '</a></div>';
	}

	/**
	 * Generates a KB explainer for an event
	 *
	 * @param int $activity
	 * @param int $status
	 * @param int $user_id
	 * @param array $set_list List of settings from the log entry
	 *
	 * @return string Sanitized HTML
	 *
	 * @since 9.6.1.3
	 */
	static function create( $activity, $status = 0, $user_id = 0, $set_list = array() ) {
		self::$done_sts = array();
		self::$user_id = $user_id;

		$explainer = array();

		// IP Blocked

		if ( $activity == 10 || $activity == 11 ) {
			if ( ( $title = cerber_get_reason( $status, '' ) )
			     && $texts = self::make_explainer( 'reason', $status, $set_list ) ) {
				$explainer[] = array( $title, $texts );
			}
		}
		else {

			if ( ( $title = cerber_get_labels( 'activity', $activity ) )
			     && $texts = self::make_explainer( 'activity', $activity, $set_list ) ) {
				$explainer[] = array( $title, $texts );
			}

			if ( $status > 0
			     && $status != 520
			     && ( $title = cerber_get_labels( 'status', $status ) )
			     && $texts = self::make_explainer( 'status', $status, $set_list ) ) {
				$explainer[] = array( $title, $texts );
			}
		}

		if ( $explainer ) {

			$html = '';

			foreach ( $explainer as $section ) {

				$html .= '<h4>' . $section[0] . '</h4>';

				foreach ( $section[1] as $class => $sec_items ) {

					$html .= '<div class="crb-kb-' . $class . '">';

					foreach ( $sec_items as $item ) {
						$html .= '<p class="' . ( $item[1] ?? '' ) . '">' . $item[0] . '</p>';
					}

					$html .= '</div>';
				}
			}

			return '<div class="crb-explainer">' . $html . '</div>';
		}

		return '';
	}

	/**
	 * Builds and returns an array holding the explainer
	 *
	 * @param string $type Type of the event
	 * @param string|int $id ID of the event
	 * @param array $set_list Settings from the log entry
	 *
	 * @return array Elements of the explainer. HTML is filtered and escaped.
	 *
	 * @since 9.6.1.3
	 */
	static function make_explainer( $type, $id, $set_list ) {

		$setting_expl = array();

		// Check for a "master" setting that guided WP Cerber

		$final = ( $set_list ) ? array_pop( $set_list ) : '';

		if ( $final
		     && ! isset( self::$done_sts[ $final ] )
		     && $st_desc = self::get_setting_desc( $final ) ) {

			$setting_expl[] = array( crb_get_icon( 'settings' ) . __( 'WP Cerber processed this request according to this setting', 'wp-cerber' ), 'crb-kb-setting-intro' );
			$setting_expl[] = array( $st_desc );
			self::$done_sts[ $final ] = 1;
		}
		else {
			$st_desc = '';
		}

		// Now build the explainer

		$expl = array();
		$act_link = self::get_ip_log_link();

		if ( $kb_entry = self::get_kb_data( $type, $id ) ) {

			// Text

			if ( $kb_entry['explainer'] ) {
				$expl['kb_explain'] = array( array( crb_strip_tags( $kb_entry['explainer'] ) ) );
			}

			if ( $kb_entry['action'] ) {
				$expl['kb_action'] = array( array( crb_strip_tags( $kb_entry['action'] ) ) );
			}

			if ( self::$closing_html ) {
				$expl['kb_closing'] = array( array( self::$closing_html ) );
			}

			if ( $act_link ) {
				$expl['kb_show_log'] = array( array( $act_link ) );
			}

			// Settings if any

			if ( $list = $kb_entry['sts_list'] ?? false ) {
				if ( $st_desc ) {
					unset( $list[ $final ] );
				}

				$list = array_diff_key( $list, self::$done_sts );

				if ( ! empty( $list ) ) {
					$setting_expl[] = array( crb_get_icon( 'settings' ) . ( ( $st_desc ) ? __( 'Other related WP Cerber settings', 'wp-cerber' ) : __( 'Settings that control behavior of WP Cerber', 'wp-cerber' ) ), 'crb-kb-setting-intro' );

					foreach ( $list as $st_id => $st_desc ) {
						$setting_expl[] = array( $st_desc );
						self::$done_sts[ $st_id ] = 1;
					}
				}
			}

			if ( $setting_expl ) {
				$expl['kb_settings'] = $setting_expl;
			}

			// Documentation links

			if ( $link = $kb_entry['doc_link'] ?? '' ) {
				$link = esc_url( $link );
				$expl['kb_link'] = array( array( crb_get_icon( 'know_more' ) . '<a href="' . $link . '" target="_blank">' . $link . '</a>' ) );
			}

		}
		else {
			if ( $setting_expl ) {

				$expl['kb_settings'] = $setting_expl;

				if ( $act_link ) {
					$expl['kb_show_log'] = array( array( $act_link ) );
				}
			}
		}

		return $expl;
	}

	/**
	 * Prepares KB entry for use to build an explainer
	 *
	 * @param string $type Type of the event
	 * @param int|string $id ID of the event
	 *
	 * @return array|false Returns false if no KB entry found
	 *
	 * @since 9.6.1.3
	 */
	static function get_kb_data( $type, $id ) {

		if ( ! $kb = CRB_Wisdom::get( $type, $id ) ) {
			return false;
		}

		$desc = $kb['kb_desc'] ?? '';

		$action = $kb['kb_action'] ?? '';

		$settings = array();

		if ( $kb['kb_sts'] ?? false ) {

			foreach ( $kb['kb_sts'] as $sts ) {
				if ( ( $setting = cerber_settings_config( array( 'setting' => $sts ) ) )
				     && $title = $setting['title'] ?? '' ) {

					$settings[ $sts ] = array( $title, $setting['tab_id'] );
				}

				$settings[ $sts ] = self::get_setting_desc( $sts );
			}
		}

		if ( $kb['kb_url'] ) {
			$doc_link = esc_url( $kb['kb_url'] );
		}
		else {
			$doc_link = '';
		}

		if ( $desc || $settings || $doc_link ) {
			return array( 'explainer' => $desc,  'action' => $action, 'sts_list' => $settings, 'doc_link' => $doc_link );
		}

		return false;
	}

	/**
	 * Returns settings title and link to the admin page where it's located
	 *
	 * @param string $setting_id WP Cerber setting ID
	 *
	 * @return string Safe HTML to be used on a web page.
	 *
	 * @since 9.6.1.3
	 */
	static function get_setting_desc( $setting_id ) {

		$title = '';
		$tab = '';
		$bm = '';

		if ( $setting = cerber_settings_config( array( 'setting' => $setting_id ) ) ) {
			if ( $title = $setting['title'] ?? '' ) {
				$tab = $setting['tab_id'];
				$bm = '#' . CRB_SETTING_PREFIX . 'global-' . $setting_id;
			}
		}
		elseif ( $setting = crb_admin_role_config( $setting_id ) ) {
			if ( $title = $setting['title'] ?? '' ) {
				$tab = 'role_policies';

				if ( ( $user = crb_get_userdata( self::$user_id ) )
				     && $role = crb_sanitize_id( array_shift( $user->roles ) ) ) { // Currently, we do not support multiple roles
					$bm = '#' . $role;
				}
			}
		}

		if ( $title ) {
			return $title . ' [ <a href="' . cerber_admin_link( $tab ) . $bm . '" target="_blank">' . __( 'Manage', 'wp-cerber' ) . '</a> ]';
		}

		return '';
	}

	/**
	 * Returns the link to the activity log, if applicable in the context
	 *
	 * @return string|false
	 */
	static function get_ip_log_link() {
		if ( ! self::$ip ) {
			return false;
		}

		$show = false;

		if ( cerber_block_check( self::$ip ) ) {
			$show = true;
		}
		elseif ( self::$activity ) {
			$in = crb_get_activity_set( 'suspicious' );
			if ( in_array( self::$activity, $in ) ) {
				$show = true;
			}
		}

		if ( $show ) {
			return crb_get_icon( 'activity' ) . '<a href="' . cerber_admin_link( 'activity' ) . '&filter_set=1&filter_ip=' . self::$ip . '">' . __( 'View log of suspicious and malicious activity from this IP address', 'wp-cerber' ) . '</a>';
		}

		return false;
	}

}

final class CRB_Wisdom {
	static $kb = array();
	static $ready = false;

	/**
	 * Returns RAW, unescaped data from the KB
	 *
	 * @param string $type
	 * @param string|int $id
	 *
	 * @return array
	 *
	 * @since 9.6.1.3
	 */
	static function get( $type, $id, $default = false ) {
		if ( ! self::$ready ) {
			self::load();
		}

		return crb_array_get( self::$kb, array( $type, $id ), $default );
	}

	/**
	 * Loading KB to a variable and to the object cache
	 *
	 * @return void
	 *
	 * @since 9.6.1.3
	 */
	static function load() {
		self::$ready = true;
		self::$kb = array();

		if ( self::$kb = cerber_cache_get( 'azoth_data' . self::determine_locale() ) ) {
			return;
		}

		$data = cerber_get_set( 'azoth_loaded' . self::determine_locale() );

		if ( ! $data
		     && ! $data = self::load_local_file() ) {
			return;
		}

		self::$kb = $data['azoth'];

		cerber_cache_set( 'azoth_data' . self::determine_locale(), self::$kb, 8 * 3600 );
	}

	/**
	 * Loading KB data from the bundled KB file
	 *
	 * @return false|array
	 *
	 * @since 9.6.1.3
	 */
	static function load_local_file() {
		if ( ! $json_text = file_get_contents( __DIR__ . '/data/azoth_data.json' ) ) {
			return false;
		}

		$data = json_decode( $json_text, true );

		if ( ! $data
		     || JSON_ERROR_NONE != json_last_error()
		     || empty( $data['azoth'] )
		     || empty( $data['kb_updated'] ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Loading updates to KB from the official WP Cerber website
	 *
	 * @param int $user_id User to determine the locale (translation) of KB files
	 *
	 * @return array|void|WP_Error
	 *
	 * @since 9.6.1.3
	 */
	static function load_remote_file( $user_id = 0 ) {

		$user_locale = self::determine_locale( $user_id );

		$response = wp_remote_get( 'https://downloads.wpcerber.com/azoth/azoth_data' . $user_locale . '.json' );

		if ( is_wp_error( $response ) ) {

			return $response;
		}

		if ( ! $body = wp_remote_retrieve_body( $response ) ) {

			return;
		}

		$loaded = json_decode( $body, true );

		if ( ! $loaded
		     || JSON_ERROR_NONE != json_last_error()
		     || empty( $loaded['azoth'] )
		     || empty( $loaded['kb_updated'] ) ) {

			return;
		}

		if ( ( $local_file = self::load_local_file() )
		     && $local_file['kb_updated'] > $loaded['kb_updated'] ) {

			// If we have a previously saved KB data, it's outdated now

			cerber_update_set( 'azoth_loaded' . $user_locale, array() );

			return;
		}

		if ( ( $stored = cerber_get_set( 'azoth_loaded' . $user_locale ) )
		     && ( $stored['kb_updated'] >= $loaded['kb_updated'] ) ) {

			// No update needed

			return;
		}

		// There is an update to KB, let's store it

		cerber_update_set( 'azoth_loaded' . $user_locale, $loaded );

		return $loaded;
	}

	/**
	 * Schedule checking for possible updates to KB.
	 *
	 * @return void
	 *
	 * @since 9.6.1.3
	 */
	static function scheduled_updating() {

		if ( get_site_transient( 'cerber_update_kb' ) ) {
			return;
		}

		cerber_bg_task_add( 'crb_azoth_check_update', array( 'load_admin' => 1, 'args' => array( get_current_user_id() ) ) );

		set_site_transient( 'cerber_update_kb', 1, 24 * 3600 );
	}

	/**
	 * Returns the user locale if it's non-English.
	 * Returns an empty string for any English-based locale.
	 *
	 * @param int $user_id  User to determine the locale (translation). Defaults to the current user.
	 *
	 * @return string
	 *
	 * @since 9.6.1.3
	 */
	static function determine_locale( $user_id = 0 ) {
		static $user_locale;

		if ( crb_get_settings( 'admin_lang' ) ) {
			return '';
		}

		if ( ! $user_locale ) {
			$user_locale = crb_sanitize_id( get_user_locale( $user_id ) );
			$user_locale = ( 0 === strpos( $user_locale, 'en_' ) ) ? '' : '_' . $user_locale;
		}

		return $user_locale;
	}

}

/**
 * Wrapper for CRB_Wisdom::load_remote_file()
 * It's used with cerber_bg_task_add() since it can't handle class methods as callbacks
 *
 * @param $user_id
 *
 * @return void
 *
 * @since 9.6.1.3
 */
function crb_azoth_check_update( $user_id ) {
	CRB_Wisdom::load_remote_file( $user_id );
}