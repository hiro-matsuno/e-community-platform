<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
	require_once dirname(__FILE__)."/../config.php";
?>

var ecom = {};

ecom.showBlockMessage = function( message, query, error ) {

	if ( undefined == error ) {error = false;}

	query.find(".ecom_block_message,.ecom_block_error_message").remove();

	var mes = $("<div><i>"+message+"</i></div>");

	if ( error ) {
		mes.addClass("ecom_block_error_message");
	} else {
		mes.addClass("ecom_block_message");
	}

	mes.click( function() {

		$(this).fadeOut( "normal", function() {
			$(this).slideUp("slow", function() {
				$(this).remove();
			});
		} );

	} );

	query.prepend( mes );

}

ecom.getUrlBase = function() {

	return '<?= CONF_URLBASE ?>';

}
