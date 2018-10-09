<?php

	namespace NPR\Data;
	use NPR\Database;
	use Log;

	class Tag {

		public $tagId;
		public $tagText;

		protected static $tags	= [];


		public function __construct() {
			$key	= strtolower($this->tagText);
			static::$tags[$key]['id']	= $this->tagId;
		}


		public static function addTag($text, Story $story) {
			$key	= strtolower($text);
			Log::message("    Adding tag [$text] to story [$story->storyId]");
			if (!isset(static::$tags[$key])) {
				static::$tags[$key]	= ['id' => null, 'text' => $text, 'stories' => []];
			}

			static::$tags[$key]['stories'][]	= $story->storyId;

		}


		public static function commitTags() {
			Log::message("Committing new tags...");
			static::commitNewTags();
			Log::message("Committing story tags...");
			static::commitStoryTags();
		}

		protected static function commitNewTags() {
			$database	= Database::getDatabase();

			// Grab a matching feed from the database...
			$query		= $database->query("
								SELECT *
								FROM `tags`
							");

			// This will create an array of tags that actually exist already...
			$allTags	= $query->fetchAll(\PDO::FETCH_CLASS, __CLASS__);


			$query		= $database->prepare("INSERT INTO `tags` (`tagText`) VALUES (:tagText)");

			$temp		= static::$tags;
			foreach ($temp as $key => $tag) {
				if ($tag['id']) {
					// Tag already exists in database
					continue;
				}

				Log::message("  New tag: ['{$key}'] ['{$tag['text']}'] (". count($tag['stories']) .")");
				// Insert our new tag here ...
				$query->execute([
					'tagText'	=> $tag['text'],
				]);

				$tagId						= $database->lastInsertId();
				static::$tags[$key]['id']	= $tagId;
				Log::message("    Inserted as id '{$tagId}'");

			}

		}


		protected static function commitStoryTags() {
			$database	= Database::getDatabase();

			$query		= $database->prepare("INSERT OR IGNORE INTO `story_tags` (`storyId`, `tagId`) VALUES (:storyId, :tagId)");

			$temp		= static::$tags;
			foreach ($temp as $tag) {

				if ($tag['stories']) {

					foreach ($tag['stories'] as $storyId) {
						Log::message("  Attaching tag [{$tag['id']}] to story [{$storyId}]");

					}
					// Insert our new tag here ...
					$query->execute([
						'tagId'		=> $tag['id'],
						'storyId'	=> $storyId,
					]);
				}
			}

		}

	}
