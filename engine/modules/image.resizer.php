<?php
/*
=============================================
 Name      : MWS Image Resizer v1.9
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : https://dle.net.tr/
 License   : MIT License
 Date      : 18.07.2018
=============================================
*/

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

require_once ENGINE_DIR . "/data/iresizer.conf.php";
require_once ENGINE_DIR . "/classes/resizer.class.php";

function download_image( $url, $target ) {
	$fp = fopen( $target, 'w+');
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_FILE, $fp );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 120 );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt( $ch, CURLOPT_HEADER, false );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
	curl_setopt( $ch, CURLOPT_MAXREDIRS, 2 );
	curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
	curl_exec( $ch );
	curl_close( $ch );
	fclose( $fp );
	if ( file_exists( $target ) && filesize( $target ) > 0 ) {
		return true;
	} else {
		@unlink( $target );
		return false;
	}
}

$site_url = parse_url( $iresizer['http_home_url'] );

function image_urls( $m ) {
	global $site_url;
	if ( strpos( $m[1], "<a" ) !== false ) {
		if ( preg_match( "#href=['\"](.+?)['\"]#is", $m[1], $m_href ) ) {
			return str_replace( $site_url['scheme'] . "://" . $site_url['host'], "", $m_href[1] );
		}
	} else {
		if ( preg_match( "#src=['\"](.+?)['\"]#is", $m[1], $m_src ) ) {
			return $m_src[1];
		}
	}
}

