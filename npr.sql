BEGIN TRANSACTION;
CREATE TABLE `tags` (
	`tagId`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`tagText`	TEXT NOT NULL UNIQUE
);
CREATE TABLE `story_tags` (
	`storyId`	INTEGER NOT NULL,
	`tagId`	INTEGER NOT NULL,
	PRIMARY KEY(`storyId`,`tagId`)
);
CREATE TABLE `story_history` (
	`historyId`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`storyId`	INTEGER NOT NULL,
	`title`	TEXT NOT NULL,
	`summary`	TEXT,
	`authorId`	INTEGER,
	`published`	TEXT NOT NULL,
	`modified`	TEXT,
	`image`	TEXT,
	`fetched`	TEXT,
	`revisionId`	INTEGER
);
CREATE TABLE `stories` (
	`storyId`	INTEGER NOT NULL UNIQUE,
	`title`	TEXT NOT NULL,
	`summary`	TEXT,
	`authorId`	INTEGER,
	`published`	TEXT NOT NULL,
	`modified`	TEXT,
	`image`	TEXT,
	`fetched`	TEXT,
	`revisionId`	INTEGER,
	PRIMARY KEY(storyId)
);
CREATE TABLE `revisions` (
	`revisionId`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`storyId`	INTEGER NOT NULL,
	`fetched`	TEXT NOT NULL,
	`text`	TEXT NOT NULL,
	`parsedText`	TEXT
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
	`lastSeen`	TEXT NOT NULL,
	PRIMARY KEY(feedId,storyId)
);
CREATE TABLE `authors` (
	`authorId`	INTEGER NOT NULL,
	`name`	TEXT NOT NULL,
	PRIMARY KEY(`authorId`)
);
CREATE VIEW `tag_usage` AS
SELECT COUNT(*) as 'count', `tagId`, `tagText` FROM  `story_tags`
LEFT JOIN `tags`
USING (`tagId`)
GROUP BY `tagId`
ORDER BY count DESC, tagText ASC;
CREATE VIEW `stories_with_history` AS
SELECT NULL as 'historyId', * FROM `stories` UNION SELECT * FROM `story_history`;
COMMIT;
