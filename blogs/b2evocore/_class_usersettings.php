<?php
/**
 * This file implements the UserSettings class, to handle user/name/value triplets. {{{
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * {@internal
 * Daniel HAHLER grants Fran�ois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER.
 *
 * @version $Id$ }}}
 *
 */
if( !defined('DB_USER') ) die( 'Please, do not access this page directly.' );

/**
 * Includes
 */
require_once( dirname(__FILE__).'/_class_abstractsettings.php' );

/**
 * Class to handle the global settings
 *
 * @package evocore
 */
class UserSettings extends AbstractSettings
{
	/**
	 * Constructor
	 *
	 * loads settings, checks db_version
	 */
	function UserSettings()
	{ // constructor
		$this->dbtablename = 'T_usersettings';
		$this->colkeynames = array( 'uset_user_ID', 'uset_name' );
		$this->colvaluename = 'uset_value';

		parent::AbstractSettings();
	}


	/**
	 * get a setting from the DB settings table
	 * @param string name of setting
	 * @param integer User ID (by default $current_User->ID will be used)
	 */
	function get( $setting, $user = '#' )
	{
		global $current_User;
		if( $user == '#' )
			return parent::get( $current_User->ID, $setting );
		else
			return parent::get( $user, $setting );
	}


	/**
	 * temporarily sets a setting (updateDB(-) writes it to DB)
	 *
	 * @param string name of setting
	 * @param mixed new value
	 * @param integer User ID (by default $current_User->ID will be used)
	 */
	function set( $setting, $value, $user = '#' )
	{
		global $current_User;
		if( $user == '#' )
			return parent::set( $current_User->ID, $setting, $value );
		else
			return parent::set( $user, $setting, $value );
	}
}

/*
 * $Log$
 * Revision 1.7  2004/10/12 17:22:29  fplanque
 * Edited code documentation.
 *
 */
?>