function image_resize( $m ) {
	global $member_id, $iresizer, $site_url;
	if ( strpos( $m[0], "resize=" ) !== false ) {
		$img = array( ); $m_res = array( );
		$img['c'] = ""; $img['a'] = "";
		if ( preg_match( "#resize=['\"](.+?)['\"]#is", $m[0], $m_res ) ) {
			$_tmp = explode( "|", str_replace( array( ",", ";", " " ), "|", $m_res[1] ) );
			foreach ( $_tmp as $attr ) {
				$_tmp2 = explode( ":", $attr );
				$img[ $_tmp2[0] ] = $_tmp2[1];
			}
		} else {
			return $m[0];
		}
		if ( preg_match( "#crop=['\"](.+?)['\"]#is", $m[0], $m_res ) ) {
			$img['c'] = $m_res[1];
		}
		if ( $iresizer['clean_prop_fail'] ) {
			$m[0] = preg_replace( "#resize=['\"](.+?)['\"]#is", "", $m[0] );
			$m[0] = preg_replace( "#crop=['\"](.+?)['\"]#is", "", $m[0] );
			$m[0] = str_replace( "  ", " ", $m[0] );
		}

		if ( preg_match( "#src=['\"](.+?)['\"]#is", $m[0], $m_src ) ) {
			$img['s'] = $m_src[1];
		}

		if ( ! empty( $iresizer['use_attrasname'] ) ) {
			if ( preg_match( "#" . $iresizer['use_attrasname'] . "=['\"](.+?)['\"]#is", $m[0], $m_src ) ) {
				$img['a'] = totranslit( $m_src[1], true, false );
			}
		}

		if ( ! array_key_exists( 'f', $img ) && $iresizer['default_force_wh'] != "" ) {
			$img['f'] = $iresizer['default_force_wh'];
		}

		$_tmp3 = pathinfo( basename( $img['s'] ) );
		$edge_map = array( "l" => 0, "w" => 1, "h" => "2" );
		$img['e'] = intval( $edge_map[ $img['e'] ] );
		$img['t'] = $_tmp3['extension'];
		if ( ! empty( $img['a'] ) ) {
			$img['k'] = $img['a'] . "-" . hash( $iresizer['hash_algorithm'], md5( implode( "-", $img ) ) );
		} else {
			$img['k'] = md5( implode( "-", $img ) );
		}

		$img['+']['hash'] = sha1( implode( "-", $img ) );
		if ( $iresizer['sub_activate'] ) {

			$_cache_file = ENGINE_DIR . "/cache/imageresizer/" . $img['+']['hash'];
			if ( $iresizer['sub_local'] ) {
				// Local Host
				$prop = array(
					'name' 		=> $iresizer['sub_local_name'],
					'path' 		=> $iresizer['sub_local_path'],
					'url' 		=> $iresizer['sub_local_url'],
					'folder' 	=> $iresizer['sub_local_folder'],
				);
			} else {
				// Remote Host
				$prop = array(
					'name' 		=> $iresizer['sub_remote_name'],
					'path' 		=> $iresizer['sub_remote_path'],
					'url' 		=> $iresizer['sub_remote_url'],
					'folder' 	=> $iresizer['sub_remote_folder'],
				);
				$img['ftp'] = ENGINE_DIR . "/cache/imageresizer/ftp/" . $img['k'] . "." . $img['t'];
			}

			if ( file_exists( $_cache_file ) ) {
				$h = fopen( $_cache_file, 'r' );
				$_tmp = fread( $h, filesize( $_cache_file ) );
				fclose( $h );
				$_data = explode( ",", $_tmp );
				if ( count( $_data ) == 4 ) {
					$img['p'] = $_data[0] . $_data[3] . $img['k'] . "." . $img['t'];
					$img['u'] = $_data[1] . $_data[3] . $img['k'] . "." . $img['t'];
					$img['+']['sub'] = $_data[2];
				} else {
					$img['p'] = $prop['path'] . $prop['folder'] . $img['k'] . "." . $img['t'];
					$img['u'] = $prop['url'] . $prop['folder'] . $img['k'] . "." . $img['t'];
					$img['+']['sub'] = $prop['name'];
				}
			} else {
				$h = fopen( $_cache_file, 'w' );
				fwrite( $h, $prop['path'] . "," . $prop['url'] . "," . $prop['name'] . "," . $prop['folder'] );
				fclose( $h );
				$img['p'] = $prop['path'] . $prop['folder'] . $img['k'] . "." . $img['t'];
				$img['u'] = $prop['url'] . $prop['folder'] . $img['k'] . "." . $img['t'];
				$img['+']['sub'] = $prop['name'];
			}

		} else {
			$img['u'] = $iresizer['site_path'] . "/uploads/cache/" . $img['k'] . "." . $img['t'];
			$img['p'] = ROOT_DIR . str_replace( $iresizer['site_path'], "", $img['u'] );
		}

		$_tmp = parse_url( $img['s'] );
		if ( ( array_key_exists( 'host', $_tmp ) && $_tmp['host'] == $site_url['host'] ) || substr( $img['s'], 0, 1 ) == "/" ) {
			$img['s'] = str_replace( $iresizer['http_home_url'], "", $img['s'] );
			$img['s'] = str_replace( str_replace( "www.", "", $iresizer['http_home_url'] ), "", $img['s'] );
			$img['s'] = str_replace( $iresizer['http_home_url'], "", str_replace( "www.", "", $img['s'] ) );
			$img['+']['local'] = "1";
		} else {
			$img['+']['local'] = "0";
		}

		$img['+']['down'] = "0";
		if ( array_key_exists( 'd', $img ) && $img['d'] && ! $img['+']['local'] ) {
			if ( $iresizer['download_externals'] ) {
				$_target = ROOT_DIR . "/uploads/cache/" . md5( $img['s'] ) . "." . $img['t'];
				$img['o'] = str_replace( "." . $img['t'], ".o." . $img['t'], $_target );
				if ( ! file_exists( $img['o'] ) ) {
					$is_down = download_image( $img['s'], $img['o'] );
					if ( ! $is_down ) return $m[0];
					else $img['+']['down'] = "1";
				}
			} else {
				return $m[0];
			}
		} else {
			$img['o'] = ROOT_DIR . '/' . str_replace( $iresizer['site_path'], "", $img['s'] );
			$img['o'] = str_replace( '//', '/', $img['o'] );
			if ( ! $img['+']['local'] ) return $m[0];
		}

		if ( file_exists( $img['o'] ) ) {
			if (
				( ! file_exists( $img['p'] ) && ! $iresizer['sub_activate'] ) ||
				( ! file_exists( $img['p'] ) && $iresizer['sub_activate'] && $iresizer['sub_local'] ) ||
				( ! file_exists( $img['ftp'] ) && $iresizer['sub_activate'] && ! $iresizer['sub_local'] )
			) {
				try {
					$si = new SimpleImage();
					$si->quality = intval( $img['q'] );
					$image = $si->load( $img['o'] );
					// 1 : Genişlik, 2 : Yükseklik, 0 : En Uzun Kenar
					//$img->smart_crop( 300, 300, "c-c" );
					if ( empty( $img['c'] ) ) {
						if ( $img['e'] == 1 || $img['e'] == 'w' ) {
							$image->fit_to_width( $img['w'] );
						} else if ( $img['e'] == 2 || $img['e'] == 'h' ) {
							$image->fit_to_height( $img['h'] );
						} else {
							$image->best_fit( $img['w'], $img['h'] );
						}
						$new_sizes = $image->sizes();
						if ( ! $img['f'] ) {
							$img['w'] = $new_sizes['w'];
							$img['h'] = $new_sizes['h'];
						}
					} else {
						$img_sizes = $image->sizes();
						if ( ( $img_sizes['w'] / $img['w'] ) < ( $img_sizes['h'] / $img['h'] ) ) {
							$image->fit_to_width( $img['w'] );
						} else {
							$image->fit_to_height( $img['h'] );
						}
						$image->smart_crop( $img['w'], $img['h'], $img['c'] );
					}

					if ( $iresizer['sub_activate'] ) {
						if ( $iresizer['sub_local'] ) {
							$image->save( $img['p'] );
						} else {
							$tmp_path = ROOT_DIR . "/uploads/cache/ftp_" . $img['k'] . "." . $img['t'];
							$image->save( $tmp_path );
							$ftp_conn = ftp_connect( $iresizer['sub_remote_server'] );
							$ftp_login = ftp_login( $ftp_conn, $iresizer['sub_remote_username'], $iresizer['sub_remote_userpass'] );
							if ( ftp_put( $ftp_conn, $img['p'], $tmp_path, FTP_BINARY ) ) {
								@unlink( $tmp_path );
								touch( $img['ftp'] );
							} else {
								return $m[0];
							}
							ftp_close( $ftp_conn );
						}
					} else {
						$image->save( $img['p'] );
					}
				} catch ( Exception $e ) {
					return $m[0];
				}

				if ( $iresizer['add_width_heigth'] ) {
					if ( strpos( $m[0], "height=" ) === false ) {
						$m[0] = str_replace( "<img", "<img height=\"{$img['h']}\"", $m[0] );
					}
					if ( strpos( $m[0], "width=" ) === false ) {
						$m[0] = str_replace( "<img", "<img width=\"{$img['w']}\"", $m[0] );
					}
				}

				if ( ! empty( $iresizer['custom_path'] ) ) $img['u'] = str_replace( "uploads/cache", $iresizer['custom_path'], $img['u'] );

				return str_replace( $img['s'], $img['u'], $m[0] );
			} else {

				$real_sizes = getimagesize( $img['p'] );
				$img['w'] = $real_sizes[0];
				$img['h'] = $real_sizes[1];
			}
			if ( $iresizer['add_width_heigth'] ) {
				if ( strpos( $m[0], "height=" ) === false ) {
					$m[0] = str_replace( "<img", "<img height=\"{$img['h']}\"", $m[0] );
				}
				if ( strpos( $m[0], "width=" ) === false ) {
					$m[0] = str_replace( "<img", "<img width=\"{$img['w']}\"", $m[0] );
				}
			}
			$_src = ( count( $m_res ) > 0 ) ? $m_res[0] : $m[0];

			if ( ! empty( $iresizer['custom_path'] ) ) $img['u'] = str_replace( "uploads/cache", $iresizer['custom_path'], $img['u'] );
			return str_replace( $img['s'], $img['u'], str_replace( " " . $_src, "", $m[0] ) );
		} else {
			return $m[0];
		}
	} else {
		return $m[0];
	}

}

?>