<?php

namespace Ngg_Path_Fix;

class SubsiteData
{
    public function getSubsites($options): array
    {
        global $wpdb;

        // get all the blog ids from the blogs table
        $blogs = $this->getBlogs();

        // build a sql statement for each blog options table, adding in the blog id for each row
        $records = [];
        $offset = ($options['current_page'] > 1) ? $options['per_page'] * ($options['current_page'] - 1) : 0;
        $limit = $options['per_page'];
        $count = 0;

        foreach ($blogs as $blog_row) {
            $count++;
            if ($count <= $offset) {
                continue;
            }

            $blogId = $wpdb->get_blog_prefix($blog_row->blog_id);

            $galleriesSql = "SELECT COUNT(gid) AS gid_count  FROM {$blogId}ngg_gallery";
            if (! $this->hasGalleries($galleriesSql)) {
                continue;
            }

            $sql = "SELECT option_value AS home,
					(SELECT option_value FROM {$blogId}options WHERE option_name='siteurl') AS url,
					(SELECT option_value FROM {$blogId}options WHERE option_name='blogname') AS name,
					(SELECT option_value FROM {$blogId}options WHERE option_name='siteurl') AS siteurl,
					(SELECT post_modified FROM {$blogId}posts ORDER BY post_modified DESC LIMIT 1) AS modified,
					({$galleriesSql}) AS galleries,
					CAST({$blog_row->blog_id} AS UNSIGNED INTEGER ) AS blog_id
			FROM {$blogId}options
			WHERE option_name = 'home'";

            $records = array_merge($records, $wpdb->get_results($sql, ARRAY_A));

            if ($count >= $offset + $limit) {
                break;
            }
        }

        if ($wpdb->last_error) {
            echo 'DB Error: ' . $wpdb->last_error;
        }

        return $records;
    }

    protected function getBlogs(): array
    {
        return get_sites(['archived' => 0]);
    }

    protected function hasGalleries(string $sql): bool
    {
        global $wpdb;

        $results = $wpdb->get_results($sql, ARRAY_A);

        return (int)current($results)['gid_count'] > 0;
    }

}