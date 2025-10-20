<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

$config = json_decode(file_get_contents('config.json'), true);
$routersFile = 'routers.json';
$routers = file_exists($routersFile) ? json_decode(file_get_contents($routersFile), true) : [];

// Jika form disubmit → simpan ke routers.json
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = [
        'name' => $_POST['name'],
        'host' => $_POST['host'],
        'user' => $_POST['user'],
        'password' => $_POST['password'],
        'port' => intval($_POST['port']),
    ];
    $routers[] = $new;
    file_put_contents($routersFile, json_encode($routers, JSON_PRETTY_PRINT));
    echo "<script>alert('Router berhasil ditambahkan!');window.location='index.php';</script>";
    exit;
}

// Ambil IP aktif dari PPPoE Server 1 & 2
function getPPPoEActive($r) {
    $data = [];
    try {
        $client = new Client([
            'host' => $r['host'],
            'user' => $r['user'],
            'pass' => $r['password'],
            'port' => $r['port'] ?? 8728,
            'timeout' => 3,
        ]);

        $query = (new Query('/ppp/active/print'));
        $result = $client->query($query)->read();

        foreach ($result as $row) {
            if (!empty($row['address'])) {
                $data[] = [
                    'ip' => $row['address'],
                    'name' => $row['name'] ?? 'client',
                    'caller-id' => $row['caller-id'] ?? '',
                    'router' => $r['name']
                ];
            }
        }
    } catch (Exception $e) {
        $data[] = ['ip' => '-', 'name' => 'Error: '.$e->getMessage(), 'router' => $r['name']];
    }
    return $data;
}

$activeClients = [];
foreach ($config['pppoe_servers'] as $srv) {
    $activeClients = array_merge($activeClients, getPPPoEActive($srv));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Router AP</title>
<style>
body { font-family: Arial; margin: 20px; background: #f9fafb; }
form { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); width: 500px; }
input, select { width: 100%; padding: 8px; margin: 6px 0; border: 1px solid #ccc; border-radius: 5px; }
button { background: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
button:hover { background: #0056b3; }
</style>
</head>
<body>

<h2>Tambah Router Access Point</h2>
<form method="post">
    <label>Nama Router</label>
    <input type="text" name="name" required>

    <label>Host / IP</label>
    <select name="host" required>
        <option value="">Pilih IP dari PPPoE Active</option>
        <?php foreach ($activeClients as $c): ?>
            <option value="<?= htmlspecialchars($c['ip']) ?>">
                <?= htmlspecialchars($c['ip'].' - '.$c['name'].' ('.$c['router'].')') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Username</label>
    <input type="text" name="user" value="admin" required>

    <label>Password</label>
    <input type="password" name="password" value="">

    <label>Port API</label>
    <input type="number" name="port" value="8728" required>

    <button type="submit">Tambah Router</button>
</form>

<p><a href="index.php">← Kembali ke Dashboard</a></p>
</body>
</html>
