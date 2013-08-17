<?php
/*
 * TSP The Bug Genie for CS-Cart Addon
 *
 * @package		TSP The Bug Genie for CS-Cart Addon
 * @filename	fn.the_bug_genie_for_cscart.php
 * @version		2.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Helper functions for the addon
 * 
 */


if ( !defined('BOOTSTRAP') )	{ die('Access denied');	}

use Tygh\Registry;

/***********
 *
 * [Functions - Addon.xml Handlers]
 *
 ***********/

/***********
 *
 * Function to uninstall languages
 *
 ***********/
function fn_tsptbg_uninstall_languages ()
{
	$names = array(
		'tsp_the_bug_genie_for_cscart',
		'tsptbg_cannot_connect_to_database_server',
		'tsptbg_db_connected',
		'tsptbg_issues_url',
		'tsptbg_project_url',
		'tsptbg_report_url',
		'tsptbg_submit_issue_name',
		'tsptbg_submit_issue_text',
		'tsptbg_submit_issue_type',
		'tsptbg_submit_project',
		'tsptbg_wiki_url',
		'tsptbg_wrong_table_prefix',
	);
	
	if (!empty($names))
	{
		db_query("DELETE FROM ?:language_values WHERE name IN (?a)", $names);
	}//endif
}//end fn_tsptbg_uninstall_languages


/***********
 *
 * Function to uninstall product metadata
 *
 ***********/
function fn_tsptbg_uninstall_product_metadata() 
{
	if (Registry::get('addons.tsp_the_bug_genie_for_cscart.delete_bug_genie_data') == 'Y')
	{
		db_query("DROP TABLE IF EXISTS `?:addon_tsp_the_bug_genie_for_cscart_product_metadata`");
	}//endif
}//end fn_tsptbg_uninstall_product_metadata


/***********
 *
 *
 * [Functions - General]
 *
 *
 ***********/

/***********
 *
 * Function to convevert option numeric keys to text
 *
 ***********/
function fn_tsptbg_convert_options_keys_to_text($product_options)
{
	$readable = array();
	
	foreach ($product_options as $option_id => $value)
	{
	
		$option_description = db_get_field("SELECT `option_name` FROM ?:product_options_descriptions WHERE `option_id` = ?i", $option_id);
		
		if (!empty($option_description))
		{
			$option_value = $value;
			
			// If the value is an integer check to see if there are variants
			if (intval($value))
			{
				$option_value = db_get_field("SELECT `variant_name` FROM ?:product_option_variants_descriptions WHERE `variant_id` = ?i", $value);
				
				// if no variants were found then set the integer value back to value;
				if (empty($option_value))
				{
					$option_value = $value;
				}//endif
				
			}//endif
			
			$readable[$option_description] = $option_value;
		
		}//endif
		
	}//endforeach
	
	return $readable;
}//end fn_tsptbg_convert_options_keys_to_text

/***********
 *
 * Function to parse text given array keys
 *
 ***********/
function fn_tsptbg_parse_text($text, $arr_keys_values)
{
	$parsed_text = $text;
	
	foreach ($arr_keys_values as $key => $value)
	{
	
		$parsed_text = preg_replace("/\{\{$key\}\}/", $value, $parsed_text);
	
	}//endforeach
	
	// Double up on line returns
	$parsed_text = preg_replace("/\n/", "\n\n", $parsed_text);
	
	return $parsed_text;

}//end fn_tsptbg_parse_text

/***********
 *
 * Function to parse text given array keys
 *
 ***********/
function fn_tsptbg_print_array($arr_keys_values)
{
	$text = "";
	
	foreach ($arr_keys_values as $key => $value)
	{
	
		$text .= "$key: $value\n\n";
	
	}//endforeach
	
	return $text;
}//end fn_tsptbg_print_array

/***********
 *
 * Function to get the value for product metadata given the field name
 *
 ***********/
function fn_tsptbg_get_product_field_value($product_id, $field_name)
{
	$value = db_get_field("SELECT `value` FROM `?:addon_tsp_the_bug_genie_for_cscart_product_metadata` WHERE `field_name` = ?s AND `product_id` = ?i", $field_name, $product_id);
	
	return $value;
}//end fn_tsptbg_get_product_field_value

/***********
 *
 * function to get list of product options for the current product
 *
 ***********/
function fn_tsptbg_get_product_input_options()
{	
	$options = array();
	
	$product_id = $_REQUEST['product_id'];
	
	if (!empty($product_id))
	{
	
		// Get unlinked product options - input values only to kep the descriptions unique
		$options_a = db_get_fields("SELECT `option_id` FROM ?:product_options WHERE product_id = ?i AND `option_type` = 'I'",$product_id);
		// Get linked product options
		$options_b = db_get_fields("SELECT ?:product_options.option_id FROM ?:product_options LEFT JOIN ?:product_global_option_links 
			ON ?:product_global_option_links.option_id = ?:product_options.option_id 
			WHERE ?:product_global_option_links.product_id = ?i AND ?:product_options.option_type = 'I'", $product_id);
		
		$options_arr = array_merge($options_a,$options_b);
		
		foreach ($options_arr as $option)
		{
			$option_name = db_get_field("SELECT `option_name` FROM ?:product_options_descriptions WHERE `option_id` = $option");
			$options[$option] = $option_name;
		}//endforeach
		
	}//endif
	
	return $options;
}//end fn_tsptbg_get_product_input_options

/***********
 *
 * Function to update product metadata
 *
 ***********/
function fn_tsptbg_update_product_metadata($product_id, $field_name, $value)
{			
	if (!empty($value))
	{
		$data = array(
			'product_id' => $product_id, 
			'field_name' => $field_name,
			'value' => htmlentities(trim($value))
		);
		db_query("REPLACE INTO ?:addon_tsp_the_bug_genie_for_cscart_product_metadata ?e", $data);
	}//endif
	else 
	{
		// Don't store a bunch of null values in the database, if a field has no value
		// simply delete it from the table
		db_query("DELETE FROM ?:addon_tsp_the_bug_genie_for_cscart_product_metadata WHERE `product_id` = ?i AND `field_name` = ?s", $product_id, $field_name);
	}//endelse
	
}//end fn_tsptbg_update_product_metadata
?>