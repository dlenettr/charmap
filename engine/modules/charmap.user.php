<?php
/*
=============================================
 Name      : CharMap v1.4
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : https://mehmethanoglu.com.tr
 License   : MIT License
=============================================
*/

if ( $_REQUEST['subaction'] == "userinfo" ) {

	$in_profile = true;
	$user_profile = $_REQUEST['user'];

	include ENGINE_DIR . "/modules/charmap.php";

	echo $tpl->result['content'];
}

?>