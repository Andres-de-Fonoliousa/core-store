const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  const cmds = [
    'kill -9 $(lsof -t -i:3001) 2>/dev/null; sleep 1',
    'echo "--- killed ---"',
    'set -x; nohup node /var/www/store-app/sham-bridge/server.js > /tmp/sham-bridge.log 2>&1 & sleep 3; echo "--- started ---"; cat /tmp/sham-bridge.log; curl -s --max-time 3 http://127.0.0.1:3001/health',
    'echo',
    'curl -s --max-time 5 -X POST http://127.0.0.1:3001/api/initialize',
    'echo',
  ].join('; ');
  conn.exec(cmds, (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:30000, debug: () => {}});
