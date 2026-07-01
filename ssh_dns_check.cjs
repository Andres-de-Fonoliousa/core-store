const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('echo "=== auth.bokla.me ==="; curl -sk --max-time 5 https://auth.bokla.me/v4/api/Session/check -d "{}" -H "Content-Type: application/json" 2>&1 | head -5; echo; echo "=== test.bokla.me ==="; curl -sk --max-time 5 https://test.bokla.me/v4/api/Session/check -d "{}" -H "Content-Type: application/json" 2>&1 | head -5; echo; echo "=== api.shamcash.sy ==="; curl -sk --max-time 5 https://api.shamcash.sy/v4/api/Session/check -d "{}" -H "Content-Type: application/json" 2>&1 | head -5', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:30000, debug: () => {}});
