const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  const cmds = [
    'echo "=== Direct IP 191.44.71.70 ==="',
    'curl -sk --max-time 10 "https://191.44.71.70/v4/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1 | head -5',
    'echo "=== auth.bokla.me on diff paths ==="',
    'curl -sk --max-time 10 "https://auth.bokla.me/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1 | head -3',
    'echo "=== test.bokla.me diff ==="',
    'curl -sk --max-time 10 "https://test.bokla.me/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1 | head -3',
    'echo "=== api.shamcash.sy nslookup ==="',
    'nslookup api.shamcash.sy 2>&1 | head -10',
    'echo "=== auth.bokla.me nslookup ==="',
    'nslookup auth.bokla.me 2>&1 | head -10',
    'echo "=== traceroute to 191.44.71.70 ==="',
    'traceroute -n -w 2 -m 10 191.44.71.70 2>&1 | tail -10',
  ].join('; ');
  conn.exec(cmds, (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:60000, debug: () => {}});
