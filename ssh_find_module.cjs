const { Client } = require('ssh2');
const conn = new Client();
conn.on('ready', () => {
  conn.exec('cd /var/www/store-app/sham-bridge && node -e "console.log(require.resolve(\\\"@jhad-dev/shamy\\\"))"; echo "==="; cat /var/www/store-app/sham-bridge/node_modules/@jhad-dev/shamy/src/Constants.js | head -3; echo "==="; cat /var/www/store-app/sham-bridge/node_modules/@jhad-dev/shamy/src/index.js | head -5', (e, s) => {
    let o = ''; s.on('data', d => o += d); s.on('close', () => { console.log(o); conn.end(); });
  });
}).on('error', e => console.error(e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:10000, debug: () => {}});
