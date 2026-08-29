<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BMCHESS_DB_VERSION', '1' );
define( 'BMCHESS_PID_COOKIE', 'bmchess_pid' );

function bmchess_rooms_table() {
	global $wpdb;
	return $wpdb->prefix . 'bmchess_rooms';
}

function bmchess_install_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = bmchess_rooms_table();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		token varchar(16) NOT NULL,
		host_pid varchar(32) NOT NULL,
		guest_pid varchar(32) DEFAULT NULL,
		host_color char(1) NOT NULL DEFAULT 'w',
		show_cards tinyint(1) NOT NULL DEFAULT 1,
		clock_sec smallint(5) unsigned NOT NULL DEFAULT 0,
		hint_layout varchar(8) NOT NULL DEFAULT '6x1',
		moves longtext NOT NULL,
		fen varchar(128) NOT NULL,
		status varchar(16) NOT NULL DEFAULT 'waiting',
		winner varchar(8) DEFAULT NULL,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY token (token),
		KEY status (status)
	) {$charset};";
	dbDelta( $sql );
	update_option( 'bmchess_db_version', BMCHESS_DB_VERSION );
}

function bmchess_maybe_install() {
	if ( get_option( 'bmchess_db_version' ) === BMCHESS_DB_VERSION ) {
		return;
	}
	bmchess_install_tables();
}

function bmchess_player_id() {
	$raw = isset( $_COOKIE[ BMCHESS_PID_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ BMCHESS_PID_COOKIE ] ) ) : '';
	if ( $raw && preg_match( '/^[a-f0-9]{32}$/', $raw ) ) {
		return $raw;
	}
	$id = bin2hex( random_bytes( 16 ) );
	if ( ! headers_sent() ) {
		setcookie( BMCHESS_PID_COOKIE, $id, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	}
	$_COOKIE[ BMCHESS_PID_COOKIE ] = $id;
	return $id;
}

function bmchess_start_fen() {
	return 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
}

function bmchess_fen_turn( $fen ) {
	$parts = explode( ' ', (string) $fen );
	return ( isset( $parts[1] ) && 'b' === $parts[1] ) ? 'b' : 'w';
}

function bmchess_room_public( $row, $pid ) {
	$host = ( $row->host_pid === $pid );
	$guest = ( $row->guest_pid && $row->guest_pid === $pid );
	$color = null;
	if ( $host ) {
		$color = $row->host_color;
	} elseif ( $guest ) {
		$color = 'w' === $row->host_color ? 'b' : 'w';
	}
	$moves = json_decode( $row->moves, true );
	if ( ! is_array( $moves ) ) {
		$moves = array();
	}
	return array(
		'token'      => $row->token,
		'status'     => $row->status,
		'fen'        => $row->fen,
		'moves'      => $moves,
		'turn'       => bmchess_fen_turn( $row->fen ),
		'color'      => $color,
		'role'       => $host ? 'host' : ( $guest ? 'guest' : 'none' ),
		'showCards'  => (bool) $row->show_cards,
		'clockSec'   => (int) $row->clock_sec,
		'hintLayout' => $row->hint_layout,
		'winner'     => $row->winner,
	);
}

function bmchess_get_room( $token ) {
	global $wpdb;
	$token = strtolower( preg_replace( '/[^a-z0-9]/', '', (string) $token ) );
	if ( strlen( $token ) < 8 ) {
		return null;
	}
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . bmchess_rooms_table() . ' WHERE token = %s', $token ) );
}

