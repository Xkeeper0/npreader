<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;


	class Story {

		public $storyId		= null;
		public $title		= null;
		public $summary		= null;
		public $authorId	= null;
		public $author		= null;
		public $published	= null;
		public $modified	= null;
		public $image		= null;
		public $fetched		= null;
		public $tags		= [];

		protected static $stories	= [];


		public function __construct($storyData = null) {
			if ($storyData !== null) {
				$this->updateFromData($storyData);
			}
		}

		public function updateFromData($storyData) {

			Log::message("    Story: [{$storyData->id}] {$storyData->title}");
			// Handle author, if the story has one
			$authorData		= \d($storyData->author);
			if ($authorData) {
				$this->author		= Author::getFromData($authorData);
				$this->authorId		= $this->author->authorId;
			} else {
				$this->author		= null;
				$this->authorId		= null;
			}


			$this->storyId		= $storyData->id;
			$this->title		= $storyData->title;
			$this->summary		= \d($storyData->summary);
			$this->published	= \d($storyData->date_published);
			$this->modified		= \d($storyData->date_modified);
			$this->image		= \d($storyData->image);

			$this->updateTags(\d($storyData->tags));

		}


		public function updateDatabase($storyData = null) {

			if ($storyData !== null) {
				$this->updateFromData($storyData);
			}

			$this->insert();

		}

		public function updateTags($tags) {
			if (!$tags) {
				return;
			}

			$this->tags	= $tags;
			foreach ($tags as $tag) {
				Tag::addTag($tag, $this);
			}
		}

		public static function getFromId($storyId, $skipDb = false) {
			$res		= \d(static::$stories[$storyId]);
			if ($res || $skipDb) {
				return $res;
			}

			$res	= static::getFromDatabase($storyId);
			if ($res) {
				static::$stories[$storyId]	= $res;
			}
			return $res;

		}


		protected static function getFromDatabase($storyId) {

			$database	= Database::getDatabase();

			// Grab a matching feed from the database...
			$query		= $database->prepare("
								SELECT * FROM `stories`
								WHERE `storyId` = :storyId
								LIMIT 1
							");
			$query->execute([
				'storyId'	=> $storyId,
				]);

			// See if it's in the database already...
			$result		= $query->fetchObject(__CLASS__);

			return $result;

		}


		protected function insert() {

			$database	= Database::getDatabase();

			// Copy the old one to the historical table, if there is one
			$query		= $database->prepare("
							INSERT INTO `story_history`
							SELECT
									NULL as historyId,
									*
							FROM `stories`
							WHERE `storyId` = :storyId
							");
			$query->execute(['storyId' => $this->storyId]);

			// Insert the new/updated story
			$query		= $database->prepare("
								REPLACE INTO `stories`
								(
									`storyId`,
									`title`,
									`summary`,
									`authorId`,
									`published`,
									`modified`,
									`image`,
									`fetched`
								) VALUES (
									:storyId,
									:title,
									:summary,
									:authorId,
									datetime(:published),
									datetime(:modified),
									:image,
									NULL
								)
							");
			$query->execute([
					'storyId'	=> $this->storyId,
					'title'		=> $this->title,
					'summary'	=> $this->summary,
					'authorId'	=> $this->authorId,
					'published'	=> $this->published,
					'modified'	=> $this->modified,
					'image'		=> $this->image,
				]);

			static::$stories[$this->storyId]	= $this;
			Log::message("    Saved story [{$this->storyId}]");

		}

	}
