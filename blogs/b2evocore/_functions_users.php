<?php
/**
 * User stuff 
 * 
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package b2evocore
 * @author This file built upon code from original b2 - http://cafelog.com/
 */
require_once dirname(__FILE__). '/_functions_groups.php';
require_once dirname(__FILE__). '/_class_user.php';


/*
 * veriflog(-)
 *
 * Verify if user is logged in
 * checking login & pass in the database
 */
function veriflog( $login_required = false )
{
	global $cookie_user, $cookie_pass, $cookie_expires, $cookie_path, $cookie_domain, $error, $core_dirout;
	global $user_login, $user_pass_md5, $userdata, $user_ID, $user_nickname, $user_email, $user_url;
	global $current_User;
	global $DB, $tableusers;

	// Reset all global variables in case some tricky stuff is trying to set them otherwise:
	// Warning: unset() prevent from setting a new global value later in the func !!! :((
	$user_login = '';
	$user_pass_md5 = '';
	$userdata = '';
	$user_ID = '';
	$user_nickname = '';
	$user_email = '';
	$user_url = '';

	// Check if user is trying to login right now:
	if( isset($_POST['log'] ) && isset($_POST['pwd'] ))
	{	// Trying to log in with a POST
		$log = strtolower(trim(strip_tags(get_magic_quotes_gpc() ? stripslashes($_POST['log']) : $_POST['log'])));
		$user_pass_md5 = md5(trim(strip_tags(get_magic_quotes_gpc() ? stripslashes($_POST['pwd']) : $_POST['pwd'])));
		unset($_POST['pwd']); // password is hashed from now on
	}
	elseif( isset($_GET['log'] ) && isset($_GET['pwd'] ))
	{	// Trying to log in with a GET
		$log = strtolower(trim(strip_tags(get_magic_quotes_gpc() ? stripslashes($_GET['log']) : $_GET['log'])));
		$user_pass_md5 = md5(trim(strip_tags(get_magic_quotes_gpc() ? stripslashes($_GET['pwd']) : $_GET['pwd'])));
		unset($_GET['pwd']); // password is hashed from now on
	}

	if( isset($log) )
	{	/*
		 * ---------------------------------------------------------
		 * User is trying to login right now
		 * ---------------------------------------------------------
		 */
		// echo 'Trying to log in right now...';

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		// Check login and password
		$user_login = $log;
		if( !( $login_ok = user_pass_ok( $user_login, $user_pass_md5, true ) ) )
		{
			// echo 'login failed!!';
			return '<strong>'. T_('ERROR'). ':</strong> '. T_('wrong login/password.');
		}
		
		// Login succeeded:
		//echo $user_login, $pass_is_md5, $user_pass,  $cookie_domain;
		if( !setcookie( $cookie_user, $log, $cookie_expires, $cookie_path, $cookie_domain ) )
			printf( T_('setcookie %s failed!'). '<br />', $cookie_user );
		if( !setcookie( $cookie_pass, $user_pass_md5, $cookie_expires, $cookie_path, $cookie_domain) )
			printf( T_('setcookie %s failed!'). '<br />', $cookie_user );
	}
	elseif( isset($_COOKIE[$cookie_user]) && isset($_COOKIE[$cookie_pass]) )
	{	/*
		 * ---------------------------------------------------------
		 * User was not trying to log in, but he already was logged in: check validity
		 * ---------------------------------------------------------
		 */
		// echo 'Was already logged in...';

		$user_login = trim(strip_tags(get_magic_quotes_gpc() ? stripslashes($_COOKIE[$cookie_user]) : $_COOKIE[$cookie_user]));
		$user_pass_md5 = trim(strip_tags(get_magic_quotes_gpc() ? stripslashes($_COOKIE[$cookie_pass]) : $_COOKIE[$cookie_pass]));
		// echo 'pass=', $user_pass_md5;

		if( ! user_pass_ok( $user_login, $user_pass_md5, true ) )
		{	// login is NOT OK:
			if( $login_required )
			{
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-cache, must-revalidate");
				header("Pragma: no-cache");

				return '<strong>'. T_('ERROR'). ':</strong> '. T_('login/password no longer valid.');
			}

			return 0;	// Wrong login but we don't care.
		}
	}
	else
	{	/*
		 * ---------------------------------------------------------
		 * User was not logged in at all
		 * ---------------------------------------------------------
		 */
		// echo ' NOT logged in...';

		if( $login_required )
		{
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			return T_('You must log in!');
			exit();
		}

		return 0;	// Not logged in but we don't care
	}

	/*
	 * Login info is OK, we set the global variables:
	 */
	// echo 'LOGGED IN';
	$userdata	= get_userdatabylogin($user_login);
	$current_User = new User( $userdata );	// COPY!
	//echo $current_User->disp('login');
	
	$user_ID = $userdata['ID'];
	$user_nickname = $userdata['user_nickname'];
	$user_email	= $userdata['user_email'];
	$user_url	= $userdata['user_url'];

	return 0;		// OK
}


