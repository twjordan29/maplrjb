<?php
class Geocoding
{
    private static $base_url = "https://nominatim.openstreetmap.org/search";

    public static function getCoordinates($address)
    {
        $url = self::$base_url . "?q=" . urlencode($address) . "&format=json&limit=1&countrycodes=ca";

        $opts = [
            "http" => [
                "header" => "User-Agent: Maplr.ca Job Board/1.0 (https://maplr.ca; contact@maplr.ca)\r\n"
            ]
        ];
        $context = stream_context_create($opts);

        $response = file_get_contents($url, false, $context);
        if ($response === FALSE) {
            return null;
        }

        $data = json_decode($response, true);

        if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon']
            ];
        }

        return null;
    }
}
?>