<?php
	
/*
Plugin Name: PTBT Events Manager
Plugin URI: 
Description: Plugin for maintaining PTBT Events
Version: 1.0
Author: Toby Jayne
Author URI: 
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ptbt_events_manager
*/

/* !0. TABLE OF CONTENTS */

/*

1. HOOKS
		1.1 - registers all our custom shortcodes
		1.2 - register custom admin column headers
		1.3 - register custom admin column data
		1.4 - register ajax actions
		1.5 - load external files to public website
		1.6 - Advanced Custom Fields Settings
		1.7 - register our custom menus
		1.8 - load external files in WordPress admin
		1.9 - register plugin options
2. SHORTCODES
		2.1 - ptbte_register_shortcodes()
		2.2 - ptbte_form_shortcode()
		2.3 - ptbte_manage_subscriptions_shortcode()
3. FILTERS
		3.1 - ptbte_subscriber_column_headers()
		3.2 - ptbte_subscriber_column_data()
		3.3 - ptbte_list_column_headers()
		3.4 - ptbte_list_column_data()
		3.5 - ptbte_admin_menus()
		3.6 - ptbte_unsubscribe()
		3.7 - ptbte_remove_subscription()
4. EXTERNAL SCRIPTS
		4.1 - Include ACF
		4.2 - ptbte_public_scripts()
5. ACTIONS
		5.1 - ptbte_save_subscription()
		5.2 - ptbte_save_subscriber()
		5.3 - ptbte_add_subscription()
6. HELPERS
		6.1 - ptbte_has_subscriptions()
		6.2 - ptbte_get_subscriber_id()
		6.3 - ptbte_get_subscritions()
		6.4 - ptbte_return_json()
		6.5 - ptbte_get_acf_key()
		6.6 - ptbte_get_subscriber_data()
		6.7 - ptbte_get_page_select()
		6.8 - ptbte_get_default_page_options()
		6.9 - ptbte_get_option()
		6.10 - ptbte_get_current_options()
		6.11 - ptbte_get_manage_subscriptions_html()
7. CUSTOM POST TYPES
		7.1 - subscribers
		7.2 - lists
8. ADMIN PAGES
		8.1 - ptbte_dashboard_admin_page()
		8.2 - ptbte_import_admin_page()
		8.3 - ptbte_options_admin_page()
9. SETTINGS
		9.1 - ptbte_register_options()
10. MISCELLANEOUS

*/


/* !1. HOOKS */

// 1.1
// hint: registers all our custom shortcodes on init
add_action('init', 'ptbte_register_shortcodes');

// 1.2
// hint: register custom admin column headers
add_filter('manage_edit-ptbte_subscriber_columns','ptbte_subscriber_column_headers');
add_filter('manage_edit-ptbte_list_columns','ptbte_list_column_headers');

// 1.3
// hint: register custom admin column data
add_filter('manage_ptbte_subscriber_posts_custom_column','ptbte_subscriber_column_data',1,2);
add_filter('manage_ptbte_list_posts_custom_column','ptbte_list_column_data',1,2);
add_action(
    'admin_head-edit.php',
    'ptbte_register_custom_admin_titles'
);

// 1.4
// hint: register ajax actions
add_action('wp_ajax_nopriv_ptbte_save_subscription', 'ptbte_save_subscription'); // regular website visitor
add_action('wp_ajax_ptbte_save_subscription', 'ptbte_save_subscription'); // admin user
add_action('wp_ajax_nopriv_ptbte_unsubscribe', 'ptbte_unsubscribe'); // regular website visitor
add_action('wp_ajax_ptbte_unsubscribe', 'ptbte_unsubscribe'); // admin user

// 1.5
// load external files to public website
add_action('wp_enqueue_scripts', 'ptbte_public_scripts');

// 1.6
// Advanced Custom Fields Settings
add_filter('acf/settings/path', 'ptbte_acf_settings_path');
add_filter('acf/settings/dir', 'ptbte_acf_settings_dir');
add_filter('acf/settings/show_admin', 'ptbte_acf_show_admin');
if( !defined('ACF_LITE') ) define('ACF_LITE',true); // turn off ACF plugin menu

