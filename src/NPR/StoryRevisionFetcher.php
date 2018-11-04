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
							");
			$query->execute();

			// See if it's in the database already...
			$allStories	= $query->fetchAll(PDO::FETCH_CLASS, Story::class);

			$count	= count($allStories);
			$done	= 0;

			if ($count === 0) {
				Log::message("No new revisions seen, nothing to do here.");
				return;
			}

		 	Log::message("Downloading revisions for $count stories");
			$errors		= 0;
			foreach ($allStories as $story) {

				try {
					$revision	= new Revision($story);

					$revision->fetchAndUpdate();
					$sleepExtra			= 0;

				} catch (\Exception $e) {
					Log::message("    Failed to fetch and update revision: ". $e->getMessage());
					$errors++;
					$sleepExtra			= 5;

				} finally {

					$done++;
					Log::message("  Finished downloading $done / $count". ($errors > 0 ? sprintf(" (%d error(s))", $errors) : ""));

					if ($done !== $count) {
						$timeToSleep	= mt_rand(50, 200) / 100 + $sleepExtra;
						Log::message("    Sleeping for ". number_format($timeToSleep, 1) ." seconds...");
						usleep($timeToSleep * 1000000);
					}

				}

			}

			Log::message("Done updating revisions!");

		}

	}
