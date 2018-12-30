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

if ( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if ( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

require_once ENGINE_DIR . "/data/charmap.conf.php";
require_once ROOT_DIR . "/language/" . $config['langs'] . "/charmap.lng";

if ( ! is_writable(ENGINE_DIR . '/data/charmap.conf.php' ) ) {
	$lang['stat_system'] = str_replace( "{file}", "engine/data/charmap.conf.php", $lang['stat_system'] );
	$fail = "<div class=\"alert alert-error\">{$lang['stat_system']}</div>";
} else $fail = "";

if ( $action == "save" ) {
	if ( $member_id['user_group'] != 1 ) { msg( "error", $lang['opt_denied'], $lang['opt_denied'] ); }
	if ( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) { die( "Hacking attempt! User not found" ); }

	$save_con = $_POST['save_con'];
	$save_con['show_charsonmain'] = intval($save_con['show_charsonmain']);
	$save_con['show_charsonpage'] = intval($save_con['show_charsonpage']);
	$save_con['show_allinpage'] = intval($save_con['show_allinpage']);
	$save_con['show_alphaonpage'] = intval($save_con['show_alphaonpage']);
	$save_con['cache'] = intval($save_con['cache']);
	$save_con['minify_cache'] = intval($save_con['minify_cache']);
	$save_con['cache_refnews'] = intval($save_con['cache_refnews']);
	$save_con['cache_timeout'] = intval($save_con['cache_timeout']);
	$save_con['show_pagesonchar'] = intval($save_con['show_pagesonchar']);
	$save_con['show_pagesonmain'] = intval($save_con['show_pagesonmain']);
	$save_con['charpage_limit'] = intval($save_con['charpage_limit']);
	$save_con['mainpage_limit'] = intval($save_con['mainpage_limit']);

	$find = array(); $replace = array();
	$find[] = "'\r'"; $replace[] = "";
	$find[] = "'\n'"; $replace[] = "";

	$save_con = $save_con + $sett;

	if ( count( $save_con['__new___cat'] ) > 0 && ! empty( $save_con['__new___url'] ) && ! empty( $save_con['__new___title'] ) ) {
		$new_name = totranslit( $save_con['__new___url'], true, false );
		$save_con[ $new_name ] = $save_con['__new___cat'];
		$save_con[ $new_name . "_title" ] = $save_con['__new___title'];
		$save_con[ $new_name . "_desc" ] = $save_con['__new___desc'];
		$save_con[ $new_name . "_keyw" ] = $save_con['__new___keyw'];
	}
	unset( $save_con['__new___title'], $save_con['__new___desc'], $save_con['__new___keyw'], $save_con['__new___url'], $save_con['__new___cat'] );

	$handler = fopen( ENGINE_DIR . '/data/charmap.conf.php', "w" );
	ksort( $save_con );
	fwrite( $handler, "<?PHP \n\n//MWS Charmap Settings\n\n\$sett = array (\n" );
	foreach ( $save_con as $name => $value ) {
		$value = ( is_array( $value ) ) ? implode(",", $value ) : $value;
		$value = trim(strip_tags(stripslashes( $value )));
		$value = htmlspecialchars( $value, ENT_QUOTES, $config['charset']);
		$value = preg_replace( $find, $replace, $value );
		$name = trim(strip_tags(stripslashes( $name )));
		$name = htmlspecialchars( $name, ENT_QUOTES, $config['charset'] );
		$name = preg_replace( $find, $replace, $name );
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		$value = str_replace( ".", "", $value );
		$value = str_replace( '/', "", $value );
		$value = str_replace( chr(92), "", $value );
		$value = str_replace( chr(0), "", $value );
		$value = str_replace( '(', "", $value );
		$value = str_replace( ')', "", $value );
		$value = str_ireplace( "base64_decode", "base64_dec&#111;de", $value );
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		$name = str_replace( ".", "", $name );
		$name = str_replace( '/', "", $name );
		$name = str_replace( chr(92), "", $name );
		$name = str_replace( chr(0), "", $name );
		$name = str_replace( '(', "", $name );
		$name = str_replace( ')', "", $name );
		$name = str_ireplace( "base64_decode", "base64_dec&#111;de", $name );
		fwrite( $handler, "'{$name}' => '{$value}',\n" );
	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );

	clear_cache( array( 'news_map_', 'map_' ) );
	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "{$PHP_SELF}?mod=charmap" );

} else if ( $action == "delete" ) {

	if ( $member_id['user_group'] != 1 ) { msg( "error", $lang['opt_denied'], $lang['opt_denied'] ); }
	if ( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) { die( "Hacking attempt! User not found" ); }
	if ( isset( $_REQUEST['page'] ) ) {

		unset( $sett[ $_REQUEST['page'] . '_title'], $sett[ $_REQUEST['page'] . '_desc'], $sett[ $_REQUEST['page'] . '_keyw'], $sett[ $_REQUEST['page'] ] );

		$find = array(); $replace = array();
		$find[] = "'\r'"; $replace[] = "";
		$find[] = "'\n'"; $replace[] = "";
		ksort( $sett );
		$handler = fopen( ENGINE_DIR . '/data/charmap.conf.php', "w" );
		fwrite( $handler, "<?PHP \n\n//MWS Charmap Settings\n\n\$sett = array (\n" );
		foreach ( $sett as $name => $value ) {
			$value = ( is_array( $value ) ) ? implode(",", $value ) : $value;
			$value = trim(strip_tags(stripslashes( $value )));
			$value = htmlspecialchars( $value, ENT_QUOTES, $config['charset']);
			$value = preg_replace( $find, $replace, $value );
			$name = trim(strip_tags(stripslashes( $name )));
			$name = htmlspecialchars( $name, ENT_QUOTES, $config['charset'] );
			$name = preg_replace( $find, $replace, $name );
			$value = str_replace( "$", "&#036;", $value );
			$value = str_replace( "{", "&#123;", $value );
			$value = str_replace( "}", "&#125;", $value );
			$value = str_replace( ".", "", $value );
			$value = str_replace( '/', "", $value );
			$value = str_replace( chr(92), "", $value );
			$value = str_replace( chr(0), "", $value );
			$value = str_replace( '(', "", $value );
			$value = str_replace( ')', "", $value );
			$value = str_ireplace( "base64_decode", "base64_dec&#111;de", $value );
			$name = str_replace( "$", "&#036;", $name );
			$name = str_replace( "{", "&#123;", $name );
			$name = str_replace( "}", "&#125;", $name );
			$name = str_replace( ".", "", $name );
			$name = str_replace( '/', "", $name );
			$name = str_replace( chr(92), "", $name );
			$name = str_replace( chr(0), "", $name );
			$name = str_replace( '(', "", $name );
			$name = str_replace( ')', "", $name );
			$name = str_ireplace( "base64_decode", "base64_dec&#111;de", $name );
			fwrite( $handler, "'{$name}' => '{$value}',\n" );
		}
		fwrite( $handler, ");\n\n?>" );
		fclose( $handler );

		clear_cache( array( 'news_map_', 'map_' ) );
		msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "{$PHP_SELF}?mod=charmap" );

	} else {
		msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "{$PHP_SELF}?mod=charmap" );
	}
}