function bmchess_rest_create_room( WP_REST_Request $request ) {
	global $wpdb;
	$pid   = bmchess_player_id();
	$color = $request->get_param( 'color' );
	if ( ! in_array( $color, array( 'w', 'b' ), true ) ) {
		$color = ( wp_rand( 0, 1 ) === 1 ) ? 'b' : 'w';
	}
	$layout = $request->get_param( 'hintLayout' );
	if ( ! in_array( $layout, array( '4x1', '6x1', '4x2', '6x2' ), true ) ) {
		$layout = '6x1';
	}
	$clock = absint( $request->get_param( 'clockSec' ) );
	if ( ! in_array( $clock, array( 0, 10, 30, 45, 60 ), true ) ) {
		$clock = 0;
	}
	$now   = current_time( 'mysql' );
	$token = bin2hex( random_bytes( 5 ) );
	$ok    = $wpdb->insert(
		bmchess_rooms_table(),
		array(
			'token'       => $token,
			'host_pid'    => $pid,
			'guest_pid'   => null,
			'host_color'  => $color,
			'show_cards'  => $request->get_param( 'showCards' ) ? 1 : 0,
			'clock_sec'   => $clock,
			'hint_layout' => $layout,
			'moves'       => wp_json_encode( array() ),
			'fen'         => bmchess_start_fen(),
			'status'      => 'waiting',
			'winner'      => null,
			'created_at'  => $now,
			'updated_at'  => $now,
		),
		array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
	);
	if ( ! $ok ) {
		return new WP_Error( 'bmchess_create', 'Impossibile creare la partita.', array( 'status' => 500 ) );
	}
	$row = bmchess_get_room( $token );
	return rest_ensure_response( bmchess_room_public( $row, $pid ) );
}

function bmchess_rest_get_room( WP_REST_Request $request ) {
	$row = bmchess_get_room( $request['token'] );
	if ( ! $row ) {
		return new WP_Error( 'bmchess_missing', 'Partita non trovata.', array( 'status' => 404 ) );
	}
	return rest_ensure_response( bmchess_room_public( $row, bmchess_player_id() ) );
}

function bmchess_rest_join_room( WP_REST_Request $request ) {
	global $wpdb;
	$row = bmchess_get_room( $request['token'] );
	if ( ! $row ) {
		return new WP_Error( 'bmchess_missing', 'Partita non trovata.', array( 'status' => 404 ) );
	}
	$pid = bmchess_player_id();
	if ( $row->host_pid === $pid || ( $row->guest_pid && $row->guest_pid === $pid ) ) {
		return rest_ensure_response( bmchess_room_public( $row, $pid ) );
	}
	if ( $row->guest_pid ) {
		return new WP_Error( 'bmchess_full', 'Tavolo pieno.', array( 'status' => 409 ) );
	}
	$wpdb->update(
		bmchess_rooms_table(),
		array(
			'guest_pid'  => $pid,
			'status'     => 'playing',
			'updated_at' => current_time( 'mysql' ),
		),
		array( 'id' => $row->id ),
		array( '%s', '%s', '%s' ),
		array( '%d' )
	);
	$row = bmchess_get_room( $row->token );
	return rest_ensure_response( bmchess_room_public( $row, $pid ) );
}

