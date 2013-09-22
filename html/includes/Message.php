<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__).'/Exception.php';

/**
 * メッセージを送信する
 * @param	Mixed	$to 宛先uidまたはuidの配列
 * @param	String	$subject
 * @param	Mixed	$body 文字列又は文字列の要素二つの配列　一つ目はeコミから表示、二つ目はメール転送
 * @param	Boolean	$conv $bodyを変換するか。trueにすると&lt;タグ&gt;を&amp;lt;タグ&amp;gt;に、
 * 改行を&lt;br$gt;に変え、URLを見つけるとリンクを張る $bodyが配列のときは変換は行われない
 * @param   Integer $from 差出人のuid 省略時はmyuid()
 * @return	なし
 */
function send_message2($to,$subject,$body,$conv=true,$from=0){
	global $COMUNI_DEBUG;
	if(!is_array($to))$to = array($to);
	if(is_array($body)){
		$body_br = array_shift($body);
		$body = array_shift($body);
	}else{
		if($conv){
			$body_br = htmlesc($body);
			$body_br = nl2br($body_br);
		}else
			$body_br = $body;
	}

	foreach($to as $t){
		$new_id   = get_seqid();
		$i = mysql_exec('insert into message_data'.
						' (id, from_uid, to_uid, subject, message, is_new, initymd)'.
						' values (%s, %s, %s, %s, %s, %s, now())',
						mysql_num($new_id),
						mysql_num($from), mysql_num($t),
						mysql_str($subject), mysql_str($body_br), mysql_num(1));
		if($mail_to = get_fwd_mail($t)){
			$body_head = get_handle($t). " 様\n\n";
			sys_fwdmail(array('to' => $mail_to, 'subject' => $subject, 'body' => $body_head. $body));
		}
	}
}

/**
 * メッセージの送信.
 * @param Number $from_uid 送信元ユーザのID.
 * @param Number $to_uid 送信先ユーザのID.
 * @param Number $gid 送信先グループのID.
 * @param Number $subject メッセージのタイトル.
 * @param Number $message メッセージ本文.
 * @return bool
 */
function send_message( $from_uid, $to_uid, $gid, $subject, $message ) {

	try {

		if($gid){

			$group = new Group( $gid );

			$users = $group->getMembers();

			$toUserIsInGroup = false;
			foreach ( $users as $user ) {

				if ( $to_uid === $user->getUid() ) {
					$toUserIsInGroup = true;
					break;
				}

			}

			if ( !$toUserIsInGroup and 0 < $to_uid ) { $users[] = new User( $to_uid ); }

			$message = 'このメールは'. get_nickname(myuid()).'様から「'. get_gname($gid). '」参加メンバー全員に送られています'. "\n".
					   '---'. "\n\n". $message;
			$message_br = nl2br($message);

			foreach ( $users as $user ) {

				$mes = new Message( null, $from_uid, $user->getUid(), $gid, true, $subject, $message_br );
				$mes->regist();

				$fwd_mail = array();
				$fwd_mail = get_fwd_mail( $user->getUid() );
				if (count($fwd_mail) > 0) {
					sys_fwdmail(array('to' => $fwd_mail, 'subject' => $subject, 'body' => $message));

				}

			}

		} else {

			$message_br = nl2br($message);

			$mes = new Message( null, $from_uid, $to_uid, $gid, true, $subject, $message_br );
			$mes->regist();

			$fwd_mail = array();
			$fwd_mail = get_fwd_mail($to_uid);
			if (count($fwd_mail) > 0) {
				$message = 'このメールは'. CONF_SITENAME. 'を利用した'.
							( 0 < $from_uid ? get_handle($from_uid). '様から' : 'システムから' )
							. get_handle($to_uid). '様へのメッセージです。'.
						   "\n". '---'. "\n\n". $message;

				sys_fwdmail(array('to' => $fwd_mail, 'subject' => $subject, 'body' => $message));
			}

		}

		return true;

	} catch ( Exception $e ) {

		return false;

	}

}

/**
 * メッセージのオブジェクト.
 *
 * @author ikeda
 */
class Message {

	/**
	 * メッセージデータを格納するデータベーステーブル.
	 */
	const DATABASE = "message_data";

	/**
	 * 新着フラグ.
	 */
	const NEW_FLAG = 1;

