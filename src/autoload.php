<?php

	// Require the important stuff
	function npr_autoloader($class) {
		$file	= __DIR__ ."/". str_replace('\\', '/',$class) .".php";
		if (file_exists($file)) {
			require_once($file);
			return;
		}

		if (substr($class, 0, 2) === "X\\") {
			// Our own vendor namespace is omitted within src/
			$class	= substr($class, 2);
			npr_autoloader($class);
		}

	}
	spl_autoload_register("npr_autoloader");
