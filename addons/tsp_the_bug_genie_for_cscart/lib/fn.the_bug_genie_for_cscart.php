<?php
/*
 * TSP The Bug Genie for CS-Cart Addon
 *
 * @package		TSP The Bug Genie for CS-Cart Addon
 * @filename	fn.the_bug_genie_for_cscart.php
 * @version		1.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Helper functions for the addon
 * 
 */


if ( !defined('AREA') )	{ die('Access denied');	}

require_once 'TSPExternalDatabaseConnect.class.php';

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
 * Function to get the active projects from The Bug Genie
 *
 ***********/
function fn_tsptbg_get_bug_genie_projects()
{
	$data = array();	
	
	if (fn_tsptbg_open_connection())
	{
		$scope = Registry::get('addons.tsp_the_bug_genie_for_cscart.scope');
		$data_hash = db_get_hash_array("SELECT `id`,`name` FROM ?:projects WHERE `scope` = ?i AND `deleted` = 0 AND `locked` = 0 AND `archived` = 0",'id',$scope);
		
		foreach ($data_hash as $k => $v)
		{
			$data[$k] = $v['name'];
			
		}//endforeach
		
		fn_tsptbg_close_connection();
	}//endif

	return $data;

}//end fn_tsptbg_get_bug_genie_projects

/***********
 *
 * Function to get the issue types from The Bug Genie
 *
 ***********/
function fn_tsptbg_get_bug_genie_issue_types()
{
	$data = array();
		
	if (fn_tsptbg_open_connection())
	{
		$scope = Registry::get('addons.tsp_the_bug_genie_for_cscart.scope');
		$data_hash = db_get_hash_array("SELECT `id`,`name` FROM ?:issuetypes WHERE `scope` = ?i", 'id', $scope);
		
		foreach ($data_hash as $k => $v)
		{
			$data[$k] = $v['name'];
			
		}//endforeach
		
		fn_tsptbg_close_connection();
	
	}//endif

	return $data;
	
}//end fn_tsptbg_get_bug_genie_issue_types

/***********
 *
 * Function to transfer The Bug Genie issue to the external database
 * if any products found that should be submitted
 *
 ***********/
