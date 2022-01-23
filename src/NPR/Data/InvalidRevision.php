<?php

	namespace X\NPR\Data;
	use X\NPR\Database;
	use X\Log;

	/**
	 * Invalid revisions are stored with a "!" at the start of the text,
	 * rather than the HTML content of a page.
	 * Yes it is a gross hack. No I do not care right now.
	 */
	class InvalidRevision extends Revision {

		/**
		 * Sets the text for this (otherwise invalid) revision.
		 */
		public function setText($text) {
			$this->text	= $text;
		}

		/**
		 * Overrides the fetch method of Revision (which gets the text)
		 */
		public function fetch() {
			Log::message("  Using invalid revision for story [". $this->story->storyId ."]");

			if (!$this->text || $this->text[0] !== "!") {
				// If the first character is not "!", force it to be so
				$this->text	= "!" . ($this->text ? (" ". $this->text) : "");
			}

			return $this->text;
		}
	}