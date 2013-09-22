<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

main();

function main() {
	
	$error_occured = false;
	$already_upgraded = false;
	
	//	filebox_data テーブルにカラムを追加。
	{
		
		$result = mysql_query( "SHOW COLUMNS FROM filebox_data LIKE 'filesize'" );
		
		if ( $result and 0 == mysql_num_rows( $result ) ) {
		
			$result = mysql_query( "ALTER TABLE filebox_data ADD COLUMN filesize int" );
			if ( !$result ) {
				print( mysql_error()."<br>\n" );
				$error_occured = true;
			}
			
		} else
		
			$already_upgraded = true;
			

		$result = mysql_query( "SHOW COLUMNS FROM filebox_data LIKE 'trashed'" );

		if ( $result and 0 == mysql_num_rows( $result ) ) {

			$result = mysql_query( "ALTER TABLE filebox_data ADD COLUMN trashed bigint default 0" );
			if ( !$result ) {
				print( mysql_error()."<br>\n" );
				$error_occured = true;
			}

		} else

			$already_upgraded = true;
		

		$result = mysql_query( "SHOW COLUMNS FROM filebox_data LIKE 'org_filename'" );

		if ( $result and 0 == mysql_num_rows( $result ) ) {

			$result = mysql_query( "ALTER TABLE filebox_data ADD COLUMN org_filename text" );
			if ( !$result ) {
				print( mysql_error()."<br>\n" );
				$error_occured = true;
			}

		} else

			$already_upgraded = true;

	}
	
	//	quota テーブルの作成
	{
		
		$result = mysql_query( "SHOW TABLES LIKE 'filebox_config'" );
		
		if ( $result and 0 == mysql_num_rows( $result ) ) {
			
			$result = mysql_query( "CREATE TABLE filebox_config ("
									." disk_quota bigint NOT NULL DEFAULT 0,"
									." user_quota bigint NOT NULL DEFAULT 0,"
									." youtube_user text,"
									." youtube_passwd text,"
									." group_level int not null default 50,"
									." user_level int not null default 0"
									.")" );
			if ( !$result ) {
				print( mysql_error()."<br>\n" );
				$error_occured = true;
			}
			
			$result = mysql_query( "INSERT INTO filebox_config (disk_quota,user_quota) VALUES (65535*65535, 100*1024*1024)" );
			if ( !$result ) {
				print( mysql_error()."<br>\n" );
				$error_occured = true;
			}
						
		} else
		
			$already_upgraded = true;
		
	}
	
	//	アップロード済みのファイルのサイズを取得し、データベースに登録。
	//	また、オリジナルファイル名を現在のファイルタイトル名にする.
	{
		
		$result = mysql_query( "SELECT id, filename, name, org_filename, filesize FROM filebox_data" );
		
		if ( $result ) {
			
			while ( $row = mysql_fetch_array( $result ) ) {

				$id = $row['id'];
				
				if ( null === $row["filesize"] ) {

					$filename = $row['filename'];

					$path = dirname( __FILE__ ).'/../../../databox/filebox/o/'.substr( $filename, 0, 1 ).'/'.$filename;

					if ( file_exists( $path ) ) $filesize = filesize( $path );
					else $filesize = 0;

					$result2 = mysql_query( "UPDATE filebox_data SET filesize=$filesize WHERE id=$id" );
					if ( !$result2 ) {
						print( mysql_error()."<br>\n" );
						$error_occured = true;
					}

				}

				if ( !$row["org_filename"] ) {

					$ext = "";

					if ( preg_match( "/\.[^\.]+$/", $row["filename"], $match ) ) {
						$ext = $match[0];
					}

					$org_filename = substr( $row["filename"], 0, 6 ).$ext;

					$result2 = mysql_query( "update filebox_data set org_filename='{$org_filename}' where id=$id" );
					if ( !$result2 ) {
						print( mysql_error()."<br>\n" );
						$error_occured = true;
					}

				}
				
			}
			
		} else {
			
			print( mysql_error()."<br>\n" );
			$error_occured = true;
			
		}
				
	}
	
	if ( $error_occured ) throw new Exception( __FILE__." の実行中にエラーが発生しました.<br/>\n" );

}
?>