// 1.7 
// hint: register our custom menus
add_action('admin_menu', 'ptbte_admin_menus');

// 1.8
// hint: load external files in WordPress admin
add_action('admin_enqueue_scripts', 'ptbte_admin_scripts');

// 1.9
// register plugin options
add_action('admin_init', 'ptbte_register_options');



/* !2. SHORTCODES */
// 2.1
// hint: registers all our custom shortcodes
function ptbte_register_shortcodes() {
	add_shortcode('ptbte_form', 'ptbte_form_shortcode');
	add_shortcode('ptbte_manage_subscriptions', 'ptbte_manage_subscriptions_shortcode');
}
// 2.2
// hint: returns a html string for a email capture form
function ptbte_form_shortcode( $args, $content="") {
	
	// get the list id
	$list_id = 0;
	if( isset($args['id']) ) $list_id = (int)$args['id'];
	
	// title
	$title = '';
	if( isset($args['title']) ) $title = (string)$args['title'];
	
	// setup our output variable - the form html 
	$output = '
	
		<div class="ptbte">
		
			<form id="ptbte_register_form" name="ptbte_form" class="ptbte-form" method="post"
			action="/ptbt-events/wp-admin/admin-ajax.php?action=ptbte_save_subscription" method="post">
			
				<input type="hidden" name="ptbte_list" value="'. $list_id .'">';
				if( strlen($title) ):
					$output .= '<h3 class="ptbte-title">'. $title .'</h3>';
				endif;
				$output .='<p class="ptbte-input-container">
				
					<label>Your Name</label><br />
					<input type="text" name="ptbte_fname" placeholder="First Name" />
					<input type="text" name="ptbte_lname" placeholder="Last Name" />
				
				</p>
				
				<p class="ptbte-input-container">
				
					<label>Your Email</label><br />
					<input type="email" name="ptbte_email" placeholder="ex. you@email.com" />
				
				</p>';
				
				// including content in our form html if content is passed into the function
				if( strlen($content) ):
				
					$output .= '<div class="ptbte-content">'. wpautop($content) .'</div>';
				
				endif;
				
				// completing our form html
				$output .= '<p class="ptbte-input-container">
				
					<input type="submit" name="ptbte_submit" value="Sign Me Up!" />
				
				</p>
			
			</form>
		
		</div>
	
	';
	
	// return our results/html
	return $output;
	
}

// 2.3
// hint: displays a form for managing the users list subscriptions
// example: [ptbte_manage_subscriptions]
function ptbte_manage_subscriptions_shortcode( $args, $content="" ) {
	
	// setup our return string
	$output = '<div class="ptbte ptbte-manage-subscriptions">';
	
	try {
		
		// get the email address from the URL
		$email = ( isset( $_GET['email'] ) ) ? esc_attr( $_GET['email'] ) : '';
		
		// get the subscriber id from the email address
		$subscriber_id = ptbte_get_subscriber_id( $email );
		
		// get subscriber data 
		$subscriber_data = ptbte_get_subscriber_data( $subscriber_id );
		
		// IF subscriber exists
		if( $subscriber_id ):
		
			// get subscriptions html
			$output = ptbte_get_manage_subscriptions_html( $subscriber_id );
			
		else:
		
			// invalid link
			$output .= '<p>This link is invalid.</p>';
		
		endif;
	
	
	} catch(Exception $e) {
		
		// php error
		
	}
	
	// close our html div tag
	$output .= '</div>';
	
	// return our html
	return $output;
	
}



/* !3. FILTERS */

// 3.1
function ptbte_subscriber_column_headers( $columns ) {
	
	// creating custom column header data
	$columns = array(
		'cb'=>'<input type="checkbox" />',
		'title'=>__('Subscriber Name'),
		'email'=>__('Email Address'),	
	);
	
	// returning new columns
	return $columns;
	
}

