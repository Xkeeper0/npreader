<?php

	namespace X\NPR;
	use PDO;
	use X\NPR\Data\Story;
	use X\NPR\Data\Revision;
	use X\Log;


	class StoryRevisionFetcher {


		public static function updateAllMissing() {

			$database	= Database::getDatabase();

			// Grab a matching feed from the database...
			$query		= $database->prepare("
								SELECT * FROM `stories`
								WHERE `revisionId` IS NULL
								LIMIT 2
							");
			$query->execute();

			// See if it's in the database already...
			$allStories	= $query->fetchAll(PDO::FETCH_CLASS, Story::class);

			$count	= count($allStories);
			$done	= 0;

		 	Log::message("Downloading revisions for $count stories");
			foreach ($allStories as $story) {
				$revision	= new Revision($story);
				$revision->fetchAndUpdate();

				$done++;
				Log::message("  Finished downloading $done / $count");
				if ($done !== $count) {
					$timeToSleep	= mt_rand(2, 4);
					Log::message("    Sleeping for $timeToSleep seconds...");
					sleep($timeToSleep);
				}
			}

			Log::message("Done updating revisions!");

		}

	}
