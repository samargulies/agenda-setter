<?php
/*
Plugin Name: Agenda Setter
Plugin URI: http://github.com/samargulies/agenda-setter
Description: A simple wordpress event calendar
Version: 0.1
Author: Sam Margulies
Author URI: http://www.belabor.org
License: Copyright 2010 Sam Margulies
*/

/*  Copyright 2010 Sam Margulies (email : friend@temple.edu)

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

//set DEBUG to true to print debugging information
define("DEBUG", TRUE);

// load SCB Framework
require dirname( __FILE__ ) . '/scb/load.php';

// initiate SCB Framework and Classes
function _as_init() {
	AS_Query::init();
}
scb_init( '_as_init' );

// TODO: make these part of options page
$event_post_types = array('post', 'page');

// Create meta-boxes for event date and time
// adapted from Date Field by Matthew Haines-Young (http://matth.eu/wordpress-date-field-plugin)

// an array containing arrays for each field.

$as_new_meta_boxes = array( 
	"date" => array(
		"name" => "date",
		"std" => "",
		"title" => "Event Start Time",
		"description" => "Select a start date and time.",
		"type" => "date"
	),
	"end_date" => array(
		"name" => "end_date",
		"std" => "",
		"title" => "Event End Time",
		"description" => "<em>Optional</em>. Select an end date and time.",
		"type" => "date"
	)

);

function as_new_meta_boxes() {
global $post, $as_new_meta_boxes;

	foreach($as_new_meta_boxes as $as_meta_box) {
		
		if ($as_meta_box['type'] == 'date') {
			$as_meta_box_value = get_post_meta($post->ID, '_' . $as_meta_box['name'].'_value', true);
			
			if($as_meta_box_value == "") {
				$as_meta_box_value = $as_meta_box['std'];  
			}
			
			echo '<input type="hidden" name="'.$as_meta_box['name'].'_noncename" id="' . $as_meta_box['name'].'_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';  
 			
 			echo "<style> .jj, .hh, .mn { width:2em; } .aa { width:3.4em; } .remove {width:100%;padding-top:1em;}</style>";
 			
			echo "<h4>{$as_meta_box['title']}</h4>";  
			
						
			// Month
			
			if ($as_meta_box_value) { 
				$month = date('n', $as_meta_box_value);
			} else {
				$month = 0;
			}
			
			$monthname = array(1 => "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
			
			echo "<select name='{$as_meta_box['name']}_value_month' class='mm' >";
			
			for($currentmonth = 1; $currentmonth <= 12; $currentmonth++)
			{
				echo '<option value="';
				echo intval($currentmonth);
				echo '"';
				if ($currentmonth == $month) {
					echo ' selected="selected" ';
				}
				echo '>' . $monthname[$currentmonth] . '</option>';
			}
			
			echo '</select>';
			
			
			// Day
			
			if ($as_meta_box_value) { 
				$day = date("j", $as_meta_box_value); }
			else {
				$day = '';
			}
			
			echo "<input name='{$as_meta_box['name']}_value_day' class='jj' type='text' value='$day'>, ";
			
			
			// Year	
			
			if ($as_meta_box_value) {
				$year = date('Y', $as_meta_box_value);
			}
			else {
				$year = '';
			}
			
			echo "<input name='{$as_meta_box['name']}_value_year' class='aa' type='text' value='$year'> @ ";
					
					
			// Hour
			
			if ($as_meta_box_value) {
				$hour = date('H', $as_meta_box_value);
			}
			else {
				$hour = '';
			}

			echo "<input name='{$as_meta_box['name']}_value_hour' class='hh' type='text' value='$hour'> : ";
			
			
			// Minute
			
			if ($as_meta_box_value) {
				$min = date('i', $as_meta_box_value);
			} else {
				$min = '';
			}
			
			echo "<input name='{$as_meta_box['name']}_value_minute' class='mn' type='text' value='$min'>";


			// Remove Checkbox
			
			if ($as_meta_box_value) {
				echo "<div class='remove'><input name='{$as_meta_box['name']}_remove' type='checkbox' value='true'> Remove {$as_meta_box['title']}</div>";
			}
			
			// Labels
			
			echo '<p>' . $as_meta_box['description'];
			
			if ( $as_meta_box_value ) {
				echo ' The currently selected date is <strong>' . date("l F j, Y", $as_meta_box_value) . " at " . date("h:i A", $as_meta_box_value) . '</strong>.</p>';
			} else {
				echo ' No date is currently selected.</p>';
			}

		} else {
		
			$as_meta_box_value = get_post_meta($post->ID, '_' . $as_meta_box['name'].'_value', true);
   
			if($as_meta_box_value == "") {
				$as_meta_box_value = $as_meta_box['std'];  
			}
			
			echo'<input type="hidden" name="'.$as_meta_box['name'].'_noncename" id="'.$as_meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';  
 
			echo'<h4>'.$as_meta_box['title'].'</h4>';  

			echo'<input type="text" name="'.$as_meta_box['name'].'_value" value="'.$as_meta_box_value.'" style="width:99.5%;"/><br />';  

			echo'<p><label for="'.$as_meta_box['name'].'_value">'.$as_meta_box['description'].'</label></p>';  
			
		}
	}
}

// TODO: allow add_meta_box post_type array (see tutv) and use $event_post_types variable

function as_create_meta_box() {
	if ( function_exists('add_meta_box') ) {
        add_meta_box( 'as-new-meta-boxes', 'Add Event Information', 'as_new_meta_boxes', 'post', 'normal', 'high' );
	}
}
add_action('admin_menu', 'as_create_meta_box');  
    
function as_save_postdata( $post_id ) {
	global $post, $as_new_meta_boxes;

	foreach($as_new_meta_boxes as $as_meta_box) {
		
		$data = $_POST[$as_meta_box['name'].'_value'];
		
		if ($as_meta_box['type'] == 'date') {
			if ( !wp_verify_nonce( $_POST[$as_meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
				return $post_id;
			}
			                  
			if ( !current_user_can( "edit_{$_POST['post_type']}", $post_id ) ) {
				return $post_id;
			}	

			$day = (int) $_POST[$as_meta_box['name'].'_value_day'];
			$month = $_POST[$as_meta_box['name'].'_value_month'];
			$year = (int) $_POST[$as_meta_box['name'].'_value_year'];
			$hour = (int) $_POST[$as_meta_box['name'].'_value_hour'];
			$min = (int) $_POST[$as_meta_box['name'].'_value_minute'];
			$should_be_removed = ($_POST[$as_meta_box['name'].'_remove']) ? true : false;
			
			if ($day != '' || $year != '' || $hour != '' || $min != ''){
				$date = strtotime ($month.'/'.$day.'/'.$year.' '.$hour.':'.$min);
				$date_day = date('Ymd', $date);
			} else { 
				$should_be_removed = true;
			}
			
						
			if ($should_be_removed) {
				delete_post_meta($post_id, '_' . $as_meta_box['name'] . '_value');
				delete_post_meta($post_id, '_' . $as_meta_box['name'] . '_day_value');	
				$as_event_tax_tags = '';
			} else {
				update_post_meta($post_id, '_' . $as_meta_box['name'] . '_value', $date);
				update_post_meta($post_id, '_' . $as_meta_box['name'] . '_day_value', $date_day);
				$as_event_tax_tags = 'as_event_post';
			}
			
								
		} else {
			if ( !wp_verify_nonce( $_POST[$as_meta_box['name'] . '_noncename'], plugin_basename(__FILE__) )) {
				return $post_id;
			}
			                  
			if ( !current_user_can( "edit_{$_POST['post_type']}", $post_id ) ) {
				return $post_id;
			}	

			$data = $_POST[$as_meta_box['name'] . '_value'];

			update_post_meta($post_id, '_' . $as_meta_box['name'] . '_value', $data);
		}
		
		// if event has a start date, add to events taxonomy 
		if( $as_meta_box['name'] == 'date' ) {
			wp_set_post_terms($post_id, $as_event_tax_tags, 'as_event', false);
		}
		
		//programmatically generate end date for all events
		if( $as_meta_box['name'] == 'end_date' ) {
			$as_meta_box_end_date = get_post_meta($post_id, '_end_date_value', true);
			if ($as_meta_box_end_date) {
				update_post_meta($post_id, '_end_date_generated_value', $as_meta_box_end_date);
			} else {
				$as_meta_box_start_date = get_post_meta($post_id, '_date_value', true);
				
				$next_day_from_start_date =  $as_meta_box_start_date + (60 * 60 * 24);
				update_post_meta($post_id, '_end_date_generated_value', $next_day_from_start_date);

			}
		}
//1288728600
	} /* end foreach */
} /* end as_save_postdata() */
add_action('save_post', 'as_save_postdata');

