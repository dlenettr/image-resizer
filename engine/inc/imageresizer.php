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

if ( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if ( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

require_once ENGINE_DIR . "/data/iresizer.conf.php";
require_once ROOT_DIR . "/language/" . $config['langs'] . "/iresizer.lng";


$cache_chmod = substr( sprintf('%o', fileperms( ENGINE_DIR . '/cache/imageresizer' ) ), -4 );
$cache_ftp_chmod = substr( sprintf('%o', fileperms( ENGINE_DIR . '/cache/imageresizer/ftp' ) ), -4 );
$upload_chmod = substr( sprintf('%o', fileperms( ROOT_DIR . '/uploads/cache' ) ), -4 );

if ( ! is_writable( ENGINE_DIR . '/data/iresizer.conf.php' ) ) {
	$lang['stat_system'] = str_replace( "{file}", "engine/data/iresizer.conf.php", $lang['stat_system'] );
	$fail = "<div class=\"alert alert-error\">{$lang['stat_system']}</div>";
}
if ( ! in_array( $cache_chmod, array( "0700", "0755", "0775", "0777" ) ) ) {
	$fail = "<div class=\"alert alert-error\"><b>engine/cache/imageresizer/</b> " . $lang['iresizer_33'] . "</div>";
}
if ( ! in_array( $cache_ftp_chmod, array( "0700", "0755", "0775", "0777" ) ) ) {
	$fail = "<div class=\"alert alert-error\"><b>engine/cache/imageresizer/ftp/</b> " . $lang['iresizer_33'] . "</div>";
}
if ( ! in_array( $upload_chmod, array( "0700", "0755", "0775", "0777" ) ) ) {
	$fail = "<div class=\"alert alert-error\"><b>uploads/cache/</b> " . $lang['iresizer_33'] . "</div>";
}

$action = $_REQUEST['action'];

if ( $action == "ftp_flush" ) {
	$path = ENGINE_DIR . '/cache/imageresizer';
	$files = scandir( $path );
	$count = 0;
	foreach( $files as $t ) {
		if ( $t <> "." && $t <> ".." ) {
			if ( is_file( $path . "/" . $t ) ) {
				@unlink( $path . "/" . $t );
				$count++;
			}
		}
	}
	die( str_replace( "%", $count, $lang['iresizer_40'] ) );
}

if ( $action == "save" ) {
	if ( $member_id['user_group'] != 1 ) { msg( "error", $lang['opt_denied'], $lang['opt_denied'] ); }
	if ( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) { die( "Hacking attempt! User not found" ); }

	$find = array(); $replace = array();
	$find[] = "'\r'"; $replace[] = "";
	$find[] = "'\n'"; $replace[] = "";

	$save_con = $_POST['save_con'];
	$save_con['clean_prop_fail'] = intval( $save_con['clean_prop_fail'] );
	$save_con['add_width_heigth'] = intval( $save_con['add_width_heigth'] );
	$save_con['download_externals'] = intval( $save_con['download_externals'] );
	$save_con['sub_activate'] = intval( $save_con['sub_activate'] );
	$save_con['sub_local'] = intval( $save_con['sub_local'] );
	$save_con['default_force_wh'] = intval( $save_con['default_force_wh'] );
	$save_con = array_merge( $iresizer, $save_con );

	$handler = fopen( ENGINE_DIR . '/data/iresizer.conf.php', "w" );
	fwrite( $handler, "<?PHP \n\n//MWS Image Resizer Settings\n\n\$iresizer = array (\n" );
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
		//$value = str_replace( ".", "", $value );
		//$value = str_replace( '/', "", $value );
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

	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "{$PHP_SELF}?mod=imageresizer" );

}

echoheader( "<i class=\"fa fa-image\"></i> MWS Image Resizer", $lang['iresizer_0'] );
echo <<< HTML
<style></style>
<script type="text/javascript"></script>
HTML;

function showRow( $title = "", $description = "", $field = "", $id = "" ) {
	echo "<tr id=\"" . $id . "\"><td class=\"col-xs-6 col-sm-6 col-md-7\"><h6 class=\"media-heading text-semibold\">{$title}</h6><span class=\"text-muted text-size-small hidden-xs\">{$description}</span></td><td class=\"col-xs-6 col-sm-6 col-md-5\">{$field}</td></tr>";
}

function showSep( ) {
	echo "<tr><td class=\"col-xs-12\" colspan=\"2\">&nbsp;</td></tr>";
}

function makeDropDown( $options, $name, $selected ) {
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

function makeCheckBox( $name, $selected ) {
	$selected = $selected ? "checked" : "";
	return "<input class=\"switch\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$selected}>";
}

function foldersize($path) {
	$total_size = 0;
	$files = scandir($path);
	$cleanPath = rtrim($path, '/'). '/';
	foreach($files as $t) {
		if ($t<>"." && $t<>"..") {
			$currentFile = $cleanPath . $t;
			if (is_dir($currentFile)) {
				$size = foldersize($currentFile);
				$total_size += $size;
			} else {
				$size = filesize($currentFile);
				$total_size += $size;
			}
		}
	}
	return $total_size;
}

$cache_1 = foldersize( ROOT_DIR . "/uploads/cache/" );
$cache_2 = foldersize( ROOT_DIR . "/engine/cache/imageresizer/" );
$cache_size = formatsize( $cache_1 + $cache_2 );
$ftp_cache_size = formatsize( $cache_2 );

echo '<div id="dialog-confirm" style="display:none" title=""></div>';
echo <<< HTML
<script>
$(document).ready( function() {
	$("input[name='save_con[use_attrasname]']").change( function() {
		var state = $(this).is(":checked");
		if ( state == true ) {
			$("tr#hash_algorithm").hide();
		} else {
			$("tr#hash_algorithm").show();
		}
	});
	$("input[name='save_con[use_attrasname]']").change();

	$("input[name='save_con[sub_local]']").change( function() {
		var state = $(this).is(":checked");
		if ( state == true ) {
			$("tr#sub_remote_name").hide();
			$("tr#sub_remote_url").hide();
			$("tr#sub_remote_path").hide();
			$("tr#sub_remote_folder").hide();
			$("tr#sub_remote_server").hide();
			$("tr#sub_remote_username").hide();
			$("tr#sub_remote_userpass").hide();
			$("tr#sub_local_name").show();
			$("tr#sub_local_url").show();
			$("tr#sub_local_path").show();
			$("tr#sub_local_folder").show();
		} else {
			$("tr#sub_remote_name").show();
			$("tr#sub_remote_url").show();
			$("tr#sub_remote_path").show();
			$("tr#sub_remote_folder").show();
			$("tr#sub_remote_server").show();
			$("tr#sub_remote_username").show();
			$("tr#sub_remote_userpass").show();
			$("tr#sub_local_name").hide();
			$("tr#sub_local_url").hide();
			$("tr#sub_local_path").hide();
			$("tr#sub_local_folder").hide();
		}
	});
	$("input[name='save_con[sub_local]']").change();

	$("input[name='save_con[sub_activate]']").change( function() {
		var state = $(this).is(":checked");
		if ( state == true ) {
			$("tr#site_path").hide();
			$("tr#custom_path").hide();
			$("tr#sub_local").show();
			$("input[name='save_con[sub_local]']").change();
		} else {
			$("tr#site_path").show();
			$("tr#custom_path").show();
			$("tr#sub_local").hide();
			$("tr#sub_remote_name").hide();
			$("tr#sub_remote_url").hide();
			$("tr#sub_remote_path").hide();
			$("tr#sub_remote_folder").hide();
			$("tr#sub_remote_server").hide();
			$("tr#sub_remote_username").hide();
			$("tr#sub_remote_userpass").hide();
			$("tr#sub_local_name").hide();
			$("tr#sub_local_url").hide();
			$("tr#sub_local_path").hide();
			$("tr#sub_local_folder").hide();
		}
	});
	$("input[name='save_con[sub_activate]']").change();

	$("input[name='save_con[custom_path]']").on( 'keyup', function() {
		$("span#custom_path_text").text( $(this).val() );
	});

	$("#ftp_flush").on('click', function() {
		DLEconfirm( '{$lang['iresizer_34']}<br />{$lang['iresizer_35']}', '{$lang['iresizer_36']}', function () {
			ShowLoading('');
			$.get("", { action: 'ftp_flush' }, function(data) {
				HideLoading('');
				DLEalert(data, '{$lang['iresizer_39']}');
			});
		});
	});
});
</script>
HTML;
echo $fail;

// settings
if ( ! $action || $action == "settings" ) {

	echo <<< HTML
<form action="{$PHP_SELF}?mod=imageresizer&amp;action=save" class="systemsettings" method="post">
	<div class="panel panel-default">
		<div class="panel-heading">
			{$lang['iresizer_47']}
			<div class="heading-elements">
				<ul class="icons-list">
					<li>
						<a href="#" onclick="return false;" id="ftp_flush"><i class="fa fa-trash"></i> FTP [ {$ftp_cache_size} ]</a>
					</li>
					<li>&nbsp;&nbsp;&nbsp;</li>
					<li>
						<a href="#" onclick="return false;"><i class="fa fa-hdd-o"></i> {$cache_size}</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="table-responsive">
			<table class="table table-normal">
HTML;

			showRow( $lang['iresizer_3'], $lang['iresizer_4'], "<input type=\"text\" class=\"form-control\" name=\"save_con[http_home_url]\" value=\"{$iresizer['http_home_url']}\" size=\"60\">", "http_home_url" );
			showRow( $lang['iresizer_5'], $lang['iresizer_6'], makeCheckBox( "save_con[clean_prop_fail]", "{$iresizer['clean_prop_fail']}" ), "clean_prop_fail" );

			$supported_hashings = array();
			foreach( hash_algos() as $hashing ) {
				$supported_hashings[ $hashing ] = $hashing . " &nbsp; Example: " . hash( $hashing, "image" );
			}

			showRow( $lang['iresizer_7'], $lang['iresizer_8'], "<input type=\"text\" class=\"form-control\" name=\"save_con[use_attrasname]\" value=\"{$iresizer['use_attrasname']}\" size=\"10\">", "use_attrasname" );
			showRow( $lang['iresizer_15'], $lang['iresizer_16'], makeDropDown( $supported_hashings, "hash_algorithm", $iresizer['hash_algorithm'] ), "hash_algorithm" );

			showRow( $lang['iresizer_9'], $lang['iresizer_10'], makeCheckBox( "save_con[add_width_heigth]", "{$iresizer['add_width_heigth']}" ), "add_width_heigth" );
			showRow( $lang['iresizer_11'], $lang['iresizer_12'], makeCheckBox( "save_con[default_force_wh]", "{$iresizer['default_force_wh']}" ), "default_force_wh" );
			showRow( $lang['iresizer_13'], $lang['iresizer_14'], makeCheckBox( "save_con[download_externals]", "{$iresizer['download_externals']}" ), "download_externals" );

			showRow( $lang['iresizer_17'], $lang['iresizer_18'], makeCheckBox( "save_con[sub_activate]", "{$iresizer['sub_activate']}" ), "sub_activate" );
			showRow( $lang['iresizer_19'], $lang['iresizer_20'], "<input type=\"text\" class=\"form-control\" name=\"save_con[site_path]\" value=\"{$iresizer['site_path']}\" size=\"60\">", "site_path" );
			showRow( $lang['iresizer_21'], str_replace( "%text%", $iresizer['custom_path'], $lang['iresizer_22'] ), "<input type=\"text\" class=\"form-control\" name=\"save_con[custom_path]\" value=\"{$iresizer['custom_path']}\" size=\"60\">", "custom_path" );

			showRow( $lang['iresizer_23'], $lang['iresizer_24'], makeCheckBox( "save_con[sub_local]", "{$iresizer['sub_local']}" ), "sub_local" );

			showRow( $lang['iresizer_25'], $lang['iresizer_26'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_local_name]\" value=\"{$iresizer['sub_local_name']}\" size=\"60\">", "sub_local_name" );
			showRow( $lang['iresizer_27'], $lang['iresizer_28'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_local_url]\" value=\"{$iresizer['sub_local_url']}\" size=\"60\">", "sub_local_url" );
			showRow( $lang['iresizer_29'], $lang['iresizer_30'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_local_path]\" value=\"{$iresizer['sub_local_path']}\" size=\"60\">", "sub_local_path" );
			showRow( $lang['iresizer_31'], $lang['iresizer_32'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_local_folder]\" value=\"{$iresizer['sub_local_folder']}\" size=\"60\">", "sub_local_folder" );

			showRow( $lang['iresizer_25'], $lang['iresizer_26'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_remote_name]\" value=\"{$iresizer['sub_remote_name']}\" size=\"60\">", "sub_remote_name" );
			showRow( $lang['iresizer_27'], $lang['iresizer_28'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_remote_url]\" value=\"{$iresizer['sub_remote_url']}\" size=\"60\">", "sub_remote_url" );
			showRow( $lang['iresizer_37'], $lang['iresizer_38'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_remote_path]\" value=\"{$iresizer['sub_remote_path']}\" size=\"60\">", "sub_remote_path" );
			showRow( $lang['iresizer_31'], $lang['iresizer_32'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_remote_folder]\" value=\"{$iresizer['sub_remote_folder']}\" size=\"60\">", "sub_remote_folder" );

			showRow( $lang['iresizer_41'], $lang['iresizer_42'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_remote_server]\" value=\"{$iresizer['sub_remote_server']}\" size=\"60\">", "sub_remote_server" );
			showRow( $lang['iresizer_43'], $lang['iresizer_44'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_remote_username]\" value=\"{$iresizer['sub_remote_username']}\" size=\"60\">", "sub_remote_username" );
			showRow( $lang['iresizer_45'], $lang['iresizer_46'], "<input type=\"text\" class=\"form-control\" name=\"save_con[sub_remote_userpass]\" value=\"{$iresizer['sub_remote_userpass']}\" size=\"60\">", "sub_remote_userpass" );

echo <<< HTML
				<tr>
					<td colspan="5">
						<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
						<input type="submit" class="btn btn-success" value="{$lang['user_save']}">
					</td>
				</tr>
			</table>

		</div>
	</div>
</form>
HTML;

}

echofooter();
?>