echoheader( "<i class=\"fa fa-sitemap\"></i> MWS Char Map v1.3", $lang['charmap_1'] );
echo <<< HTML
	<script type="text/javascript">
	$(document).ready( function() {
		$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
	});
	var is_open = false;
	function ShowOrHidePanel( id ) {
		if ( is_open == false ) {
			$("#"+id).slideDown();
			$('.chzn-container').css({'width': '350px'});
			is_open = true;
		} else {
			$("#"+id).slideUp();
			is_open = false;
		}
	}
	function onCategoryChange(obj) {
		var value = $(obj).val();
		if ($.isArray(value)) {} else {}
	}
    </script>
HTML;

function showRow( $title = "", $description = "", $field = "" ) {
	echo "<tr><td class=\"col-xs-6 col-sm-6 col-md-7\"><h6 class=\"media-heading text-semibold\">{$title}</h6><span class=\"text-muted text-size-small hidden-xs\">{$description}</span></td><td class=\"col-xs-6 col-sm-6 col-md-5\">{$field}</td></tr>\n";
}

function showSep( ) {
	echo "<tr><td class=\"col-xs-12\" colspan=\"2\">&nbsp;</td></tr>\n";
}

function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" style=\"min-width:100px;\" name=\"{$name}\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"{$value}\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function makeCheckBox($name, $selected) {
	$selected = $selected ? "checked" : "";
	return "<div class=\"text-center\"><input class=\"switch\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$selected}></div>";
}

