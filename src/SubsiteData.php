<?php

namespace Ngg_Path_Fix;

class SubsiteData
{
    public function getSubsites($options)
    {
        global $wpdb;

        $options = AdminPage::clearGeneralOptions($options);

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

            $prefix = $wpdb->base_prefix;
            $bid = $wpdb->get_blog_prefix($blog_row->blog_id);
            $sql = "SELECT option_value AS home,
					(SELECT option_value FROM {$bid}options WHERE option_name='siteurl') AS url,
					(SELECT option_value FROM {$bid}options WHERE option_name='blogname') AS name,
					(SELECT option_value FROM {$bid}options WHERE option_name='template') AS theme,
					(SELECT post_modified FROM {$bid}posts ORDER BY post_modified DESC LIMIT 1) AS modified,
					(SELECT COUNT(ID) FROM {$bid}posts WHERE post_status = 'publish' AND post_type='page') AS pages,
					(SELECT COUNT(ID) FROM {$bid}posts WHERE post_status = 'publish' AND post_type='post') AS posts,
					(SELECT COUNT(term_id) FROM {$bid}terms) AS cats,
					(SELECT COUNT(user_id) FROM {$prefix}usermeta WHERE meta_key = '{$bid}capabilities' AND meta_value NOT LIKE '%subscriber%') AS users,
					CAST({$blog_row->blog_id} AS UNSIGNED INTEGER ) AS blog_id
			FROM {$bid}options
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

}