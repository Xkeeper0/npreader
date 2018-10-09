<?php

	namespace NPR\Data;
	use NPR\Database;
	use Log;


	class Story {

		public $storyId		= null;
		public $title		= null;
		public $summary		= null;
		public $authorId	= null;
		public $author		= null;
		public $published	= null;
		public $modified	= null;
		public $image		= null;
		public $fetched		= null;

		protected static $stories	= [];


	}
