<?php


	class Log {
		public static function message($message, $level = null) {
			$msg	= "Ymd His > $message\n";

			file_put_contents("out.log", date($msg, FILE_APPEND));
			
			if (defined('STDERR') && posix_isatty(STDERR)) {
				fwrite(STDERR, $msg);
			}

		}
	}
