<?php


	class Log {
		public static function message($message, $level = null) {
			$msg	= date("Ymd His") ." > $message\n";

			file_put_contents("out.log", date($msg, FILE_APPEND));

			if (defined('STDERR') && (!function_exists('posix_isatty') || posix_isatty(STDERR))) {
				fwrite(STDERR, $msg);
			}

		}
	}
