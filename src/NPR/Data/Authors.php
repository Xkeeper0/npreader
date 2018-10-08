<?php

	namespace NPR\Data;

	class Authors {

		// Known authors
		protected static $authors	= [
				'linked'	=> [],
				'unlinked'	=> [],
			];


		// "author":{"name":"Genevieve Valentine"}
		// "author":{"name":"Chris Arnold","url":"https:\/\/www.npr.org\/people\/2100196\/chris-arnold?utm_medium=JSONFeed&utm_campaign=homepagetopstories"



		public static function getAuthor($authorData) {
			$name	= \d($authorData->name);
			$url	= \d($authorData->url);
			$id		= null;

			if ($url !== null) {
				$id	= static::extractIdFromUrl($url);
			}

			// Author has an ID, see if we know about it
			if ($id) {
				$res	= \d(static::$authors['linked'][$id]);
				if ($res) {
					return $res;
				} else {
					return static::createAuthor($id, $name);
				}

			} else {
				$res	= \d(static::$authors['unlinked'][$name]);
				if ($res) {
					return $res;
				} else {
					return static::createAuthor(null, $name);
				}

			}

		}


		protected static function createAuthor($authorId, $authorName) {

			$author	= Author::fromDatabase($authorId, $authorName);

			if ($authorId === null) {
				// If unknown, insert into both holding areas
				static::$authors['unlinked'][$authorName]			= $author;
				static::$authors['linked'][$author->authorId]		= $author;

			} else {
				// Insert only into the 'known' one
				static::$authors['linked'][$author->authorId]		= $author;				
			}

			return $author;
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
		 * Gets an author URL from an ID
		 * 
		 * @param int $id Author ID
		 * @return string URL to view that author on NPR
		 */
		public static function getUrlFromId($id) {
			return "https://www.npr.org/people/$id/";
		}


	}
