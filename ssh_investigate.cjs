const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  const cmds = [
    'curl -sk --max-time 10 https://test.bokla.me/v4/api/Session/check -d "{}" -H "Content-Type: application/json" 2>&1 | head -5',
    'echo "==="',
    'curl -sk --max-time 10 https://test.bokla.me/v4/api/Account/balances -d "{}" -H "Content-Type: application/json" 2>&1 | head -5',
    'echo "==="',
    'curl -sk --max-time 10 https://api.shamcash.sy/v4/api/Session/check -d "{}" -H "Content-Type: application/json" 2>&1 | head -5',
    'echo "==="',
    'cat /var/www/store-app/.env 2>/dev/null | grep -i sham',
    'echo "==="',
    'which tor 2>/dev/null; curl -sk --max-time 15 --socks5-hostname 127.0.0.1:9050 https://test.bokla.me/v4/api/Session/check -d "{}" -H "Content-Type: application/json" 2>&1 | head -5',
    'echo "==="',
    'curl -sk --max-time 10 https://google.com -o /dev/null -w "%{http_code}" 2>/dev/null',
    'echo "==="',
    'cat /var/www/store-app/sham-bridge/node_modules/@jhad-dev/shamy/src/Constants.js'
  ].join('; ');
  conn.exec(cmds, (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:90000, debug: () => {}});
