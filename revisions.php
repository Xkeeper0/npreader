<?php


	require_once("src/include.php");


	\X\NPR\StoryRevisionFetcher::updateAllMissing();
	\X\NPR\RevisionUpdater::parseMissingRevisions();
