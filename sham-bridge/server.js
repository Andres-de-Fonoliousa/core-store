import express from 'express';
import cors from 'cors';
import QRCode from 'qrcode';
import { ShamClient } from '@jhad-dev/shamy';
import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

// ─── Config ────────────────────────────────────────────────────────────────
const PORT = parseInt(process.env.PORT || '3001', 10);
const SESSION_DIR = process.env.SESSION_DIR || join(__dirname, 'sessions');
const LARAVEL_WEBHOOK = process.env.LARAVEL_WEBHOOK || 'http://127.0.0.1/api/sham-cash/webhook';
const WEBHOOK_SECRET = process.env.WEBHOOK_SECRET || '';
const MONITOR_INTERVAL = parseInt(process.env.MONITOR_INTERVAL || '15000', 10);

// ─── State ─────────────────────────────────────────────────────────────────
let client = null;
let isReady = false;
let lastKnownTranId = 0;
let monitorInterval = null;

// ─── Express ───────────────────────────────────────────────────────────────
const app = express();
app.use(cors());
app.use(express.json());

// ─── Helpers ───────────────────────────────────────────────────────────────

function saveSession(data) {
  const file = join(SESSION_DIR, 'session.json');
  if (!existsSync(SESSION_DIR)) {
    mkdirSync(SESSION_DIR, { recursive: true });
  }
  writeFileSync(file, JSON.stringify(data, null, 2));
}

function loadSession() {
  const file = join(SESSION_DIR, 'session.json');
  if (existsSync(file)) {
    return JSON.parse(readFileSync(file, 'utf-8'));
  }
  return null;
}

// ─── Initialize ShamClient ────────────────────────────────────────────────

async function initClient() {
  client = new ShamClient({ sessionDir: SESSION_DIR });

  client.on('connecting', (msg) => {
    console.log('[Sham]', msg);
  });

  client.on('qr', async (payload) => {
    console.log('[Sham] QR emitted — scan with Sham Cash app');
    latestQR = payload;
    // Save QR as PNG for web access
    const qrDir = process.env.QR_PUBLIC_DIR || join(__dirname, 'public');
    if (!existsSync(qrDir)) mkdirSync(qrDir, { recursive: true });
    try {
      await QRCode.toFile(join(qrDir, 'sham-qr.png'), payload, {
        type: 'png', width: 400, margin: 2,
      });
      console.log('[Sham] QR saved to', join(qrDir, 'sham-qr.png'));
    } catch (err) {
      console.error('[Sham] QR save failed:', err.message);
    }
  });

  client.on('ready', (data) => {
    console.log('[Sham] Ready —', data.message);
    isReady = true;
  });

  client.on('transaction', async (tx) => {
    console.log('[Sham] New transaction:', tx.id, 'type:', tx.type);

    // tx.type === 1 = incoming
    if (tx.type === 'incoming' || tx.type === 1) {
      await forwardToLaravel({
        tranId: tx.id,
        amount: tx.amount,
        currencyId: tx.currency || 1,
        currencyName: null,
        peerUserName: tx.peer,
        note: tx.note,
        tranDate: tx.date,
        tranTime: tx.time,
      });
    }

    if (tx.id && tx.id > lastKnownTranId) {
      lastKnownTranId = tx.id;
    }
  });

  client.on('error', (err) => {
    console.error('[Sham] Error:', err.message);
  });

  client.on('info', (msg) => {
    console.log('[Sham Info]', msg);
  });

  try {
    await client.initialize();
  } catch (err) {
    console.error('[Sham] Init failed:', err.message);
  }
}

async function forwardToLaravel(txData) {
  try {
    const payload = {
      transactions: [{
        tranId: txData.tranId,
        amount: txData.amount,
        currencyId: txData.currencyId || 1,
        currencyName: txData.currencyName || 'USD',
        peerUserName: txData.peerUserName,
        peerAccountNumber: txData.peerAccountNumber,
        peerAccountAddress: txData.peerAccountAddress,
        note: txData.note,
        tranDate: txData.tranDate,
        tranTime: txData.tranTime,
      }],
    };

    const headers = { 'Content-Type': 'application/json' };
    if (WEBHOOK_SECRET) {
      headers['X-Sham-Secret'] = WEBHOOK_SECRET;
    }

    await fetch(LARAVEL_WEBHOOK, {
      method: 'POST',
      headers,
      body: JSON.stringify(payload),
    });

    console.log('[Sham] Forwarded transaction', txData.tranId, 'to Laravel');
  } catch (err) {
    console.error('[Sham] Failed to forward transaction:', err.message);
  }
}

// ─── Monitor ───────────────────────────────────────────────────────────────

function startMonitor() {
  if (monitorInterval) {
    clearInterval(monitorInterval);
  }

  monitorInterval = setInterval(async () => {
    if (!isReady || !client) return;

    try {
      const logs = await client.history.getLogs(1, 5);

      if (logs?.log && logs.log.length > 0) {
        for (const tx of logs.log) {
          // Only process incoming (tranKind === 1)
          if (tx.tranKind !== 1) continue;
          if (tx.tranId <= lastKnownTranId) continue;

          await forwardToLaravel({
            tranId: tx.tranId,
            amount: tx.amount,
            currencyId: tx.currencyId,
            currencyName: tx.currencyName,
            peerUserName: tx.peerUserName,
            peerAccountNumber: tx.peerAccountNumber,
            peerAccountAddress: tx.peerAccountAddress,
            note: tx.note,
            tranDate: tx.tranDate,
            tranTime: tx.tranTime,
          });

          if (tx.tranId > lastKnownTranId) {
            lastKnownTranId = tx.tranId;
          }
        }
      }
    } catch (err) {
      console.error('[Sham] Monitor poll error:', err.message);
    }
  }, MONITOR_INTERVAL);

  console.log(`[Sham] Monitor started (interval: ${MONITOR_INTERVAL}ms)`);
}

