<?php

	namespace X\NPR;
	use PDO;
	use X\NPR\Data\Revision;
	use X\Log;


	class RevisionUpdater {

		protected const ALL_REVISIONS		= 1;
		protected const MISSING_REVISIONS	= 2;
		protected const SPECIFIC_REVISIONS	= 3;


		public static function parseMissingRevisions() {
			$toUpdate	= static::getRevisions(static::MISSING_REVISIONS);
			static::updateRevisions($toUpdate);

		}

		public static function parseAllRevisions() {
			$toUpdate	= static::getRevisions(static::ALL_REVISIONS);
			static::updateRevisions($toUpdate);

		}

		public static function parseSpecificRevisions($id) {
			if (!is_numeric($id) && !is_array($id)) {
				// "I need you to get something from the store."
				// "What?"
				// ¯\_(ツ)_/¯
				throw new \InvalidArgumentException("Updating specific revisions requires an ID or an array of IDs");

			} elseif (is_array($id)) {
				// Multiple IDs, so make sure they're all numbers
				$ids		= array_map('intval', $id);

			}	elseif (is_numeric($id)) {
				// Just one, so put it in an array all by itself
				$ids		= [intval($id)];
			}

			$toUpdate	= static::getRevisions(static::SPECIFIC_REVISIONS, $ids);
			static::updateRevisions($toUpdate);

		}

		protected static function getRevisions($which, $ids = null) {
			$database	= Database::getDatabase();

			$where		= "";

			switch ($which) {
				case static::ALL_REVISIONS:
					// We're updating EVERYTHING! You should probably not do this often
					$where	= "1";
					break;

				case static::MISSING_REVISIONS:
					// Just new ones that we haven't parsed yet.
					$where	= "`parsedText` IS NULL";
					break;

				case static::SPECIFIC_REVISIONS:
					if ($ids === null) {
						throw new \InvalidArgumentException("Told to update specifics revisions but not told which ones..?");
					}
					// Given a list of specific ones, update just those.
					$param		= implode(", ", $ids);

					// $param will either be a single number or a '#, #, #' ... string here
					$where	= "`revisionId` IN ($param)";
					break;

				default:
					// @TODO better exception
					throw new \InvalidArgumentException("I don't know what you did to get here but it wasn't correct.");
					break;

			}

			// Grab a matching feed from the database...
			$query		= $database->prepare($x = "
								SELECT * FROM `revisions`
								WHERE $where
							");
			$query->execute();
			$result		= $query->fetchAll(PDO::FETCH_CLASS, Revision::class);
			return $result;

		}


		protected static function updateRevisions($revisions) {

			$count	= count($revisions);
			Log::message("Updating $count revision parses ...");

			foreach ($revisions as $revision) {
				$revision->parseAndSave();
			}
			Log::message("Done updating revision parses!");
		}

	}
