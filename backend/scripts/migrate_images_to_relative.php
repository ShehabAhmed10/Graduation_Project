<?php
// Simple migration script: convert full image URLs in `attraction_images.image_url` to relative paths
// Usage: php migrate_images_to_relative.php

require_once __DIR__ . '/../admin/includes/db_config.php';
require_once __DIR__ . '/../admin/includes/functions.php';

if (!defined('UPLOADS_URL') || !defined('UPLOADS_PATH')) {
    echo "Please ensure UPLOADS_URL and UPLOADS_PATH are defined in your config.\n";
    exit(1);
}

$rows = db_select("SELECT id, image_url FROM attraction_images WHERE image_url IS NOT NULL");
$updated = 0;
foreach ($rows as $r) {
    $url = $r['image_url'];
    // if it's already relative (doesn't start with http), skip
    if (!preg_match('#^https?://#i', $url)) continue;

    // if URL starts with UPLOADS_URL, convert
    $uploads_base = rtrim(UPLOADS_URL, '/');
    if (strpos($url, $uploads_base) === 0) {
        $rel = ltrim(substr($url, strlen($uploads_base)), '/');
        $res = db_execute("UPDATE attraction_images SET image_url = ? WHERE id = ?", [$rel, $r['id']]);
        if ($res !== false) $updated++;
    }
}

echo "Migration complete. Updated: $updated rows.\n";

?>