<?php
/*
 * functions releted WPML
 */

defined( 'ABSPATH' ) || exit;

/* Get default language */
if ( ! function_exists( 'lordcros_core_get_default_language' ) ) {
	function lordcros_core_get_default_language() {
		global $sitepress;
		if ( $sitepress ) {
			return $sitepress->get_default_language();
		} elseif ( defined( 'WPLANG' ) ) {
			return WPLANG;
		} else {
			return "en";
		}
	}
}

/* Switch language */
if ( ! function_exists( 'lordcros_core_switch_language' ) ) {
	function lordcros_core_switch_language( $lang ) {
		if ( class_exists( 'SitePress' ) ) {
			global $sitepress;
			$sitepress->switch_lang( $lang );
		}
	}
}

/* Get sibling room id in original language */
if ( ! function_exists( 'lordcros_core_room_org_id' ) ) {
	function lordcros_core_room_org_id( $id ) {
		return lordcros_core_get_default_language_post_id( $id, 'room' );
	}
}

/* get sibling room id in current language */
if ( ! function_exists( 'lordcros_core_room_clang_id' ) ) {
	function lordcros_core_room_clang_id( $id ) {
		return lordcros_core_get_current_language_post_id( $id, 'room' );
	}
}

/* Get sibling post id in default language */
if ( ! function_exists( 'lordcros_core_get_default_language_post_id' ) ) {
	function lordcros_core_get_default_language_post_id( $id, $post_type = 'post' ) {
		if ( class_exists( 'SitePress' ) ) {
			global $sitepress;

			$default_language = $sitepress->get_default_language();
			return icl_object_id( $id, $post_type, true, $default_language );
		} else {
			return $id;
		}
	}
}

/* Get sibling post id in current language */
if ( ! function_exists( 'lordcros_core_get_current_language_post_id' ) ) {
	function lordcros_core_get_current_language_post_id( $id, $post_type = 'post' ) {
		if ( class_exists( 'SitePress' ) ) {
			return icl_object_id( $id, $post_type, true );
		} else {
			return $id;
		}
	}
}

/* get permalink of current language */
if ( ! function_exists( 'lordcros_core_get_permalink_clang' ) ) {
	function lordcros_core_get_permalink_clang( $post_id )
	{
		$url = "";
		if ( class_exists( 'SitePress' ) ) {
			$language = ICL_LANGUAGE_CODE;

			$lang_post_id = icl_object_id( $post_id , 'page', true, $language );

			if( 0 != $lang_post_id ) {
				$url = get_permalink( $lang_post_id );
			} else {
				// No page found, it's most likely the homepage
				global $sitepress;
				$url = $sitepress->language_url( $language );
			}
		} else {
			$url = get_permalink( $post_id );
		}

		return esc_url( $url );
	}
}