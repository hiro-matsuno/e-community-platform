<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/**
 * 入力フォーム固有のIDを生成、チェックするモジュール.
 *
 * CSRF攻撃対策に、全ての入力フォームでこれを利用してください.
 *
 * @author ikeda
 */
class FormBuildId {

	const PARAM_NAME = "form_build_id";

	static private $formBuildId;

	/**
	 * form に埋め込むための build id を取得する.
	 *
	 * formBuildId はプロセス中で唯一のものになります.
	 * つまり、1ページ中で複数のフォームを作成したとしても
	 * 同じ formBuildId が取得される仕様です.
	 * 作成された formBuildId は SESSION パラメータとして保存されます.
	 * 取得した formBuildId は FormBuildId::PARAM_NAME というパラメータ名でフォームに埋め込んでください.
	 *
	 * @return Number formBuildId
	 *
	 */
	public static function getFormBuildId() {

		if ( null === FormBuildId::$formBuildId ) {

			FormBuildId::$formBuildId = md5( time().CONF_RANDOM_SEED );
			if ( !isset( $_SESSION[FormBuildId::PARAM_NAME] ) ) {
				$_SESSION[FormBuildId::PARAM_NAME] = array();
			}
			$_SESSION[FormBuildId::PARAM_NAME][] = FormBuildId::$formBuildId;
			
		}

		return FormBuildId::$formBuildId;

	}

	/**
	 *
	 * フォームに埋め込まれた formBuildId がこのセッションで発行済のものかどうかをチェックします.
	 *
	 * 本関数が true を返した場合、送られたリクエストは編集画面を通って編集された
	 * ものであることを示します。false を返した場合は、自動生成されたリクエストで
	 * ユーザの意図に反して送られたものである可能性があります。
	 *
	 * 一度チェックされた ID は二度と利用することができません.
	 *
	 * @param Number $formBuildId
	 *
	 * @return boolean
	 *
	 */
	public static function checkFormBuildId() {

		$formBuildId = null;

		if ( isset( $_REQUEST[FormBuildId::PARAM_NAME] )
			and $_REQUEST[FormBuildId::PARAM_NAME] ) {
			
			$formBuildId = $_REQUEST[FormBuildId::PARAM_NAME];

		} else {

			return false;
			
		}

		if ( is_array( $_SESSION[FormBuildId::PARAM_NAME] ) ) {

			$index = array_search( $formBuildId, $_SESSION[FormBuildId::PARAM_NAME] );
			$_SESSION[FormBuildId::PARAM_NAME][$index] = null;

			$validId = ( false !== $index );

			return $validId;

		}

	}

}
?>
