<?php
require_once 'RouterOS.php';
header('Content-Type: application/json');

$routers = json_decode(file_get_contents('routers.json'), true);
$result = [];

foreach ($routers as $r) {
    $api = new RouterOS();
    if ($api->connect($r['host'], $r['user'], $r['pass'], $r['port'])) {
        $rows = $api->query('/caps-man/registration-table/print', [
            '=.proplist=mac,interface,tx-ccq,rx-ccq,rx-signal,tx-signal,signal,signal-strength'
        ]);
        if (!$rows) {
            $rows = $api->query('/interface/wireless/registration-table/print', [
                '=.proplist=mac,interface,tx-ccq,rx-ccq,signal,signal-strength'
            ]);
        }

        foreach ($rows as $row) {
            $result[] = [
                'router' => $r['name'],
                'mac' => $row['mac'] ?? '',
                'iface' => $row['interface'] ?? '',
                'signal' => $row['signal-strength'] ?? $row['rx-signal'] ?? $row['signal'] ?? '',
                'tx_ccq' => $row['tx-ccq'] ?? '',
                'rx_ccq' => $row['rx-ccq'] ?? ''
            ];
        }
        $api->disconnect();
    }
}
echo json_encode($result);