/*
 * logout
 *
 * Log the user out:
 */
function logout()
{
	global $cookie_user, $cookie_pass, $cookie_expired, $cookie_path, $cookie_domain;
	global $user_login, $user_pass_md5, $userdata, $user_ID, $user_nickname, $user_email, $user_url;

	// Reset all global variables
	// Note: unset is bugguy on globals
	$user_login = '';
	$user_pass_md5 = '';
	$userdata = '';
	$user_ID = '';
	$user_nickname = '';
	$user_email = '';
	$user_url = '';

	setcookie( 'cafeloguser' );		// OLD
	setcookie( 'cafeloguser', '', $cookie_expired, $cookie_path, $cookie_domain); // OLD
	setcookie( $cookie_user, '', $cookie_expired, $cookie_path, $cookie_domain);

	setcookie( 'cafelogpass' );		// OLD
	setcookie( 'cafelogpass', '', $cookie_expired, $cookie_path, $cookie_domain);	// OLD
	setcookie( $cookie_pass, '', $cookie_expired, $cookie_path, $cookie_domain);

}

/**
 * is_logged_in(-)
 */
function is_logged_in()
{
	global $user_ID, $generating_static;

	if( isset($generating_static) )
	{	// When generating static page, we should always consider we are not logged in.
		return false;
	}

	return (!empty($user_ID));
}



/*
 * user_pass_ok(-)
 */
function user_pass_ok( $user_login, $user_pass, $pass_is_md5 = false )
{
	$userdata = get_userdatabylogin($user_login);
	// echo 'got data for: ', $userdata['user_login'];

	if( !$pass_is_md5 ) $user_pass = md5( $user_pass );
	// echo 'pass: ', $user_pass, '/', $userdata['user_pass'];

	return ($user_pass == $userdata['user_pass']);
}


/**
 * get_userdatabylogin(-)
 */
function get_userdatabylogin( $user_login )
{
	global $DB, $tableusers, $cache_userdata, $use_cache;
	if( (empty($cache_userdata[$user_login])) OR (!$use_cache) )
	{
		$sql = "SELECT * 
						FROM $tableusers 
						WHERE user_login = '".$DB->escape($user_login)."'";
		$myrow = $DB->get_row( $sql, ARRAY_A );
		$cache_userdata[$user_login] = $myrow;
	}
	else
	{
		$myrow = $cache_userdata[$user_login];
	}
	return($myrow);
}

/*
 * get_userdata(-)
 */
function get_userdata($userid)
{
	global $DB,$tableusers, $cache_userdata;
	if( empty($cache_userdata[$userid] ) )
	{	// We do a progressive cache load beacuse there can be many many users!
		$sql = "SELECT *
						FROM $tableusers
						WHERE ID = $userid";
		if( $myrow = $DB->get_row( $sql, ARRAY_A ) )
		{
			$cache_userdata[ $myrow['ID'] ] = $myrow;
		}
	}

	if( ! isset( $cache_userdata[$userid] ) )
	{
		die('Requested user does not exist!');
	}

	return $cache_userdata[$userid];
}


/*
 * get_usernumposts(-)
 */
