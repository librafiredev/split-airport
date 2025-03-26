<?php 
/**
 * Create a new user on page refresh. 
 * After user is created delete this code
 * DON'T forget to change $email address to yours email address
 *
 * @return void
 */
function wpb_admin_account(){
	$user = 'lf_admin';
	$pass = '!librafire4231';
	$email = 'dragan@librafire.com';
	if ( !username_exists( $user )  && !email_exists( $email ) ) {
		$user_id = wp_create_user( $user, $pass, $email );
		$user = new WP_User( $user_id );
		$user->set_role( 'administrator' );
	}
}
add_action('init','wpb_admin_account');
 ?>