// 3.2
function ptbte_subscriber_column_data( $column, $post_id ) {
	
	// setup our return text
	$output = '';
	
	switch( $column ) {
		
		case 'title':
			// get the custom name data
			$fname = get_field('ptbte_fname', $post_id );
			$lname = get_field('ptbte_lname', $post_id );
			$output .= $fname .' '. $lname;
			break;
		case 'email':
			// get the custom email data
			$email = get_field('ptbte_email', $post_id );
			$output .= $email;
			break;
		
	}
	
	// echo the output
	echo $output;
	
}

// 3.2.2
// hint: registers special custom admin title columns
function ptbte_register_custom_admin_titles() {
    add_filter(
        'the_title',
        'ptbte_custom_admin_titles',
        99,
        2
    );
}

// 3.2.3
// hint: handles custom admin title "title" column data for post types without titles
function ptbte_custom_admin_titles( $title, $post_id ) {
   
    global $post;
	
    $output = $title;
   
    if( isset($post->post_type) ):
                switch( $post->post_type ) {
                        case 'ptbte_subscriber':
	                            $fname = get_field('ptbte_fname', $post_id );
	                            $lname = get_field('ptbte_lname', $post_id );
	                            $output = $fname .' '. $lname;
	                            break;
                }
        endif;
   
    return $output;
}

// 3.3
function ptbte_list_column_headers( $columns ) {
	
	// creating custom column header data
	$columns = array(
		'cb'=>'<input type="checkbox" />',
		'title'=>__('List Name'),	
		'shortcode'=>__('Shortcode'),	
	);
	
	// returning new columns
	return $columns;
	
}

// 3.4
function ptbte_list_column_data( $column, $post_id ) {
	
	// setup our return text
	$output = '';
	
	switch( $column ) {
		
		case 'shortcode':
			$output .= '[ptbte_form id="'. $post_id .'"]';
			break;
		
	}
	
	// echo the output
	echo $output;
	
}

// 3.5
// hint: registers custom plugin admin menus
function ptbte_admin_menus() {
	
	/* main menu */
	
		$top_menu_item = 'ptbte_dashboard_admin_page';
	    
	    add_menu_page( '', 'List Builder', 'manage_options', 'ptbte_dashboard_admin_page', 'ptbte_dashboard_admin_page', 'dashicons-email-alt' );
    
    /* submenu items */
    
	    // dashboard
	    add_submenu_page( $top_menu_item, '', 'Dashboard', 'manage_options', $top_menu_item, $top_menu_item );
	    
	    // email lists
	    add_submenu_page( $top_menu_item, '', 'Email Lists', 'manage_options', 'edit.php?post_type=ptbte_list' );
	    
	    // subscribers
	    add_submenu_page( $top_menu_item, '', 'Subscribers', 'manage_options', 'edit.php?post_type=ptbte_subscriber' );
	    
	    // import subscribers
	    add_submenu_page( $top_menu_item, '', 'Import Subscribers', 'manage_options', 'ptbte_import_admin_page', 'ptbte_import_admin_page' );
	    
	    // plugin options
	    add_submenu_page( $top_menu_item, '', 'Plugin Options', 'manage_options', 'ptbte_options_admin_page', 'ptbte_options_admin_page' );

}


/* !4. EXTERNAL SCRIPTS */

// 4.1
// Include ACF
include_once( plugin_dir_path( __FILE__ ) .'lib/advanced-custom-fields/acf.php' );

// 4.2
// hint: loads external files into PUBLIC website
function ptbte_public_scripts() {
	
	// register scripts with WordPress's internal library
	wp_register_script('ptbt-events-js-public', plugins_url('/js/public/ptbt-events.js',__FILE__), array('jquery'),'',true);
	wp_register_style('ptbt-events-css-public', plugins_url('/css/public/ptbt-events.css',__FILE__));
	
	// add to que of scripts that get loaded into every page
	wp_enqueue_script('ptbt-events-js-public');
	wp_enqueue_style('ptbt-events-css-public');
	
}

// 4.3
// hint: loads external files into wordpress ADMIN
function ptbte_admin_scripts() {
	
	// register scripts with WordPress's internal library
	wp_register_script('snappy-list-builder-js-private', plugins_url('/js/private/snappy-list-builder.js',__FILE__), array('jquery'),'',true);
	
	// add to que of scripts that get loaded into every admin page
	wp_enqueue_script('snappy-list-builder-js-private');
	
}


