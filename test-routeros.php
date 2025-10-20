<?php
require 'vendor/autoload.php';

use RouterOS\Client;
use RouterOS\Query;

try {
    $client = new Client([
        'host' => '172.16.30.8', // Ganti dengan IP Mikrotik kamu
        'user' => 'rondi',
        'pass' => '21184662',
        'port' => 8728,
        'timeout' => 5,
    ]);

    $query = (new Query('/interface/wireless/registration-table/print'));
    $response = $client->query($query)->read();

    echo "<pre>";
    print_r($response);
    echo "</pre>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
