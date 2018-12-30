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

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die("Hacking attempt!");
}

define( 'DEBUG', false );

require_once ENGINE_DIR . "/data/charmap.conf.php";
require_once ROOT_DIR . "/language/" . $config['langs'] . "/charmap.lng";

if ( $config['version_id'] < "10.3" ) {
	$config['allow_alt_url'] = ( $config['allow_alt_url'] == "yes" ) ? "1" : "0";
	$config['allow_multi_category'] = ( $config['allow_multi_category'] == "yes" ) ? "1" : "0";
	$config['allow_links'] = ( $config['allow_links'] == "yes" ) ? "1" : "0";
}

$name = ( isset( $_REQUEST['name'] ) ) ? @$db->safesql( trim( totranslit( $_REQUEST['name'], true, false ) ) ) : false;
$char = '';
$page = '1';
$user = false;
$tpl_file = "charmap.tpl";
$show_nav = true;
$xfield = true;

if ( isset( $_REQUEST['args'] ) ) {

	$args = @$db->safesql( trim( $_REQUEST['args'] ) );
	if ( preg_match( "#/page([0-9]+){1}#", $args, $matches ) ) { $page = $matches[1]; $args = str_replace( "/page" . $page, "", $args ); }
	if ( preg_match( "#/u_([^/]*)#is", $args, $matches ) ) { $user = $matches[1]; $args = str_replace( "/u_" . $user, "", $args ); }
	if ( preg_match( "#/(\w+){1}#is", $args, $matches ) ) { $char = $matches[1];}

}

if ( isset( $in_profile ) && $in_profile ) {
	$tpl_file = "charmap_user.tpl";
	$user = $db->safesql( $user_profile );
	$name = "sitemap";
}

if ( DEBUG ) {
	echo "<pre>";
	echo "args: " . $args . "<br />";
	echo "user: " . $user . "<br />";
	echo "name: " . $name . "<br />";
	echo "char: " . $char . "<br />";
	echo "page: " . $page . "<br />";
	echo "</pre>";
}

if ( $sett['show_allinpage'] ) $sett['sitemap'] = "";

