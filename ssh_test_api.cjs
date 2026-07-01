const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('sleep 2 && curl -s --max-time 3 http://127.0.0.1:3001/health && echo && curl -s --max-time 3 -X POST http://127.0.0.1:3001/api/initialize && echo && sleep 8 && curl -s --max-time 5 http://127.0.0.1:3001/health && echo && curl -s --max-time 10 http://127.0.0.1:3001/api/status && echo && curl -s --max-time 10 http://127.0.0.1:3001/api/balances && echo', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:60000, debug: () => {}});
