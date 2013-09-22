<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/**
 * upgrade.php
 * 
 * 現在のデータベースバージョンとソースコードのバージョンを比較し、
 * データベースのアップグレードに必要なスクリプトを実行する.
 * 
 * アップグレードスクリプトの仕様
 * ・manager/upgrade/[バージョン] ディレクトリに配置する。
 * ・データベースのバージョンより新しいもののうち、バージョンが古いディレクトリからスクリプトが順次実行される。
 * ・ディレクトリ内でのスクリプトの起動順序はスクリプト名を辞書順にソートして決定する。
 *   スクリプト名の最初に [2桁の数字]_ の PREFIX を付け、起動順序を明示することを推奨する (必須ではない)。
 * ・アップデートスクリプトが失敗した場合、Exception クラスを継承した例外をスローするように記述する。
 *   例外が発生した場合、以降のアップデートスクリプトは実行されない。
 * ・既にアップデート済みの場合でもスクリプトが呼び出される可能性がある点に留意して記述する。
 *   (たとえば sql クエリでは if not exists などで二重定義を防止する.)
 * ・スクリプト中で標準出力に echo されたメッセージはアップデート実行結果の表示に追加される (エラー出力などに利用する)。 
 * 
*/

require '../../lib.php';

/**
 * システムバージョンを表すクラスです。
 */
class Version {
	
	/**
	 * バージョンナンバーを表すカンマ区切り文字列.
	 * @var string
	 */
	private $version;
	
	
	/**
	 * $version を解析して得られたバージョンナンバーの数字配列.
	 * @var array
	 */
	private $numbers = array();
	
	public function Version( $version ) {
		
		$this->version = $version;
		
		$num_array = preg_split( '/\./', $this->version );
		
		foreach( $num_array as $num ) { $this->numbers[] = intval( $num ); }
		
	}
	
	public function getVersion() { return $this->version; }

	public static function compare ( $a, $b ) {
		
		for ( $i = 0; count( $a->numbers ) > $i; ++$i ) {
			
			$src = $a->numbers[$i];
			
			if ( count( $b->numbers ) <= $i ) $dest = 0;
			else $dest = $b->numbers[$i];
			
			if ( $src < $dest ) return -1;
			else if ( $src > $dest ) return 1;
			
		}
		
		return 0;
		
	}
	
}

/**
 * データベースのバージョンを取得する。
 * @return Version オブジェクト、または取得に失敗した場合にfalseを返します。
 */
function check_version() {
	
	$result = mysql_query( "show tables like 'options'" );
	
	if ( !$result ) return false;
	
	if ( 0 < mysql_num_rows( $result ) ) {
		
		$result = mysql_query( "SELECT option_key, option_value FROM options where option_key='version'" );
		
		if ( $result and $row = mysql_fetch_array( $result ) ) {
			
			return new Version( $row['option_value'] );
			
		} else 
			return false;
		
	} else {
		return new Version( "2.0.0" );
	}
	
}

/**
 * @param $now_version 現在のバージョンを示す Version オブジェクト
 * @return アップデートスクリプトを適用するべきバージョンを格納する配列。
 */
function get_upgrades( $now_version ) {
	
	$versions = array();
	
	$res_dir = opendir( '.' );
	
	while( $file_name = readdir( $res_dir ) ){
		
		if ( '..' != $file_name and '.' != $file_name
			 and preg_match( '/^[\d\.]+$/', $file_name ) and is_dir( $file_name ) ) {
			
			$version = new Version( $file_name );
			if ( 0 < Version::compare( $version, $now_version ) )
				$versions[] = $version;
			
		}
		
	}
	
	closedir( $res_dir );
	
	usort( $versions, "Version::compare" );

	return $versions;
	
}

/**
 * 必要なアップグレードを行う
 * @param $now_versions 現在のバージョンを表す Version オブジェクト.
 * @param $versions アップグレードを行うべきバージョンを格納する配列.
 * @return 処理後のバージョンを返す.
 */
