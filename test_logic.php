<?php

// Simulate Laravel framework minimal boot for the model part if needed, 
// but here we just simulate the HTTP call and logic.

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Mock some parts
class MockConfig {
    public static function getSettings() {
        return (object)['tmdb_key' => 'edcd52275afd8b8c152c82f1ce3933a2'];
    }
}

function fetchTMDB($endpoint, $params = []) {
    $apiKey = 'edcd52275afd8b8c152c82f1ce3933a2';
    $params['api_key'] = $apiKey;
    $params['endpoint'] = $endpoint;
    
    // Using file_get_contents to avoid full Laravel Http setup here for simplicity
    $query = http_build_query($params);
    $url = "https://joetank.online/tmdb.php?$query";
    echo "Calling Proxy: $url\n";
    $res = file_get_contents($url);
    return json_decode($res, true);
}

function getAgeRating($type, $tmdbId) {
    $endpoint = $type === 'movie' ? "movie/$tmdbId/release_dates" : "tv/$tmdbId/content_ratings";
    $params = $type === 'movie' ? ['region' => 'BR'] : [];
    
    $data = fetchTMDB($endpoint, $params);
    $results = $data['results'] ?? [];

    foreach ($results as $result) {
        if (($result['iso_3166_1'] ?? '') === 'BR') {
            if ($type === 'movie') {
                foreach ($result['release_dates'] as $rd) {
                    if (!empty($rd['certification'])) {
                        return $rd['certification'];
                    }
                }
            } else {
                return $result['rating'] ?? null;
            }
        }
    }
    return null;
}

$id = 1084242; // Zootopia 2
echo "Testing Movie ID: $id\n";
$rating = getAgeRating('movie', $id);
echo "Result: '" . ($rating ?? "NULL") . "'\n";
