<?php

namespace Ngg_Path_Fix;

class FixPaths
{
    protected ?string $blogBasePath;
    protected string $blogPrefix;

    public function __construct()
    {
        global $wpdb;

        if (! isset($_REQUEST['blog_id'])) {
            return;
        }
        $blogId = $_REQUEST['blog_id'];

        $this->blogBasePath = $this->getBlogBaseUrl($blogId);
        $this->blogPrefix = $wpdb->get_blog_prefix($blogId);

        $file = plugin_dir_path(__FILE__) . 'js/path-fix.js';
        $cacheBuster = filemtime($file);
        wp_register_script('path-fix', plugins_url('/js/path-fix.js', __FILE__), array(), $cacheBuster, TRUE);
        wp_enqueue_script('path-fix');
    }

    public function render()
    {
        $blogId = $_REQUEST['blog_id'];


        $data = $this->getGalleries($blogId);
        echo '<h2>Fix Gallery Paths</h2>';
        echo '<div><strong>Click the [ Test ] button to see if the suggested path works.</strong></div>';
        echo '<div><strong>When the images appear (i.e., are not broken), use [ Submit ] to update the gallery path.</strong></div><br/>';
        echo '<table style="width: 90%; border-collapse: collapse;">';
        echo '<thead style="background-color: #d0d0d0">';
        echo '<th style="width: 40%">';
        echo 'Gallery Path';
        echo '</th>';
        echo '<th style="width: 55%">';
        echo 'Suggested Path';
        echo '</th>';
        echo '<th>';
        echo '</th>';
        echo '<th>';
        echo '</th>';
        echo '</thead>';
        foreach ($data as $gallery) {
            $suggestedPath = $this->suggestedPath($gallery['path']);
            $pagesWithGalleries = $this->getPagesWithGalleries($blogId, $gallery['gid']);
            echo '<tr data-gallery="' . $gallery['gid'] . '">';
            echo '<td>';
            echo '    <span data-check="' . $gallery['gid'] . '"></span>';
            echo '    <span data-current="' . $gallery['gid'] . '" style="font-weight: bolder;">' . $gallery['path'] . '</span>';
            echo '</td>';
            echo '<td><input data-path="' . $gallery['gid'] . '" type="text" value="' . $suggestedPath . '" style="width: 100%;"></td>';
            echo '<td><button data-test="' . $gallery['gid'] . '" style="cursor: pointer;">Test</button></td>';
            echo '<td><button data-submit="' . $gallery['gid'] . '" style="cursor: pointer;" disabled>Submit</button></td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td colspan="3" style="padding-left: 1rem;">';
            echo $pagesWithGalleries;
            echo '  <div data-pictures="' . $gallery['gid'] . '"></div>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function loadGalleryImages()
    {
        $blogId = $_REQUEST['blog_id'];
        $galleryId = $_REQUEST['gallery_id'];
        $path = $_REQUEST['path'];

        $pictures = $this->getPictures($blogId, $galleryId, $path);

        wp_send_json($pictures);

        die();
    }

    public function updateGalleryPath()
    {
        global $wpdb;

        $blogId = $_REQUEST['blog_id'];
        $galleryId = $_REQUEST['gallery_id'];
        $path = $_REQUEST['path'];
        $sql = "UPDATE {$this->blogPrefix}ngg_gallery SET path = '{$path}' WHERE gid = '{$galleryId}';";

        $wpdb->query($sql);

        wp_send_json(['success' => true]);

        die();
    }

    protected function getGalleries(int $blogId): array
    {
        global $wpdb;

        $sql = "SELECT *  FROM {$this->blogPrefix}ngg_gallery";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    protected function suggestedPath(string $path): string
    {
        $newPath = preg_replace('/(wp-content\/)(blogs.dir\/)([\d]+)(\/)(files\/)(.*)/', '$1uploads/sites/$3/$6', $path);

        return $newPath;
    }

    protected function getPictures(int $blogId, int $galleryId, string $suggestedPath): array
    {
        global $wpdb;

        $pictures = [];

        $sql = "SELECT *  FROM {$this->blogPrefix}ngg_pictures WHERE galleryid = {$galleryId}";

        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach ($results as $result) {
            $pictures[] = $this->getBlogBaseUrl($blogId) . '/' . $suggestedPath . '/' . $result['filename'];
        }

        return $pictures;
    }

    protected function getBlogBaseUrl(?int $blogId): ?string
    {
        if (! $blogId) {
            return null;
        }

        global $wpdb;

        $sql = "SELECT *  FROM wp_blogs WHERE blog_id = {$blogId}";
        $site = current($wpdb->get_results($sql, ARRAY_A));


        return 'https://' . $site['domain'] . $site['path'];
    }

    protected function getPagesWithGalleries(int $blogId, int $galleryId): string
    {
        global $wpdb;
        $html = '';
        $rows = '';

        $sql = "SELECT *  FROM {$this->blogPrefix}posts";
        $sql .= " WHERE post_status = 'publish'";

        $posts = $wpdb->get_results($sql, ARRAY_A);

        if ($posts) {
            foreach ($posts as $post) {
                if (preg_match('/(\[nggallery)(.*)(id=' . $galleryId . ')(.*)(])/', $post['post_content'])) {
                    $rows = '<tr><td>';
                    $rows .= "<a href=\"{$post['guid']}\" target=\"_blank\">{$post['guid']}</a>";
                    $rows .= '</td></tr>';
                }
            }
            if ($rows) {
                $html = '<hr>';
                $html .= '<div style="font-weight: bolder;">This gallery is found on page(s):</div>';
                $html .= '<table>';
                $html .= $rows;
                $html .= '</table>';
                $html .= '<hr>';
            }
        }

        return $html;
    }

}