	/**
	 * メッセージID.
	 * @var Nuumber
	 */
	private $id;

	/**
	 * 送信元ユーザID.
	 * @var Number
	 */
	private $from_uid;

	/**
	 * 送信先ユーザID.
	 * @var Number
	 */
	private $to_uid;

	/**
	 * 送信先グループID.
	 * @var Number
	 * @TODO データベースに存在しないが、登録するように記述されていてエラーになっていた.
	 * 採用するかどうか検討が必要. 採用する場合はデータベースの再構築が必要.
	 */
	private $gid;

	/**
	 * 新着フラグ.
	 * 0 or Message::NEW_FLAG
	 * @var Number
	 */
	private $is_new;

	/**
	 * メッセージのタイトル.
	 * @var String
	 */
	private $subject;

	/**
	 * メッセージの本文.
	 * @var String
	 */
	private $message;

	/**
	 * メッセージ送信日時.
	 * @var String
	 */
	private $initymd;

	/**
	 * コンストラクタ.
	 * @param Number $id
	 * @param Number $from_uid
	 * @param Number $to_uid
	 * @param Number $gid
	 * @param Number $is_new
	 * @param String $subject
	 * @param String $message
	 * @throws SQLExceptiopn データベースからのデータ取得に失敗した.
	 * @throws DataNotFroundException 指定したIDのデータは存在しない.
	 */
	public function __construct( $id=null, $from_uid=null, $to_uid=null,
								$gid=null, $is_new=null,
								$subject=null, $message=null ) {

		$this->id = $id;
		$this->from_uid = null;
		$this->to_uid = null;
		$this->gid = null;
		$this->is_new = null;
		$this->subject = null;
		$this->message = null;
		$this->initymd = null;

		if ( null !== $id ) {

			$result = mysql_exec( "select id, from_uid, to_uid, is_new, subject, message, initymd"
								." from ".Message::DATABASE
								." where id=%d",
								mysql_num( $this->id ) );

			if ( !$result ) { throw new SQLException( mysql_error() ); }

			if ( false !== ( $row = mysql_fetch_array($result) ) ) {

				$this->from_uid = (int)$row["from_uid"];
				$this->to_uid = (int)$row["to_uid"];
//				$this->gid = (int)$row["gid"];
				$this->is_new = (int)$row["is_new"];
				$this->subject = $row["subject"];
				$this->message = $row["message"];
				$this->initymd = $row["initymd"];

			} else {
				throw new DataNotFoundException( "Message is not found." );
			}

		}

		if( null !== $from_uid ) { $this->from_uid = $from_uid; }
		if( null !== $to_uid ) { $this->to_uid = $to_uid; }
		if( null !== $gid ) { $this->gid = $gid; }
		if( null !== $is_new ) { $this->is_new = (int)$is_new; }
		if( null !== $subject ) { $this->subject = $subject; }
		if( null !== $message ) { $this->message = $message; }

	}

	public function getId() { return $this->id; }
	public function getFromUid() { return $this->from_uid; }
	public function getToUid() { return $this->to_uid; }
	public function getGid() { return $this->gid; }
	public function getIsNew() { return $this->is_new; }
	public function getSubject() { return $this->subject; }
	public function getMessage() { return $this->message; }
	public function getDate() { return $this->initymd; }

	public function setFromUid( $from_uid ) { $this->from_uid = (int)$from_uid; }
	public function setToUid( $to_uid ) { $this->to_uid = (int)$to_uid; }
	public function setGid( $gid ) { $this->gid = (int)$gid; }
	public function setIsNew( $is_new ) { $this->is_new = (int)$is_new; }
	public function setSubject( $subject ) { $this->subject = $subject; }
	public function setMessage( $message ) { $this->message = $message; }

