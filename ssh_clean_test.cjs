const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  const cmds = [
    'kill -9 $(lsof -t -i:3001) 2>/dev/null; sleep 2',
    'setsid node /var/www/store-app/sham-bridge/server.js < /dev/null > /tmp/sham-bridge.log 2>&1 &',
    'sleep 4',
    'curl -s --max-time 3 http://127.0.0.1:3001/health',
    'echo',
    'curl -s --max-time 5 -X POST http://127.0.0.1:3001/api/initialize',
    'echo',
    'sleep 8',
    'curl -s --max-time 5 http://127.0.0.1:3001/health',
    'echo',
    'curl -s --max-time 20 http://127.0.0.1:3001/api/status',
    'echo',
    'curl -s --max-time 20 http://127.0.0.1:3001/api/balances',
    'echo',
    'curl -s --max-time 20 "http://127.0.0.1:3001/api/transactions?page=1&limit=2"',
    'echo'
  ].join('; ');
  conn.exec(cmds, (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:90000, debug: () => {}});
