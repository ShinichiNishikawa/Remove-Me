<?php
/*
Plugin Name: Remove Me
Plugin URI: https://github.com/ShinichiNishikawa/Remove-Me
Description: This is a plugin to allow users to remove their own account.
Author: Shinichi Nishikawa
Version: 0.0
Author URI: http://nskw-style.com
*/

add_action( 'init', 'remove_me' );
function remove_me() {
	
	// return multisite
	if ( is_multisite() )
		return;
	
	// return if user is not logged in
	if ( !is_user_logged_in() )
		return;

	// return if no $_POST['remove_me']
	if ( !isset( $_POST['remove_me'] ) || $_POST['remove_me'] !== 'sure' )
		return;

	// now all the request is posted on single install WP by
	// a logged in user.

	// if current user can delete users, it can be the only admin
	// so return.
	if ( current_user_can( 'delete_users' ) )
		wp_die( 'Admins should\'t delete theirselves by this plugin.' );

	// check the senders ID
	if ( !isset( $_POST['senders_id'] ) || '' == $_POST['senders_id'] )
		wp_die( 'Something\'s wrong.' );

	// Check if the id of the user who requested it is a int
	$senders_id = intval( $_POST['senders_id'], 10 );
	if ( ! $senders_id )
		wp_die( 'Something\'s wrong.' );

	// compare senders matches the logged in user
	$current_user = wp_get_current_user();
	if ( $current_user->ID != $senders_id )
		wp_die( 'Something\'s wrong.' );
	
	if ( !check_admin_referer( 'randtext', '_my_nonce' ) )
		return;

	// Finally, delete.
	require_once( ABSPATH.'wp-admin/includes/user.php' );
	$deleted = wp_delete_user( $current_user->ID, 1 );
	
	if ( $deleted ) {
		wp_die( 'Deleted.' );
	} else {
		wp_die( 'Failed.' );
	}
		
}

// display delete form by calling this function
function display_remove_me_form() {

	// return multisite
	if ( is_multisite() )
		return;
	
	// return if user is not logged in
	if ( ! is_user_logged_in() )
		wp_die( 'You need to login to see this.<br><a href="' . home_url('/') . '">Go to home page</a>.' );
	
	// if current user can delete users, it can be the only admin
	// so return.
	if ( current_user_can( 'delete_users' ) )
		return;

	$current_user = wp_get_current_user();
	$senders_id = $current_user->ID;
	?>
<form id="remove-me" method="post" action="">
	<label for="remove_me">Do you really want to delete your account? By clicking the button, WordPress will delete all of your information and your posts.</label>
	<input type="checkbox" id="remove_me" name="remove_me" value="sure" />
	<input type="hidden" id="senders_id" name="senders_id" value="<?php echo (int)$senders_id; ?>">
	<?php wp_nonce_field( 'randtext', '_my_nonce' ); ?>
	<br />
	<input type="submit" value="Delete my account." />
</form>
	<?php
	
}