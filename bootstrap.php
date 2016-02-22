<?php

if ( defined( 'ABSPATH' ) && function_exists('add_action') ) {
	if ( ! has_action( 'init', array( 'Voce_Error_Logging', 'create_post_type' ) ) ) {
		Voce_Error_Logging::init();
	}
}