// add events taxonomy
function as_taxonomies() {
	global $event_post_types;

	register_taxonomy( 'as_event', $event_post_types, array( 
		'hierarchical' => false, 
		'labels' => array(
			'name' => 'Agenda Events', 
			'singular_name' => 'Event'
		), 
		'query_var' => true, 
		'rewrite' => false,
		'public' => true, 
		'show_ui' => false,
		 
	));
	
}
add_action( 'init', 'as_taxonomies', 0 );

// add event date to events list
function as_content_filter($content){
	// see if the post given has any 'as_event' tags
	$as_event_terms = wp_get_object_terms(get_the_ID(), 'as_event', 'ids');
	if( ( !empty($as_event_terms) || is_tax('as_event') ) && !is_feed() ) {
		$date = get_post_meta(get_the_ID(), '_date_value', true);
		$end_date = get_post_meta(get_the_ID(), '_end_date_value', true);
		
		if ($date) {
			$date_string .= "<div class='as_event_date'>";
			$date_string .= "<span class='as_month'>" . date('M', $date) . "</span>";
			$date_string .= " <span class='as_day'>" . date('j', $date) . "</span></div>";
		}
		if ($end_date && ( date('Yz', $date) != date('Yz', $end_date) ) ) {
			$date_string .= "<span class='as_day_sep'> to </span>";
			$date_string .= "<div class='as_event_date'>";
			$date_string .= "<span class='as_month'>" . date('M', $end_date) . "</span>";
			$date_string .= " <span class='as_day'>" . date('j', $end_date) . "</span></div>";
		}
	}
	return $date_string . $content;
}
add_filter( "the_content", "as_content_filter" );