/* !5. ACTIONS */

// 5.1
// hint: saves subscription data to an existing or new subscriber
function ptbte_save_subscription() {
	
	// setup default result data
	$result = array(
		'status' => 0,
		'message' => 'Subscription was not saved. ',
		'error'=>'',
		'errors'=>array()
	);
	
	try {
		
		// get list_id
		$list_id = (int)$_POST['ptbte_list'];
	
		// prepare subscriber data
		$subscriber_data = array(
			'fname'=> esc_attr( $_POST['ptbte_fname'] ),
			'lname'=> esc_attr( $_POST['ptbte_lname'] ),
			'email'=> esc_attr( $_POST['ptbte_email'] ),
		);
		
		// setup our errors array
		$errors = array();
		// form validation
		if( !strlen( $subscriber_data['fname'] ) ) $errors['fname'] = 'First name is required.';
		if( !strlen( $subscriber_data['email'] ) ) $errors['email'] = 'Email address is required.';
		if( strlen( $subscriber_data['email'] ) && !is_email( $subscriber_data['email'] ) ) $errors['email'] = 'Email address must be valid.';
		// IF there are errors
		if( count($errors) ):
			// append errors to result structure for later use
			$result['error'] = 'Some fields are still required. ';
			$result['errors'] = $errors;
		else: 
		// IF there are no errors, proceed...
		// attempt to create/save subscriber
		$subscriber_id = ptbte_save_subscriber( $subscriber_data );
		
		// IF subscriber was saved successfully $subscriber_id will be greater than 0
		if( $subscriber_id ):
		
			// IF subscriber already has this subscription
			if( ptbte_subscriber_has_subscription( $subscriber_id, $list_id ) ):
			
				// get list object
				$list = get_post( $list_id );
				
				// return detailed error
					$result['error'] = esc_attr( $subscriber_data['email'] .' is already subscribed to '. $list->post_title .'.');
				
			else: 
			
				// save new subscription
				$subscription_saved = ptbte_add_subscription( $subscriber_id, $list_id );
		
				// IF subscription was saved successfully
				if( $subscription_saved ):
				
					// subscription saved!
					$result['status']=1;
					$result['message']='Subscription saved';
					else: 
						// return detailed error
						$result['error'] = 'Unable to save subscription.';
					endif;
				
				endif;
			
			endif;
		
		endif;
		
		
	} catch ( Exception $e ) {
		
	}
	
	// return result as json
	ptbte_return_json($result);
	
}

// 5.2
// hint: creates a new subscriber or updates and existing one
function ptbte_save_subscriber( $subscriber_data ) {
	
	// setup default subscriber id
	// 0 means the subscriber was not saved
	$subscriber_id = 0;
	
	try {
		
		$subscriber_id = ptbte_get_subscriber_id( $subscriber_data['email'] );
		
		// IF the subscriber does not already exists...
		if( !$subscriber_id ):
		
			// add new subscriber to database	
			$subscriber_id = wp_insert_post( 
				array(
					'post_type'=>'ptbte_subscriber',
					'post_title'=>$subscriber_data['fname'] .' '. $subscriber_data['lname'],
					'post_status'=>'publish',
				), 
				true
			);
		
		endif;
		
		// add/update custom meta data
		update_field(ptbte_get_acf_key('ptbte_fname'), $subscriber_data['fname'], $subscriber_id);
		update_field(ptbte_get_acf_key('ptbte_lname'), $subscriber_data['lname'], $subscriber_id);
		update_field(ptbte_get_acf_key('ptbte_email'), $subscriber_data['email'], $subscriber_id);
		
	} catch( Exception $e ) {
		
		// a php error occurred
		
	}
	
	// return subscriber_id
	return $subscriber_id;
	
}

