<?php
require 'vendor/autoload.php';

$routersFile = 'routers.json';
$routers = file_exists($routersFile) ? json_decode(file_get_contents($routersFile), true) : [];
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
</style>
</head>
<body>
<h2>➕ Tambah Router AP</h2>
<a href="index.php">⬅️ Kembali ke Dashboard</a>

<form method="post" action="">
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

<h3>🔍 Cari IP dari PPPoE Server</h3>
<input type="text" id="searchBox" placeholder="Ketik nama PPPoE..." onkeyup="liveSearch()" />

<div id="results">
  <p><i>Ketik untuk mencari data...</i></p>
</div>

<script>
function selectIP(ip) {
  document.getElementById('host').value = ip;
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
      .then(html => {
        document.getElementById('results').innerHTML = html;
      })
      .catch(err => {
        document.getElementById('results').innerHTML = "<p>Error: " + err + "</p>";
      });
  }, 400);
}
</script>
</body>
</html>