function bmchess_rest_move( WP_REST_Request $request ) {
	global $wpdb;
	$row = bmchess_get_room( $request['token'] );
	if ( ! $row ) {
		return new WP_Error( 'bmchess_missing', 'Partita non trovata.', array( 'status' => 404 ) );
	}
	$pid = bmchess_player_id();
	$pub = bmchess_room_public( $row, $pid );
	if ( 'none' === $pub['role'] ) {
		return new WP_Error( 'bmchess_seat', 'Non sei in questa partita.', array( 'status' => 403 ) );
	}
	if ( 'playing' !== $row->status ) {
		return new WP_Error( 'bmchess_wait', 'La partita non è ancora iniziata.', array( 'status' => 409 ) );
	}
	if ( $pub['color'] !== $pub['turn'] ) {
		return new WP_Error( 'bmchess_turn', 'Non è il tuo turno.', array( 'status' => 409 ) );
	}
	$from  = strtolower( (string) $request->get_param( 'from' ) );
	$to    = strtolower( (string) $request->get_param( 'to' ) );
	$promo = strtolower( (string) $request->get_param( 'promo' ) );
	$san   = sanitize_text_field( (string) $request->get_param( 'san' ) );
	$fen   = sanitize_text_field( (string) $request->get_param( 'fen' ) );
	if ( ! preg_match( '/^[a-h][1-8]$/', $from ) || ! preg_match( '/^[a-h][1-8]$/', $to ) ) {
		return new WP_Error( 'bmchess_uci', 'Mossa non valida.', array( 'status' => 400 ) );
	}
	if ( $promo && ! in_array( $promo, array( 'q', 'r', 'b', 'n' ), true ) ) {
		$promo = '';
	}
	if ( ! $fen ) {
		return new WP_Error( 'bmchess_fen', 'Posizione mancante.', array( 'status' => 400 ) );
	}
	$moves   = $pub['moves'];
	$moves[] = array(
		'from'  => $from,
		'to'    => $to,
		'promo' => $promo,
		'san'   => $san,
		'uci'   => $from . $to . $promo,
		'by'    => $pub['color'],
	);
	$over   = $request->get_param( 'over' ) ? 1 : 0;
	$winner = sanitize_text_field( (string) $request->get_param( 'winner' ) );
	$status = $over ? 'finished' : 'playing';
	if ( $winner && ! in_array( $winner, array( 'w', 'b', 'draw' ), true ) ) {
		$winner = null;
	}
	$wpdb->update(
		bmchess_rooms_table(),
		array(
			'moves'      => wp_json_encode( $moves ),
			'fen'        => $fen,
			'status'     => $status,
			'winner'     => $over ? $winner : null,
			'updated_at' => current_time( 'mysql' ),
		),
		array( 'id' => $row->id ),
		array( '%s', '%s', '%s', '%s', '%s' ),
		array( '%d' )
	);
	$row = bmchess_get_room( $row->token );
	return rest_ensure_response( bmchess_room_public( $row, $pid ) );
}

function bmchess_rest_resign( WP_REST_Request $request ) {
	global $wpdb;
	$row = bmchess_get_room( $request['token'] );
	if ( ! $row ) {
		return new WP_Error( 'bmchess_missing', 'Partita non trovata.', array( 'status' => 404 ) );
	}
	$pid = bmchess_player_id();
	$pub = bmchess_room_public( $row, $pid );
	if ( 'none' === $pub['role'] || ! $pub['color'] ) {
		return new WP_Error( 'bmchess_seat', 'Non sei in questa partita.', array( 'status' => 403 ) );
	}
	$winner = 'w' === $pub['color'] ? 'b' : 'w';
	$wpdb->update(
		bmchess_rooms_table(),
		array(
			'status'     => 'finished',
			'winner'     => $winner,
			'updated_at' => current_time( 'mysql' ),
		),
		array( 'id' => $row->id ),
		array( '%s', '%s', '%s' ),
		array( '%d' )
	);
	$row = bmchess_get_room( $row->token );
	return rest_ensure_response( bmchess_room_public( $row, $pid ) );
}

function bmchess_register_rest() {
	register_rest_route(
		'bmchess/v1',
		'/rooms',
		array(
			'methods'             => 'POST',
			'callback'            => 'bmchess_rest_create_room',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'bmchess/v1',
		'/rooms/(?P<token>[a-zA-Z0-9]+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'bmchess_rest_get_room',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'bmchess/v1',
		'/rooms/(?P<token>[a-zA-Z0-9]+)/join',
		array(
			'methods'             => 'POST',
			'callback'            => 'bmchess_rest_join_room',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'bmchess/v1',
		'/rooms/(?P<token>[a-zA-Z0-9]+)/move',
		array(
			'methods'             => 'POST',
			'callback'            => 'bmchess_rest_move',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'bmchess/v1',
		'/rooms/(?P<token>[a-zA-Z0-9]+)/resign',
		array(
			'methods'             => 'POST',
			'callback'            => 'bmchess_rest_resign',
			'permission_callback' => '__return_true',
		)
	);
}

add_action( 'init', 'bmchess_maybe_install' );
add_action( 'rest_api_init', 'bmchess_register_rest' );
register_activation_hook( BMCHESS_FILE, 'bmchess_install_tables' );