	/**
	 * メッセージをデータベースに登録する、つまり送信.
	 * @throws SQLExceptiopn データベースからのデータ取得に失敗した.
	 * @throws DataNotFroundException 指定したIDのデータは存在しない.
	 */
	public function regist() {

		if ( null === $this->id ) {

			//	@TODO insert id について、auto_increment で与えている箇所と
			//	get_seqid で得た値を設定している箇所がある。
			//	どちらが正しいのか分からないがとりあえず auto_increment を採用した.

			if ( false !== mysql_exec( "insert into ".Message::DATABASE
									." ( from_uid, to_uid, is_new, subject, message )"
									." value( %d, %d, %d, %s, %s )",
									mysql_num( $this->from_uid ),
									mysql_num( $this->to_uid ),
									mysql_num( $this->is_new ),
									mysql_str( $this->subject ),
									mysql_str( $this->message ) ) ) {

				$this->id = mysql_insert_id();

				$result = mysql_exec( "select initymd from ".Message::DATABASE
									." where id=%d",
									mysql_num( $this->id ) );

				if ( !$result ) { throw new SQLException( mysql_error() ); }

				if ( false !== ( $row = mysql_fetch_array($result) ) ) {
					$this->initymd = $row["initymd"];
				} else {
					throw new DataNotFoundException( "Failed to insert data." );
				}

			} else {
				throw new SQLException( mysql_error() );
			}

		} else {

			if ( false !== mysql_exec( "update ".Message::DATABASE." set"
									." from_uid=%d, to_uid=%d, is_new=%d,"
									." subject=%s, message=%s"
									." where id=%d",
									mysql_num( $this->from_uid ),
									mysql_num( $this->to_uid ),
									mysql_num( $this->is_new ),
									mysql_str( $this->subject ),
									mysql_str( $this->message ),
									mysql_num( $this->id ) ) ) {

			} else {
				throw new SQLException( mysql_error() );
			}

		}

	}

	/**
	 * メッセージを削除する.
	 */
	public function delete() {
		Message::deleteMessage( $this->id );
	}

	/**
	 * 指定IDのメッセージを消去する.
	 * インスタンス化の必要が無いため、消去のみが目的の場合は Message::delete より効率的.
	 * @param Number $id 
	 */
	public static function deleteMessage( $id ) {

		if ( !mysql_exec( "delete from ".Message::DATABASE." where id=%s",
							mysql_num( $id ) ) ) {

			new SQLException( mysql_error() );

		}

	}

	/**
	 * 比較関数.
	 * @param Message $obj
	 * @return bool
	 */
	public function equals( $obj ) {

		return ( $this->id === $obj->id
				and $this->from_uid === $obj->from_uid
				and $this->to_uid === $obj->to_uid
				and $this->gid === $obj->gid
				and $this->is_new === $obj->is_new
				and $this->subject === $obj->subject
				and $this->message === $obj->message
				and $this->initymd === $obj->initymd );
		
	}

	/**
	 * 送信元、送信先を指定し、該当するMessageオブジェクトを取得する.
	 * @param mixed $to userid or User 送信先ユーザID.
	 * @param mixed $from userid or User 送信元ユーザID.
	 * @return array 該当するMessageオブジェクトの配列.
	 * @throws SQLExceptiopn データベースからのデータ取得に失敗した.
	 */
	static public function getMessages( $to=null, $from=null ) {

		$array = array();

		$toId = null;
		$fromId = null;

		if ( "User" == get_class( $to ) ) { $toId = $to->getUid(); }
		else { $toId = $to; }

		if ( "User" == get_class( $from ) ) { $fromId = $from->getUid(); }
		else { $fromId = $from; }

		$result = mysql_exec( "select id, from_uid, to_uid, is_new, subject, message, initymd"
							." from ".Message::DATABASE
							.( ( (null!=$toId) or (null!=$fromId) ) ? " where" : "" )
							.( (null!=$toId) ? " to_uid=%d" : "" )
							.( ( (null!=$toId) or (null!=$fromId) ) ? " or" : "" )
							.( (null!=$fromId) ? " from_uid=%d" : "" )
							." order by initymd desc",
							$toId, $fromId );

		if ( !$result ) { throw new SQLException( mysql_error() ); }

		while ( false !== ( $row = mysql_fetch_array($result) ) ) {

			$message = new Message();

			$message->id = (int)$row["id"];
			$message->from_uid = (int)$row["from_uid"];
			$message->to_uid = (int)$row["to_uid"];
//			$message->gid = $row["gid"];
			$message->is_new = (int)$row["is_new"];
			$message->subject = $row["subject"];
			$message->message = $row["message"];
			$message->initymd = $row["initymd"];

			$array[] = $message;

		}

		return $array;

	}

}

?>
