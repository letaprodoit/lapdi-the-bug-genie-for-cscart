<?php
/*
 * TSP The Bug Genie for CS-Cart Addon
 *
 * @package		TSP The Bug Genie for CS-Cart Addon
 * @filename	TSPExternalDatabaseConnect.class.php
 * @version		1.0.0
 * @author		Sharron Denice, The Software People, LLC on 2013/02/09
 * @copyright	Copyright © 2013 The Software People, LLC (www.thesoftwarepeople.com). All rights reserved
 * @license		APACHE v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @brief		Class for connecting to an external database
 * 
 */


class TSPExternalDatabaseConnect
{
	const CONNECTION_NAME = "the_bug_genie_for_cscart";

	/***********
	 *
	 * Create connection to the external database
	 *
	 ***********/
	public static function initiateExternalDB($data)
	{
		$db_conn = db_initiate(
			$data['db_host'],
			$data['db_user'],
			$data['db_password'],
			$data['db_name'],
			TSPExternalDatabaseConnect::CONNECTION_NAME,
			$data['table_prefix']
		);

		return $db_conn;
	}//end initiateExternalDB
	
	/***********
	 *
	 * Get database connction name
	 *
	 ***********/
	public static function getDatabaseConnectionName($data)
	{
		return TSPExternalDatabaseConnect::CONNECTION_NAME;
	}//end connectToExternalDB

	/***********
	 *
	 * Connect to existing database connetion
	 *
	 ***********/
	public static function connectToExternalDB($data)
	{
		return db_connect_to(TSPExternalDatabaseConnect::CONNECTION_NAME, $data['db_name']);
	}//end connectToExternalDB

	/***********
	 *
	 * Connect to the main store database
	 *
	 ***********/
	public static function connectToInternalDB()
	{
		return db_connect_to_main();
	}//end connectToInternalDB

	/***********
	 *
	 * Test database connection
	 *
	 ***********/
	public static function testDatabaseConnection($data)
	{
		$status = false;

		if (!empty($data['db_host']) && !empty($data['db_user']) && !empty($data['db_name']) && !empty($data['db_password'])) {
			$new_db = TSPExternalDatabaseConnect::initiateExternalDB($data);

			if ($new_db != null) {
				$status = true;
			}
		}

		TSPExternalDatabaseConnect::connectToInternalDB();

		return $status;
	}//end testDatabaseConnection

	/***********
	 *
	 * Test table prefixes
	 *
	 ***********/
	public static function testTablePrefix($data)
	{
		$status = false;
		TSPExternalDatabaseConnect::connectToExternalDB($data);

		$tables = db_get_array("SHOW TABLES LIKE '" . $data['table_prefix'] . "log';");

		if (!empty($tables)) {
			$status = true;
		}

		TSPExternalDatabaseConnect::connectToInternalDB();

		return $status;
	}//end testTablePrefix
}//end TSPExternalDatabaseConnect