function upgrade( $now_version, $versions ) {
	
	//	バージョン 2.1.0 より下ではシステム設定テーブルが無いので作成する.
	if ( 0 < Version::compare( new Version( "2.1.0" ), $now_version ) ) {
		
		if ( !mysql_query( "CREATE TABLE if not exists options"
							." ( option_key text NOT NULL,"
							." option_value text NOT NULL,"
							." uid int DEFAULT 0,"
							." gid int DEFAULT 0 )"
							." CHARACTER SET utf8" ) 
			or !mysql_query( "INSERT INTO options ( option_key, option_value )"
							." VALUES ( 'version', '".$now_version->getVersion()."' )" ) )
				
			return $now_version;
		
	}
	
	//	スクリプトが適用される毎に上がっていく.
	$current_version = $now_version;
	
	//	バージョンナンバー順にアップデートスクリプトを適用.
	foreach ( $versions as $version ) {
		
		$scripts = array();
		
		$res_dir = opendir( $version->getVersion() );
		
		while( $file_name = readdir( $res_dir ) ){
			
			if ( '..' != $file_name and '.' != $file_name
				 and preg_match( '/\.php$/', $file_name ) and !is_dir( $file_name ) ) {
				
				 $scripts[] = $version->getVersion()."/".$file_name;
				
			}
			
		}
		
		closedir( $res_dir );
		
		sort( $scripts );
		
		try {
			
			foreach ( $scripts as $scr ) { require $scr; }
			$current_version = $version;
			
		} catch ( Exception $e ) {
			
			echo $e->getMessage()."\n";
			break;
			
		}
	
	}
	
	{
		
		if ( !mysql_query( "UPDATE options SET option_value='".$current_version->getVersion()."'"
							." WHERE option_key='version'" ) )
			return $now_version;
		
	}
	
	return $current_version;
	
}


su_check();

$now_version = check_version();

$versions = get_upgrades( $now_version );

switch( $_REQUEST['act'] ) {
	
	case 'upgrade':
		
		ob_start();
		$new_version = upgrade( $now_version, $versions );
		$scr_message = ob_get_contents();
		ob_end_clean();
				
		$message = ( ( 0 == Version::compare( new Version( SOURCE_VERSION ), $new_version ) ) 
					? "<div style=\"color: #3fff7f;\"><i>アップグレードされました</i></div>\n" 
					: "<div style=\"color: #ff3f3f;\"><i>アップグレードに失敗しました</i></div>\n" );
					
		if ( '' != $scr_message )
			$message .= "<div style=\"font-size: 0.8em; padding: 8px; border: solid 1px red;"
						." color: red; background-color: #ffcfcf;\">"
						.$scr_message
						."</div>";

		$now_version = $new_version; 
		
}

if ( !( $revision = EcomUtil::DefaultFilter( `git log -1` ) ) ) {
	$dir = dirname(__FILE__)."/../../";
	$revision = EcomUtil::DefaultFilter( `svn info $dir` );
}

ob_start();
?>

<div style="padding: 4px;">
	<div><?php echo $message; ?></div>
	現在のシステムのバージョンは <?php $str = $now_version->getVersion(); echo $str; ?> です。
	<?
		if ( $revision ) {

			echo EcomUtil::debugString( "<div class=\"ecom_block_message\" style=\"padding: 0; margin: 8px\">"
										."<h4 style=\"background-color: mistyrose\">"
										."現在のリビジョン</h4>"
										."<div style=\"padding: 4px\">"
										.$revision
										."</div>"
										."</div>" );
			
		}
	?>
</div>

<?php
if ( 0 < Version::compare( new Version( SOURCE_VERSION ), $now_version ) 
		and 0 < count ( $versions ) ) {
	
	$highest = $versions[count( $versions )-1];
	
	echo '<div style="padding: 4px;">'
		.'<button style="border: outset; background-color: #cfcfcf; cursor: pointer;"'
		.' onClick="location.href=\'upgrade.php?act=upgrade\';">upgrade</button>'
		.'バージョン '.SOURCE_VERSION.' にアップグレードできます。'
		.'</div>';
		
}
?>

<?php

$html = ob_get_contents();

ob_end_clean();

show_input( array(title   => 'システムのアップグレード',
			  icon    => 'write',
			  content => $html ) );
			  
?>