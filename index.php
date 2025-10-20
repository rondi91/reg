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
body { font-family: Arial, sans-serif; background:#f8fafc; margin:20px; }
h2 { margin-bottom:5px; }
.card {
  background:#fff; border-radius:10px; padding:10px 15px; margin-bottom:20px;
  box-shadow:0 2px 5px rgba(0,0,0,0.1);
}
.table { border-collapse:collapse; width:100%; }
.table th, .table td { padding:8px 10px; border-bottom:1px solid #e5e7eb; text-align:left; }
.table th { background:#f1f5f9; }
.status-online{color:green;} .status-offline{color:red;}
select, input[type=text]{padding:5px 8px;border-radius:6px;border:1px solid #ccc;}
.search-box {margin-bottom:10px;}
.signal {
  font-weight:bold;
  padding:4px 8px;
  border-radius:6px;
  display:inline-block;
  color:#fff;
}
.signal.strong { background:#16a34a; }   /* hijau */
.signal.medium { background:#facc15; color:#000; } /* kuning */
.signal.weak { background:#dc2626; }     /* merah */
</style>
</head>
<body>
<h2>📡 Real-Time Wireless Registration Monitor</h2>

<?php if(empty($routers)): ?>
<p><em>Belum ada router ditambahkan.</em></p>
<?php else: ?>
<div style="margin-bottom:10px;">
  Interval update:
  <select id="intervalSelect">
    <option value="3000">3 detik</option>
    <option value="5000" selected>5 detik</option>
    <option value="10000">10 detik</option>
    <option value="30000">30 detik</option>
  </select>
  &nbsp;|&nbsp;
  Filter radio-name:
  <input type="text" id="searchInput" placeholder="Ketik radio-name...">
</div>

<div id="data-container">Memuat data...</div>
<?php endif; ?>

<script>
let refreshTimer = null;
const container = document.getElementById("data-container");
const select = document.getElementById("intervalSelect");
const searchInput = document.getElementById("searchInput");

function getSignalColor(signalStr) {
  if (!signalStr) return "";
  // ambil nilai rata-rata jika format misal "-56/-60"
  let sig = parseInt(signalStr.split('/')[0]);
  if (isNaN(sig)) return "";
  if (sig >= -60) return "strong";
  if (sig >= -75) return "medium";
  return "weak";
}

async function loadData() {
  try {
    const res = await fetch("get_data.php");
    const data = await res.json();
    const filter = searchInput.value.trim().toLowerCase();
    let html = "";
    data.forEach(router => {
      html += `<div class="card"><h3>${router.name}</h3>`;
      if (router.error) {
        html += `<p class="status-offline">Error: ${router.error}</p>`;
      } else if (router.clients.length === 0) {
        html += `<p>Tidak ada client terhubung</p>`;
      } else {
        const filtered = filter
          ? router.clients.filter(c => (c["radio-name"]||"").toLowerCase().includes(filter))
          : router.clients;
        if (filtered.length === 0) {
          html += `<p><em>Tidak ada client cocok filter "${filter}"</em></p>`;
        } else {
          html += `<table class="table">
            <tr>
              <th>Interface</th><th>MAC</th><th>Radio Name</th>
              <th>Signal</th><th>TX CCQ</th><th>RX CCQ</th>
              <th>TX Rate</th><th>RX Rate</th><th>Uptime</th>
            </tr>`;
          filtered.forEach(c => {
            const signalClass = getSignalColor(c.signal);
            html += `<tr>
              <td>${c.interface || '-'}</td>
              <td>${c["mac-address"] || '-'}</td>
              <td>${c["radio-name"] || '-'}</td>
              <td><span class="signal ${signalClass}">${c.signal || '-'}</span></td>
              <td>${c["tx-ccq"] || '-'}</td>
              <td>${c["rx-ccq"] || '-'}</td>
              <td>${c["tx-rate"] || '-'}</td>
              <td>${c["rx-rate"] || '-'}</td>
              <td>${c["uptime"] || '-'}</td>
            </tr>`;
          });
          html += `</table>`;
        }
      }
      html += `</div>`;
    });
    container.innerHTML = html;
  } catch (e) {
    container.innerHTML = `<p style="color:red">Gagal memuat data (${e.message})</p>`;
  }
}

function setRefresh(interval) {
  if (refreshTimer) clearInterval(refreshTimer);
  refreshTimer = setInterval(loadData, interval);
  loadData();
}

select.addEventListener("change", e => setRefresh(parseInt(e.target.value)));
searchInput.addEventListener("input", loadData);
setRefresh(parseInt(select.value));
</script>
</body>
</html>
