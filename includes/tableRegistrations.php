<?php
/*
Copyright (C) March 2013, Joseph P. Gibson.

This program is free software to USPS Members developing software for USPS Squadrons or Districts: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Owner: 	Joseph P. Gibson
		USPS District 5 Webmaster 
*/
//require_once("/home/content/82/7781582/html/libraries/usps/tableAccess.php");
//require_once("c:/users/joe/websites/uspsd5/libraries/usps/tableAccess.php");
//require_once("/web/joomla/libraries/usps/tableAccess.php");
require_once(JPATH_LIBRARIES ."/usps/tableAccess.php");
//*************************************************************************
class tableD5Registrations extends USPStableAccess{
// Routines that utilize data from the events table to display information.  
// Only generated and used on pages that display a list of events
// Called from 'calendar.php' to list district or national events
// Called from 'squad_events' to list all squadron events
// Called from 'booklet routines' to list a variety of events 
//********************************* Public Variables **********************
//********************************* Private Variables *********************
private $squad_no;
public $display_by;
public $mtg_types;
//*************************************************************************
function __construct($db, $caller=''){
global $exc, $jobs, $blob,  $mbr, $sqd;
	// Creates the variables to contain identity of data and tables 
	parent::__construct('d5_registrations', $db, $caller);
	//$this->squad_no=$sqd;
}// constructer
//****************************************************************************

//****************************************************************************
Function getTotals($event_id){
	// Creates a single line report showing Ammounts Total Purchases, Received and Owed 
	$select = "event_id='$event_id'";
	$total_purchases =  $this->sum('total_fee',$select);
	//$select = "event_id='$event_id' and paid='1' ";
	$select = "event_id='$event_id' ";
	$total_received = $this->sum('amount_received',$select);
	$select = "event_id='$event_id' and paid='0' ";
	//$total_pending = $this->sum('total_fee',$se);
	$total_pending = ($total_purchases - $total_received);  //
	return 'Conference Income: $ '.$total_purchases.',  Funds Received: $ '.$total_received.', Receivables: $ '.$total_pending ;
}
//****************************************************************************



}// class
?>