function minify_cache( $html ) {
	global $sett;
	if ( $sett['minify_cache'] ) {
		$search = array( '/ {2,}/', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s', '/<!--.*?-->/', '/>\s</' );
		$replace = array( ' ', '', '', '><' );
		return preg_replace( $search, $replace, $html );
	} else {
		return $html;
	}
}

if ( $name ) {

	if ( in_array( $name, array_keys( $sett ) ) ) {

		require_once ENGINE_DIR . "/api/api.class.php";

		$cat_ids = explode( ",", $db->safesql( $sett[ $name ] ) );
		if ( $config['allow_multi_category'] ) {
			$where_cat = " AND p.category regexp '[[:<:]](" . implode ( '|', $cat_ids ) . ")[[:>:]]'";
		} else {
			$where_cat = " AND p.category IN ('" . implode ( "','", $cat_ids ) . "')";
		}
		if ( $in_profile ) $where_cat = "";

		if ( $user ) {
			$user_link = "/u_" . $db->safesql( $user );
			$where_user = " AND p.autor = '" . $db->safesql( $user ) . "'";
		} else {
			$user_link = "";
			$where_user = "";
		}

		if ( $char ) {
			$_page['current'] = ( $page == 0 ) ? 1 : $page;
			if ( $page > 1 && $sett['show_pagesonchar'] ) {
				$show_pages = true; $sett['charpage_limit'] = intval( $sett['charpage_limit'] );
			} else {
				$show_pages = false;
			}
		} else {
			$_page['current'] = ( $page == 0 ) ? 1 : $page;
			if ( $page > 1 && $sett['show_pagesonmain'] ) {
				$show_pages = true; $sett['mainpage_limit'] = intval( $sett['mainpage_limit'] );
			} else {
				$show_pages = false;
			}
		}

		$cache_text = false;
		if ( $sett['cache'] ) {
			$cache_prefix = ( $sett['cache_refnews'] ) ? "news_" : "";
			$cache_name = $cache_prefix . "map_" . $char . "_" . md5( $name . $_page['current'] . $_page['total'] . $where_cat . $where_user . $config['skin'] );
			$cache_timeout = intval( $sett['cache_timeout'] ) * 3600;
			$cache_text = $dle_api->load_from_cache ( $cache_name, $timeout = $cache_timeout );
		}
		$in_cache = ( $cache_text ) ? true : false;

		if ( ! $in_profile ) {
			$metatags['title'] = $sett[ $name . "_title" ];
			$metatags['description'] = $sett[ $name . "_desc" ];
			$metatags['keywords'] = $sett[ $name . "_keyw" ];
			$metatags['header_title'] = $sett[ $name . "_title" ];

			if ( $name == "sitemap" ) {
				$metatags['title'] = $sett[ "main_title" ];
				$metatags['description'] = $sett[ "main_desc" ];
				$metatags['keywords'] = $sett[ "main_keyw" ];
				$metatags['header_title'] = $sett[ "main_title" ];
				$where_cat = "";
			}
			$metatags['title'] = str_replace( "%user%", $db->safesql( $user ), $metatags['title'] );
			$metatags['header_title'] = str_replace( "%user%", $db->safesql( $user ), $metatags['header_title'] );
			$metatags['description'] = str_replace( "%user%", $db->safesql( $user ), $metatags['description'] );
		}


		if ( $in_cache ) {

			$tpl->result['content'] = $cache_text;

		} else {

			if ( $char ) {

				if ( $sett['show_pagesonchar'] ) {
					$count = $db->super_query( "SELECT COUNT(p.id) as total FROM " . PREFIX . "_post p WHERE LCASE( SUBSTR( TRIM( LEADING '{' FROM TRIM( LEADING '[' FROM TRIM( LEADING '(' FROM p.title ) ) ), 1, 1 ) ) = '{$char}' AND p.approve=1{$where_cat}{$where_user} ORDER BY p.title ASC" );
					$_page['items'] = $count['total'];
					$_page['total'] = ceil( $count['total'] / $sett['charpage_limit'] );
					if ( ( $_page['current'] * $sett['charpage_limit'] ) <= ( $_page['total'] * $sett['charpage_limit'] ) ) {
						$limit_0 = ( $_page['current'] - 1 ) * $sett['charpage_limit'];
					} else {
						$page_out = true;
						$limit_0 = ( $_page['total'] - 1 ) * $sett['charpage_limit'];
					}
					$limit = " LIMIT {$limit_0},{$sett['charpage_limit']}";
				} else {
					$limit = "";
				}

				if ( $page_out ) {
					@header( "HTTP/1.0 404 Not Found" );
					msgbox( $lang['all_err_1'], "<ul><li>ID: #14 " . $lang['charmap_err14'] . "</li></ul>" );
				} else {

					if ( $sett['show_pagesonchar'] ) {
						$tpl->load_template( "charmap_nav.tpl" );
						$nav_template = $tpl->copy_template; $tpl->clear();
					}

					$tpl->load_template( $tpl_file );
					if ( $sett['show_charsonpage'] ) {
						$char_table = "";
						preg_match( "'\\[charlist\\](.*?)\\[/charlist\\]'si", $tpl->copy_template, $t_charlist ); $t_charlist = $t_charlist[1];
						preg_match( "'\\[char\\](.*?)\\[/char\\]'si", $tpl->copy_template, $t_char ); $t_char = $t_char[1];
						$charlist = array_merge( range('0', '9'), range('A', 'Z') );
						foreach( $charlist as $alpha ) {
							$char_link = ( $config['allow_alt_url'] ) ? $config['http_home_url']. $name . "/" . $alpha . $user_link . ".html" : $config['http_home_url']. "index.php?do=charmap&name=" . $name . "&args=/" . $alpha . $user_link;
							$char_table .= str_replace( array( "{char}", "{char-link}" ), array( ucfirst( $alpha ), strtolower( $char_link ) ), $t_char );
						}
						$tpl->set_block( "'\\[char\\](.*?)\\[/char\\]'si", $char_table );
						$tpl->set_block( "'\\[charlist\\](.*?)\\[/charlist\\]'si", "$1" );
					} else {
						$tpl->set_block( "'\\[charlist\\](.*?)\\[/charlist\\]'si", "" );
					}

					$content_table = "";
					preg_match( "'\\[contentlist\\](.*?)\\[/contentlist\\]'si", $tpl->copy_template, $t_contlist ); $t_contlist = $t_contlist[1];
					preg_match( "'\\[content\\](.*?)\\[/content\\]'si", $tpl->copy_template, $t_cont ); $t_cont = $t_cont[1];

					$xf_sql = ( $xfield ) ? ", p.xfields" : "";
					$db->query( "SELECT p.id, p.date, p.title, p.category, p.alt_name{$xf_sql} FROM " . PREFIX . "_post p WHERE LCASE( SUBSTR( TRIM( LEADING '{' FROM TRIM( LEADING '[' FROM TRIM( LEADING '(' FROM p.title ) ) ), 1, 1 ) ) = '{$char}' AND p.approve=1{$where_cat}{$where_user} ORDER BY p.title ASC{$limit}" );

					$content_count = 0;
					while ( $row = $db->get_row() ) {
						$row['category'] = intval( $row['category'] );
						if ( $config['allow_alt_url'] ) {
							if ( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
								if ( $row['category'] and $config['seo_type'] == 2 ) {
									$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
								} else {
									$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
								}
							} else {
								$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
							}
						} else {
							$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
						}
						if ( function_exists( 'mb_substr ' ) ) {
							$curr_char = mb_substr( totranslit( stripslashes( trim( $row['title'], "{[(" ) ), true, false ), 0, 1, 'UTF-8' );
						} else {
							$curr_char = substr( totranslit( stripslashes( trim( $row['title'], "{[(" ) ), true, false ), 0, 1 );
						}
						$curr_char_link = ( $config['allow_alt_url'] ) ? $config['http_home_url']. $name . "/" . $curr_char . $user_link . ".html" : $config['http_home_url']. "index.php?do=charmap&name=" . $name . "&args=/" . $curr_char . $user_link;
						if ( $sett['show_alphaonpage'] ) {
							$t_contc = str_replace( array( "{alpha}", "{alpha-link}" ), array( ucfirst( $curr_char ), $curr_char_link ), $t_cont );
							$t_contc = preg_replace( "'\\[alpha\\](.*?)\\[/alpha\\]'si", "\\1", $t_contc );
							$sett['show_alphaonpage'] = "0";
						} else { $t_contc = preg_replace( "'\\[alpha\\](.*?)\\[/alpha\\]'si", "", $t_cont ); }


						if ( $xfield ) {
							$xfields = xfieldsload();
							$xfieldsdata = xfieldsdataload( $row['xfields'] );
							foreach ( $xfields as $value ) {
								$preg_safe_name = preg_quote( $value[0], "'" );
								if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
									$temp_array = explode( ",", $xfieldsdata[$value[0]] );
									$value3 = array();
									foreach ($temp_array as $value2) {
										$value2 = trim($value2);
										$value2 = str_replace("&#039;", "'", $value2);
										if( $config['allow_alt_url'] ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" . urlencode( $value2 ) . "/\">" . $value2 . "</a>";
										else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xf=" . urlencode( $value2 ) . "\">" . $value2 . "</a>";
									}
									$xfieldsdata[$value[0]] = implode(", ", $value3);
									unset($temp_array);
									unset($value2);
									unset($value3);
								}
								if( empty( $xfieldsdata[$value[0]] ) ) {
									$t_contc = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $t_contc );
									$t_contc = str_replace( "[xfnotgiven_{$value[0]}]", "", $t_contc );
									$t_contc = str_replace( "[/xfnotgiven_{$value[0]}]", "", $t_contc );
								} else {
									$t_contc = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $t_contc );
									$t_contc = str_replace( "[xfgiven_{$value[0]}]", "", $t_contc );
									$t_contc = str_replace( "[/xfgiven_{$value[0]}]", "", $t_contc );
								}
								$xfieldsdata[$value[0]] = stripslashes( $xfieldsdata[$value[0]] );
								if ( $config['allow_links'] AND $value[3] == "textarea" AND function_exists('replace_links') ) $xfieldsdata[$value[0]] = replace_links( $xfieldsdata[$value[0]], $replace_links['news'] );
								$t_contc = str_replace( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $t_contc );
							}
						}

						$content_table .= str_replace( array( "{title}", "{full-link}" ), array( stripslashes( $row['title'] ), $full_link ), $t_contc );

						$content_count++;
					}
					$db->free();

					if ( $sett['show_pagesonchar'] ) {
						$prev_pages = "";
						$next_pages = "";
						$prev_link = "";
						$next_link = "";
						$curr_page = "<span>" . $_page['current'] ."</span>";

						$user_link = "/" . $char . $user_link;

						if ( $_page['total'] > 10 ) {
							if ( $_page['current'] > 4 ) {
								$prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page1" . $user_link . ".html\">1</a><a href=\"" . $config['http_home_url'] . $name . "/page2" . $user_link . ".html\">2</a><span>..</span><span>..</span>";
								for ( $curr = $_page['current'] - 2; $curr <= $_page['current'] - 1; $curr++ ) { $prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							} else {
								for ( $curr = 1; $curr <= $_page['current'] - 1; $curr++ ) { $prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							}
							if ( $_page['current'] > ( $_page['total'] - 4 ) ) {
								for ( $curr = $_page['current'] + 1; $curr <= $_page['total']; $curr++ ) { $next_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							} else {
								for ( $curr = $_page['current'] + 1; $curr <= $_page['current'] + 2; $curr++ ) { $next_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
								$next_pages .= "<span>..</span><span>..</span><a href=\"" . $config['http_home_url'] . $name . "/page" . ($_page['total'] - 1) . $user_link . ".html\">" . ($_page['total'] - 1) . "</a><a href=\"" . $config['http_home_url'] . $name . "/page" . ($_page['total']) . $user_link . ".html\">" . ($_page['total']) . "</a>";
							}
						} else {
							for ( $curr = 1; $curr < $_page['current']; $curr++ ) { $prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							for ( $curr = $_page['current'] + 1; $curr <= $_page['total']; $curr++ ) { $next_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							if ( ! empty( $prev_pages ) ) { $prev_link = "<a href=\"" . $config['http_home_url'] . $name . "/page" . ( $_page['current'] - 1 ) . $user_link . ".html\">"; }
							if ( ! empty( $next_pages ) ) { $next_link = "<a href=\"" . $config['http_home_url'] . $name . "/page" . ( $_page['current'] + 1 ) . $user_link . ".html\">"; }
						}

						$nav_template = str_replace( "{pages}", $prev_pages . $curr_page . $next_pages, $nav_template );

						if ( ! empty( $prev_link ) ) {
							$nav_template = str_replace( "[prev-link]", $prev_link, $nav_template );
							$nav_template = str_replace( "[/prev-link]", "</a>", $nav_template );
						} else {
							$nav_template = preg_replace( "'\[prev-link\](.*?)\[/prev-link\]'si", "", $nav_template );
						}
						if ( ! empty( $next_link ) ) {
							$nav_template = str_replace( "[next-link]", $next_link, $nav_template );
							$nav_template = str_replace( "[/next-link]", "</a>", $nav_template );
						} else {
							$nav_template = preg_replace( "'\[next-link\](.*?)\[/next-link\]'si", "", $nav_template );
						}
						if( ! $config['allow_alt_url'] ) {
							$nav_template = preg_replace( "#{$name}\/{$char}\/page([0-9]+)\.html#", "index.php?do=charmap&name={$name}&args=/{$char}/page$1", $nav_template );
						}
						$tpl->set( "{navigator}", $nav_template );

					} else { $tpl->set( "{navigator}", "" ); }

					$tpl->set( "{total}", $content_count );
					$tpl->set( "{total-items}", $_page['items'] );
					$tpl->set( "{page-current}", $_page['current'] );
					$tpl->set( "{page-total}", $_page['total'] );

					$tpl->set_block( "'\\[content\\](.*?)\\[/content\\]'si", $content_table );
					$tpl->set_block( "'\\[contentlist\\](.*?)\\[/contentlist\\]'si", "$1" );

					$tpl->set_block( "'\\[on-char\\](.*?)\\[/on-char\\]'si", "$1" );
					$tpl->set_block( "'\\[on-main\\](.*?)\\[/on-main\\]'si", "" );
					$tpl->set_block( "'\\[on-map\\](.*?)\\[/on-map\\]'si", "" );
					if ( $user ) { $tpl->set_block( "'\\[on-user\\](.*?)\\[/on-user\\]'si", "$1" ); }
					else { $tpl->set_block( "'\\[on-user\\](.*?)\\[/on-user\\]'si", "" ); }
					$tpl->set( "{title}", $sett[ $name . '_title'] );
					$tpl->set( "{description}", $sett[ $name . '_desc'] );
					$tpl->set( "{url}", ( $config['allow_alt_url'] ) ? $config['http_home_url'] . $name . "/" . $char . $user_link . ".html" : $config['http_home_url'] . "index.php?do=charmap&name=" . $name . "&args=/" . $char . $user_link );
					$tpl->set( "{user}", $db->safesql( $user ) );
					$tpl->set( "{user-link}", ( $config['allow_alt_url'] ) ? $config['http_home_url']. "user/" . $db->safesql( $user ) : $config['http_home_url'] . "index.php?subaction=userinfo&user=" . $db->safesql( $user ) );

				}

			} else {

				$sett['ignore_chars'] = "{[(";

				if ( $in_profile && $sett['userpage_limit'] != 0 ) {
					$sett['show_pagesonmain'] = true;
					$sett['mainpage_limit'] = intval( $sett['userpage_limit'] );
					$show_nav = ( $sett['userpage_nav'] ) ? true : false;
				}

				if ( $sett['show_pagesonmain'] ) {

					$count = $db->super_query( "SELECT COUNT(p.id) as total FROM " . PREFIX . "_post p WHERE p.approve=1{$where_cat}{$where_user}" );
					$_page['items'] = $count['total'];
					$_page['total'] = ceil( $count['total'] / $sett['mainpage_limit'] );
					if ( ( $_page['current'] * $sett['mainpage_limit'] ) <= ( $_page['total'] * $sett['mainpage_limit'] ) ) {
						$limit_0 = ( $_page['current'] - 1 ) * $sett['mainpage_limit'];
					} else {
						$page_out = true;
						$limit_0 = ( $_page['total'] - 1 ) * $sett['mainpage_limit'];
					}
					$limit = " LIMIT {$limit_0},{$sett['mainpage_limit']}";
				} else {
					$limit = "";
				}

				if ( $page_out ) {

					@header( "HTTP/1.0 404 Not Found" );
					msgbox( $lang['all_err_1'], "<ul><li>ID: #14 " . $lang['charmap_err14'] . "</li></ul>" );

				} else {

					if ( $sett['show_pagesonmain'] ) {
						$tpl->load_template( "charmap_nav.tpl" );
						$nav_template = $tpl->copy_template; $tpl->clear();
					}

					$tpl->load_template( $tpl_file );

					if ( $sett['show_charsonmain'] ) {
						$char_table = "";
						preg_match( "'\\[charlist\\](.*?)\\[/charlist\\]'si", $tpl->copy_template, $t_charlist ); $t_charlist = $t_charlist[1];
						preg_match( "'\\[char\\](.*?)\\[/char\\]'si", $tpl->copy_template, $t_char ); $t_char = $t_char[1];
						$charlist = array_merge( range('0', '9'), range('A', 'Z') );
						foreach( $charlist as $alpha ) {
							$alpha_link = ( $config['allow_alt_url'] ) ? $config['http_home_url']. $name . "/" . $alpha . $user_link . ".html" : $config['http_home_url']. "index.php?do=charmap&name=" . $name . "&args=/" . $alpha . $user_link;
							$char_table .= str_replace( array( "{char}", "{char-link}" ), array( ucfirst( $alpha ), strtolower( $alpha_link ) ), $t_char );
						}
						$tpl->set_block( "'\\[char\\](.*?)\\[/char\\]'si", $char_table );
						$tpl->set_block( "'\\[charlist\\](.*?)\\[/charlist\\]'si", "$1" );
					} else {
						$tpl->set_block( "'\\[charlist\\](.*?)\\[/charlist\\]'si", "" );
					}

					$content_table = "";
					preg_match( "'\\[contentlist\\](.*?)\\[/contentlist\\]'si", $tpl->copy_template, $t_contlist ); $t_contlist = $t_contlist[1];
					preg_match( "'\\[content\\](.*?)\\[/content\\]'si", $tpl->copy_template, $t_cont ); $t_cont = $t_cont[1];

					$xf_sql = ( $xfield ) ? ", p.xfields" : "";
					$db->query( "SELECT p.id, p.date, p.title, p.category, p.alt_name{$xf_sql} FROM " . PREFIX . "_post p WHERE p.approve='1'{$where_cat}{$where_user} ORDER BY p.title ASC{$limit}" );

					$content_count = 0;
					$last_char = "";
					while ( $row = $db->get_row() ) {
						$row['category'] = intval( $row['category'] );
						if ( $config['allow_alt_url'] ) {
							if ( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
								if ( $row['category'] and $config['seo_type'] == 2 ) {
									$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
								} else {
									$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
								}
							} else {
								$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
							}
						} else {
							$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
						}

						if ( function_exists( 'mb_substr') ) {
							$curr_char = mb_substr( totranslit( stripslashes( trim( $row['title'], $sett['ignore_chars'] ) ), true, false ), 0, 1, 'UTF-8' );
						} else {
							$curr_char = substr( totranslit( stripslashes( trim( $row['title'], $sett['ignore_chars'] ) ), true, false ), 0, 1 );
						}

						$curr_char_link = ( $config['allow_alt_url'] ) ? $config['http_home_url']. $name . "/" . $curr_char . $user_link . ".html" : $config['http_home_url']. "index.php?do=charmap&name=" . $name . "&args=/" . $curr_char . $user_link;
						if ( $curr_char != $last_char ) {
							$t_contc = str_replace( array( "{alpha}", "{alpha-link}" ), array( ucfirst( $curr_char ), $curr_char_link ), $t_cont );
							$t_contc = preg_replace( "'\\[alpha\\](.*?)\\[/alpha\\]'si", "\\1", $t_contc );
						}
						else { $t_contc = preg_replace( "'\\[alpha\\](.*?)\\[/alpha\\]'si", "", $t_cont ); }

						if ( $xfield ) {
							$xfields = xfieldsload();
							$xfieldsdata = xfieldsdataload( $row['xfields'] );
							foreach ( $xfields as $value ) {
								$preg_safe_name = preg_quote( $value[0], "'" );
								if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
									$temp_array = explode( ",", $xfieldsdata[$value[0]] );
									$value3 = array();
									foreach ($temp_array as $value2) {
										$value2 = trim($value2);
										$value2 = str_replace("&#039;", "'", $value2);
										if( $config['allow_alt_url'] ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" . urlencode( $value2 ) . "/\">" . $value2 . "</a>";
										else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xf=" . urlencode( $value2 ) . "\">" . $value2 . "</a>";
									}
									$xfieldsdata[$value[0]] = implode(", ", $value3);
									unset($temp_array);
									unset($value2);
									unset($value3);
								}
								if( empty( $xfieldsdata[$value[0]] ) ) {
									$t_contc = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $t_contc );
									$t_contc = str_replace( "[xfnotgiven_{$value[0]}]", "", $t_contc );
									$t_contc = str_replace( "[/xfnotgiven_{$value[0]}]", "", $t_contc );
								} else {
									$t_contc = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $t_contc );
									$t_contc = str_replace( "[xfgiven_{$value[0]}]", "", $t_contc );
									$t_contc = str_replace( "[/xfgiven_{$value[0]}]", "", $t_contc );
								}
								$xfieldsdata[$value[0]] = stripslashes( $xfieldsdata[$value[0]] );
								if ($config['allow_links'] AND $value[3] == "textarea" AND function_exists('replace_links')) $xfieldsdata[$value[0]] = replace_links ( $xfieldsdata[$value[0]], $replace_links['news'] );
								$t_contc = str_replace( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $t_contc );
							}
						}

						$content_table .= str_replace( array( "{title}", "{full-link}" ), array( stripslashes( $row['title'] ), $full_link ), $t_contc );
						$last_char = $curr_char;

						$content_count++;
					}
					$db->free();


					if ( $in_profile && $show_nav == false ) {
						$sett['show_pagesonmain'] = '0';
					}

					if ( $sett['show_pagesonmain'] ) {
						$prev_pages = "";
						$next_pages = "";
						$prev_link = "";
						$next_link = "";
						$curr_page = "<span>" . $_page['current'] ."</span>";

						if ( $_page['total'] > 10 ) {
							if ( $_page['current'] > 4 ) {
								$prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . $user_link . ".html\">1</a><a href=\"" . $config['http_home_url'] . $name . "/page2" . $user_link . ".html\">2</a><span>..</span><span>..</span>";
								for ( $curr = $_page['current'] - 2; $curr <= $_page['current'] - 1; $curr++ ) { $prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							} else {
								for ( $curr = 1; $curr <= $_page['current'] - 1; $curr++ ) { $prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							}
							if ( $_page['current'] > ( $_page['total'] - 4 ) ) {
								for ( $curr = $_page['current'] + 1; $curr <= $_page['total']; $curr++ ) { $next_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							} else {
								for ( $curr = $_page['current'] + 1; $curr <= $_page['current'] + 2; $curr++ ) { $next_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
								$next_pages .= "<span>..</span><span>..</span><a href=\"" . $config['http_home_url'] . $name . "/page" . ($_page['total'] - 1) . $user_link . ".html\">" . ($_page['total'] - 1) . "</a><a href=\"" . $config['http_home_url'] . $name . "/page" . ($_page['total']) . $user_link . ".html\">" . ($_page['total']) . "</a>";
							}
						} else {
							for ( $curr = 1; $curr < $_page['current']; $curr++ ) { $prev_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							for ( $curr = $_page['current'] + 1; $curr <= $_page['total']; $curr++ ) { $next_pages .= "<a href=\"" . $config['http_home_url'] . $name . "/page" . $curr . $user_link . ".html\">" . $curr . "</a>"; }
							if ( ! empty( $prev_pages ) ) { $prev_link = "<a href=\"" . $config['http_home_url'] . $name . "/page" . ( $_page['current'] - 1 ) . $user_link . ".html\">"; }
							if ( ! empty( $next_pages ) ) { $next_link = "<a href=\"" . $config['http_home_url'] . $name . "/page" . ( $_page['current'] + 1 ) . $user_link . ".html\">"; }
						}

						$nav_template = str_replace( "{pages}", $prev_pages . $curr_page . $next_pages, $nav_template );

						if ( ! empty( $prev_link ) ) {
							$nav_template = str_replace( "[prev-link]", $prev_link, $nav_template );
							$nav_template = str_replace( "[/prev-link]", "</a>", $nav_template );
						} else {
							$nav_template = preg_replace( "'\[prev-link\](.*?)\[/prev-link\]'si", "", $nav_template );
						}
						if ( ! empty( $next_link ) ) {
							$nav_template = str_replace( "[next-link]", $next_link, $nav_template );
							$nav_template = str_replace( "[/next-link]", "</a>", $nav_template );
						} else {
							$nav_template = preg_replace( "'\[next-link\](.*?)\[/next-link\]'si", "", $nav_template );
						}
						if( ! $config['allow_alt_url'] ) {
							$nav_template = preg_replace( "#{$name}\/page([0-9]+)\.html#", "index.php?do=charmap&name={$name}&args=/page$1", $nav_template );
						}
						$tpl->set( "{navigator}", $nav_template );

					} else { $tpl->set( "{navigator}", "" ); }

					$tpl->set( "{total}", $content_count );
					$tpl->set( "{total-items}", $_page['items'] );
					$tpl->set( "{page-current}", $_page['current'] );
					$tpl->set( "{page-total}", $_page['total'] );

					if ( $user ) { $tpl->set_block( "'\\[on-user\\](.*?)\\[/on-user\\]'si", "$1" ); }
					else { $tpl->set_block( "'\\[on-user\\](.*?)\\[/on-user\\]'si", "" ); }
					$tpl->set( "{user}", $db->safesql( $user ) );
					$tpl->set( "{user-link}", ( $config['allow_alt_url'] ) ? $config['http_home_url']. "user/" . $db->safesql( $user ) : $config['http_home_url'] . "index.php?subaction=userinfo&user=" . $db->safesql( $user ) );

					$tpl->set_block( "'\\[content\\](.*?)\\[/content\\]'si", $content_table );
					$tpl->set_block( "'\\[contentlist\\](.*?)\\[/contentlist\\]'si", "$1" );

					if ( $name == "sitemap" ) {
						$tpl->set_block( "'\\[on-char\\](.*?)\\[/on-char\\]'si", "" );
						$tpl->set_block( "'\\[on-main\\](.*?)\\[/on-main\\]'si", "" );
						$tpl->set_block( "'\\[on-map\\](.*?)\\[/on-map\\]'si", "$1" );
						$tpl->set( "{title}", $sett['main_title'] );
						$tpl->set( "{description}", $sett['main_desc'] );
						$tpl->set( "{url}", ( $config['allow_alt_url'] ) ? $config['http_home_url']. $name . $user_link . ".html" : $config['http_home_url']. "index.php?do=charmap&name=" . $name . $user_link );
					} else {
						$tpl->set_block( "'\\[on-char\\](.*?)\\[/on-char\\]'si", "" );
						$tpl->set_block( "'\\[on-main\\](.*?)\\[/on-main\\]'si", "$1" );
						$tpl->set_block( "'\\[on-map\\](.*?)\\[/on-map\\]'si", "" );
						$tpl->set( "{title}", $sett[ $name . '_title'] );
						$tpl->set( "{description}", $sett[ $name . '_desc'] );
						$tpl->set( "{url}", ( $config['allow_alt_url'] ) ? $config['http_home_url']. $name . $user_link . ".html" : $config['http_home_url']. "index.php?do=charmap&name=" . $name . $user_link );
					}
				}
			}
			$tpl->compile( "charmap" );

			$cache_text = $tpl->result['charmap'];

			if ( $sett['cache'] ) {
				$dle_api->save_to_cache( $cache_name, minify_cache( $cache_text ) );
			}
			$tpl->result['content'] = $cache_text;

		}

	} else {
		$static_result = $db->super_query( "SELECT * FROM " . PREFIX . "_static WHERE name='{$name}'" );
		if( $static_result['id'] ) {
			include ENGINE_DIR . "/modules/static.php";
		} else {
			msgbox( $lang['all_err_1'], "<ul><li>ID: #12 " . $lang['charmap_err12'] . "</li></ul>" );
		}
	}

} else {
	$static_result = $db->super_query( "SELECT * FROM " . PREFIX . "_static WHERE name='{$name}'" );
	if( $static_result['id'] ) {
		include ENGINE_DIR . "/modules/static.php";
	} else {
		@header( "HTTP/1.0 404 Not Found" );
		msgbox( $lang['all_err_1'], "<ul><li>ID: #13 " . $lang['charmap_err12'] . "</li></ul>" );
	}
}

?>