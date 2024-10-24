ALTER TABLE `#__yendifvideoshare_categories` ENGINE=INNODB;
ALTER TABLE `#__yendifvideoshare_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__yendifvideoshare_categories` CHANGE name title VARCHAR(255);
ALTER TABLE `#__yendifvideoshare_categories` CHANGE published state TINYINT(1);
ALTER TABLE `#__yendifvideoshare_categories` ADD COLUMN `checked_out` INT(11) UNSIGNED AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_categories` ADD COLUMN `checked_out_time` DATETIME NULL DEFAULT NULL AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_categories` ADD COLUMN `created_by` INT(11) NULL DEFAULT 0 AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_categories` ADD COLUMN `modified_by` INT(11) NULL DEFAULT 0 AFTER `ordering`;

ALTER TABLE `#__yendifvideoshare_videos` ENGINE=INNODB;
ALTER TABLE `#__yendifvideoshare_videos` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__yendifvideoshare_videos` CHANGE ogg ogv VARCHAR(255);
ALTER TABLE `#__yendifvideoshare_videos` CHANGE published state TINYINT(1);
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `vimeo` VARCHAR(255) NULL DEFAULT "" AFTER `youtube`;
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `checked_out` INT(11) UNSIGNED AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `checked_out_time` DATETIME NULL DEFAULT NULL AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `created_by` INT(11) NULL DEFAULT 0 AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `modified_by` INT(11) NULL DEFAULT 0 AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `updated_date` DATETIME NULL DEFAULT NULL AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `import_id` INT(11) UNSIGNED NULL DEFAULT 0 AFTER `ordering`;
ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `import_key` VARCHAR(255) NULL DEFAULT "" AFTER `ordering`;

CREATE TABLE IF NOT EXISTS `#__yendifvideoshare_imports` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `service` VARCHAR(25) NULL DEFAULT "youtube",
    `type` VARCHAR(25) NULL DEFAULT "playlist",    
    `playlist` VARCHAR(255) NULL DEFAULT "",
    `channel` VARCHAR(255) NULL DEFAULT "",
    `username` VARCHAR(255) NULL DEFAULT "",
    `search` VARCHAR(255) NULL DEFAULT "",
    `videos` TEXT NULL,
    `exclude` TEXT NULL,
    `order_by` VARCHAR(25) NULL DEFAULT "relevance",
    `limit` INT(11) UNSIGNED NOT NULL, 
    `schedule` INT(11) UNSIGNED NULL DEFAULT 1, 
    `reschedule` TINYINT(1) NULL DEFAULT 1,
    `import_state` VARCHAR(25) NULL DEFAULT "", 
    `params` TEXT NULL, 
    `history` TEXT NULL,
    `next_import_date` DATETIME NULL DEFAULT NULL,    
    `video_catid` INT(11) UNSIGNED NOT NULL,
    `video_date` VARCHAR(25) NULL DEFAULT "imported",
    `video_userid` INT(11) NULL DEFAULT 0,
    `video_state` TINYINT(1) NULL DEFAULT 1,
    `state` TINYINT(1) NULL DEFAULT 1,
    `checked_out` INT(11) UNSIGNED,
    `checked_out_time` DATETIME NULL DEFAULT NULL,
    `created_by` INT(11) NULL DEFAULT 0,
    `modified_by` INT(11) NULL DEFAULT 0,     
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__yendifvideoshare_adverts` ENGINE=INNODB;
ALTER TABLE `#__yendifvideoshare_adverts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__yendifvideoshare_adverts` CHANGE published state TINYINT(1);
ALTER TABLE `#__yendifvideoshare_adverts` ADD COLUMN `checked_out` INT(11) UNSIGNED AFTER `clicks`;
ALTER TABLE `#__yendifvideoshare_adverts` ADD COLUMN `checked_out_time` DATETIME NULL DEFAULT NULL AFTER `clicks`;
ALTER TABLE `#__yendifvideoshare_adverts` ADD COLUMN `created_by` INT(11) NULL DEFAULT 0 AFTER `clicks`;
ALTER TABLE `#__yendifvideoshare_adverts` ADD COLUMN `modified_by` INT(11) NULL DEFAULT 0 AFTER `clicks`;

ALTER TABLE `#__yendifvideoshare_ratings` ENGINE=INNODB;
ALTER TABLE `#__yendifvideoshare_ratings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__yendifvideoshare_ratings` ADD COLUMN `updated_date` DATETIME NULL DEFAULT NULL AFTER `sessionid`;

ALTER TABLE `#__yendifvideoshare_likes_dislikes` ENGINE=INNODB;
ALTER TABLE `#__yendifvideoshare_likes_dislikes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__yendifvideoshare_likes_dislikes` ADD COLUMN `created_date` DATETIME NULL DEFAULT NULL AFTER `sessionid`;
ALTER TABLE `#__yendifvideoshare_likes_dislikes` ADD COLUMN `updated_date` DATETIME NULL DEFAULT NULL AFTER `sessionid`;