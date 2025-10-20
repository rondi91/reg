<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Real-Time Wireless Signal Monitor</title>
<style>
body { font-family: Arial, sans-serif; background: #f6f8fa; margin: 20px; }
h2 { background: #007bff; color: white; padding: 10px; border-radius: 5px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
th, td { padding: 8px 10px; border: 1px solid #ddd; text-align: left; }
th { background: #f0f0f0; }
.signal-good { color: green; font-weight: bold; }
.signal-weak { color: orange; font-weight: bold; }
.signal-bad { color: red; font-weight: bold; }
</style>
</head>
<body>
<h1>📡 Real-Time Wireless Signal Monitor</h1>
<div id="content">Loading data...</div>

<script>
async function loadData() {
    const res = await fetch('api.php');
    const data = await res.json();
    let html = '';

    data.forEach(router => {
        html += `<h2>${router.router} (${router.host})</h2>`;

        if (router.error) {
            html += `<p style="color:red;">Error: ${router.error}</p>`;
            return;
        }

        if (!router.wireless.length) {
            html += `<p>Tidak ada data wireless.</p>`;
            return;
        }

        html += `<table>
            <tr><th>Interface</th><th>MAC Address</th><th>Signal (dBm)</th><th>TX CCQ (%)</th><th>RX CCQ (%)</th><th>Uptime</th></tr>`;

        router.wireless.forEach(w => {
            let sClass = 'signal-good';
            let signalVal = parseInt(w.signal);
            if (signalVal < -70) sClass = 'signal-bad';
            else if (signalVal < -60) sClass = 'signal-weak';

            html += `<tr>
                <td>${w.interface}</td>
                <td>${w.mac}</td>
                <td class="${sClass}">${w.signal}</td>
                <td>${w.tx_ccq}</td>
                <td>${w.rx_ccq}</td>
                <td>${w.uptime}</td>
            </tr>`;
        });

        html += `</table>`;
    });

    document.getElementById('content').innerHTML = html;
}

// Refresh tiap 3 detik
loadData();
setInterval(loadData, 3000);
</script>
</body>
</html>