// 5.3
// hint: adds list to subscribers subscriptions
function ptbte_add_subscription( $subscriber_id, $list_id ) {
	
	// setup default return value
	$subscription_saved = false;
	
	// IF the subscriber does NOT have the current list subscription
	if( !ptbte_subscriber_has_subscription( $subscriber_id, $list_id ) ):
	
		// get subscriptions and append new $list_id
		$subscriptions = ptbte_get_subscriptions( $subscriber_id );
		$subscriptions[]=$list_id;
		
		// update ptbte_subscriptions
		update_field( ptbte_get_acf_key('ptbte_subscriptions'), $subscriptions, $subscriber_id );
		
		// subscriptions updated!
		$subscription_saved = true;
	
	endif;
	
	// return result
	return $subscription_saved;
	
}

/* !6. HELPERS */

// 6.1
// hint: returns true or false
function ptbte_subscriber_has_subscription( $subscriber_id, $list_id ) {
	
	// setup default return value
	$has_subscription = false;
	
	// get subscriber
	$subscriber = get_post($subscriber_id);
	
	// get subscriptions
	$subscriptions = ptbte_get_subscriptions( $subscriber_id );
	
	// check subscriptions for $list_id
	if( in_array($list_id, $subscriptions) ):
	
		// found the $list_id in $subscriptions
		// this subscriber is already subscribed to this list
		$has_subscription = true;
	
	else:
	
		// did not find $list_id in $subscriptions
		// this subscriber is not yet subscribed to this list
	
	endif;
	
	return $has_subscription;
	
}

// 6.2
// hint: retrieves a subscriber_id from an email address
function ptbte_get_subscriber_id( $email ) {
	
	$subscriber_id = 0;
	
	try {
	
		// check if subscriber already exists
		$subscriber_query = new WP_Query( 
			array(
				'post_type'		=>	'ptbte_subscriber',
				'posts_per_page' => 1,
				'meta_key' => 'ptbte_email',
				'meta_query' => array(
				    array(
				        'key' => 'ptbte_email',
				        'value' => $email,  // or whatever it is you're using here
				        'compare' => '=',
				    ),
				),
			)
		);
		
		// IF the subscriber exists...
		if( $subscriber_query->have_posts() ):
		
			// get the subscriber_id
			$subscriber_query->the_post();
			$subscriber_id = get_the_ID();
			
		endif;
	
	} catch( Exception $e ) {
		
		// a php error occurred
		
	}
		
	// reset the Wordpress post object
	wp_reset_query();
	
	return (int)$subscriber_id;
	
}

// 6.3
// hint: returns an array of list_id's
function ptbte_get_subscriptions( $subscriber_id ) {
	
	$subscriptions = array();
	
	// get subscriptions (returns array of list objects)
	$lists = get_field( ptbte_get_acf_key('ptbte_subscriptions'), $subscriber_id );
	
	// IF $lists returns something
	if( $lists ):
	
		// IF $lists is an array and there is one or more items
		if( is_array($lists) && count($lists) ):
			// build subscriptions: array of list id's
			foreach( $lists as &$list):
				$subscriptions[]= (int)$list->ID;
			endforeach;
		elseif( is_numeric($lists) ):
			// single result returned
			$subscriptions[]= $lists;
		endif;
	
	endif;
	
	return (array)$subscriptions;
	
}

// 6.4
function ptbte_return_json( $php_array ) {
	
	// encode result as json string
	$json_result = json_encode( $php_array );
	
	// return result
	die( $json_result );
	
	// stop all other processing 
	exit;
	
}


//6.5
// hint: gets the unique act field key from the field name
function ptbte_get_acf_key( $field_name ) {
	
	$field_key = $field_name;
	
	switch( $field_name ) {
		
		case 'ptbte_fname':
			$field_key = 'field_5a6a0a87f032c';
			break;
		case 'ptbte_lname':
			$field_key = 'field_5a6a0ab1f032d';
			break;
		case 'ptbte_email':
			$field_key = 'field_5a6a0ac4f032e';
			break;
		case 'ptbte_subscriptions':
			$field_key = 'field_5a6a0af0f032f';
			break;
		
	}
	
	return $field_key;
	
}


