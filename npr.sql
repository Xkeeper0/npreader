BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS `story_text` (
	`storyId`	INTEGER NOT NULL,
	`text`	TEXT NOT NULL,
	`fetched`	TEXT NOT NULL,
	PRIMARY KEY(`storyId`)
);
CREATE TABLE IF NOT EXISTS `stories` (
	`storyId`	INTEGER NOT NULL UNIQUE,
	`title`	TEXT NOT NULL,
	`summary`	TEXT,
	`published`	TEXT NOT NULL,
	`modified`	TEXT,
	`image`	TEXT,
	`fetched`	TEXT,
	PRIMARY KEY(`storyId`)
);
CREATE TABLE IF NOT EXISTS `feeds` (
	`feedId`	INTEGER NOT NULL UNIQUE,
	`title`	TEXT NOT NULL,
	`updated`	TEXT,
	PRIMARY KEY(`feedId`)
);
CREATE TABLE IF NOT EXISTS `feed_stories` (
	`feedId`	INTEGER NOT NULL,
	`storyId`	INTEGER NOT NULL,
	PRIMARY KEY(`storyId`,`feedId`)
);
COMMIT;
