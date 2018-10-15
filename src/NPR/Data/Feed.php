<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;


	class Feed {

		public $feedId	= null;
		public $title	= null;
		public $updated	= null;
		public $data	= null;

		protected static $feeds	= [];


		public function __construct($feedId = null, $feedData = null) {
			if ($feedId !== null) {
				$this->feedId	= $feedId;
			}

			if ($feedData === null) {
				$feedData = static::fetchData($this->feedId);
			}

			$this->updateFromData($feedData);

		}


		public static function fetchData($feedId) {
			Log::message("Fetching feed [$feedId]");
			$json	= file_get_contents("https://www.npr.org/feeds/{$feedId}/feed.json");
			$data	= json_decode($json);
			return $data;
		}


		public function parseStories() {
			foreach ($this->data->items as $storyData) {
				Collection\Stories::addStory($storyData);
			}
		}


		protected static function getDataFromId($feedId) {
			return new static($feedId, static::fetchData($feedId));
		}

		protected function updateFromData($feedData) {
			$this->title	= $feedData->title;
			$this->data		= $feedData;
			$this->insert();

		}

		public static function getFromId($feedId) {

			// If we already know about this feed, just give it
			$res		= \d(static::$feeds[$feedId]);
			if ($res) {
				return $res;
			}

			$database	= Database::getDatabase();

			// Grab a matching feed from the database...
			$query		= $database->prepare("
								SELECT * FROM `feeds`
								WHERE `feedId` = :feedId
								LIMIT 1
							");
			$query->execute([
				'feedId'	=> $feedId,
				]);

			// See if it's in the database already...
			$result		= $query->fetchObject(__CLASS__);

			if (!$result) {
				// Otherwise, create a new one
				$result	= static::getDataFromId($feedId);
			}

			return $result;

		}




		protected function insert() {
			$database	= Database::getDatabase();

			if ($this->feedId === null) {
				throw new \RuntimeException("Tried to insert a feed that doesn't exist?");
			}

			$query		= $database->prepare("REPLACE INTO `feeds` (`feedId`, `title`, `updated`) VALUES (:feedId, :title, datetime('now'))");
			$query->execute([
				'feedId'	=> $this->feedId,
				'title'		=> $this->title,
				]);

		}


	}
