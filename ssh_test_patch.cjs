const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('sleep 3; curl -s --max-time 3 http://127.0.0.1:3001/health; echo; curl -s --max-time 3 http://127.0.0.1:3001/api/session; echo; curl -s --max-time 3 -X POST http://127.0.0.1:3001/api/initialize; echo; sleep 6; curl -s --max-time 3 http://127.0.0.1:3001/health', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:30000, debug: () => {}});
