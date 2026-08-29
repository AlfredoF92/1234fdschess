<?php
/**
 * Plugin Name: BMChess
 * Description: Gioco BM Chess in WordPress. Shortcode: [logo] [header-menu] [bm-chess-home]
 * Version: 1.0.3
 * Author: BM Chess
 * Text Domain: bmchess
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BMCHESS_VERSION', '1.0.3' );
define( 'BMCHESS_FILE', __FILE__ );
define( 'BMCHESS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BMCHESS_URL', plugin_dir_url( __FILE__ ) );

function bmchess_game_url() {
	return BMCHESS_URL . 'game/';
}

function bmchess_post_has_shortcode( $post = null ) {
	$post = $post ? $post : get_post();
	if ( ! $post || empty( $post->post_content ) ) {
		return false;
	}
	return has_shortcode( $post->post_content, 'bm-chess-home' )
		|| has_shortcode( $post->post_content, 'header-menu' );
}

function bmchess_post_needs_game_assets( $post = null ) {
	$post = $post ? $post : get_post();
	if ( ! $post || empty( $post->post_content ) ) {
		return false;
	}
	return has_shortcode( $post->post_content, 'bm-chess-home' )
		|| has_shortcode( $post->post_content, 'header-menu' );
}

function bmchess_should_enqueue() {
	if ( is_singular() && bmchess_post_has_shortcode() ) {
		return true;
	}
	return ! empty( $GLOBALS['bmchess_shortcode_used'] );
}

function bmchess_enqueue_styles() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$game_url = bmchess_game_url();

	wp_enqueue_style(
		'bmchess-game',
		$game_url . 'css/style.css',
		array(),
		BMCHESS_VERSION
	);
	wp_enqueue_style(
		'bmchess-wp',
		BMCHESS_URL . 'assets/bmchess-wp.css',
		array( 'bmchess-game' ),
		BMCHESS_VERSION
	);
}

function bmchess_enqueue_assets() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	bmchess_enqueue_styles();

	$game_url = bmchess_game_url();

	if ( function_exists( 'wp_enqueue_script_module' ) ) {
		wp_enqueue_script_module(
			'bmchess-app',
			$game_url . 'js/app.js',
			array(),
			BMCHESS_VERSION
		);
	} else {
		add_action(
			'wp_footer',
			function () use ( $game_url ) {
				echo '<script type="module" src="' . esc_url( $game_url . 'js/app.js?ver=' . BMCHESS_VERSION ) . '"></script>' . "\n";
			},
			20
		);
	}
}

function bmchess_boot_script() {
	$game_url = bmchess_game_url();
	return 'window.BMCHESS_BASE=' . wp_json_encode( $game_url ) . ';'
		. '(function(){var KEY="5minchess.uiLang";var lang="en";try{var saved=localStorage.getItem(KEY);if(saved==="it"||saved==="en")lang=saved;}catch(e){}'
		. 'document.documentElement.lang=lang;window.CHESS_LANG=lang;'
		. 'window.setChessLang=function(next){if(next!=="it"&&next!=="en")return false;try{localStorage.setItem(KEY,next);}catch(e){}location.reload();return false;};})();';
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_singular() && bmchess_post_needs_game_assets() ) {
			bmchess_enqueue_assets();
		}
	}
);

add_filter(
	'body_class',
	function ( $classes ) {
		if ( bmchess_should_enqueue() ) {
			$classes[] = 'bmchess-has-game';
		}
		return $classes;
	}
);

function bmchess_render_home( $atts = array() ) {
	$GLOBALS['bmchess_shortcode_used'] = true;
	bmchess_enqueue_assets();

	$game_url = bmchess_game_url();
	ob_start();
	echo '<script>' . bmchess_boot_script() . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	include BMCHESS_DIR . 'templates/game.php';
	return ob_get_clean();
}

add_shortcode( 'bm-chess-home', 'bmchess_render_home' );

function bmchess_render_header_menu( $atts = array() ) {
	$GLOBALS['bmchess_shortcode_used'] = true;
	bmchess_enqueue_assets();

	$game_url = bmchess_game_url();
	ob_start();
	echo '<script>' . bmchess_boot_script() . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	include BMCHESS_DIR . 'templates/header-menu.php';
	return ob_get_clean();
}

add_shortcode( 'header-menu', 'bmchess_render_header_menu' );

function bmchess_render_logo( $atts = array() ) {
	wp_enqueue_style(
		'bmchess-wp',
		BMCHESS_URL . 'assets/bmchess-wp.css',
		array(),
		BMCHESS_VERSION
	);

	$src = esc_url( bmchess_game_url() . 'img/logo.svg' );
	$home = esc_url( home_url( '/' ) );
	return '<a class="bmchess-logo" href="' . $home . '"><img src="' . $src . '" alt="BM Chess" width="48" height="48"></a>';
}

add_shortcode( 'logo', 'bmchess_render_logo' );
