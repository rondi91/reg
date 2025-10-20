<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

$routersFile = 'routers.json';
$routers = file_exists($routersFile) ? json_decode(file_get_contents($routersFile), true) : [];

// === HAPUS ROUTER ===
if (isset($_GET['delete'])) {
    $index = intval($_GET['delete']);
    if (isset($routers[$index])) {
        unset($routers[$index]);
        $routers = array_values($routers);
        file_put_contents($routersFile, json_encode($routers, JSON_PRETTY_PRINT));
        header("Location: index.php?deleted=1");
        exit;
    }
}

// === AMBIL DATA WIRELESS REGISTRATION ===
function getWirelessRegistration($router)
{
    try {
        $client = new Client([
            'host' => $router['host'],
            'user' => $router['user'],
            'pass' => $router['password'],
            'port' => $router['port'] ?? 8728,
            'timeout' => 3,
        ]);

        $query = new Query('/interface/wireless/registration-table/print');
        $data = $client->query($query)->read();
        return $data ?: [];
    } catch (Exception $e) {
        return [['error' => $e->getMessage()]];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Wireless Registration Monitor</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f8fafc;
    margin: 20px;
}
h2 { margin-bottom: 10px; }
.table {
    border-collapse: collapse;
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.table th, .table td {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}
.table th { background: #f1f5f9; font-weight: bold; }
.table tr:hover { background: #f9fafb; }
.btn {
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 13px;
}
.btn-add { background: #2563eb; color: #fff; }
.btn-del { background: #dc2626; color: #fff; }
.notice {
    background: #d1fae5;
    color: #065f46;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
}
.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 6px;
}
.router-block {
    margin-bottom: 25px;
}
</style>
</head>
<body>

<h2>📡 Wireless Registration Monitor</h2>

<?php if (isset($_GET['deleted'])): ?>
<div class="notice">Router berhasil dihapus.</div>
<?php endif; ?>

<p>
    <a href="add_router.php" class="btn btn-add">➕ Tambah Router</a>
</p>

<?php if (empty($routers)): ?>
<p><em>Belum ada router terdaftar.</em></p>
<?php else: ?>
    <?php foreach ($routers as $i => $r): ?>
        <div class="router-block">
            <h3>🔹 <?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['host']) ?>)
                <a href="?delete=<?= $i ?>" class="btn btn-del" onclick="return confirm('Yakin hapus router ini?')">Hapus</a>
            </h3>

            <?php $regs = getWirelessRegistration($r); ?>

            <?php if (isset($regs[0]['error'])): ?>
                <div class="error">Error: <?= htmlspecialchars($regs[0]['error']) ?></div>
            <?php elseif (empty($regs)): ?>
                <div class="error">Tidak ada data wireless terdeteksi.</div>
            <?php else: ?>
                <table class="table">
                    <tr>
                        <th>No</th>
                        <th>MAC Address</th>
                        <th>Interface</th>
                        <th>Uptime</th>
                        <th>Signal</th>
                        <th>TX CCQ</th>
                        <th>RX CCQ</th>
                        <th>TX Rate</th>
                        <th>RX Rate</th>
                        <th>Last IP</th>
                    </tr>
                    <?php foreach ($regs as $n => $w): ?>
                    <tr>
                        <td><?= $n + 1 ?></td>
                        <td><?= $w['mac-address'] ?? '-' ?></td>
                        <td><?= $w['interface'] ?? '-' ?></td>
                        <td><?= $w['uptime'] ?? '-' ?></td>
                        <td><?= $w['signal-strength'] ?? '-' ?></td>
                        <td><?= $w['tx-ccq'] ?? '-' ?></td>
                        <td><?= $w['rx-ccq'] ?? '-' ?></td>
                        <td><?= $w['tx-rate'] ?? '-' ?></td>
                        <td><?= $w['rx-rate'] ?? '-' ?></td>
                        <td><?= $w['last-ip'] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
// Auto-refresh setiap 10 detik agar data real-time
setTimeout(() => {
    location.reload();
}, 10000);
</script>

</body>
</html>
