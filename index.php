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
        $routers = array_values($routers); // rapikan index array
        file_put_contents($routersFile, json_encode($routers, JSON_PRETTY_PRINT));
        header("Location: index.php?deleted=1");
        exit;
    }
}

// === FUNGSI AMBIL DATA ROUTER ===
function getWirelessInfo($router)
{
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
<title>Dashboard Wireless Monitor</title>
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
}
.table th, .table td {
    padding: 10px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}
.table th {
    background: #f1f5f9;
    font-weight: bold;
}
.table tr:hover {
    background: #f9fafb;
}
.actions {
    display: flex;
    gap: 8px;
}
.btn {
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 13px;
}
.btn-add { background: #2563eb; color: #fff; }
.btn-del { background: #dc2626; color: #fff; }
.btn-del:hover { background: #b91c1c; }
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
</style>
</head>
<body>

<h2>📡 Dashboard Wireless Router</h2>

<?php if (isset($_GET['deleted'])): ?>
<div class="notice">Router berhasil dihapus.</div>
<?php endif; ?>

<p>
    <a href="add_router.php" class="btn btn-add">➕ Tambah Router</a>
</p>

<?php if (empty($routers)): ?>
<p><em>Belum ada router terdaftar. Tambahkan lewat menu di atas.</em></p>
<?php else: ?>
<table class="table">
    <tr>
        <th>No</th>
        <th>Nama Router</th>
        <th>Host</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($routers as $i => $r): ?>
        <?php
        $status = getWirelessInfo($r);
        $statusText = isset($status[0]['error'])
            ? "<span class='error'>Error: " . htmlspecialchars($status[0]['error']) . "</span>"
            : count($status) . " client(s) terhubung";
        ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['host']) ?></td>
            <td><?= $statusText ?></td>
            <td class="actions">
                <a href="?delete=<?= $i ?>" class="btn btn-del" onclick="return confirm('Yakin hapus router ini?')">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
