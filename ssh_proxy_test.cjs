const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  // Test 1: Direct curl verbose
  // Test 2: Through Tor SOCKS5
  const cmds = [
    'echo "=== DIRECT ==="',
    'curl -v --max-time 10 -k "https://auth.bokla.me/v4/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1 | tail -20',
    'echo "=== VIA TOR ==="',
    'curl -v --max-time 15 --socks5-hostname 127.0.0.1:9050 -k "https://auth.bokla.me/v4/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1 | tail -20',
    'echo "=== DIRECT GET BALANCES ==="',
    'curl -v --max-time 10 -k "https://auth.bokla.me/v4/api/Account/balances" -H "Content-Type: application/json" -d "{}" 2>&1 | tail -20',
  ].join('; ');
  conn.exec(cmds, (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:60000, debug: () => {}});
