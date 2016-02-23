<?php

// create convenience function for logging
if ( !function_exists( 'voce_error_log' ) ) {
	function voce_error_log( $title, $error, $tags = array( ) ) {
		return Voce_Error_Logging::error_log( $title, $error, $tags );
	}
}