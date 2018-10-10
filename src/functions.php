<?php


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
