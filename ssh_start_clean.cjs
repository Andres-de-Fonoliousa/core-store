const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('kill $(lsof -t -i:3001) 2>/dev/null; sleep 1; setsid node /var/www/store-app/sham-bridge/server.js < /dev/null > /tmp/sham-bridge.log 2>&1 & sleep 2; cat /tmp/sham-bridge.log; curl -s --max-time 3 http://127.0.0.1:3001/health', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:15000, debug: () => {}});
