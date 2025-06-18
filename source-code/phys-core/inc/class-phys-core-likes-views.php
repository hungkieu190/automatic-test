<?php

class Physc_Core_PostLikes {

	function __construct() {
		add_action( 'publish_post', array( &$this, 'setup_likes' ) );
		add_action( 'wp_ajax_post-likes', array( &$this, 'ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_post-likes', array( &$this, 'ajax_callback' ) );
	}

	function setup_likes( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		add_post_meta( $post_id, '_post_likes', '0', true );
	}

	function ajax_callback() {
		if ( isset( $_POST['likes_id'] ) ) {
			// Click event. Get and Update Count
			$post_id = str_replace( 'post-likes-', '', $_POST['likes_id'] );
			echo ent2ncr( $this->like_this( $post_id, 'update' ) );
		} else {
			// AJAXing data in. Get Count
			$post_id = str_replace( 'post-likes-', '', $_POST['post_id'] );
			echo ent2ncr( $this->like_this( $post_id, 'get' ) );
		}

		exit;
	}

	function like_this( $post_id, $action = 'get' ) {
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		switch ( $action ) {

			case 'get':
				$likes = get_post_meta( $post_id, '_post_likes', true );
				if ( ! $likes ) {
					$likes = 0;
					add_post_meta( $post_id, '_post_likes', $likes, true );
				}

				return '<i class="fa fa-heart-o"></i><span class="post-likes-number">' . $likes . '</span>';
				break;

			case 'update':
				$likes = get_post_meta( $post_id, '_post_likes', true );
				if ( isset( $_COOKIE['post_likes_' . $post_id] ) ) {
					return $likes;
				}

				$likes ++;
				update_post_meta( $post_id, '_post_likes', $likes );
				setcookie( 'post_likes_' . $post_id, $post_id, time() * 20, '/' );

				return '<i class="fa fa-heart-o"></i><span class="post-likes-number">' . $likes . '</span>';
				break;

		}
	}

	function do_likes() {
		global $post;

		$output = $this->like_this( $post->ID );

		$class = 'post-likes';
		$title = esc_html__( 'Like this', 'physc-vc-addon' );
		if ( isset( $_COOKIE['post_likes_' . $post->ID] ) ) {
			$class = 'post-likes active';
			$title = esc_html__( 'You already like this', 'physc-vc-addon' );
		}

		return '<a href="javascript:void(0)" class="' . $class . '" id="post-likes-' . $post->ID . '" title="' . $title . '">' . $output . '</a>';
	}

}

global $post_likes;
$post_likes = new Physc_Core_PostLikes();

/**
 * Template Tag
 */
if ( ! function_exists( 'physc_post_likes' ) ) {
	function physc_post_likes() {
		global $post_likes;
		echo ent2ncr( $post_likes->do_likes() );
	}
}
if ( ! function_exists( 'physc_set_post_views' ) ) {
	function physc_set_post_views( $postID ) {
		$count_key = 'phys_post_views_count';
		$count     = get_post_meta( $postID, $count_key, true );
		if ( $count == '' ) {
			$count = 0;
			delete_post_meta( $postID, $count_key );
			add_post_meta( $postID, $count_key, '0' );
		} else {
			$count ++;
			update_post_meta( $postID, $count_key, $count );
		}
	}
}

if ( ! function_exists( 'physc_track_post_views' ) ) {
	function physc_track_post_views( $post_id ) {
		if ( ! is_single() ) {
			return;
		}
		if ( empty ( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}
		physc_set_post_views( $post_id );
	}

	add_action( 'wp_head', 'physc_track_post_views' );
}

if ( ! function_exists( 'physc_get_post_views' ) ) {
	function physc_get_post_views( $postID ) {
		$count_key = 'phys_post_views_count';
		$count     = get_post_meta( $postID, $count_key, true );
		if ( $count == '' ) {
			delete_post_meta( $postID, $count_key );
			add_post_meta( $postID, $count_key, '0' );

			return '<span class="count-view"><i class="fa fa-eye"></i>0</span>';
		}

		return '<span class="count-view"><i class="fa fa-eye"></i>' . $count . '</span>';
	}
}
