<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

global $group_columns, $user_columns, $columns_target;
global $SYS_PROFILE;

$profile_columns = array();

$user_columns = array('thumb'      => 'ユーザーアイコン',
					  'name'       => '名前',
					  'name_kana'  => '名前(カタカナ)',
					  'zip'		   => '郵便番号',
					  'address'    => '居住地',
					  'tel'		   => '電話番号',
					  'sex'        => '性別',
					  'birthday'   => '生年月日',
					  'blood'      => '血液型',
					  'birthplace' => '出身地',
					  'hobby'      => '趣味',
					  'job'        => '職業',
                      'profile'    => 'プロフィール',
					  'fav1'       => '好きな',
					  'fav2'       => '好きな',
					  'fav3'       => '好きな');

$group_columns = array('thumb'      => 'グループアイコン',
					   'name'       => 'グループ名称',
					   'address'    => '活動拠点',
                       'profile'    => '概要');

$columns_target = array('thumb'      => 'text',
						'name'       => 'text',
						'name_kana'  => 'text',
						'zip'        => 'text',
						'address'    => 'text',
						'tel'        => 'text',
						'sex'        => 'value',
						'birthday'   => 'timestamp',
						'blood'      => 'value',
						'birthplace' => 'text',
						'hobby'      => 'text',
						'job'        => 'text',
                		'profile'    => 'text',
						'fav1'       => 'text',
						'fav2'       => 'text',
						'fav3'       => 'text');

$SYS_PROFILE = array(
	'user' => array('thumb'      => 'ユーザーアイコン',
					'name'       => '名前',
					'name_kana'  => '名前(カタカナ)',
					'zip'        => '郵便番号',
					'address'    => '居住地',
					'tel'        => '電話番号',
					'sex'        => '性別',
					'birthday'   => '生年月日',
					'blood'      => '血液型',
					'birthplace' => '出身地',
					'hobby'      => '趣味',
					'job'        => '職業',
					'profile'    => 'プロフィール',
					'fav1'       => '好きな',
					'fav2'       => '好きな',
					'fav3'       => '好きな'),

	'group' => array('thumb'     => 'グループアイコン',
					 'name'      => 'グループ名称',
					 'address'   => '活動拠点',
                     'profile'   => '概要'),

	'target' => array('thumb'      => 'text',
					  'name'       => 'text',
					  'name_kana'  => 'text',
					  'zip'        => 'text',
					  'address'    => 'text',
					  'tel'        => 'text',
					  'sex'        => 'value',
					  'birthday'   => 'timestamp',
					  'blood'      => 'value',
					  'birthplace' => 'text',
					  'hobby'      => 'text',
				 	  'job'        => 'text',
                	  'profile'    => 'text',
					  'fav1'       => 'text',
					  'fav2'       => 'text',
					  'fav3'       => 'text'));

?>
