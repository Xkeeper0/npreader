<?php

	//unlink("npr.db");

	require_once("src/include.php");

	//$test	= \NPR\Database::getInstance();
	//var_dump($test	= \NPR\Data\Authors::extractIdFromUrl('https://www.npr.org/people/14562108/bill-chappell?utm_medium=JSONFeed&utm_campaign=homepagetopstories'));

/*
	var_dump(
		\NPR\Data\Author::getAuthorFromData(
			(object)[
				'name' => 'Bob Test 2',
				'url' => 'https://www.npr.org/people/14562108/bill-chappell?utm_medium=JSONFeed&utm_campaign=homepagetopstories'
				]
			)
	);
*/

//	$fetcher	= new NPR\FeedFetcher();
//	$feeds		= $fetcher->fetch();

	$feed		= NPR\Data\Feed::getFromId(1002);

	$feed->parseStories();
	
