<?php

if (defined('WPSEO_FILE')) {
    
    add_action( 'wpseo_run_upgrade', function($version) { 
        global $wpdb;
        $sqls = [
            'CREATE UNIQUE INDEX `'.$wpdb->prefix .'yoast_migrations_version` ON `'.$wpdb->prefix .'yoast_migrations`(`version`)',
            'CREATE INDEX `object_type_and_sub_type` ON `'.$wpdb->prefix .'yoast_indexable`(`object_type`, `object_sub_type`)',
            'CREATE INDEX `permalink_hash` ON `'.$wpdb->prefix .'yoast_indexable`(`permalink_hash`)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `created_at` datetime',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP',
            'CREATE INDEX `post_taxonomy` ON `'.$wpdb->prefix .'yoast_primary_term`(`post_id`, `taxonomy`)',
            'CREATE INDEX `post_term` ON `'.$wpdb->prefix .'yoast_primary_term`(`post_id`, `term_id`)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_primary_term` ADD COLUMN `created_at` datetime',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_primary_term` ADD COLUMN `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP',
            'CREATE INDEX `indexable_id` ON `'.$wpdb->prefix .'yoast_indexable_hierarchy`(`indexable_id`)',
            'CREATE INDEX `ancestor_id` ON `'.$wpdb->prefix .'yoast_indexable_hierarchy`(`ancestor_id`)',
            'CREATE INDEX `depth` ON `'.$wpdb->prefix .'yoast_indexable_hierarchy`(`depth`)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `blog_id` bigint(20) DEFAULT 1 NOT NULL',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable_hierarchy` ADD COLUMN `blog_id` bigint(20) DEFAULT 1 NOT NULL',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_primary_term` ADD COLUMN `blog_id` bigint(20) DEFAULT 1 NOT NULL',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `language` varchar(32)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `region` varchar(32)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `schema_page_type` varchar(64)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `schema_article_type` varchar(64)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `breadcrumb_title` `breadcrumb_title` text',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `title` `title` text',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `open_graph_title` `open_graph_title` text',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `twitter_title` `twitter_title` text',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `open_graph_image_source` `open_graph_image_source` text',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `twitter_image_source` `twitter_image_source` text',
            'CREATE INDEX `object_id_and_type` ON `'.$wpdb->prefix .'yoast_indexable`(`object_id`, `object_type`)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `has_ancestors` tinyint(1) DEFAULT \'0\'',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `permalink_hash` `permalink_hash` varchar(40)',
            //'SHOW KEYS FROM `'.$wpdb->prefix .'yoast_indexable`',
            //'SHOW KEYS FROM `'.$wpdb->prefix .'yoast_indexable`',
            'CREATE INDEX `permalink_hash_and_object_type` ON `'.$wpdb->prefix .'yoast_indexable`(`permalink_hash`, `object_type`)',
            //'SELECT * FROM '.$wpdb->prefix .'yoast_seo_links LIMIT 1',
            //'SHOW KEYS FROM `'.$wpdb->prefix .'yoast_seo_links`',
            'CREATE INDEX `link_direction` ON `'.$wpdb->prefix .'yoast_seo_links`(`post_id`, `type`)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_seo_links` ADD COLUMN `indexable_id` int(11) UNSIGNED',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_seo_links` ADD COLUMN `target_indexable_id` int(11) UNSIGNED',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_seo_links` ADD COLUMN `height` int(11) UNSIGNED',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_seo_links` ADD COLUMN `width` int(11) UNSIGNED',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_seo_links` ADD COLUMN `size` int(11) UNSIGNED',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_seo_links` ADD COLUMN `language` varchar(32)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_seo_links` ADD COLUMN `region` varchar(32)',
            'CREATE INDEX `indexable_link_direction` ON `'.$wpdb->prefix .'yoast_seo_links`(`indexable_id`, `type`)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `post_status` `post_status` varchar(20)',
            'CREATE INDEX `subpages` ON `'.$wpdb->prefix .'yoast_indexable`(`post_parent`, `object_type`, `post_status`, `object_id`)',
            //'SHOW KEYS FROM `'.$wpdb->prefix .'yoast_indexable`',
            'CREATE INDEX `prominent_words` ON `'.$wpdb->prefix .'yoast_indexable`(`prominent_words_version`, `object_type`, `object_sub_type`, `post_status`)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `estimated_reading_time_minutes` int(11)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `object_id` `object_id` bigint(20)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `author_id` `author_id` bigint(20)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` CHANGE `post_parent` `post_parent` bigint(20)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_primary_term` CHANGE `post_id` `post_id` bigint(20)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_primary_term` CHANGE `term_id` `term_id` bigint(20)',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `version` int(11) DEFAULT 1',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `object_last_modified` datetime',
            'ALTER TABLE `'.$wpdb->prefix .'yoast_indexable` ADD COLUMN `object_published_at` datetime',
            'CREATE INDEX `published_sitemap_index` ON `'.$wpdb->prefix .'yoast_indexable`(`object_published_at`, `is_robots_noindex`, `object_type`, `object_sub_type`)',
        ];

        foreach ($sqls as $sql) {
            //$wpdb->query($sql);
        }
    });
}
