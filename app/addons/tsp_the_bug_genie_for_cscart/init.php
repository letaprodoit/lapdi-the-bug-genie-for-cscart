<?php
/*
 * TSP The Bug Genie for CS-Cart Addon
 *
 * @package		TSP The Bug Genie for CS-Cart Addon
 * @filename	init.php
 * @version		2.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Hook register for addon
 * 
 */

if ( !defined('BOOTSTRAP') )	{ die('Access denied');	}

fn_register_hooks(
	'delete_product_post',
	'finish_payment',
	'place_order',
	'update_product_post'
);

?>