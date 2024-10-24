CREATE TABLE IF NOT EXISTS `#__yendifvideoshare_categories` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,    
    `title` VARCHAR(255) NOT NULL,
    `alias` VARCHAR(255) COLLATE utf8_bin NULL,
    `parent` INT(11) NULL DEFAULT 0,    
    `image` VARCHAR(255) NULL DEFAULT "",
    `description` TEXT NULL,
    `access` VARCHAR(25) NULL DEFAULT 0,    
    `meta_keywords` TEXT NULL,
    `meta_description` TEXT NULL,
    `state` TINYINT(1) NULL DEFAULT 1,
    `ordering` INT(11) NULL DEFAULT 0,
    `checked_out` INT(11) UNSIGNED,
    `checked_out_time` DATETIME NULL DEFAULT NULL,
    `created_by` INT(11) NULL DEFAULT 0,
    `modified_by` INT(11) NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__yendifvideoshare_videos` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,         
    `title` VARCHAR(255) NOT NULL,
    `alias` VARCHAR(255) COLLATE utf8_bin NULL,
    `catid` INT(11) UNSIGNED NOT NULL,    
    `type` VARCHAR(25) NULL DEFAULT "general",
    `mp4` VARCHAR(255) NULL DEFAULT "",
    `mp4_hd` VARCHAR(255) NULL DEFAULT "",
    `webm` VARCHAR(255) NULL DEFAULT "",
    `ogv` VARCHAR(255) NULL DEFAULT "",
    `youtube` VARCHAR(255) NULL DEFAULT "",
    `vimeo` VARCHAR(255) NULL DEFAULT "",
    `hls` VARCHAR(255) NULL DEFAULT "",
    `dash` VARCHAR(255) NULL DEFAULT "",
    `thirdparty` TEXT NULL,
    `image` VARCHAR(255) NULL DEFAULT "",
    `captions` TEXT NULL,
    `duration` VARCHAR(15) NULL DEFAULT "",
    `description` TEXT NULL,
    `userid` INT(11) NULL DEFAULT 0,
    `access` VARCHAR(25) NULL DEFAULT 0,
    `views` INT(11) NULL DEFAULT 0,   
    `featured` TINYINT(1) NULL DEFAULT 0, 
    `rating` DECIMAL(5,2) NULL DEFAULT 0,   
    `preroll` INT(11) NULL DEFAULT -1, 
    `postroll` INT(11) NULL DEFAULT -1,    
    `meta_keywords` TEXT NULL,
    `meta_description` TEXT NULL,
    `state` TINYINT(1) NULL DEFAULT 1,
    `published_up` DATETIME NULL DEFAULT NULL,
    `published_down` DATETIME NULL DEFAULT NULL,
    `ordering` INT(11) NULL DEFAULT 0,
    `checked_out` INT(11) UNSIGNED,
    `checked_out_time` DATETIME NULL DEFAULT NULL,
    `created_by` INT(11) NULL DEFAULT 0,
    `modified_by` INT(11) NULL DEFAULT 0,
    `created_date` DATETIME NULL DEFAULT NULL,
    `updated_date` DATETIME NULL DEFAULT NULL,
    `related` TEXT NULL, 
    `import_id` INT(11) UNSIGNED NULL DEFAULT 0,  
    `import_key` VARCHAR(255) NULL DEFAULT "",  
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

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
    `video_description` TINYINT(1) NULL DEFAULT 0,
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

CREATE TABLE IF NOT EXISTS `#__yendifvideoshare_adverts` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,    
    `title` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NULL DEFAULT "both",
    `mp4` VARCHAR(255) NOT NULL DEFAULT "",
    `link` VARCHAR(255) NULL DEFAULT "",
    `impressions` INT(11) NULL DEFAULT 0,
    `clicks` INT(11) NULL DEFAULT 0,
    `state` TINYINT(1) NULL DEFAULT 1,
    `checked_out` INT(11) UNSIGNED,
    `checked_out_time` DATETIME NULL DEFAULT NULL,
    `created_by` INT(11) NULL DEFAULT 0,
    `modified_by` INT(11) NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__yendifvideoshare_ratings` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,     
    `videoid` INT(11) NULL DEFAULT 0,    
    `rating` DECIMAL(2,1) NULL DEFAULT 0,
    `userid` INT(11) NULL DEFAULT 0,
    `sessionid` VARCHAR(255) NOT NULL,
    `created_date` DATETIME NULL DEFAULT NULL,
    `updated_date` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__yendifvideoshare_likes_dislikes` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,    
    `videoid` INT(11) NULL DEFAULT 0,    
    `likes` INT(11) NULL DEFAULT 0,
    `dislikes` INT(11) NULL DEFAULT 0,
    `userid` INT(11) NULL DEFAULT 0,
    `sessionid` VARCHAR(255) NOT NULL,
    `created_date` DATETIME NULL DEFAULT NULL,
    `updated_date` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__yendifvideoshare_options` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,    
    `name` VARCHAR(255) NOT NULL,
    `value` TEXT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;