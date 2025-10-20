<?php
require 'vendor/autoload.php';

$routersFile = 'routers.json';
$routers = file_exists($routersFile) ? json_decode(file_get_contents($routersFile), true) : [];

// --- Simpan router via AJAX ---
if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    $newRouter = [
        'name' => $_POST['name'] ?? 'Router Baru',
        'host' => $_POST['host'] ?? '',
        'user' => $_POST['user'] ?? 'admin',
        'password' => $_POST['password'] ?? '',
        'port' => intval($_POST['port'] ?? 8728)
    ];

    if (!empty($newRouter['host'])) {
        $routers[] = $newRouter;
        file_put_contents($routersFile, json_encode($routers, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Host kosong']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Router AP</title>
<style>
body { font-family: Arial, sans-serif; background: #f8fafc; margin: 20px; }
h2 { margin-bottom: 10px; }
form {
  background: #fff; padding: 20px; border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1); width: 400px;
}
input, button {
  padding: 8px; margin: 6px 0; border-radius: 6px;
  border: 1px solid #d1d5db; width: 100%;
}
button {
  background: #2563eb; color: white; cursor: pointer; border: none;
}
button:hover { background: #1e40af; }
.table {
  border-collapse: collapse; width: 100%; background: #fff;
  border-radius: 8px; overflow: hidden; margin-top: 10px;
}
.table th, .table td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
.table th { background: #f1f5f9; text-align: left; }
.btn-select {
  background: #16a34a; color: #fff; padding: 5px 10px;
  border-radius: 6px; text-decoration: none;
}
.btn-select:hover { background: #15803d; }
#results { margin-top: 20px; }
#notif {
  background: #dcfce7; color: #166534; padding: 8px;
  border-radius: 6px; display: none; margin-bottom: 10px;
}
</style>
</head>
<body>
<h2>➕ Tambah Router AP</h2>
<a href="index.php">⬅️ Kembali ke Dashboard</a>

<div id="notif"></div>

<form method="post" id="routerForm">
  <label>Nama Router</label>
  <input type="text" name="name" id="name" placeholder="Nama Router" required>
  <label>Host/IP</label>
  <input type="text" name="host" id="host" placeholder="IP Router" required>
  <label>User</label>
  <input type="text" name="user" id="user" value="admin">
  <label>Password</label>
  <input type="password" name="password" id="password" placeholder="Password Router">
  <label>Port</label>
  <input type="number" name="port" id="port" value="8728">
  <button type="submit">💾 Simpan Router</button>
</form>

<h3>🔍 Cari IP dari PPPoE Server</h3>
<input type="text" id="searchBox" placeholder="Ketik nama PPPoE..." onkeyup="liveSearch()" />

<div id="results">
  <p><i>Ketik untuk mencari data...</i></p>
</div>

<script>
function showNotif(msg) {
  const notif = document.getElementById('notif');
  notif.innerText = msg;
  notif.style.display = 'block';
  setTimeout(() => notif.style.display = 'none', 3000);
}

function saveRouter(name, host) {
  const data = new FormData();
  data.append('ajax', '1');
  data.append('name', name || 'Router ' + host);
  data.append('host', host);
  data.append('user', 'rondi');
  data.append('password', '21184662');
  data.append('port', 8728);

  fetch('add_router.php', { method: 'POST', body: data })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        showNotif('✅ Router ' + host + ' berhasil disimpan!');
      } else {
        showNotif('❌ Gagal menyimpan router: ' + (res.error || ''));
      }
    })
    .catch(err => showNotif('❌ Error: ' + err));
}

function selectIP(ip, name) {
  document.getElementById('host').value = ip;
  document.getElementById('name').value = name;
  saveRouter(name, ip);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

let timer;
function liveSearch() {
  clearTimeout(timer);
  const query = document.getElementById('searchBox').value.trim();
  if (query.length < 2) {
    document.getElementById('results').innerHTML = "<p><i>Ketik minimal 2 huruf untuk mencari...</i></p>";
    return;
  }
  timer = setTimeout(() => {
    fetch('search_pppoe.php?q=' + encodeURIComponent(query))
      .then(res => res.text())
      .then(html => document.getElementById('results').innerHTML = html)
      .catch(err => document.getElementById('results').innerHTML = "<p>Error: " + err + "</p>");
  }, 400);
}
</script>
</body>
</html>
