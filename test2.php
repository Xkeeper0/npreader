<?php

	//unlink("npr.db");

	require_once("src/include.php");

	//$test	= \NPR\Database::getInstance();
	//var_dump($test	= \NPR\Data\Authors::extractIdFromUrl('https://www.npr.org/people/14562108/bill-chappell?utm_medium=JSONFeed&utm_campaign=homepagetopstories'));
	
	var_dump(
		\NPR\Data\Authors::getAuthor(
			(object)[
				'name' => 'Bob Test 2',
				'url' => 'https://www.npr.org/people/14562108/bill-chappell?utm_medium=JSONFeed&utm_campaign=homepagetopstories'
				]
			)
	);



