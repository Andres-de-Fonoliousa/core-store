const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('ps aux | grep "node.*sham-bridge" | grep -v grep | awk "{print \\$2}" | xargs -r kill -9; sleep 2; echo "--- processes ---"; ps aux | grep "node.*sham-bridge" | grep -v grep', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:10000, debug: () => {}});
