<?php
/*
Plugin Name: Current Comments
Plugin URI: http://wordpress.com
Description: Live comments widget for WordPress, powered by Backbone.js
Version: 0.1
Author: allendav
Author URI: http://allendav.wordpress.com/
License: GPL2
*/

/*  Copyright 2013

  This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

require "current-comments-widget.php";

class Current_Comments {

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		// Load plugin text domain
		// add_action( 'init', array( $this, 'plugin_textdomain' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		// Register our widget
		add_action( 'widgets_init', array( $this, 'register_plugin_widget' ) );

		// Add our read-only comments endpoint
		add_action( 'wp_ajax_currcomm_read', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_nopriv_currcomm_read', array( $this, 'handle_ajax_request' ) );
	} // end constructor

	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	public function register_plugin_styles() {
		wp_enqueue_style( 'current-comments-styles', plugins_url( 'current-comments/css/styles.css' ) );
	} // end register_plugin_styles

	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	public function register_plugin_scripts() {
		wp_enqueue_script( 'current-comments-script', plugins_url( 'current-comments/js/script.js' ), array( 'jquery', 'jquery-color', 'underscore', 'backbone' ) );
		wp_localize_script( 'current-comments-script', 'Current_Comments_Ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
	} // end register_plugin_scripts

	/**
	 * Register our widget.
	 */
	public function register_plugin_widget() {
		register_widget( 'Current_Comments_Widget' );
	} // end register_plugin_widget

	/**
	 * AJAX handler
	 */
	public function handle_ajax_request() {
		$comments = get_comments( array( 
			'status' => 'approve',
			'order'  => 'ASC',
			'number' => 10
			)
		);

		$response = array();

		foreach ( (array) $comments as $comment ) {
			$response[] = array(
				'id'               => $comment->comment_ID,
				'author'           => $comment->comment_author,
				'author_url'       => $comment->comment_author_url,
				'post_title'       => get_the_title( $comment->comment_post_ID ),
				'permalink'        => get_comment_link( $comment ),
				'comment_date_gmt' => strtotime( $comment->comment_date_gmt )
			);
		}

		header( 'Content-Type: application/json', true, 200 );
		die( json_encode( $response ) );
	} // end handle_ajax_request
}

/*
 * Instantiate!
 */
$current_comments = new Current_Comments();
