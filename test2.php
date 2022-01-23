<?php


	require_once("src/include.php");




	$feeds	= [1002, 1001, 1032, 1039];
	$feed	= [];
	foreach ($feeds as $id) {
		$feed[$id]		= \X\NPR\Data\Feed::getFromId($id);

	}

	foreach ($feed as $f) {
		$f->parseStories();
	}

	\X\NPR\Data\Collection\Stories::commit();
	\X\NPR\Data\Tag::commitTags();

	// Update stories with missing revisions
	\X\NPR\StoryRevisionFetcher::updateAllMissing();
	\X\NPR\RevisionUpdater::parseMissingRevisions();
