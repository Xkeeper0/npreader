<?php

	// Include required files

	if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
		// Composer is installed, use its autoloader
		require_once(__DIR__ . "/../vendor/autoload.php");

	} else {
		// No Composer installation, use our own simple one
		require_once(__DIR__ . "/autoload.php");

	}

	define("HTML2MD_HEADER_STYLE", "ATX");

	require_once(__DIR__ . "/functions.php");
	require_once(__DIR__ . "/html2markdown.php");
