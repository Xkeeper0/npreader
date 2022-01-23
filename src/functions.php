<?php

	// @TODO use ??, php7 is not new at this point lol
	function d(&$v, $r = null) {
		return isset($v) ? $v : $r;
	}


	function rekey($array, $func) {

		$out	= [];
		foreach ($array as $oldKey => $value) {
			$newKey			= $func($value);
			$out[$newKey]	= $value;
			unset($array[$oldKey]);
		}
		
		return $out;
	}

	/**
	 * Enables an error handler that will throws warnings as RuntimeExceptions
	 * Used to (sigh) catch certain warnings that should be exceptions...
	 */
	function throw_warnings($enable = true) {
		static $is_enabled = false;

		if ($enable && !$is_enabled) {
			$enabled = true;
			set_error_handler(
				function ($severity, $message, $file, $line) {
					throw new \RuntimeException($message);
				},
				E_WARNING | E_USER_WARNING
			);
			return true;
		} elseif ($enable && $is_enabled) {
			$enabled = false;
			restore_error_handler();
			return false;
		}
	}

