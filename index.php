<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Realtime Wireless Monitor</title>
<style>
body { font-family: Arial, sans-serif; background:#f8f9fa; padding:20px; }
h2 { color:#333; }
table { width:100%; border-collapse: collapse; margin-top:10px; background:white; }
th, td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#007bff; color:white; }
tbody tr:nth-child(even){background:#f2f2f2;}
</style>
</head>
<body>
<h2>Realtime Wireless Signal & CCQ Monitor</h2>
<table id="wifi-table">
  <thead>
    <tr>
      <th>Router</th>
      <th>MAC</th>
      <th>Interface</th>
      <th>Signal (dBm)</th>
      <th>TX CCQ (%)</th>
      <th>RX CCQ (%)</th>
    </tr>
  </thead>
  <tbody><tr><td colspan="6">Loading data...</td></tr></tbody>
</table>

<script>
async function loadRealtime() {
  try {
    const res = await fetch('api.php');
    const data = await res.json();
    const tbody = document.querySelector('#wifi-table tbody');
    tbody.innerHTML = '';
    if (!data.length) {
      tbody.innerHTML = '<tr><td colspan="6">Tidak ada data wireless</td></tr>';
      return;
    }
    data.forEach(d => {
      tbody.innerHTML += `
        <tr>
          <td>${d.router}</td>
          <td>${d.mac}</td>
          <td>${d.iface}</td>
          <td>${d.signal}</td>
          <td>${d.tx_ccq}</td>
          <td>${d.rx_ccq}</td>
        </tr>
      `;
    });
  } catch (err) {
    console.error('Fetch error:', err);
  }
}

// refresh tiap 5 detik
loadRealtime();
setInterval(loadRealtime, 5000);
</script>
</body>
</html>
