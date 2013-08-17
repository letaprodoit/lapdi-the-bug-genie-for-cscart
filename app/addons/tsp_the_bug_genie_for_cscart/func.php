<?php
/*
 * TSP The Bug Genie for CS-Cart Addon
 *
 * @package		TSP The Bug Genie for CS-Cart Addon
 * @filename	func.php
 * @version		2.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Hook loader for addon
 * 
 */


if ( !defined('BOOTSTRAP') )	{ die('Access denied');	}

use Tygh\Registry;

require_once 'lib/fn.the_bug_genie_for_cscart.php';

/***********
 *
 * HOOKS
 *
 ***********/

/***********
 *
 * Delete product metadata
 *
 ***********/
function fn_tsp_the_bug_genie_for_cscart_delete_product_post($product_id)
{
	db_query("DELETE FROM ?:addon_tsp_the_bug_genie_for_cscart_product_metadata WHERE `product_id` = ?i", $product_id);
}//end fn_tsp_the_bug_genie_for_cscart_delete_product_post

/***********
 *
 * Function to update the product metadata
 *
 ***********/
function fn_tsp_the_bug_genie_for_cscart_update_product_post(&$product_data, $product_id, $lang_code, $create)
{

	if (!empty($product_id) && !empty($product_data))
	{
		$field_names = Registry::get('tsptbg_product_data_field_names');
		
		foreach ($field_names as $field_name => $fdata)
		{	
			if (array_key_exists($field_name, $product_data))
			{
				$value = $product_data[$field_name];
				fn_tsptbg_update_product_metadata($product_id, $field_name, $value);
			}//endif		
		}//endforeach

	}//endif
}//end fn_tsp_the_bug_genie_for_cscart_update_product_post

?>