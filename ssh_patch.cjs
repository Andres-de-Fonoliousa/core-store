const { Client } = require('ssh2');
const conn = new Client();

const constantsPath = '/var/www/store-app/sham-bridge/node_modules/@jhad-dev/shamy/src/Constants.js';
const newContent = `// src/Constants.js - PATCHED for test.bokla.me

const API_BASE = 'https://test.bokla.me/v4';
const PAYMENT_BASE = 'https://payment.shamcash.com/v4';

const RSA_PUBLIC_KEY = \`-----BEGIN RSA PUBLIC KEY-----
MIIBCgKCAQEAuj8jcVjIoCND5p0ZIDMcNkPV3YzF3zywvB0az6Vorb+VHeAlUHut
WNRMmyVr3Eu+pPx27v+V7V60Nq9j5QSTeHXC4ndMuHrRUDc8IEhDcbOFdPEwrA6Q
UH+K1d8VQUcXOHPRcx0xEDtNwW8dKP6ySI3tt61HWp+/s133+OIAUKyH5BmWmauj
tJWaRfxwVA3okvwHMgWRfK0Nyxe6yFnmO4izOqKt/Pph0uPZVXL4/JawC5lvuwbk
SMuPGJjRN34YuMje1mkvArHTSeJ7dplqG6rXIg1X75m1elFu4GiLCc76SqgQBmXW
KSe5sprj2OrooP5B/liFD0LnsuVBWRarFQIDAQAB
-----END RSA PUBLIC KEY-----\`;

const STATIC_AES_KEY = "g0Zrgp8XRK/BN2ZAtUfJDQ==";

module.exports = {
    API_BASE,
    PAYMENT_BASE,
    RSA_PUBLIC_KEY,
    STATIC_AES_KEY
};`;

conn.on('ready', () => {
  conn.sftp((err, sftp) => {
    if (err) { console.error('SFTP:', err.message); conn.end(); return; }
    sftp.writeFile(constantsPath, Buffer.from(newContent), (err) => {
      if (err) { console.error('Write:', err.message); conn.end(); return; }
      console.log('Constants.js patched → test.bokla.me');
      conn.exec('pkill -9 -f "node server.js" 2>/dev/null; sleep 1; cd /var/www/store-app/sham-bridge && BIND=127.0.0.1 PORT=3001 nohup node server.js > /tmp/sham-bridge.log 2>&1 & disown', (e2) => {
        console.log('Bridge restarted');
        conn.end();
      });
    });
  });
}).on('error', e => console.error('Error:', e.message)).connect({host:'130.94.47.170', port:22, username:'root', password:'Qrt157154@', readyTimeout:15000, debug: () => {}});