// 6.6
// hint: returns an array of subscriber data including subscriptions
function ptbte_get_subscriber_data( $subscriber_id ) {
	
	// setup subscriber_data
	$subscriber_data = array();
	
	// get subscriber object
	$subscriber = get_post( $subscriber_id );
	
	// IF subscriber object is valid
	if( isset($subscriber->post_type) && $subscriber->post_type == 'ptbte_subscriber' ):
	
		$fname = get_field( ptbte_get_acf_key('ptbte_fname'), $subscriber_id);
		$lname = get_field( ptbte_get_acf_key('ptbte_lname'), $subscriber_id);
	
		// build subscriber_data for return
		$subscriber_data = array(
			'name'=> $fname .' '. $lname,
			'fname'=>$fname,
			'lname'=>$lname,
			'email'=>get_field( ptbte_get_acf_key('ptbte_email'), $subscriber_id),
			'subscriptions'=>ptbte_get_subscriptions( $subscriber_id )
		);
		
	
	endif;
	
	// return subscriber_data
	return $subscriber_data;
	
}

// 6.7
// hint: returns html for a page selector
function ptbte_get_page_select( $input_name="ptbte_page", $input_id="", $parent=-1, $value_field="id", $selected_value="" ) {
	
	// get WP pages
	$pages = get_pages( 
		array(
			'sort_order' => 'asc',
			'sort_column' => 'post_title',
			'post_type' => 'page',
			'parent' => $parent,
			'status'=>array('draft','publish'),	
		)
	);
	
	// setup our select html
	$select = '<select name="'. $input_name .'" ';
	
	// IF $input_id was passed in
	if( strlen($input_id) ):
	
		// add an input id to our select html
		$select .= 'id="'. $input_id .'" ';
	
	endif;
	
	// setup our first select option
	$select .= '><option value="">- Select One -</option>';
	
	// loop over all the pages
	foreach ( $pages as &$page ): 
	
		// get the page id as our default option value
		$value = $page->ID;
		
		// determine which page attribute is the desired value field
		switch( $value_field ) {
			case 'slug':
				$value = $page->post_name;
				break;
			case 'url':
				$value = get_page_link( $page->ID );
				break;
			default:
				$value = $page->ID;
		}
		
		// check if this option is the currently selected option
		$selected = '';
		if( $selected_value == $value ):
			$selected = ' selected="selected" ';
		endif;
	
		// build our option html
		$option = '<option value="' . $value . '" '. $selected .'>';
		$option .= $page->post_title;
		$option .= '</option>';
		
		// append our option to the select html
		$select .= $option;
		
	endforeach;
	
	// close our select html tag
	$select .= '</select>';
	
	// return our new select 
	return $select;
	
}

// 6.8
// hint: returns default option values as an associative array
function ptbte_get_default_options() {
	
	$defaults = array();
	
	try {
		
		// get front page id
		$front_page_id = get_option('page_on_front');
	
		// setup default email footer
		$default_email_footer = '
			<p>
				Sincerely, <br /><br />
				The '. get_bloginfo('name') .' Team<br />
				<a href="'. get_bloginfo('url') .'">'. get_bloginfo('url') .'</a>
			</p>
		';
		
		// setup defaults array
		$defaults = array(
			'ptbte_manage_subscription_page_id'=>$front_page_id,
			'ptbte_confirmation_page_id'=>$front_page_id,
			'ptbte_reward_page_id'=>$front_page_id,
			'ptbte_default_email_footer'=>$default_email_footer,
			'ptbte_download_limit'=>3,
		);
	
	} catch( Exception $e) {
		
		// php error
		
	}
	
	// return defaults
	return $defaults;
	
	
}

