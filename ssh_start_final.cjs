const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('pkill -9 -f "node server.js" 2>/dev/null; sleep 2; cd /var/www/store-app/sham-bridge && BIND=127.0.0.1 PORT=3001 nohup node server.js > /tmp/sham-bridge.log 2>&1 & disown', (e, s) => {
    let o = '';
    s.on('data', d => o += d);
    s.on('close', () => {
      console.log('Killed old, started new');
      conn.end();
    });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:10000, debug: () => {}});
