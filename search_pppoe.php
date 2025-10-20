<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

header("Content-Type: text/html; charset=UTF-8");

$q = $_GET['q'] ?? '';
if (strlen($q) < 2) {
    echo "<p>Ketik minimal 2 huruf...</p>";
    exit;
}

$routers = [
    ['name' => 'Server 192.168.255.21', 'host' => '192.168.255.21', 'user' => 'rondi', 'password' => '21184662', 'port' => 8728],
    ['name' => 'Server 192.168.255.26', 'host' => '192.168.255.26', 'user' => 'rondi', 'password' => '21184662', 'port' => 8728],
];

function getPPPActive($r) {
    try {
        $client = new Client([
            'host' => $r['host'],
            'user' => $r['user'],
            'pass' => $r['password'],
            'port' => $r['port'],
            'timeout' => 3
        ]);
        $data = $client->query(new Query('/ppp/active/print'))->read();
        return $data;
    } catch (Exception $e) {
        return [['error' => $e->getMessage()]];
    }
}

echo "<div style='margin-top:10px;'>";

foreach ($routers as $srv) {
    $list = getPPPActive($srv);
    echo "<h4>🌐 {$srv['name']} ({$srv['host']})</h4>";

    if (isset($list[0]['error'])) {
        echo "<p><i>Error koneksi: {$list[0]['error']}</i></p>";
        continue;
    }

    $found = [];
    foreach ($list as $d) {
        if (isset($d['name']) && stripos($d['name'], $q) !== false) {
            $found[] = $d;
        }
    }

    if (empty($found)) {
        echo "<p><i>Tidak ditemukan hasil untuk '<b>{$q}</b>'</i></p>";
    } else {
        echo "<table class='table' style='width:100%;border-collapse:collapse;background:#fff;'>";
        echo "<tr style='background:#f1f5f9;'><th>No</th><th>Nama</th><th>IP</th><th>Uptime</th><th>Aksi</th></tr>";
        $i = 1;
        foreach ($found as $d) {
            $name = htmlspecialchars($d['name'] ?? '-');
            $ip = htmlspecialchars($d['address'] ?? '-');
            $uptime = htmlspecialchars($d['uptime'] ?? '-');
            echo "<tr>
                    <td>{$i}</td>
                    <td>{$name}</td>
                    <td>{$ip}</td>
                    <td>{$uptime}</td>
                    <td><a href='#' class='btn-select' onclick=\"selectIP('{$ip}', '{$name}')\">Pilih</a></td>

                 </tr>";
            $i++;
        }
        echo "</table>";
    }
}
echo "</div>";
