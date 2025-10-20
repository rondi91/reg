<?php
require 'vendor/autoload.php';
use RouterOS\Client;
use RouterOS\Query;

$routersFile = 'routers.json';
$routers = file_exists($routersFile) ? json_decode(file_get_contents($routersFile), true) : [];

// --- Router PPPoE sumber IP ---
$pppoeRouters = [
    ['name' => 'Router Server 1', 'host' => '192.168.255.20', 'user' => 'rondi', 'password' => '21184662', 'port' => 8728],
    ['name' => 'Router Server 2', 'host' => '192.168.255.26', 'user' => 'rondi', 'password' => '21184662', 'port' => 8728]
];

// --- Simpan router baru ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRouter = [
        'name' => $_POST['name'] ?? 'Router Baru',
        'host' => $_POST['host'] ?? '',
        'user' => $_POST['user'] ?? 'admin',
        'password' => $_POST['password'] ?? '',
        'port' => intval($_POST['port'] ?? 8728),
    ];

    if (!empty($newRouter['host'])) {
        $routers[] = $newRouter;
        file_put_contents($routersFile, json_encode($routers, JSON_PRETTY_PRINT));
        header("Location: index.php?added=1");
        exit;
    }
}

// --- Ambil daftar IP aktif dari PPPoE servers ---
function getPPPActiveIPs($router)
{
    try {
        $client = new Client([
            'host' => $router['host'],
            'user' => $router['user'],
            'pass' => $router['password'],
            'port' => $router['port'] ?? 8728,
            'timeout' => 3,
        ]);

        $query = new Query('/ppp/active/print');
        $data = $client->query($query)->read();

        $ips = [];
        foreach ($data as $d) {
            if (isset($d['address'])) {
                $ips[] = $d['address'];
            }
        }
        return $ips;
    } catch (Exception $e) {
        return ['Error: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Router AP</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f8fafc;
    margin: 20px;
}
h2 { margin-bottom: 10px; }
form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    width: 400px;
}
input[type=text], input[type=password], input[type=number] {
    width: 100%;
    padding: 8px;
    margin: 6px 0 10px 0;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}
button {
    background: #2563eb;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
button:hover { background: #1e40af; }
.table {
    border-collapse: collapse;
    width: 100%;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-top: 20px;
}
.table th, .table td {
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
}
.table th { background: #f1f5f9; font-weight: bold; }
.btn-select {
    background: #16a34a;
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
}
.btn-select:hover { background: #15803d; }
</style>
<script>
function selectIP(ip) {
    document.getElementById('host').value = ip;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</head>
<body>

<h2>➕ Tambah Router AP</h2>
<p><a href="index.php">⬅️ Kembali ke Dashboard</a></p>

<form method="post">
    <label>Nama Router</label>
    <input type="text" name="name" placeholder="Nama Router" required>

    <label>Host/IP</label>
    <input type="text" name="host" id="host" placeholder="IP Router" required>

    <label>User</label>
    <input type="text" name="user" value="admin">

    <label>Password</label>
    <input type="password" name="password" placeholder="Password Router">

    <label>Port</label>
    <input type="number" name="port" value="8728">

    <button type="submit">💾 Simpan Router</button>
</form>

<h3>🌐 Pilih IP dari PPPoE Server</h3>

<?php foreach ($pppoeRouters as $srv): ?>
    <h4><?= htmlspecialchars($srv['name']) ?> (<?= htmlspecialchars($srv['host']) ?>)</h4>
    <?php $ips = getPPPActiveIPs($srv); ?>
    <?php if (empty($ips)): ?>
        <p><i>Tidak ada IP aktif atau gagal koneksi.</i></p>
    <?php else: ?>
        <table class="table">
            <tr><th>No</th><th>Alamat IP</th><th>Aksi</th></tr>
            <?php foreach ($ips as $i => $ip): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($ip) ?></td>
                    <td><a href="#" class="btn-select" onclick="selectIP('<?= htmlspecialchars($ip) ?>')">Pilih</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endforeach; ?>

</body>
</html>
