const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('kill $(lsof -t -i:3001) 2>/dev/null; sleep 2; setsid node /var/www/store-app/sham-bridge/server.js < /dev/null > /tmp/sham-bridge.log 2>&1 &', (e, s) => {
    let o = '';
    s.on('data', d => o += d);
    s.on('close', () => {
      console.log('Started with setsid');
      conn.end();
    });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:10000, debug: () => {}});
