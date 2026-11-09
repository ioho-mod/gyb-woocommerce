<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! defined( 'GetYourBill' ) ) {
	exit();
}


spl_autoload_register('getyourbill_autoloader');

function getyourbill_autoloader($className)
{
    if ( ! is_string( $className ) || ! preg_match( '/^[A-Za-z0-9_\\\\]+$/', $className ) ) {
        return;
    }

    $file = str_replace( '\\', DIRECTORY_SEPARATOR, $className );
    $path = GetYourBillPATH . $file . '.class.php';

	if ( file_exists( $path ) ) {
		// nosemgrep: audit.php.lang.security.file.inclusion-arg -- Class name is regex-validated and resolved to a plugin-local .class.php file.
		require_once $path;
	}
}