// add syles for event date conditionally
function as_conditionally_add_styles($posts){
	// see if the first post given has any 'as_event' tags
 	$as_event_terms = wp_get_object_terms($posts[0]->ID, 'as_event', 'ids');
	if ( ( !empty($as_event_terms) || is_tax('as_event') ) && !is_feed() ) {
		wp_register_style('as-styles', WP_PLUGIN_URL . '/agenda-setter/styles.css');
		wp_enqueue_style('as-styles');
	}
 
	return $posts;
}
add_filter('the_posts', 'as_conditionally_add_styles');

// return customizable list of upcoming events
// TODO: allow past events

function as_get_events($display='', $args='') {
	global $event_post_types, $post;

	$defaults = array(
		'post_type' => $event_post_types,
		'as_event' => 'as_event_post',
		'posts_per_page' => 5,
		'as_event_date' => 'upcoming',
		'suppress_filters' => 'false'
	);
	$args = wp_parse_args( $args, $defaults );	

	$events_query = new WP_Query($args);
 	
	if ( $events_query->have_posts() ) : 
		$content = '<ul>';
		while ( $events_query->have_posts() ): $events_query->the_post() ;
			switch ($display) :

			case 'full' :
				$content .= "<li><h2><a href='" . get_permalink() . "'>" . get_the_title() . "</a></h2>" . get_the_content() . "</li>";
				break;
			case 'list' :
			default :
				$content .= "<li><a href='" . get_permalink() . "'>" . get_the_title() . "</a></li>";
				break;
	
			endswitch;
		endwhile;
		$content .= '</ul>'; 
		
	else:
		if( DEBUG )
			echo 'no upcoming events for this as_get_events() query';
	endif;
	
	wp_reset_postdata();

	return $content;
}

//add shortcode to display upcoming events
// [agenda foo="foo-value"]
function as_shortcode_handler($atts) {
	extract( shortcode_atts( array(
		'display' => 'list',
		'num' => 5,
	), $atts) );
	
	$args = array(
		'posts_per_page' => $num
	);
	
	return as_get_events($display, $args);
}
add_shortcode('agenda', 'as_shortcode_handler');


// Register our widget.
function as_load_widgets() {
	register_widget( 'Agenda_Setter_Widget' );
}
add_action( 'widgets_init', 'as_load_widgets' );

/**
 * Agenda Setter Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.
 *
 * TODO: Add options for number of items, display format, etc. 
 *
 * @since 0.1
 */
class Agenda_Setter_Widget extends WP_Widget {

