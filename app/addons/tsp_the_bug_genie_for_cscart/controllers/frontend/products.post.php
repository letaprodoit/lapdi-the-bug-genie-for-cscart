<?php
/*
 * TSP The Bug Genie for CS-Cart Addon
 *
 * @package		TSP The Bug Genie for CS-Cart Addon
 * @filename	products.post.php
 * @version		2.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Products post hook for customer area
 * 
 */

if ( !defined('BOOTSTRAP') ) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	return;
}//endif

use Tygh\Registry;

$product_id = $_REQUEST['product_id'];
$params = $_REQUEST;

// View Supplier Products: Show appointment information
if ($mode == 'view' && !empty($product_id))
{
	// Get current product data
	$product_data = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true, false, true, false);
	$product_metadata = db_get_hash_array("SELECT * FROM ?:addon_tsp_the_bug_genie_for_cscart_product_metadata WHERE `product_id` = $product_id", 'field_name');
	
	if (!empty($product_metadata))
	{
		Registry::get('view')->assign('tsptbg_has_data', true); // don't show blank fields in customer area
	}//endif
	
	if (!empty($product_data))
	{					
		$field_names = Registry::get('tsptbg_product_data_field_names');

		$product_addon_fields = array();
		
		foreach ($field_names as $field_name => $fdata)
		{		
			$value = "";
			
			if ($fdata['admin_only'])
			{
				continue; // skip admin only fields
			}//endif
			
			// only display fields that have data
			if (array_key_exists($field_name, $product_metadata))
			{			
				$value = $product_metadata[$field_name]['value'];
				
				if ($fdata['type'] == 'T')
				{
					$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
				}//endif

				if (!empty($fdata['options_func']))
				{
					$fdata['options'] = call_user_func($fdata['options_func']);
				}//endif

				$product_addon_fields[] = array(
					'title' => __($field_name),
					'name' => $field_name,
					'value' => $value,
					'icon' => $fdata['icon'],
					'width' => $fdata['width'],
					'class' => $fdata['class'],
					'type' => $fdata['type'],
					'hint' => $fdata['hint'],
					'options' => $fdata['options'],
					'readonly' => true //Customer will always view this as readonly
				);
			}//endif
		
		}//endforeach
		
		Registry::get('view')->assign('tsptbg_product_addon_fields', $product_addon_fields);
		
	}//endif

}//endif
?>