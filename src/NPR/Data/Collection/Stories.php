<?php

	namespace X\NPR\Data\Collection;
	use X\NPR\Data\Story;
	use X\NPR\Database;
	use X\Log;
	use PDO;


	class Stories {

		static protected $stories	= [];


		private function __construct() {}


		public static function addStory($storyData) {
			$id		= intval($storyData->id);
			static::$stories[$id]	= $storyData;
		}


		public static function getReferencedStories() {

			$database	= Database::getDatabase();

			// Grab all stories we have locally
			$query		= $database->query($x = "
								SELECT	*
								FROM	`stories`
								WHERE	`storyId` IN (". static::getStoryIdQuery() .")
							");

			// This will create an array of tags that actually exist already...
			$result		= $query->fetchAll(PDO::FETCH_CLASS, Story::class);

			return rekey($result, function ($s) { return intval($s->storyId); });

		}


		public static function commit() {
			Log::message("Checking for updated stories...");
			$dbStories	= static::getReferencedStories();

			//var_dump(static::$stories, $dbStories);
			foreach (static::$stories as $story) {
				$dbStory	= \d($dbStories[intval($story->id)]);

				if (static::compare($story, $dbStory)) {
					if ($dbStory) {
						Log::message("  Updating story {$story->id}");
						$dbStory->updateDatabase($story);
					} else {
						Log::message("  New story {$story->id}");
						$story	= new Story($story);
						$story->updateDatabase();
					}
				};
			}
		}


		protected static function compare($new, $old) {
			if (!$old) {
				// If there isn't an old story, obviously insert it.
				return true;
			}
			$newTime	= \d($new->date_modified);
			$oldTime	= \d($old->modified);

			if ($newTime && !$oldTime) {
				// If the old one doesn't have a modified date, update it too.
				return true;
			}

			$newTime	= $newTime ? new \DateTime($newTime, new \DateTimeZone("UTC")) : null;
			$oldTime	= $oldTime ? new \DateTime($oldTime, new \DateTimeZone("UTC")) : null;

			if ($newTime > $oldTime) {
				return true;
			} elseif ($newTime == $oldTime) {
				// No update needed
				return false;
			} else {
				// uh.
				Log::message("This story was modified EARLIER than the one we have? Is time flowing backwards?? New: ". $newTime->format("Y-m-d H:i:s P") . ", Old: ". $oldTime->format("Y-m-d H:i:s P"));
				return true;
			}

		}


		protected static function getStoryIdQuery() {
			// Based on the cast above, array keys will always be (int),
			// so we can do this without risking injection
			$ids	= array_keys(static::$stories);
			return implode(", ", $ids);
		}

		public static function getStoryId($id) {
			$id		= intval($id);
			return \d(static::$stories[$id], null);
		}


	}