function fn_tsptbg_transfer_issue($order_id, $user_id = null)
{
	$order_info = fn_get_order_info($order_id);
	
	if (!empty($order_info))
	{
		if (empty($user_id)) $user_id = $order_info['user_id']; // Get user id if null
				
		$db_data = Registry::get('addons.tsp_the_bug_genie_for_cscart');
		$user_info = fn_get_user_info($user_id, false);
		$scope = Registry::get('addons.tsp_the_bug_genie_for_cscart.scope');
		
		$products = $order_info['items'];
		
		// Loop through products
		foreach ($products as $k => $product)
		{
		
			$product_id = $product['product_id'];
			
			$issue_data = array();
			$user_data = array();
			
			// Determine if this product should be submitted to The Bug Genie
			$issue_data['project_id'] = fn_tsptbg_get_product_field_value($product_id, 'tsptbg_submit_project');
			
			// If the product data should be submitted to TBG as a new issue
			// in the project then continue
			if (!empty($issue_data['project_id']))
			{
				// Get issue type should not be null
				$issue_data['issuetype'] = fn_tsptbg_get_product_field_value($product_id, 'tsptbg_submit_issue_type');
				
				// Get scope
				$issue_data['scope'] = $scope;
				
				// Get timestamp
				$issue_data['posted'] = time();
				$issue_data['last_updated'] = $issue_data['posted'];

				// Get the issue name
				$name_option_id = fn_tsptbg_get_product_field_value($product_id, 'tsptbg_submit_issue_name');
				if (!empty($name_option_id))
				{
					$issue_data['name'] = $product['extra']['product_options'][$name_option_id]; // issue names are ALWAYS input values
				}//endif
				else 
				{
					$issue_data['name'] = "New Issue Submitted by " . $user_info['lastname'] . ", ". $user_info['firstname'];
				}//endelse
				
				$readable_data = fn_tsptbg_convert_options_keys_to_text($product['extra']['product_options']);
									
				// Get and parse the issue description
				$description = fn_tsptbg_get_product_field_value($product_id, 'tsptbg_submit_issue_text');	
				if (!empty($description))
				{
					$issue_data['description'] = fn_tsptbg_parse_text($description, $readable_data); // parse using user template
				}//endif
				else 
				{
					$issue_data['description'] = fn_tsptbg_print_array($readable_data); // display values

				}//endelse
				
				// Prepare to connect to The Bug Genie database
				if (fn_tsptbg_open_connection())
				{
					$user_id = 1; //default id is admin
					
					// If we are adding the user into the database
					if (Registry::get('addons.tsp_the_bug_genie_for_cscart.add_user') == 'Y')
					{
						$user_exists = db_get_field("SELECT `id` FROM ?:users WHERE `email` = ?s", $user_info['email']);
						
						// if the user doesn't exist add them
						if (empty($user_exists))
						{
						
							$salt = db_get_field("SELECT `value` FROM ?:settings WHERE `name` = 'salt' AND `module` = 'core'");
							$raw_password = fn_generate_password(8);
							$salted_password = crypt($raw_password, '$2a$07$'.$salt.'$');
							
							// store the passwords (we will need to show this in the notification email)
							$user_info['raw_password'] = $raw_password;
							$user_info['password'] = $salted_password;
							
							$user_data = array(
								'email' => $user_info['email'],
								'username' => $user_info['user_login'],
								'realname' => $user_info['firstname'] . " " . $user_info['lastname'],
								'buddyname' => $user_info['firstname'] . " ". $user_info['lastname'],
								'password' => $salted_password,
								'private_email' => 1,
								'language' => 'sys',
								'use_gravatar' => 1,
								'joined' => time(),
								'activated' => 1,
								'enabled' => 1,
								'deleted' => 0,
							);

							$user_id = db_query("INSERT INTO ?:users ?e", $user_data);
							
							// if the user was added update the user scope
							if (!empty($user_id))
							{
								$user_scope = array(
									'confirmed' => 1,
									'user_id' => $user_id,
									'group_id' => Registry::get('addons.tsp_the_bug_genie_for_cscart.user_group'),
									'scope' => $scope
								);
								db_query("INSERT INTO ?:userscopes ?e", $user_scope);
							}//endif

						}//endif							
						
					}//endif
					
					// If we have an issue
					if (!empty($issue_data))
					{
						// Issue numbers are incremented based on the project they are in, issues have an ID number that is auto-generated
						// and then there is an issue number that is incremented based on the project its associated with
						// the issue number will need to be incremented
						$previous_issue_no = db_get_field("SELECT MAX(issue_no) FROM ?:issues WHERE `project_id` = ?i", $issue_data['project_id']);
						
						$issue_data['issue_no'] = intval($previous_issue_no) + 1;
						$issue_data['posted_by'] = $user_id;
						$issue_data['assignee_user'] = Registry::get('addons.tsp_the_bug_genie_for_cscart.assignee_user');
						$issue_data['assignee_team'] = Registry::get('addons.tsp_the_bug_genie_for_cscart.assignee_team');
						$issue_data['status'] = Registry::get('addons.tsp_the_bug_genie_for_cscart.status');
						$issue_data['deleted'] = 0;
						$issue_data['blocking'] = 0;
						$issue_data['locked'] = 0;
						$issue_data['user_pain'] = 0;
						$issue_data['reproduction_steps'] = "";
						
						$issue_id = db_query("INSERT INTO ?:issues ?e", $issue_data);
					}//endif
					
				}//endif

				fn_tsptbg_close_connection();
				
			}//endif
						
		}//endforeach
	
	}//endif

}//end fn_tsptbg_transfer_issue

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

	
/***********
 *
 * Function to open the external database connection
 *
 ***********/
function fn_tsptbg_open_connection()
{	
	$connected = true;
	$db_test_passed = true;
	$table_test_passed = true;
	$initialized = Registry::get('runtime.dbs.' . TSPExternalDatabaseConnect::getDatabaseConnectionName());
	
	$db_data = Registry::get('addons.tsp_the_bug_genie_for_cscart');

	//If the database has never been initalized run some tests
	if (empty($initialized))
	{
		// If we are not able to connect to the database
		if (!TSPExternalDatabaseConnect::testDatabaseConnection($db_data))
		{
			$db_test_passed = false;
		}
		// … and not able to list database tables
		elseif (!TSPExternalDatabaseConnect::testTablePrefix($db_data))
		{
			$table_test_passed = false;
		}//endif
	}//enif
	
	if ($db_test_passed && $table_test_passed)
	{
		TSPExternalDatabaseConnect::connectToExternalDB($db_data);
	}//endif
	
	return $connected;
}//end fn_tsptbg_open_connection

/***********
 *
 * Function to close the external database connection and connect
 * to the original database
 *
 ***********/
function fn_tsptbg_close_connection()
{
	TSPExternalDatabaseConnect::connectToInternalDB();
}//end fn_tsptbg_close_connection
?>