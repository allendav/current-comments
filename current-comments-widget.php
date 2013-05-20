<?php

/*
 * Current Comments - current-comments-widget.php
 */

/*  Copyright 2013 allendav

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

class Current_Comments_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
	 		'current-comments-widget', // Base ID
			'Current Comments Widget', // Name
			array( 'description' => __( 'Live comments widget for WordPress, powered by Backbone.js', 'current-comments' ) )
		);
	}

	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		$title = $instance['title'];
		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		$time = time();
		echo "<ul class='current-comments-container'></ul>";

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form($instance) {
		$defaults = array( 'title' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title_ID = $this->get_field_id( 'title' );
		$title_name = $this->get_field_name( 'title' );
		$title_value = esc_attr( $instance['title'] );

		echo "<p>";
		echo "<label for='$title_ID'>Title: </label>";
		echo "<input type='text' id='$title_ID' name='$title_name' value='$title_value' />";
		echo "</p>";
	}
}
