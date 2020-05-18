<?php
/*
 * @package		USPS Libraries 
 * @subpackage	tableD5VHQAB.php.
 * @purpose		Interface to USPSd5 and USPS.org database tables
 * @copyright	Copyright (C) 2015 Joseph P. Gibson. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
defined('_JEXEC') or die;
// jimport('usps.includes.routines');

abstract class JoeFactory{
		
public static $connection;
public static $library;
public static $table;

//*****************************************************************************
public static function getDatabase($db){
	if (! isset(self::$connection[$db["dbname"]])){
		self::$connection[$db["dbname"]] = @mysqli_connect($db["hostname"], $db["username"], $db["password"], $db["dbname"]);
		$err = mysqli_connect_error();
		if (!self::$connection[$db["dbname"]] or $err){
			printf("\nCan't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
			exit(0);
		}
	}
	return self::$connection[$db["dbname"]];
}		
//*****************************************************************************
public static function getLibrary($name,$param=''){
	if (!isset(self::$library[$name])){
	//if (!self::$table[$name]){
		!self::$library[$name] = new $name($param);
	}
	return self::$library[$name];
}
//*****************************************************************************
public static function getTable($name,$db="db_d5"){
	if (!isset(self::$table[$name])){
	//if (!self::$table[$name]){
		!self::$table[$name] = new $name($db, '');
	}
	return self::$table[$name];
}		
}
?>