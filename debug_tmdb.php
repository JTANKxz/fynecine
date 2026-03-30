<?php

$tmdbId = 1084242;
$apiKey = 'edcd52275afd8b8c152c82f1ce3933a2';
$url = "https://api.themoviedb.org/3/movie/$tmdbId/release_dates?api_key=$apiKey";

echo "Fetching: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$results = $data['results'] ?? [];

$foundBr = false;
foreach ($results as $result) {
    if ($result['iso_3166_1'] === 'BR') {
        $foundBr = true;
        echo "Found BR:\n";
        print_r($result);
        
        if (isset($result['release_dates'])) {
            foreach ($result['release_dates'] as $rd) {
                echo "Certification: '" . ($rd['certification'] ?? 'NULL') . "'\n";
            }
        }
    }
}

if (!$foundBr) {
    echo "BR not found in results.\n";
    echo "Available countries: ";
    foreach ($results as $result) {
        echo $result['iso_3166_1'] . " ";
    }
    echo "\n";
}