	// Widget setup.
	function Agenda_Setter_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'agenda-setter', 'description' => __('Display a list of upcoming events.', 'agenda-setter') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'agenda-setter-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'agenda-setter-widget', __('Agenda', 'agenda-setter'), $widget_ops, $control_ops );
	}
	
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
			
		$show_on_archive_only = isset( $instance['show_on_archive_only'] ) ? $instance['show_on_archive_only'] : false;
		
		$num_entries = $instance['num_entries'] ? $instance['num_entries'] : 5;
		
		$content = as_get_events('list', array(
			'posts_per_page' => $num_entries		
			)
		);
		
		if ( $content ) {
			
			/* Before widget (defined by themes). */
			echo $before_widget;
	
			/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title )
				echo $before_title . $title . $after_title;
				
			echo $content;
			
			/* After widget (defined by themes). */
			echo $after_widget;
		}
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		$instance['num_entries'] = strip_tags( $new_instance['num_entries'] );
		
		/* No need to strip tags. */
		$instance['show_on_archive_only'] = $new_instance['show_on_archive_only'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 
			'title' => __('Upcoming Events', 'agenda-setter'), 
			'show_on_archive_only' => false,
			'num_entries' => 5
			);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'agenda-setter'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" class="widefat" type="text" value="<?php echo $instance['title']; ?>" />
		</p>
		
		<!-- Number of Events: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'num_entries' ); ?>"><?php _e('Number of events to show:', 'agenda-setter'); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_entries' ); ?>" name="<?php echo $this->get_field_name( 'num_entries' ); ?>" type="text" size="3" value="<?php echo $instance['num_entries']; ?>" />
		</p>

		<!-- Show Only on Archives? Checkbox -->
		<?php /*

		<p>
			<input class="checkbox" type="checkbox" <?php if( $instance['show_on_archive_only'] ) echo "checked"; ?> id="<?php echo $this->get_field_id( 'show_on_archive_only' ); ?>" name="<?php echo $this->get_field_name( 'show_on_archive_only' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_on_archive_only' ); ?>"><?php _e('Show Only on Archives?', 'agenda-setter'); ?></label>
		</p>
		
		*/ ?>

	<?php
	}
}

/*
function as_query_events_taxonomy( $query ) {
	
	if( $query->get('as_event') ) {
		
		$query->set('suppress_filters', 'false');
		// retrieve events by end date
		$query->set('meta_key', '_end_date_generated_value');
		$query->set('orderby', 'meta_value');

		if ( $as_event_date == 'past' ) {
			$meta_compare = '<';
			$order = "DESC";
		} else {
			$meta_compare = '>=';
			$order = "ASC";
		}
		$query->set('post_type', $event_post_types);
		$query->set('meta_compare', $meta_compare);
		$query->set('meta_value', time());
		$query->set('order', $order);
	}
}
add_action('pre_get_posts', 'as_query_events_taxonomy');
*/

class AS_Query {

	function init() {
		add_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
		add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
		add_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ), 10, 2 );
		add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
	}

	function posts_where( $where, $wp_query ) {
		global $wpdb;
		
		if( $wp_query->get('as_event') == 'as_event_post' ) {
		
			if ( $wp_query->get('as_event_date') == 'past' ) {
				$meta_compare = '<';
			} else {
				$meta_compare = '>=';
			}
			// retrieve events by end date
			$where .= " AND postmeta.meta_value $meta_compare " . time();
		
		}
		
		return $where;
	}
	
	function posts_join( $join, $wp_query ) {
		global $wpdb;
		if( $wp_query->get('as_event') == 'as_event_post' ) {
			
			$join .= " INNER JOIN {$wpdb->postmeta} AS postmeta_start_date ON ( postmeta_start_date.post_id = {$wpdb->posts}.ID AND postmeta_start_date.meta_key = '_date_value' ) INNER JOIN {$wpdb->postmeta} AS postmeta ON ( postmeta.post_id = {$wpdb->posts}.ID AND postmeta.meta_key = '_end_date_generated_value' ) ";
		
		}
		
		return $join;
	}
	
	
	function posts_groupby( $group, $wp_query ) {
		global $wpdb;
		if( $wp_query->get('as_event') == 'as_event_post' ) {
			$group = " {$wpdb->posts}.ID ";
		}
		return $group;
	}	
	
	
	function posts_orderby( $orderby, $wp_query ) {
		global $as_event_date, $wpdb;
		
		if( $wp_query->get('as_event') == 'as_event_post' ) {
			
			if ( $wp_query->get('as_event_date') == 'past' ) {
				$order = 'DESC';
			} else {
				$order = 'ASC';
			}
			
			$orderby = " postmeta_start_date.meta_value $order";
		
		}
		
		return $orderby;
	}	
}

// let wordpress know about custom query vars
function as_query_vars( $query_vars ) {
	
  $query_vars[] = "as_event_date";
  return $query_vars;
}
add_filter('query_vars', 'as_query_vars' );


function as_add_rewrite_rules( $wp_rewrite ) {
	$new_rules = array( 
		'events/' => 'index.php?as_event=as_event_post',
		'events/(.+)' => 'index.php?as_event=as_event_post&as_event_date=' . $wp_rewrite->preg_index(1),
	);

	// Add the new rewrite rule into the top of the global rules array
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}
//add_action('generate_rewrite_rules', 'as_add_rewrite_rules');

function as_debug() {
	if( DEBUG ) {
		global $wp_query;
		echo "<!-- WP_QUERY: ";
		var_dump($wp_query);
		echo " -->";
	}
}
add_filter('wp_footer', 'as_debug');


// TODO: fix upcoming events order
?>