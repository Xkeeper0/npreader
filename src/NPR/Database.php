<?php

	namespace NPR;
	use PDO;

	class Database {

		protected static $databases	= [];

		protected $filename		= "npr.db";
		protected $structure	= "npr.sql";
		protected $database		= null;

		/**
		 * Create sqlite db instance
		 */
		protected function __construct($filename = null, $structure = null) {
			if ($filename) {
				$this->filename		= $filename;
			}
			if ($structure) {
				$this->structure	= $structure;
			}

			$needsInit		= !file_exists($this->filename);

			$database		= new PDO("sqlite:". $this->filename);
			$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			if ($needsInit) {
				$this->init($database);
			}

			$this->database	= $database;

		}

		/**
		 * No clone
		 */
		protected function __clone() {
			// Don't.
		}



		protected function init($db) {
			$sql	= file_get_contents($this->structure);
			$db->exec($sql);
		}



		/**
		 * Get a singleton instance of our database
		 */
		public static function getDatabase($name = "default", $filename = null, $structure = null) {
			if (!isset(static::$databases[$name])) {
				static::$databases[$name]	= new static($filename, $structure);
			}

			return static::$databases[$name]->database();
		}


		protected function database() {
			return $this->database;
		}


	}

