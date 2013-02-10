<?php
/*
 * TSP The Bug Genie for CS-Cart Addon
 *
 * @package		TSP The Bug Genie for CS-Cart Addon
 * @filename	addons.post.php
 * @version		1.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Addons post hook for admin area
 * 
 */


if ( !defined('AREA') )	{ die('Access denied');	}

if ($_SERVER['REQUEST_METHOD'] == 'GET' and $mode == 'update' and $_REQUEST['addon'] == 'tsp_the_bug_genie_for_cscart')
{

	if (fn_tsptbg_open_connection())
	{
		//fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('tsptbg_db_connected'));
		fn_tsptbg_close_connection();
	}//endif 
	else 
	{
		fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('tsptbg_cannot_connect_to_database_server'));
	}//endelse
}//endif

?>