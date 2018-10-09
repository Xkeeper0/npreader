<?php

	namespace NPR\Data;
	use NPR\Database;
	use Log;


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
			$author			= Author::getFromData($storyData->author);
			Log::message("  Got story id {$storyData->id}: Author {$author->authorId} - {$storyData->title}");

			$this->storyId		= $storyData->id;
			$this->title		= $storyData->title;
			$this->summary		= \d($storyData->summary);
			$this->authorId		= $author->authorId;
			$this->author		= $author;
			$this->published	= \d($storyData->date_published);
			$this->modified		= \d($storyData->date_modified);
			$this->image		= \d($storyData->image);
			$this->tags			= \d($storyData->tags);
			$this->insert();

			if ($this->tags) {
				foreach ($this->tags as $tag) {
					Tag::addTag($tag, $this);
				}
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

			$query		= $database->prepare("
								REPLACE INTO `stories`
								(
									`storyId`,
									`title`,
									`summary`,
									`authorId`,
									`published`,
									`modified`,
									`image`
								) VALUES (
									:storyId,
									:title,
									:summary,
									:authorId,
									datetime(:published),
									datetime(:modified),
									:image
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

		}

	}
