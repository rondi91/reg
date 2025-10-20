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
.table th { background:#f1f5f9; cursor:pointer; user-select:none; }
.status-online{color:green;} .status-offline{color:red;}
select, input[type=text]{padding:5px 8px;border-radius:6px;border:1px solid #ccc;}
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
.sort-arrow { font-size:12px; margin-left:5px; color:#6b7280; }
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
  let sig = parseInt(signalStr.split('/')[0]);
  if (isNaN(sig)) return "";
  if (sig >= -60) return "strong";
  if (sig >= -75) return "medium";
  return "weak";
}

function parseSignalValue(signalStr) {
  if (!signalStr) return -999;
  const num = parseInt(signalStr.split('/')[0]);
  return isNaN(num) ? -999 : num;
}

function parseRate(rateStr) {
  if (!rateStr) return 0;
  const num = parseInt(rateStr);
  return isNaN(num) ? 0 : num;
}

function parseCCQ(ccqStr) {
  return parseInt(ccqStr) || 0;
}

function parseUptime(uptimeStr) {
  if (!uptimeStr) return 0;
  const parts = uptimeStr.split(/d|h|m|s/);
  let total = 0;
  let match;
  const regex = /(\d+)d|(\d+)h|(\d+)m|(\d+)s/g;
  while ((match = regex.exec(uptimeStr)) !== null) {
    if (match[1]) total += parseInt(match[1]) * 86400;
    if (match[2]) total += parseInt(match[2]) * 3600;
    if (match[3]) total += parseInt(match[3]) * 60;
    if (match[4]) total += parseInt(match[4]);
  }
  return total;
}

async function loadData() {
  try {
    const res = await fetch("get_data.php");
    const data = await res.json();
    const filter = searchInput.value.trim().toLowerCase();
    let html = "";

    data.forEach((router, routerIndex) => {
      html += `<div class="card"><h3>${router.name}</h3>`;
      if (router.error) {
        html += `<p class="status-offline">Error: ${router.error}</p>`;
      } else if (router.clients.length === 0) {
        html += `<p>Tidak ada client terhubung</p>`;
      } else {
        let filtered = filter
          ? router.clients.filter(c => (c["radio-name"]||"").toLowerCase().includes(filter))
          : router.clients;

        // Default: sort berdasarkan sinyal (kuat→lemah)
        filtered.sort((a,b) => parseSignalValue(b.signal) - parseSignalValue(a.signal));

        const tableId = `table-${routerIndex}`;
        html += `<table class="table" id="${tableId}">
          <thead>
            <tr>
              <th data-sort="text">Interface</th>
              <th data-sort="text">MAC</th>
              <th data-sort="text">Radio Name</th>
              <th data-sort="signal">Signal</th>
              <th data-sort="ccq">TX CCQ</th>
              <th data-sort="ccq">RX CCQ</th>
              <th data-sort="rate">TX Rate</th>
              <th data-sort="rate">RX Rate</th>
              <th data-sort="uptime">Uptime</th>
            </tr>
          </thead><tbody>`;

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

        html += `</tbody></table>`;
      }
      html += `</div>`;
    });

    container.innerHTML = html;

    // Tambahkan event listener ke header tabel untuk sorting manual
    document.querySelectorAll("table th").forEach(th => {
      th.addEventListener("click", function() {
        const table = th.closest("table");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        const sortType = th.getAttribute("data-sort");
        const index = Array.from(th.parentNode.children).indexOf(th);
        const ascending = th.classList.toggle("asc");

        rows.sort((a, b) => {
          const A = a.children[index].innerText.trim();
          const B = b.children[index].innerText.trim();
          switch (sortType) {
            case "signal": return ascending ? parseSignalValue(A) - parseSignalValue(B) : parseSignalValue(B) - parseSignalValue(A);
            case "rate": return ascending ? parseRate(A) - parseRate(B) : parseRate(B) - parseRate(A);
            case "ccq": return ascending ? parseCCQ(A) - parseCCQ(B) : parseCCQ(B) - parseCCQ(A);
            case "uptime": return ascending ? parseUptime(A) - parseUptime(B) : parseUptime(B) - parseUptime(A);
            default: return ascending ? A.localeCompare(B) : B.localeCompare(A);
          }
        });
        tbody.innerHTML = "";
        rows.forEach(r => tbody.appendChild(r));
      });
    });

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