function get_usernumposts( $userid )
{
	global $DB, $tableposts;
	return $DB->get_var( "SELECT count(*)
												FROM $tableposts
												WHERE post_author = $userid" );
}


/*
 * get_user_info(-)
 */
function get_user_info( $show = '', $this_userdata = '' )
{
	global $userdata;

	if( empty( $this_userdata ) )
	{	// We want the current user
		$this_userdata = & $userdata;
	}

	switch( $show )
	{
		case 'ID':
			$output = $this_userdata['ID'];
			break;

		case 'num_posts':
			$output = get_usernumposts( $this_userdata['ID'] );
			break;

		case 'level':
		case 'firstname':
		case 'lastname':
		case 'nickname':
		case 'idmode':
		case 'email':
		case 'url':
		case 'icq':
		case 'aim':
		case 'msn':
		case 'yim':
		case 'notify':
		case 'locale':
			$output = $this_userdata['user_'. $show];
			break;

		case 'login':
		default:
			$output = $this_userdata['user_login'];
			break;
	}
	return trim($output);
}


/*
 * user_info(-)
 *
 * Template tag
 */
function user_info( $show = '', $format = 'raw', $display = true )
{
	$content = get_user_info( $show );
	$content = format_to_output( $content, $format );
	if( $display )
		echo $content;
	else
		return $content;
}



/*
 * user_login_link(-)
 *
 * Template tag; Provide a link to login
 */
function user_login_link( $before = '', $after = '', $link_text = '', $link_title = '#' )
{
	global $htsrv_url, $blog;

	if( is_logged_in() ) return false;

	if( $link_text == '' ) $link_text = T_('Login...');
	if( $link_title == '#' ) $link_title = T_('Login if you have an account...');

	$redir = '';
	if( !empty( $blog ) )
	{	// We'll want to return to this blog after login
		$redir = '?redirect_to='.htmlspecialchars( get_bloginfo('blogurl') );
	}

	echo $before;
	echo '<a href="', $htsrv_url, '/login.php', $redir, '" title="', $link_title, '">';
	echo $link_text;
	echo '</a>';
	echo $after;
}

/*
 * user_register_link(-)
 *
 * Template tag; Provide a link to new user registration
 */
function user_register_link( $before = '', $after = '', $link_text = '', $link_title = '#' )
{
	global $htsrv_url, $blog, $Settings;

	if( is_logged_in() || !$Settings->get('newusers_canregister'))
	{	// There's no need to provide this link if already logged in or if we won't let him register
		return false;
	}

	if( $link_text == '' ) $link_text = T_('Register...');
	if( $link_title == '#' ) $link_title = T_('Register to open an account...');

	$redir = '';
	if( !empty( $blog ) )
	{	// We'll want to return to this blog after login
		$redir = '?redirect_to='.htmlspecialchars( get_bloginfo('blogurl') );
	}

	echo $before;
	echo '<a href="',  $htsrv_url, '/register.php', $redir, '" title="', $link_title, '">';
	echo $link_text;
	echo '</a>';
	echo $after;
}


/*
 * user_logout_link(-)
 *
 * Template tag; Provide a link to logout
 */
function user_logout_link( $before = '', $after = '', $link_text = '', $link_title = '#' )
{
	global $htsrv_url, $user_login, $blog;

	if( ! is_logged_in() ) return false;

	if( $link_text == '' ) $link_text = T_('Logout (%s)');
	if( $link_title == '#' ) $link_title = T_('Logout from your account');

	$redir = '';
	if( !empty( $blog ) )
	{	// We'll want to return to this blog after login
		$redir = '&amp;redirect_to='.htmlspecialchars( get_bloginfo('blogurl') );
	}

	echo $before;
	echo '<a href="', $htsrv_url, '/login.php?action=logout', $redir, '" title="', $link_title, '">';
	printf( $link_text, $user_login );
	echo '</a>';
	echo $after;
}

/*
 * user_admin_link(-)
 *
 * Template tag; Provide a link to the backoffice
 */
function user_admin_link( $before = '', $after = '', $page = 'b2edit.php', $link_text = '', $link_title = '#' )
{
	global $admin_url, $blog, $current_User;

	if( ! is_logged_in() ) return false;

	if( $current_User->get('level') == 0 )
	{ // If user is NOT active:
		return false;
	}

	if( $link_text == '' ) $link_text = T_('Admin');
	if( $link_title == '#' ) $link_title = T_('Go to the back-office');
	if( !preg_match('/[&?]blog=/', $page) ) $page = url_add_param( $page, 'blog='.$blog );

	echo $before;
	echo '<a href="', $admin_url, '/', $page, '" title="', $link_title, '">';
	echo $link_text ;
	echo '</a>';
	echo $after;
}


/*
 * user_profile_link(-)
 *
 * Template tag; Provide a link to user profile
 */
function user_profile_link( $before = '', $after = '', $link_text = '', $link_title = '#' )
{
	global $user_login, $pagenow;

	if( ! is_logged_in() ) return false;

	if( $link_text == '' ) $link_text = T_('Profile (%s)');
	if( $link_title == '#' ) $link_title = T_('Edit your profile');

	echo $before;
	echo '<a href="';
	bloginfo( 'blogurl', 'raw' );
	echo '?disp=profile" title="', $link_title, '">';
	printf( $link_text, $user_login );
	echo '</a>';
	echo $after;
}

/**
 * Display "User profile" title if it has been requested
 *
 * {@internal profile_title(-) }}
 *
 * @param string Prefix to be displayed if something is going to be displayed
 * @param mixed Output format, see {@link format_to_output()} or false to
 *								return value instead of displaying it
 */
function profile_title( $prefix = ' ', $display = 'htmlbody' )
{
	global $disp;

	if( $disp == 'profile' )
	{
		$info = $prefix.T_('User profile');
		if ($display)
			echo format_to_output( $info, $display );
		else
			return $info;
	}
}


?>
