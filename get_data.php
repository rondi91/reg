<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

header('Content-Type: application/json');
$routers = file_exists('routers.json') ? json_decode(file_get_contents('routers.json'), true) : [];
$result = [];

foreach ($routers as $r) {
  $entry = ['name'=>$r['name'] ?? $r['host'],'clients'=>[],'error'=>null];
  try {
    $client = new Client([
      'host'=>$r['host'],
      'user'=>$r['user'],
      'pass'=>$r['password'],
      'port'=>$r['port'] ?? 8728,
      'timeout'=>2
    ]);
    $query = new Query('/interface/wireless/registration-table/print');
    $data = $client->query($query)->read();
    foreach ($data as $d) {
      $entry['clients'][] = [
        'interface'=>$d['interface'] ?? '',
        'mac-address'=>$d['mac-address'] ?? '',
        'radio-name'=>$d['radio-name'] ?? '',
        'signal'=>$d['signal-strength'] ?? '',
        'tx-ccq'=>$d['tx-ccq'] ?? '',
        'rx-ccq'=>$d['rx-ccq'] ?? '',
        'tx-rate'=>$d['tx-rate'] ?? '',
        'rx-rate'=>$d['rx-rate'] ?? ''
      ];
    }
  } catch (Exception $e) {
    $entry['error'] = $e->getMessage();
  }
  $result[] = $entry;
}
echo json_encode($result);
