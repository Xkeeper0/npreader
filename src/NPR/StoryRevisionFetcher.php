<?php

	namespace X\NPR;
	use PDO;
	use X\NPR\Data\Story;
	use X\NPR\Data\Revision;


	class StoryRevisionFetcher {


		public static function updateAllMissing() {

			$database	= Database::getDatabase();

			// Grab a matching feed from the database...
			$query		= $database->prepare("
								SELECT * FROM `stories`
								WHERE `revisionId` IS NULL
								LIMIT 1
							");
			$query->execute();

			// See if it's in the database already...
			$result		= $query->fetchObject(Story::class);

			var_dump($result);

		}

	}
