const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  const cmds = [
    'echo "=== torsocks curl ==="',
    'torsocks curl -v --max-time 15 -k "https://api.shamcash.sy/v4/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1 | head -20',
    'echo "=== torsocks curl exit code ==="',
    'torsocks curl -s --max-time 15 -o /dev/null -w "%{http_code}" -k "https://api.shamcash.sy/v4/api/Session/check" -H "Content-Type: application/json" -d "{}" 2>&1',
    'echo'
  ].join('; ');
  conn.exec(cmds, (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:45000, debug: () => {}});
