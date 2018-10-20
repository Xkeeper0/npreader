<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;

	class Revision {

		public $revisionId	= null;
		public $storyId		= null;
		public $text		= null;
		public $parsedText	= null;

		public $story		= null;


		public function __construct(Story $story = null) {
			if ($story) {
				$this->story	= $story;
				$this->storyId	= $story->storyId;

			} elseif (!$this->storyId) {
				// @todo better exception/message
				throw new \Exception("you, uh, can't do this");
			}
		}


		public function fetchAndUpdate() {
			$database	= Database::getDatabase();

			$this->fetch();

			$query		= $database->prepare("
								INSERT INTO `revisions`
								(
									`revisionId`,
									`storyId`,
									`fetched`,
									`text`
								) VALUES (
									NULL,
									:storyId,
									datetime('now'),
									:text
								)
							");
			$query->execute([
					'storyId'	=> $this->story->storyId,
					'text'		=> $this->text,
				]);

			$this->revisionId	= $database->lastInsertId();
			Log::message("  Inserted new revision [". $this->revisionId ."]");

			$this->story->addRevision($this);

		}

		public function fetch() {
			Log::message("  Fetching story text for story [". $this->story->storyId ."]");
			$this->text	= static::getText($this->story->storyId);
			return $this->text;
		}


		protected static function getText($storyId) {
			$text	= file_get_contents("https://text.npr.org/s.php?sId=$storyId");
			Log::message("    Story text for [$storyId]: ". strlen($text) ." byte(s)");
			return $text;
		}


		public function parseAndSave() {
			if (!$this->revisionId) {
				throw new \Exception("Can't save a revision we haven't saved yet!");
			}

			Log::message("  Updating parsed text for revision [". $this->revisionId ."] ...");
			$parsedText	= $this->parseToMarkdown();


			$database	= Database::getDatabase();

			$query		= $database->prepare("
								UPDATE `revisions`
								SET		`parsedText`	= :parsedText
								WHERE	`revisionId`	= :revisionId
							");
			$query->execute([
					'parsedText'	=> $parsedText,
					'revisionId'	=> $this->revisionId,
				]);

			Log::message("    Updated parsed text for revision [". $this->revisionId ."]");

		}


		public function parseToMarkdown() {

			// First, clean up the original article HTML...
			$htmlDoc	= static::cleanupHTML($this->text);

			// ...then extract just the <body> element...
			$cleanDoc	= static::extractHTMLBody($htmlDoc);

			// ...then turn it into Markdown for storage.
			$this->parsedText	= trim(html2markdown($cleanDoc->saveHTML())) ."\r\n";
			return $this->parsedText;

		}


		protected static function cleanupHTML($html) {

			// Make a new document object and make it look decent
			$doc						= new \DOMDocument();
			$doc->preserveWhiteSpace	= false;
			$doc->formatOutput			= true;

			// Ensure the loaded HTML is treated as UTF-8
			@$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
			$doc->normalizeDocument();

			// Cargo-culting that likely doesn't do anything but doesn't hurt either
			$root	= $doc->documentElement;
			$root->normalize();
			$body	= $root->getElementsByTagName("body")->item(0);
			$body->normalize();

			// Get rid of obnoxious <script>s
			// In theory we could just target the single one there is, but...
			// ...eh
			static::deleteAllTags($body, "script");

			// Delete empty text nodes (caused by, of all things, whitespace)
			// Done in two foreach() loops because deleting in place causes Problems
			$delort	= [];
			foreach ($body->childNodes as $node) {
				if ($node->nodeName === "#text" && trim($node->nodeValue) === "") {
					$delort[]	= $node;
				}
			}

			foreach ($delort as $deleted) {
				$body->removeChild($deleted);
			}

			// Do it again, but for spurious <p> tags that are now empty
			// Sometimes these are created by DOMDocument being a little
			// over-eager with things like <hr> (-> "<p></p><hr><p></p>")
			$delort	= [];
			foreach ($body->childNodes as $node) {
				if ($node->nodeName === "p" && trim($node->nodeValue) === "" && !$node->hasChildNodes()) {
					$delort[]	= $node;
				}
			}
			foreach ($delort as $deleted) {
				$body->removeChild($deleted);
			}

			// Remove the text-only site's header ...
			$body->removeChild($body->firstChild);
			$body->removeChild($body->firstChild);

			// ...and the footer
			$body->removeChild($body->lastChild);
			$body->removeChild($body->lastChild);

			// @TODO Need to update the first few elements;
			// the first one should be converted to a header.
			// The second one is either [By (author)] or not present;
			// the third one is "NPR.org, (date)" OR "(Program name)",
			// then "(middle dot)", and then
			// EITHER "Updated (time)" OR the first line of the story text
			// Yes, this is absolutely as obnoxious as it seems...
			// It may be a better idea to regex this in the Markdown phase,
			// just because at that point we can trust it's (probably) plain text.

			return $doc;

		}


		protected static function extractHTMLBody(\DOMDocument $document) {

			// Get the original <body> element...
			$body	= $document->getElementsByTagName("body")->item(0);
			$clone	= $body->cloneNode(true);

			// ...and then stuff it into a nice, new document,
			// without any of the other tags (html, head, etc.)
			$clean	= new \DOMDocument();
			$clean->preserveWhiteSpace	= false;
			$clean->formatOutput		= true;
			$clean->appendChild($clean->importNode($clone, true));

			return $clean;
		}



		protected static function deleteAllTags(\DOMElement $element, $tagName) {
			$junkA	= $element->getElementsByTagName($tagName);

			$delete	= [];
			foreach ($junkA as $junk) {
				$delete[]	= $junk;
			}

			foreach ($delete as $now) {
				$now->parentNode->removeChild($now);
			}

			return $element;

		}


	}
