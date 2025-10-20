<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

header('Content-Type: application/json');

$routersFile = 'routers.json';
$routers = file_exists($routersFile) ? json_decode(file_get_contents($routersFile), true) : [];

$results = [];

foreach ($routers as $router) {
    $entry = [
        'name' => $router['name'] ?? $router['host'],
        'clients' => [],
        'error' => null
    ];

    try {
        $client = new Client([
            'host' => $router['host'],
            'user' => $router['user'],
            'pass' => $router['password'],
            'port' => $router['port'] ?? 8728,
            'timeout' => 2,
        ]);
        $query = new Query('/interface/wireless/registration-table/print');
        $data = $client->query($query)->read();

        // Ambil data penting
        foreach ($data as $d) {
            $entry['clients'][] = [
                'interface' => $d['interface'] ?? '',
                'mac-address' => $d['mac-address'] ?? '',
                'signal' => $d['signal-strength'] ?? '',
                'tx-ccq' => $d['tx-ccq'] ?? '',
                'rx-ccq' => $d['rx-ccq'] ?? '',
                'tx-rate' => $d['tx-rate'] ?? '',
                'rx-rate' => $d['rx-rate'] ?? '',
            ];
        }
    } catch (Exception $e) {
        $entry['error'] = $e->getMessage();
    }

    $results[] = $entry;
}

echo json_encode($results);
