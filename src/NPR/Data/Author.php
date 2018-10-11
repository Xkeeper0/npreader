<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;

	class Author {

		public $authorId			= null;
		public $name				= null;


		// Known authors (internal cache)
		protected static $authors	= [
				'null'	=> [],			// Collection of id-less authors
			];


		/**
		 * Make a new author object and insert it into the database
		 * @param int|null $authorId   NPR Author ID (or null if none)
		 * @param string $name     Author name
		 */
		public function __construct($authorId = null, $name = null) {
			if ($authorId === null && $name === null) {
				$this->authorId		= intval($this->authorId);
				return;
			}

			Log::message("      Adding author [". (\d($authorId, "new")) ."] '$name'");
			$this->authorId			= $authorId;
			$this->name				= $name;

			$this->insert();

		}


		/**
		 * Get an author from feed a feed's data
		 * Will check internal cache, then database, and create if missing
		 * @param  object $authorData "author" object from feed json
		 * @return Author author object
		 */
		public static function getFromData($authorData) {

			$name	= \d($authorData->name);
			$url	= \d($authorData->url);
			$id		= null;

			if ($url !== null) {
				$id	= static::extractIdFromUrl($url);
			}

			if ($id) {
				// Author has an NPR ID, see if we know of it already
				$res	= \d(static::$authors[$id]);
			} else {
				// No NPR ID, see if we know their name
				$res	= \d(static::$authors[null][$name]);
			}

			if (!$res) {
				// We don't, so let's create one for them
				$res		= static::getFromDatabase($id, $name);

				if ($id) {
					// NPR IDs are their own keys...
					static::$authors[$id]			= $res;
				} else {
					// ...non-linked authors are under a null key, indexed by name
					static::$authors[null][$name]	= $res;
				}
			}

			return $res;

		}


		/**
		 * Find (and return) an author's entry in the database
		 * if $authorId is specified, it will be used instead of $name
		 * @param  int|null $authorId authorId of the author
		 * @param  int|null $name     Name of the author
		 * @return Author|false  An Author object or false if not found
		 */
		public static function getFromDatabase($authorId, $name = null) {

			$database	= Database::getDatabase();

			// Find by id or name, preferring id
			// NPR authors always have ids > 1,
			// text-only (non-link) authors are assigned < 0
			$query		= $database->prepare("
								SELECT * FROM `authors`
								WHERE
									(`authorId` = :authorId)
									OR
									(`name` = :name AND `authorId` < 0)
								ORDER BY `authorId` DESC
								LIMIT 1
							");
			$query->execute([
				'authorId'	=> $authorId,
				'name'		=> $name,
				]);

			$result		= $query->fetchObject(__CLASS__);
			if ($result) {
				// Author already exists
				if ($name && $result->name !== $name) {
					Log::message("    Author [{$authorId}] changed names? From '{$result->name}' to '$name'");
					$result->updateName($name);
				}
				return $result;

			} else {
				// Create new author
				return new static($authorId, $name);

			}

		}


		/**
		 * Update an author's name
		 * @param  string $name new name to use
		 * @return void
		 */
		public function updateName($name) {
			if ($this->authorId === null || $this->authorId < 0) {
				throw new \RuntimeException("Tried to update name-only author");
			}

			$this->name	= $name;
			$this->insert();
		}


		/**
		 * Insert author into db
		 * Inserts the author object into the database
		 *
		 * @return void
		 */
		protected function insert() {
			$database	= Database::getDatabase();

			if ($this->authorId === null) {
				// If there is no ID for this author, generate a new one for them
				// id-less authors are given an auto-decrementing negative id,
				// as NPR-linked (with id) authors are positive IDs
				$query			= $database->query("SELECT MIN(`authorId`) AS 'min' FROM `authors`");
				$result			= $query->fetch();
				$this->authorId	= min(0, intval($result['min'])) - 1;
			}

			// REPLACE INTO in the event of name changes
			$query		= $database->prepare("REPLACE INTO `authors` (`authorId`, `name`) VALUES (:authorId, :name)");
			$query->execute([
				'authorId'	=> $this->authorId,
				'name'		=> $this->name,
				]);

		}


		/**
		 * Gets the ID from a NPR author URL (most of the time)
		 *
		 * @param string $url URL to extract from
		 * @return int|null author id (if found), otherwise null
		 */
		public static function extractIdFromUrl($url) {
			//
			$matches	= [];
			$matched	= preg_match('#npr\.org/people/([0-9]+)/#is', $url, $matches);
			if ($matched) {
				return $matches[1];
			} else {
				return null;
			}
		}


		/**
		 * Gets an author's NPR URL
		 *
		 * @param int|null $id Author ID; null for the author itself
		 * @return string URL to view that author on NPR
		 */
		public function getUrl($id = null) {
			if ($id === null && isset($this) && $this instanceof static) {
				return ($this->authorId > 0 ? "https://www.npr.org/people/{$this->authorId}/" : null);
			}

			return "https://www.npr.org/people/$id/";
		}


	}