echo <<<HTML
{$fail}
<form action="{$PHP_SELF}?mod=charmap&action=save" name="conf" class="systemsettings" id="conf" method="post">
	<div style="display:none" id="addmap">
		<div class="panel panel-flat">
			<div class="panel-heading">
				<b>{$lang['charmap_28']}</b>
				<div class="heading-elements">
					<ul class="icons-list">
						<li>
							<a href="javascript:ShowOrHidePanel('addmap');"><i class="fa fa-map-marker"></i> {$lang['charmap_27']}</a>
						</li>
					</ul>
				</div>
			</div>
			<table class="table table-hover table-normal">
HTML;

	$categories_list = CategoryNewsSelection( 0, 0 );
	showRow( str_replace( "{%page%}", "\"" . $lang['charmap_29'] . "\"", $lang['charmap_24'] ), $lang['charmap_25'], "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"save_con[__new___cat][]\" onchange=\"onCategoryChange(this)\" class=\"categoryselect\" multiple style=\"width:100%;max-width:310px;\">{$categories_list}</select>" );
	showRow( str_replace( "{%page%}", "\"" . $lang['charmap_29'] . "\"", $lang['charmap_2'] ), $lang['charmap_3'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[__new___title]\" value=\"\" size=\"40\">" );
	showRow( str_replace( "{%page%}", "\"" . $lang['charmap_29'] . "\"", $lang['charmap_30'] ), $lang['charmap_31'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[__new___url]\" value=\"\" size=\"40\">" );
	showRow( str_replace( "{%page%}", "\"" . $lang['charmap_29'] . "\"", $lang['charmap_4'] ), $lang['charmap_5'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[__new___desc]\" value=\"\" size=\"40\">" );
	showRow( str_replace( "{%page%}", "\"" . $lang['charmap_29'] . "\"", $lang['charmap_6'] ), $lang['charmap_7'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[__new___keyw]\" value=\"\" size=\"40\">" );

echo <<<HTML
			</table>
			<div class="panel-footer">
				<div class="pull-right">
					<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
					<button class="btn btn-md bg-primary btn-raised"><i class="fa fa-floppy-o position-left"></i>{$lang['charmap_32']}</button>
				</div>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<b>{$lang['charmap_1']}</b>
			<div class="heading-elements">
				<ul class="icons-list">
					<li>
						<a href="{$config['http_home_url']}sitemap.html" target="_blank"><i class="fa fa-sitemap"></i> {$lang['charmap_42']}</a>
					</li>
					<li>
						<a href="javascript:ShowOrHidePanel('addmap');"><i class="fa fa-map-marker"></i> {$lang['charmap_27']}</a>
					</li>
				</ul>
			</div>
		</div>

		<table class="table table-hover table-normal">
HTML;

	$writed = array( 'main_title', 'main_desc', 'main_keyw', 'show_charsonmain', 'show_charsonpage', 'show_alphaonpage', 'show_allinpage', 'cache', 'minify_cache', 'cache_refnews', 'cache_timeout' );
	$page_name = ( $config['allow_alt_url'] ) ? "sitemap.html" : "index.php?do=charmap&name=sitemap";

	showRow( str_replace( "{%page%}", "<a target=\"_blank\" href=\"" . $config['http_home_url'] . $page_name . "\">" . $lang['charmap_26'] . "</a>", $lang['charmap_2'] ), $lang['charmap_3'] . "." . $lang['charmap_41'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[main_title]\" value=\"{$sett['main_title']}\" size=\"40\">" );
	showRow( str_replace( "{%page%}", "<a target=\"_blank\" href=\"" . $config['http_home_url'] . $page_name . "\">" . $lang['charmap_26'] . "</a>", $lang['charmap_4'] ), $lang['charmap_5'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[main_desc]\" value=\"{$sett['main_desc']}\" size=\"40\">" );
	showRow( str_replace( "{%page%}", "<a target=\"_blank\" href=\"" . $config['http_home_url'] . $page_name . "\">" . $lang['charmap_26'] . "</a>", $lang['charmap_6'] ), $lang['charmap_7'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[main_keyw]\" value=\"{$sett['main_keyw']}\" size=\"40\">" );
	showRow( $lang['charmap_8'], $lang['charmap_9'], makeCheckBox( "save_con[show_charsonmain]", "{$sett['show_charsonmain']}" ) );
	showRow( $lang['charmap_10'], $lang['charmap_11'], makeCheckBox( "save_con[show_charsonpage]", "{$sett['show_charsonpage']}" ) );
	showRow( $lang['charmap_12'], $lang['charmap_13'], makeCheckBox( "save_con[show_alphaonpage]", "{$sett['show_alphaonpage']}" ) );
	showRow( $lang['charmap_14'], $lang['charmap_15'], makeCheckBox( "save_con[show_allinpage]", "{$sett['show_allinpage']}" ) );
	showRow( $lang['charmap_16'], $lang['charmap_17'], makeCheckBox( "save_con[cache]", "{$sett['cache']}" ) );
	showRow( $lang['charmap_18'], $lang['charmap_19'], makeCheckBox( "save_con[minify_cache]", "{$sett['minify_cache']}" ) );
	showRow( $lang['charmap_20'], $lang['charmap_21'], makeCheckBox( "save_con[cache_refnews]", "{$sett['cache_refnews']}" ) );
	showRow( $lang['charmap_22'], $lang['charmap_23'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[cache_timeout]\" value=\"{$sett['cache_timeout']}\" size=4>" );
	showRow( $lang['charmap_35'], $lang['charmap_36'], makeCheckBox( "save_con[show_pagesonmain]", "{$sett['show_pagesonmain']}" ) . "<br /><input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[mainpage_limit]\" value=\"{$sett['mainpage_limit']}\" size=\"5\">" );
	showRow( $lang['charmap_37'], $lang['charmap_38'], makeCheckBox( "save_con[show_pagesonchar]", "{$sett['show_pagesonchar']}" ) . "<br /><input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[charpage_limit]\" value=\"{$sett['charpage_limit']}\" size=\"5\">" );
	showRow( $lang['charmap_39'], $lang['charmap_40'], makeCheckBox( "save_con[userpage_nav]", "{$sett['userpage_nav']}" ) . "<br /><input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[userpage_limit]\" value=\"{$sett['userpage_limit']}\" size=\"5\">" );

	$pages = array();
	foreach( $sett as $k_sett => $v_sett ) { if ( ! in_array( $k_sett, $writed ) ) { if ( strpos( $k_sett, "_title" ) ) { $pages[] = substr( $k_sett, 0, -6 ); } } }

	foreach( $pages as $page ) {
		showSep( );
		$categories_list = CategoryNewsSelection( explode(",", $sett[$page] ), 0 );
		$page_link = ( $config['allow_alt_url'] ) ? $config['http_home_url']. $page . ".html" : $config['http_home_url']. "index.php?do=charmap&name=" . $page;
		$delete_link = $PHP_SELF . "?mod=charmap&action=delete&page=" . $page . "&user_hash=" . $dle_login_hash;

		showRow( str_replace( "{%page%}", "\"" . $page . "\"", $lang['charmap_2'] ), $lang['charmap_3'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;width: 75%\" name=\"save_con[" . $page . "_title]\" value=\"" . $sett["{$page}_title"] . "\" size=\"40\">&nbsp;&nbsp;<a href=\"" . $page_link . "\" target=\"_blank\" class=\"tip\" title=\"{$lang['charmap_33']}\"><span class=\"btn btn-sm btn-info\"><i class=\"fa fa-share\"></i></span></a>&nbsp;&nbsp;<a href=\"" . $delete_link . "\" class=\"tip\" title=\"{$lang['charmap_34']}\"><span class=\"btn btn-sm btn-danger\"><i class=\"fa fa-trash\"></i></span></a>" );
		showRow( str_replace( "{%page%}", "\"" . $page . "\"", $lang['charmap_24'] ), $lang['charmap_25'], "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"save_con[" . $page . "][]\" onchange=\"onCategoryChange(this)\" class=\"categoryselect\" multiple style=\"width:100%;max-width:310px;\">{$categories_list}</select>" );
		showRow( str_replace( "{%page%}", "\"" . $page . "\"", $lang['charmap_4'] ), $lang['charmap_5'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[" . $page . "_desc]\" value=\"" . $sett["{$page}_desc"] . "\" size=\"40\">" );
		showRow( str_replace( "{%page%}", "\"" . $page . "\"", $lang['charmap_6'] ), $lang['charmap_7'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[" . $page . "_keyw]\" value=\"" . $sett["{$page}_keyw"] . "\" size=\"40\">" );
	}

echo <<<HTML
		</table>
		<div class="panel-footer">
			<div class="pull-right">
				<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
				<button class="btn bg-teal btn-raised"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
			</div>
		</div>
	</div>
</form>
HTML;

echofooter();
?>