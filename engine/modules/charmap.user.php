<?php
/*
=============================================
 Name      : MWS Char Map v1.3
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : http://dle.net.tr/
 License   : MIT License
 Date      : 01.02.2018
=============================================
*/

if ( $_REQUEST['subaction'] == "userinfo" ) {

	$in_profile = true;
	$user_profile = $_REQUEST['user'];

	include ENGINE_DIR . "/modules/charmap.php";

	echo $tpl->result['content'];
}

?>