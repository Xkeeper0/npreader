<?php

	class Database {

		protected static $instance	= null;
		protected $database			= null;


		/**
		 * Create sqlite db instance
		 */
		protected function __construct() {
			$needsInit		= !file_exists("npr.sqlite");

			$database		= new PDO("sqlite:npr.sqlite");
			$this->database	= $database;
			$this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if ($needsInit) {
				$this->init();
			}

		}

		/**
		 * No clone
		 */
		protected function __clone() {
			// Don't.
		}



		protected function init() {
			$sql	= file_get_contents("npr.sqlite");
			$this->database->exec($databaseStructure);
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

	}


	// Structure for smol database
	$databaseStructure	= <<<SQL
CREATE TABLE "clips" (
	`filename`	TEXT NOT NULL,
	`date`	TEXT NOT NULL,
	`location`	INTEGER NOT NULL,
	`device`	INTEGER NOT NULL,
	`camera`	INTEGER,
	`time_of_day`	TEXT,
	`indoor_outdoor`	TEXT,
	`has_human`		INTEGER,
	`has_car`		INTEGER,
	`make_model` TEXT NOT NULL,
	`verified`	INTEGER NOT NULL,
	`resolution`	TEXT,
	`framerate`	NUMERIC,
	`duration`	NUMERIC,
	PRIMARY KEY(filename)
)
SQL;

	$database->exec($databaseStructure);
