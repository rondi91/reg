<?php
require 'vendor/autoload.php';
$routers = file_exists('routers.json') ? json_decode(file_get_contents('routers.json'), true) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Wireless Live Monitor</title>
<style>
body { font-family: Arial, sans-serif; background: #f8fafc; margin: 20px; }
h2 { margin-bottom: 10px; }
.table { border-collapse: collapse; width: 100%; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.table th, .table td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
.table th { background: #f1f5f9; }
.status-online { color: green; }
.status-offline { color: red; }
</style>
</head>
<body>
<h2>📡 Live Wireless Registration Monitor</h2>

<?php if (empty($routers)): ?>
<p><em>Belum ada router ditambahkan.</em></p>
<?php else: ?>
<div id="data-container">Memuat data...</div>
<?php endif; ?>

<script>
async function loadData() {
  try {
    const res = await fetch("get_data.php");
    const data = await res.json();
    let html = `
      <table class="table">
        <tr>
          <th>Router</th>
          <th>Interface</th>
          <th>MAC Address</th>
          <th>Signal</th>
          <th>TX CCQ</th>
          <th>RX CCQ</th>
          <th>TX Rate</th>
          <th>RX Rate</th>
          <th>Status</th>
        </tr>`;
    data.forEach(router => {
      if (router.error) {
        html += `<tr><td colspan="9" class="status-offline">${router.name} - ${router.error}</td></tr>`;
      } else if (router.clients.length === 0) {
        html += `<tr><td colspan="9">${router.name} - Tidak ada client terhubung</td></tr>`;
      } else {
        router.clients.forEach(c => {
          html += `
            <tr>
              <td>${router.name}</td>
              <td>${c.interface || '-'}</td>
              <td>${c['mac-address'] || '-'}</td>
              <td>${c.signal || '-'}</td>
              <td>${c['tx-ccq'] || '-'}</td>
              <td>${c['rx-ccq'] || '-'}</td>
              <td>${c['tx-rate'] || '-'}</td>
              <td>${c['rx-rate'] || '-'}</td>
              <td class="status-online">Online</td>
            </tr>`;
        });
      }
    });
    html += `</table>`;
    document.getElementById("data-container").innerHTML = html;
  } catch (err) {
    document.getElementById("data-container").innerHTML = "<p style='color:red'>Gagal memuat data.</p>";
  }
}

// panggil pertama kali
loadData();
// update otomatis setiap 5 detik
setInterval(loadData, 5000);
</script>
</body>
</html>
