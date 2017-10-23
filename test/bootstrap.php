<?php

// We use errors in PHP 5 to correctly test Type errors
if(PHP_MAJOR_VERSION > 5) {
    // Trigger errors even when they are suppressed with @
    set_error_handler(function ($errno, $str, $file, $line, $context = null){
        throw new ErrorException( $str, 0, $errno, $file, $line );
    });
}

include 'vendor/autoload.php';
