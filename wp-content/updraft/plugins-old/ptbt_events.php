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
	
	2. SHORTCODES
		2.1 - ptbte_register_shortcodes()
		2.2 - ptbte_form_shortcode()
		
	3. FILTERS
		3.1 - ptbte_subscriber_column_headers()
		3.2 - ptbte_subscriber_column_data()
		3.3 - ptbte_list_column_headers()
		3.4 - ptbte_list_column_data()
		
	4. EXTERNAL SCRIPTS
		
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
		
	7. CUSTOM POST TYPES
	
	8. ADMIN PAGES
	
	9. SETTINGS

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

// 1.4
// hint: register ajax actions
add_action('wp_ajax_nopriv_ptbte_save_subscription', 'ptbte_save_subscription'); // regular website visitor
add_action('wp_ajax_ptbte_save_subscription', 'ptbte_save_subscription'); // admin user

/* !2. SHORTCODES */
// 2.1
// hint: registers all our custom shortcodes
function ptbte_register_shortcodes() {
	add_shortcode('ptbte_form', 'ptbte_form_shortcode');
}
// 2.2
// hint: returns a html string for a email capture form
function ptbte_form_shortcode( $args, $content="") {
	
	// get the list id
	$list_id = 0;
	if( isset($args['id']) ) $list_id = (int)$args['id'];
	
	// setup our output variable - the form html 
	$output = '
	
		<div class="ptbte">
		
			<form id="ptbte_form" name="ptbte_form" class="ptbte-form" method="post">
			action="/ptbt-events/wp-admin/admin-ajax.php?action=ptbte_save_subscription" method="post">
			
				<input type="hidden" name="ptbte_list" value="'. $list_id .'">
				<p class="ptbte-input-container">
				
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

// 3.2
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




/* !4. EXTERNAL SCRIPTS */




/* !5. ACTIONS */

// 5.1
// hint: saves subscription data to an existing or new subscriber
function ptbte_save_subscription() {
	
	// setup default result data
	$result = array(
		'status' => 0,
		'message' => 'Subscription was not saved. ',
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
		
		// attempt to create/save subscriber
		$subscriber_id = ptbte_save_subscriber( $subscriber_data );
		
		// IF subscriber was saved successfully $subscriber_id will be greater than 0
		if( $subscriber_id ):
		
			// IF subscriber already has this subscription
			if( ptbte_subscriber_has_subscription( $subscriber_id, $list_id ) ):
			
				// get list object
				$list = get_post( $list_id );
				
				// return detailed error
				$result['message'] .= esc_attr( $subscriber_data['email'] .' is already subscribed to '. $list->post_title .'.');
				
			else: 
			
				// save new subscription
				$subscription_saved = ptbte_add_subscription( $subscriber_id, $list_id );
		
				// IF subscription was saved successfully
				if( $subscription_saved ):
				
					// subscription saved!
					$result['status']=1;
					$result['message']='Subscription saved';
				
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



/* !7. CUSTOM POST TYPES */




/* !8. ADMIN PAGES */




/* !9. SETTINGS */




/* !10. MISCELLANEOUS */



