const { Client } = require('ssh2');
const fs = require('fs');
const path = require('path');
const conn = new Client();

const content = fs.readFileSync(path.join(__dirname, 'sham-bridge', 'server.js'), 'utf-8');

conn.on('ready', () => {
  conn.sftp((err, sftp) => {
    if (err) { console.error('SFTP:', err.message); conn.end(); return; }
    sftp.writeFile('/var/www/store-app/sham-bridge/server.js', Buffer.from(content), (err) => {
      if (err) { console.error('Write:', err.message); conn.end(); return; }
      console.log('Original server.js restored');
      conn.exec('pkill -9 -f "node server.js" 2>/dev/null; sleep 1', (e2) => {
        conn.exec('cd /var/www/store-app/sham-bridge && BIND=127.0.0.1 PORT=3001 nohup node server.js > /tmp/sham-bridge.log 2>&1 & disown', (e3) => {
          console.log('Bridge restarted');
          conn.end();
        });
      });
    });
  });
}).on('error', e => console.error('Error:', e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:15000, debug: () => {}});
