<?php

	namespace X\NPR;
	use PDO;
	use X\NPR\Data\Story;
	use X\NPR\Data\Revision;
	use X\NPR\Data\InvalidRevision;
	use X\NPR\Exception\StoryNotFoundException;
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

				} catch (StoryNotFoundException $e) {
					Log::message("    Story not found when fetching: ". $e->getMessage());

					// Logic for giving up parsing. Yes it is stupid. No I do not care (right now).
					// Check how long it has been since this story was last updated/published;
					// we'll keep trying for one day and then give up
					$storyDate	= new \DateTime($story->modified ?? $story->published);
					$nowDate	= new \DateTime("now");
					$diffTime	= $nowDate->getTimestamp() - $storyDate->getTimestamp();
					Log::message("    Age of revision/publish: ". $diffTime ." second(s)");

					if ($diffTime >= 86400) {
						// If it's been over a day, give up and insert an empty revision.
						// There is probably a better way to do this, but for the time being
						// this will have to suffice
						Log::message("    Giving up on downloading revision. :(");
						$revision	= new InvalidRevision($story);
						$revision->setText("This story was unable to be archived.\n\nException: ". $e->getMessage());
						$revision->fetchAndUpdate();

					} else {
						Log::message("    Will retry on next update cycle.");
					}

					$errors++;
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
