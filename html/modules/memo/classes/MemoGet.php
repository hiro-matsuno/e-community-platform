<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/../../../includes/User.php";
require_once dirname(__FILE__)."/../../../includes/Element.php";
require_once dirname(__FILE__)."/MemoModule.php";

/**
 * Description of MemoGet
 *
 * @author ikeda
 */
class MemoGet {

	const OK = 0;
	const FATAL = -1;
	const DENIED = -2;

	private $request;


	public function __construct( $request ) {
		$this->request = $request;
	}

	public function get() {

		$result = (Object)array();

		try {

			switch ( $this->request["act"] ) {

				case "regist":
					$this->registData();
					$result->code = MemoGet::OK;
					$result->form_build_id = FormBuildId::getFormBuildId();
					break;

				default:
					throw new Exception();
					break;

			}

		} catch ( PermissionDeniedException $e ) {

			$result->code = MemoGet::DENIED;
			$result->message = "その操作を行なう権限がありません.";

		} catch ( Exception $e ) {

			$result->code = MemoGet::DENIED;
			$result->message = "処理に失敗しました.";
			if ( defined( "DEBUG_OUTPUT" ) ) {
				$result->message .= "<div>".$e->__toString()."</div>";
			}

		}

		return json_encode( $result );

	}

	private function registData() {

		$blockId = ( isset( $this->request["blk_id"] ) ? (int)$this->request["blk_id"] : null );
		$string = ( isset( $this->request["data"] ) ? $this->request["data"] : null );

		if ( null === $string ) { return; }

		if ( false === FormBuildId::checkFormBuildId() ) {
			throw new Exception();
		}

		$string = htmlspecialchars($string);

		$me = User::getMe();
		$blockElement = new Element( $blockId );
		
		if ( Permission::USER_LEVEL_EDITOR > $blockElement->getOwnerLevel( $me ) ) {
			throw new PermissionDeniedException();
		}

		$data = MemoData::createInstanceByBlockId($blockId);
		if ( null === $data ) { $data = new MemoData(); }

		$data->setData( $string );
		$data->setBlockId( $blockId );

		$data->regist();

	}

}
?>
