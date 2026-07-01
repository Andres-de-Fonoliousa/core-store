const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('ps aux | grep "node server" | grep -v grep; echo "==="; cat /tmp/sham-bridge.log 2>/dev/null | tail -10; echo "==="; ss -tlnp 2>/dev/null | grep 3001', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:10000, debug: () => {}});
