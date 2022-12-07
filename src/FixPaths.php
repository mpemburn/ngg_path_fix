<?php

namespace Ngg_Path_Fix;

class FixPaths
{
    protected string $blogBasePath;

    public function __construct()
    {
        $this->blogBasePath = $this->getBlogBaseUrl($_REQUEST['blog_id']);

        $file = plugin_dir_path(__FILE__) . 'js/path-fix.js';
        $cacheBuster = filemtime($file);
        wp_register_script('path-fix', plugins_url('/js/path-fix.js', __FILE__), array(), $cacheBuster, TRUE);
        wp_enqueue_script('path-fix');
    }

    public function render()
    {
        $blogId = $_REQUEST['blog_id'];

        $data = $this->getGalleries($blogId);

        echo '<table style="width: 90%;">';
        echo '<thead style="background-color: #d0d0d0">';
        echo '<th style="width: 40%">';
        echo 'Current Path';
        echo '</th>';
        echo '<th style="width: 55%">';
        echo 'Suggested Path';
        echo '</th>';
        echo '<th>';
        echo '</th>';
        echo '</thead>';
        foreach ($data as $gallery) {
            $suggestedPath = $this->suggestedPath($gallery['path']);
            echo '<tr data-gallery="' . $gallery['gid'] . '" style="cursor: pointer;">';
            echo '<td><strong>' . $gallery['path'] . '</strong></td>';
            echo '<td><input data-gallery="' . $gallery['gid'] . '" type="text" value="' . $suggestedPath . '" style="width: 100%;"></td>';
            echo '<td><button data-gallery="' . $gallery['gid'] . '">Submit</button></td>';
            echo '</tr>';
            echo '<tr style="display: none;">';
            echo '<td colspan="3">' . $this->getPictures($blogId, $gallery['gid'], $suggestedPath) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    protected function getGalleries(int $blogId): array
    {
        global $wpdb;

        $sql = "SELECT *  FROM wp_{$blogId}_ngg_gallery";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    protected function getPictures(int $blogId, int $galleryId, string $suggestedPath): string
    {
        global $wpdb;
        $html = '';
//
//        $sql = "SELECT *  FROM wp_{$blogId}_ngg_pictures WHERE galleryid = {$galleryId}";
//
//        $pictures = $wpdb->get_results($sql, ARRAY_A);
//
//        $count = 0;
//        $html .= '<div>';
//        foreach ($pictures as $picture) {
//            if ($count % 12 === 0) {
//                $html .= '</div>';
//                $html .= '<div>';
//            }
//            $html .= '<img src="' . $this->blogBasePath . $suggestedPath . '/' . $picture['filename'] . '" style="height: 50px; padding: 5px;">';
//
//            $count++;
//        }
//        $html .= '</div>';

        return $html;
    }

    protected function getBlogBaseUrl(int $blogId): string
    {
        global $wpdb;

        $sql = "SELECT *  FROM wp_blogs WHERE blog_id = {$blogId}";
        $site = current($wpdb->get_results($sql, ARRAY_A));


        return 'https://' . $site['domain'] . $site['path'];
    }

    protected function suggestedPath(string $path): string
    {
        $newPath = preg_replace('/(wp-content\/)(blogs.dir\/)([\d]+)(\/)(files\/)(.*)/', '$1uploads/sites/$3/$6', $path);

        return $newPath;
    }
}
