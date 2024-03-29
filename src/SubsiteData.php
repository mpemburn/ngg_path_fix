<?php

namespace Ngg_Path_Fix;

class SubsiteData
{
    public function getSubsites($options): array
    {
        global $wpdb;

        $options = AdminPage::clearGeneralOptions($options);

        // get all the blog ids from the blogs table
        $blogs = $this->getBlogs();

        // build a sql statement for each blog options table, adding in the blog id for each row
        $records = [];

        foreach ($blogs as $blog_row) {

            $blogId = $wpdb->get_blog_prefix($blog_row->blog_id);

            if (!$this->tableExists("{$blogId}ngg_gallery")) {
                continue;
            }

            $galleriesSql = "SELECT COUNT(gid) AS gid_count  FROM {$blogId}ngg_gallery";
            if (!$this->hasGalleries($galleriesSql)) {
                continue;
            }

            $sql = "SELECT option_value AS home,
					(SELECT option_value FROM {$blogId}options WHERE option_name='siteurl') AS url,
					(SELECT option_value FROM {$blogId}options WHERE option_name='blogname') AS name,
					(SELECT option_value FROM {$blogId}options WHERE option_name='siteurl') AS siteurl,
					(SELECT post_modified FROM {$blogId}posts ORDER BY post_modified DESC LIMIT 1) AS modified,
					({$galleriesSql}) AS galleries,
				    (SELECT COUNT(gid) AS gid_count  FROM {$blogId}ngg_gallery WHERE path LIKE '%wp-content/uploads/sites/%') AS fixed,	     
					CAST({$blog_row->blog_id} AS UNSIGNED INTEGER ) AS blog_id
			FROM {$blogId}options
			WHERE option_name = 'home'";

            $records = array_merge($records, $wpdb->get_results($sql, ARRAY_A));
        }

        if ($wpdb->last_error) {
            echo 'DB Error: ' . $wpdb->last_error;
        }

        $sorted = array_column($records, $options['orderby']);
        $direction = strtolower($options['order']) === 'asc' ? SORT_ASC : SORT_DESC;
        array_multisort($sorted, $direction, $records);

        return $records;
    }

    protected function getBlogs(): array
    {
        return get_sites(['archived' => 0]);
    }

    protected function tableExists(string $tableName): bool
    {
        global $wpdb;

        $sql = "SELECT COUNT(1) AS count FROM information_schema.tables ";
        $sql .= "WHERE table_schema='{$wpdb->dbname}' AND table_name='{$tableName}';";

        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach (current($results) as $result) {
            if ((int) $result === 1) {
                return true;
            }
        }

        return false;
    }

    protected function hasGalleries(string $sql): bool
    {
        global $wpdb;

        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results ? (int)current($results)['gid_count'] > 0 : false;
    }

}