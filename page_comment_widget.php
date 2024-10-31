<?php

/*
Plugin Name: Page Comments Widget
Plugin URI: http://vukstankovic.com/2012/04/11/page-comments/
Description: This plugin (widget) shows only comments posted on pages or wiki pages. It can also be used to display comments posted on posts (like regular recent comment widget).
Version: 0.31
Author: Vuk Stankovic
Author URI: http://vukstankovic.com
License: GPL2
*/

/* Add our function to the widgets_init hook. */
add_action( 'widgets_init', 'page_comment_widgets' );

/* Function that registers our widget. */
function page_comment_widgets() {
	register_widget( 'Page_Comments' );
}

class Page_Comments extends WP_Widget {

	function Page_Comments() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'page-comments', 'description' => 'Widget showing comments posted on pages only' );

		/* Widget control settings. */
		$control_ops = array();

		/* Create the widget. */
		$this->WP_Widget( 'page-comments', 'Page_Comments', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$nocomments = (int)$instance['brkomentara'];
		$type = mysql_escape_string($instance['type']);
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* Display name from widget settings. */
			global $wpdb;
						$number = $nocomments; // number of comments to get
						$filter_admin = " AND user_ID != '1'"; //exclude admin comments or any user by id
						$comments_types = "AND ( comment_type != 'trackback' AND comment_type != 'pingback')"; // get only user comments
						$term_rel = $wpdb->term_relationships;
						$comments_filter = "JOIN (".$term_rel." JOIN ".$wpdb->term_taxonomy." ON (".$wpdb->term_taxonomy.".term_taxonomy_id=".$term_rel.".term_taxonomy_id))";
						$comments_filter .= "ON (".$wpdb->comments.".comment_post_ID=".$term_rel.".object_ID)";
						$comments_filter .= "WHERE ".$wpdb->term_taxonomy.".term_id=".$cat_id." AND ".$wpdb->term_taxonomy.".taxonomy='category' ";
						$comments_filter .= $comments_types;
						$query = "SELECT * FROM $wpdb->comments "; 
			$query .= "JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) WHERE post_type = '".$type."'";
					   $query .= " AND comment_approved='1'";
			$query .= "ORDER BY comment_date DESC LIMIT $number"; 
						$comments = $wpdb->get_results($query);
						echo '<ul>';
			foreach($comments as $comment) :
				echo '<li>'.$comment->comment_author .'<br /><a href='.get_comment_link( $comment).'>'. wp_html_excerpt( $comment->comment_content, 45 ).'...</a></br> на страницу '. $title = get_the_title($comment->comment_post_ID);
			endforeach;
						echo '</ul>';
					
		/* After widget (defined by themes). */
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['brkomentara'] = strip_tags( $new_instance['brkomentara'] );
		$instance['type'] = strip_tags($new_instance['type']);
		return $instance;
	}
	
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Page Comment', 'brkomentara' => 10, 'type' => 'page' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label> <br/>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:70%;" />
		</p>
        <p>
			<label for="<?php echo $this->get_field_id( 'brkomentara' ); ?>">Number of comments to show:</label> <br/>
			<input id="<?php echo $this->get_field_id( 'brkomentara' ); ?>" name="<?php echo $this->get_field_name( 'brkomentara' ); ?>" value="<?php echo $instance['brkomentara']; ?>" style="width:70%;" />
		</p>

       		<p>
			<label for="<?php echo $this->get_field_id( 'type' ); ?>">Type of page to show comments from:</label> <br/>
			<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" style="width:70%;">
				<option value="page" <?php if($instance['type'] == "page") echo "selected='selected'" ?>>Pages</option>
				<option value="post" <?php if($instance['type'] == "post") echo "selected='selected'" ?>>Post</option>
				<option value="wiki" <?php if($instance['type'] == "wiki") echo "selected='selected'" ?>>Wiki</option>
			</select>
		</p>
<?php
	}
}
?>
