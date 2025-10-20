<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

header('Content-Type: application/json');

$routers = json_decode(file_get_contents('routers.json'), true);
$data = [];

foreach ($routers as $r) {
    try {
        $client = new Client([
            'host' => $r['host'],
            'user' => $r['user'],
            'pass' => $r['password'],
            'port' => $r['port'] ?? 8728,
            'timeout' => 3,
        ]);

        // Ambil data wireless registration
        $query = (new Query('/interface/wireless/registration-table/print'));
        $result = $client->query($query)->read();

        $entries = [];
        foreach ($result as $row) {
            $entries[] = [
                'interface' => $row['interface'] ?? '-',
                'mac' => $row['mac-address'] ?? '-',
                'signal' => $row['signal-strength'] ?? '-',
                'tx_ccq' => $row['tx-ccq'] ?? '-',
                'rx_ccq' => $row['rx-ccq'] ?? '-',
                'uptime' => $row['uptime'] ?? '-',
            ];
        }

        $data[] = [
            'router' => $r['name'],
            'host' => $r['host'],
            'wireless' => $entries
        ];
    } catch (Exception $e) {
        $data[] = [
            'router' => $r['name'],
            'host' => $r['host'],
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode($data);
