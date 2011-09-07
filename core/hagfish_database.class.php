<?php
/**
 * Hagfish - Database manager
 * 
 * A super-simple static class for managing a database connection. Not very
 * elegant, but does the trick. Database connections should be added via the 
 * controller. They can then be retrieved with HagfishDatabase::getConnection.
 * 
 * Copyright (c) 2011 Phil Newton
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *	
 *    1. Redistributions of source code must retain the above copyright 
 *       notice, this list of conditions and the following disclaimer.
 * 	
 *    2. Redistributions in binary form must reproduce the above copyright 
 *       notice, this list of conditions and the following disclaimer in the 
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package    Hagfish
 * @subpackage Core
 * @author     Phil Newton <phil@sodaware.net>
 * @copyright  2011 Phil Newton <phil@sodaware.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @since      File available since Release 1.0.0
 */


class HagfishDatabase
{
	
	protected static $_settings		= array();	/**< Array of connection settings */
	protected static $_connections	= array();	/**< Array of PDO connections */
	
	
	// ----------------------------------------------------------------------
	// -- Running queries
	// ----------------------------------------------------------------------
	
	/**
	 * Run an SQL query and return the result as an array. Mostly used to run
	 * a select query that returns 1 or more rows.
	 * 
	 * @param string $query The SQL query to run. 
	 * @param string $connection Optional connection name.
	 * @return array An array of objects containing results from the query. 
	 */
	public static function query($query, $connection = 'default')
	{
		return HagfishDatabase::getConnection($connection)
			->query($query)->fetchAll(PDO::FETCH_CLASS);
	}
	
	
	// ----------------------------------------------------------------------
	// -- Connecting
	// ----------------------------------------------------------------------
	
	/**
	 * Add configuration details for a new connection, but do not connect.
	 * 
	 * @param string $dbHost The name of the database host. Usually "localhost"
	 * @param string $dbName Name of the database.
	 * @param string $dbUser Username to connect with.
	 * @param string $dbPassword Password to connect with.
	 * @param string $connectionName Optional name for this connection. Defaults to "default".
	 */
	public static function addConnection($dbHost, $dbName, $dbUser, $dbPassword, $connectionName = 'default')
	{	
		self::$_settings[$connectionName] = array(
			'host'		=> $dbHost,
			'name'		=> $dbName,
			'username'	=> $dbUser,
			'password'	=> $dbPassword
		);
	}
	

	/**
	 * Gets the database connection instance. Will connect to the DB if the 
	 * connection does not already exist.
	 * 
	 * @param $connectionName The name of the connection to retrieve.
	 * @return PDO Database connection.
	 */
	public static function getConnection($connectionName = 'default') 
	{
		// Check connection exists
		if (!array_key_exists($connectionName, self::$_settings)) {
			throw new Exception('Not connection information found for ' . $connectionName);
		}
		
		// If not already connected, connect.
		if (!array_key_exists($connectionName, self::$_connections) || self::$_connections[$connectionName] == null) {
			
			$settings = self::$_settings[$connectionName];
			
			self::$_connections[$connectionName] = new PDO(
				sprintf('mysql:dbname=%s;host=%s', $settings['name'], $settings['host']), 
				$settings['username'],
				$settings['password']
			);
			
		}
		
		// Return connection
		return self::$_connections[$connectionName];
	}

}
