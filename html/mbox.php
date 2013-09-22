<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';
require dirname(__FILE__). '/includes/AutoLinkFilter.php';

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($act) {
	case 'del':
		message_delete();
	break;
	case 'view':
		message_view();
	break;
	default:
		print_form();
}

exit(0);

function message_delete() {

	$target = isset($_POST['target']) ? $_POST['target'] : array();

	$content = null;

	if (count($target) == 0) {
		$content = 'メッセージを選択して下さい。';
	}
	else {

		$complete = true;

		foreach ($target as $t) {

			try {

				$message = new Message( $t );

				$me = User::getMe();

				if ( $message->getToUid() === $me->getUid()
					or Permission::USER_LEVEL_ADMIN === $me->getLevel() ) {
					$message->delete();
				} else {
					$complete = false;
				}
				
			} catch ( Exception $e ) {
				$complete = false;
			}

		}

		if ( $complete ) { $content = 'メッセージを削除しました。'; }
		else { $content = "一部のメッセージの削除に失敗しました"; }

	}

	$content .= '<div style="padding: 3px; font-size: 12px;"><a href="./mbox.php">戻る</a></div>';

	$data = array('title'   => 'メッセージ削除',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);
}

function message_view() {

	$message_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	$content = null;

	try {

		$mes = new Message( $message_id );

		$me = User::getMe();

		if ( $mes->getToUid() !== $me->getUid()
			and $mes->getFromUid() !== $me->getUid()
			and Permission::USER_LEVEL_ADMIN !== $me->getLevel() ) {
			$content = '他人宛のメッセージです。';
			throw new Exception();
		}

		if ( $mes->getToUid() === $me->getUid()
			and Message::NEW_FLAG === $mes->getIsNew() ) {
			$mes->setIsNew( false );
			$mes->regist();
		}

		$subject  = $mes->getSubject();
		$from_uid = $mes->getFromUid();
		$from     = get_handle($from_uid);
		$to_uid   = $mes->getToUid();
		$to       = get_handle($to_uid);
		$gid      = $mes->getGid();
		if($gid)$to = '「'.get_gname($gid).'」の参加者全員';
		$message = AutoLinkFilter::filter( $mes->getMessage() );
		$date     = date('Y年m月d日 H:i:s', tm2time($mes->getDate()));
		$gid      = $mes->getGid();

		$content  = '<div class="common_body">'.
					'<div style="width: 50px; text-align: right; margin: 1px 0; background: #eee; float: left; padding: 3px;">題名: </div>'.
					'<div style="float: left; padding: 3px; margin: 1px 0; text-align: left;">'. $subject. '</div>'.
					'<div style="clear: both;"></div>'.
					'<div style="clear: left; text-align: right; width: 50px; margin: 1px 0; background: #eee; float: left; padding: 3px;">差出人: </div>'.
					'<div style="float: left; padding: 3px; margin: 1px 0; text-align: left; ">'. $from. '</div>'.
					'<div style="clear: both;"></div>'.
					'<div style="clear: left; text-align: right; width: 50px; margin: 1px 0; background: #eee; float: left; padding: 3px;">宛先: </div>'.
					'<div style="float: left; padding: 3px; margin: 1px 0; text-align: left; ">'. $to. '</div>'.
					'<div style="clear: both;"></div>'.
					'<div style="clear: left; text-align: right; width: 50px; margin: 1px 0; background: #eee; float: left; padding: 3px;">日時: </div>'.
					'<div style="float: left; padding: 3px; margin: 1px 0; text-align: left; ">'. $date. '</div>'.
					'<div style="clear: both;"></div>'.
					'<div style="clear: left; text-align: right; width: 50px; margin: 1px 0; background: #eee; float: left; padding: 3px;">内容: </div>'.
					'<div style="padding: 3px; clear: left; margin: 0; text-align: left; ">'.
					$message. '</div>';

		if ($from_uid > 0) {
			$content .= ($from_uid == myuid()?'':'<br><br><a href="/message.php?to='. $from_uid. '">差出人に返信する&raquo;</a>').
						($gid?'<br><br><a href="/message.php?gid='.$gid.'&to='.
						$from_uid. '">「'.get_gname($gid).'」参加メンバー全員に返信する&raquo;</a>':'');
		}
		$content .= '</div>';

		$content .= '<div style="padding: 3px; font-size: 12px;"><a href="javascript: history.back()">戻る</a></div>';

	} catch ( DataNotFoundException $e ) {
		$content = '指定されたメッセージは存在しません';
	} catch ( Exception $e ) {
		if ( null === $content ) { $content = "データの取得に失敗しました"; }
	}

	$data = array('title'   => 'メッセージ詳細',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);

	exit(0);
	
}

function print_form() {

	$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

	$me = User::getMe();

	$list = array();

	$list[] = array('del'     => '',
					'subject' => '題名',
					'from'    => '差出人',
					'to'      => '宛先',
					'initymd' => '日付');

	$messages = Message::getMessages( $me, $me );

	foreach ( $messages as $message ) {

		if ( 'new' == $mode and $message->getIsNew() !== Message::NEW_FLAG ) { continue; }

		$subject = '<a href="mbox.php?action=view&id='. $message->getId(). '">'. $message->getSubject(). '</a>';

		if ($message->getIsNew() == Message::NEW_FLAG) {
			$subject = '<strong>'. $subject. '</strong>';
		}

		$list[] = array('del'     => ($message->getFromUid()==$me->getUid()?'':'<input type="checkbox" name="target[]" value="'. $message->getId(). '">'),
						'id'      => $message->getId(),
						'subject' => $subject,
						'from'    => get_handle($message->getFromUid()),
						'to'      => $message->getGid()?'「'.get_gname($message->getGid()).'」の参加者全員':get_handle($message->getToUid()),
						'initymd' => date('Y年m月d日 H:i:s', tm2time($message->getDate())));
		
	}

	$style = array('del'     => 'width: 30px;',
				   'subject' => 'width: 50%;',
				   'to'    => 'width: 15%;',
				   'from'    => 'width: 15%;');

	$content .= '<form action="mbox.php" method="POST"><input type="hidden" name="action" value="del">';
	$content .= '<div style="padding: 2px; font-size: 12px; float: left; width: 40%;">';
	$content .= '<a href="mbox.php?mode=new">新着のみ表示</a> / ';
	$content .= '<a href="mbox.php">全て表示</a> ';
	$content .= '</div>';

	$content .= '<div style="text-align: right; float: right; width: 40%;">'.
				'<input type="submit" value="チェックした記事の削除" style="background: #f0f0f0; border: solid 1px #999; margin: 2px;"></div>';
	$content .= '<br clear="all">';
	$content .= create_list($list, $style);
//	$content .= '<div style="padding: 3px; font-size: 12px;"><a href="javascript: history.back()">戻る</a></div>';
	$content .= '</form>';

	$data = array('title'   => 'メッセージボックス',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}
?>
