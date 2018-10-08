BEGIN TRANSACTION;
CREATE TABLE `story_text` (
	`storyId`	INTEGER NOT NULL,
	`text`	TEXT NOT NULL,
	`fetched`	TEXT NOT NULL,
	PRIMARY KEY(`storyId`)
);
CREATE TABLE "stories" (
	`storyId`	INTEGER NOT NULL UNIQUE,
	`title`	TEXT NOT NULL,
	`summary`	TEXT,
	`authorId`	INTEGER,
	`published`	TEXT NOT NULL,
	`modified`	TEXT,
	`image`	TEXT,
	`fetched`	TEXT,
	PRIMARY KEY(storyId)
);
CREATE TABLE `feeds` (
	`feedId`	INTEGER NOT NULL UNIQUE,
	`title`	TEXT NOT NULL,
	`updated`	TEXT,
	PRIMARY KEY(`feedId`)
);
CREATE TABLE `feed_stories` (
	`feedId`	INTEGER NOT NULL,
	`storyId`	INTEGER NOT NULL,
	PRIMARY KEY(`storyId`,`feedId`)
);
CREATE TABLE "authors" (
	`authorId`	INTEGER NOT NULL,
	`name`	TEXT NOT NULL,
	PRIMARY KEY(authorId)
);
COMMIT;
