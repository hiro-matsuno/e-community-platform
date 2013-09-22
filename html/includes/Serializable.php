<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
//	PHP5.1以降はシステムで定義されている
if ( !interface_exists( "Serializable" ) ) {
	
	/**
	 * Description of Serializable
	 *
	 * @author ikeda
	 */
	interface Serializable {

		public function serialize();
		public function unserialize();

	}

}
?>
