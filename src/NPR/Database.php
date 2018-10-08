<?php

	namespace NPR;
	use PDO;

	class Database {

		protected static $instance	= null;
		protected static $database	= null;


		/**
		 * Create sqlite db instance
		 */
		protected function __construct() {
			$needsInit		= !file_exists("npr.db");

			$database		= new PDO("sqlite:npr.db");
			$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			if ($needsInit) {
				$this->init($database);
			}

			static::$database	= $database;

		}

		/**
		 * No clone
		 */
		protected function __clone() {
			// Don't.
		}



		protected function init($db) {
			$sql	= file_get_contents("npr.sql");
			$db->exec($sql);
		}


		/**
		 * Get a singleton instance of our database
		 */
		public static function getInstance() {
			if (!isset(static::$instance)) {
				static::$instance	= new static();
			}

			return static::$instance;
		}


		/**
		 * Get a singleton instance of our database
		 */
		public static function getDatabase() {
			if (!isset(static::$database)) {
				static::$instance	= new static();
			}

			return static::$database;
		}


	}