function stopMonitor() {
  if (monitorInterval) {
    clearInterval(monitorInterval);
    monitorInterval = null;
    console.log('[Sham] Monitor stopped');
  }
}

// ─── Routes ────────────────────────────────────────────────────────────────

// Health check
app.get('/health', (req, res) => {
  res.json({
    status: isReady ? 'ready' : 'connecting',
    ready: isReady,
    lastKnownTranId,
  });
});

// Get latest QR payload
let latestQR = null;
app.get('/qr', (req, res) => {
  if (latestQR) {
    res.json({ qr: latestQR });
  } else {
    res.status(404).json({ error: 'No QR available. Client may already be connected.' });
  }
});

// Serve QR as PNG image
app.get('/qr.png', (req, res) => {
  const qrDir = process.env.QR_PUBLIC_DIR || join(__dirname, 'public');
  const qrFile = join(qrDir, 'sham-qr.png');
  if (existsSync(qrFile)) {
    res.sendFile(qrFile);
  } else {
    res.status(404).json({ error: 'QR image not yet generated.' });
  }
});

// Check a specific transaction
app.post('/check', async (req, res) => {
  if (!isReady || !client) {
    return res.status(503).json({ found: false, error: 'client_not_ready' });
  }

  const { tranId, amount, currencyId } = req.body;

  if (!tranId) {
    return res.status(400).json({ found: false, error: 'tranId required' });
  }

  try {
    const logs = await client.history.getLogs(1, 10, { tranID: String(tranId) });

    const match = logs?.log?.find(tx => {
      const idMatch = String(tx.tranId) === String(tranId) || String(tx.strTranId) === String(tranId);
      const amountMatch = !amount || Number(tx.amount) === Number(amount);
      const currencyMatch = !currencyId || Number(tx.currencyId) === Number(currencyId);
      return idMatch && amountMatch && currencyMatch;
    });

    if (match) {
      return res.json({
        found: true,
        transaction: {
          tranId: match.tranId,
          strTranId: match.strTranId,
          amount: match.amount,
          currencyId: match.currencyId,
          currencyName: match.currencyName,
          peerUserName: match.peerUserName,
          peerAccountNumber: match.peerAccountNumber,
          peerAccountAddress: match.peerAccountAddress,
          note: match.note,
          tranDate: match.tranDate,
          tranTime: match.tranTime,
          isVerified: match.isVerified,
          tranKind: match.tranKind,
        },
      });
    }

    res.json({ found: false });
  } catch (err) {
    console.error('[Sham] Check error:', err.message);
    res.status(500).json({ found: false, error: err.message });
  }
});

// Fetch incoming transactions since a given timestamp
app.post('/incoming', async (req, res) => {
  if (!isReady || !client) {
    return res.status(503).json({ transactions: [], error: 'client_not_ready' });
  }

  const { since } = req.body;

  try {
    const allTransactions = [];
    let page = 1;
    let hasMore = true;

    while (hasMore && page <= 3) {  // max 3 pages to avoid over-fetching
      const logs = await client.history.getLogs(page, 50);
      if (!logs?.log || logs.log.length === 0) break;

      for (const tx of logs.log) {
        if (tx.tranKind !== 1) continue;  // only incoming

        if (since) {
          const txDateTime = `${tx.tranDate}T${tx.tranTime}`;
          if (txDateTime < since) {
            hasMore = false;
            break;
          }
        }

        allTransactions.push({
          tranId: tx.tranId,
          strTranId: tx.strTranId,
          amount: tx.amount,
          currencyId: tx.currencyId,
          currencyName: tx.currencyName,
          peerUserName: tx.peerUserName,
          peerAccountNumber: tx.peerAccountNumber,
          peerAccountAddress: tx.peerAccountAddress,
          note: tx.note,
          tranDate: tx.tranDate,
          tranTime: tx.tranTime,
          isVerified: tx.isVerified,
        });
      }

      hasMore = logs.haveNext === true;
      page++;
    }

    // Update lastKnownTranId
    if (allTransactions.length > 0) {
      const maxId = Math.max(...allTransactions.map(t => t.tranId));
      if (maxId > lastKnownTranId) {
        lastKnownTranId = maxId;
      }
    }

    res.json({ transactions: allTransactions });
  } catch (err) {
    console.error('[Sham] Incoming error:', err.message);
    res.status(500).json({ transactions: [], error: err.message });
  }
});

// Start/stop monitor
app.post('/monitor/start', (req, res) => {
  startMonitor();
  res.json({ message: 'Monitor started' });
});

app.post('/monitor/stop', (req, res) => {
  stopMonitor();
  res.json({ message: 'Monitor stopped' });
});

// ─── Start ─────────────────────────────────────────────────────────────────

async function start() {
  console.log('[Sham] Starting bridge...');
  console.log('[Sham] Session dir:', SESSION_DIR);

  await initClient();

  app.listen(PORT, '127.0.0.1', () => {
    console.log(`[Sham] Bridge listening on http://127.0.0.1:${PORT}`);
  });
}

start().catch(err => {
  console.error('[Sham] Fatal:', err);
  process.exit(1);
});
