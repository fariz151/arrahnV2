ALTER TABLE `#__yendifvideoshare_videos` ADD COLUMN `related` TEXT NULL AFTER `updated_date`;

ALTER TABLE `#__yendifvideoshare_imports` ADD COLUMN `video_description` TINYINT(1) NULL DEFAULT 0 AFTER `video_catid`;