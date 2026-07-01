const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('which torsocks 2>/dev/null && echo "torsocks: yes" || echo "torsocks: no"; which proxychains 2>/dev/null && echo "proxychains: yes" || echo "proxychains: no"; which proxychains4 2>/dev/null && echo "proxychains4: yes" || echo "proxychains4: no"; echo "---"; curl --socks5-hostname 127.0.0.1:9050 -s --max-time 10 -k "https://api.shamcash.sy/v4/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1 | head -5', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:30000, debug: () => {}});