// 6.9
// hint: returns the requested page option value or it's default
function ptbte_get_option( $option_name ) {
	
	// setup return variable
	$option_value = '';	
	
	
	try {
		
		// get default option values
		$defaults = ptbte_get_default_options();
		
		// get the requested option
		switch( $option_name ) {
			
			case 'ptbte_manage_subscription_page_id':
				// subscription page id
				$option_value = (get_option('ptbte_manage_subscription_page_id')) ? get_option('ptbte_manage_subscription_page_id') : $defaults['ptbte_manage_subscription_page_id'];
				break;
			case 'ptbte_confirmation_page_id':
				// confirmation page id
				$option_value = (get_option('ptbte_confirmation_page_id')) ? get_option('ptbte_confirmation_page_id') : $defaults['ptbte_confirmation_page_id'];
				break;
			case 'ptbte_reward_page_id':
				// reward page id
				$option_value = (get_option('ptbte_reward_page_id')) ? get_option('ptbte_reward_page_id') : $defaults['ptbte_reward_page_id'];
				break;
			case 'ptbte_default_email_footer':
				// email footer
				$option_value = (get_option('ptbte_default_email_footer')) ? get_option('ptbte_default_email_footer') : $defaults['ptbte_default_email_footer'];
				break;
			case 'ptbte_download_limit':
				// reward download limit
				$option_value = (get_option('ptbte_download_limit')) ? (int)get_option('ptbte_download_limit') : $defaults['ptbte_download_limit'];
				break;
			
		}
		
	} catch( Exception $e) {
		
		// php error
		
	}
	
	// return option value or it's default
	return $option_value;
	
}

// 6.10
// hint: get's the current options and returns values in associative array
function ptbte_get_current_options() {
	
	// setup our return variable
	$current_options = array();
	
	try {
	
		// build our current options associative array
		$current_options = array(
			'ptbte_manage_subscription_page_id' => ptbte_get_option('ptbte_manage_subscription_page_id'),
			'ptbte_confirmation_page_id' => ptbte_get_option('ptbte_confirmation_page_id'),
			'ptbte_reward_page_id' => ptbte_get_option('ptbte_reward_page_id'),
			'ptbte_default_email_footer' => ptbte_get_option('ptbte_default_email_footer'),
			'ptbte_download_limit' => ptbte_get_option('ptbte_download_limit'),
		);
	
	} catch( Exception $e ) {
		
		// php error
	
	}
	
	// return current options
	return $current_options;
	
}

// 6.11
// hint: generates an html form for managing subscriptions
function ptbte_get_manage_subscriptions_html( $subscriber_id ) {
	
	$output = '';
	
	try {
		
		// get array of list_ids for this subscriber
		$lists = ptbte_get_subscriptions( $subscriber_id );
		
		// get the subscriber data
		$subscriber_data = ptbte_get_subscriber_data( $subscriber_id );
		
		// set the title
		$title = $subscriber_data['fname'] .'\'s Subscriptions';
	
		// build out output html
		$output = '
			<form id="ptbte_manage_subscriptions_form" class="ptbte-form" method="post"  
			action="/wp-admin/admin-ajax.php?action=ptbte_unsubscribe">
				
				<input type="hidden" name="subscriber_id" value="'. $subscriber_id .'">
				
				<h3 class="ptbte-title">'. $title .'</h3>';
				
				if( !count($lists) ):
					
					$output .='<p>There are no active subscriptions.</p>';
				
				else:
				
					$output .= '<table>
						<tbody>';
						
						// loop over lists
						foreach( $lists as &$list_id ):
						
							$list_object = get_post( $list_id );
						
							$output .= '<tr>
								<td>'.
									$list_object->post_title
								.'</td>
								<td>
									<label>
										<input 
											type="checkbox" name="list_ids[]" 
											value="'. $list_object->ID .'" 
										/> UNSUBSCRIBE
									</label>
								</td>
							</tr>';
							
						endforeach;
						
						// close up our output html
						$output .='</tbody>
					</table>
					
					<p><input type="submit" value="Save Changes" /></p>';
				
				endif;
				
			$output .='
				</form>
			';
	
	} catch( Exception $e ) {
		
		// php error
		
	}
	
	// return output 
	return $output;
	
}



/* !7. CUSTOM POST TYPES */




/* !8. ADMIN PAGES */




/* !9. SETTINGS */

// 9.1
// hint: registers all our plugin options
function ptbte_register_options() {
	// plugin options
	register_setting('ptbte_plugin_options', 'ptbte_manage_subscription_page_id');
	register_setting('ptbte_plugin_options', 'ptbte_confirmation_page_id');
	register_setting('ptbte_plugin_options', 'ptbte_reward_page_id');
	register_setting('ptbte_plugin_options', 'ptbte_default_email_footer');
	register_setting('ptbte_plugin_options', 'ptbte_download_limit');
}

