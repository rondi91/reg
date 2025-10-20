<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

try {
    $client = new Client([
    'host' => '172.16.30.8', // ganti sesuai router kamu
        'user' => 'rondi',
        'pass' => '21184662',
        'port' => 8728,
    ]);

    $q = new Query('/interface/wireless/registration-table/print');
    $res = $client->query($q)->read();

    echo "<pre>";
    print_r($res);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
