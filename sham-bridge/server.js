import express from 'express';
import cors from 'cors';
import QRCode from 'qrcode';
import { ShamClient } from '@jhad-dev/shamy';
import { createServer } from 'http';
import { existsSync, mkdirSync, readFileSync, writeFileSync, unlinkSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const app = express();
const server = createServer(app);
app.use(cors());
app.use(express.json({ limit: '1mb' }));

const API_TIMEOUT = 8000;

function withTimeout(promise, ms = API_TIMEOUT) {
  return Promise.race([
    promise,
    new Promise((_, reject) => setTimeout(() => reject(new Error('API timeout')), ms)),
  ]);
}

const sessionsDir = join(__dirname, 'sessions');
if (!existsSync(sessionsDir)) mkdirSync(sessionsDir, { recursive: true });

let shamClient = null;
let sseClients = [];
let lastKnownTranId = 0;
let isReady = false;

function broadcast(event, data) {
  sseClients.forEach(res => {
    res.write(`event: ${event}\ndata: ${JSON.stringify(data)}\n\n`);
  });
}

function setupShamClient() {
  if (shamClient) shamClient.removeAllListeners();
  isReady = false;
  shamClient = new ShamClient({ sessionDir: sessionsDir });

  shamClient.on('connecting', (msg) => {
    console.log('[Sham]', msg);
    broadcast('status', { status: 'connecting', message: 'Connecting to ShamCash...' });
  });

  shamClient.on('qr', async (qrData) => {
    console.log('[Sham] QR emitted');
    try {
      const qrImage = await QRCode.toDataURL(qrData, { width: 280, margin: 2, errorCorrectionLevel: 'M' });
      broadcast('qr', { image: qrImage, raw: qrData });
    } catch (err) {
      broadcast('qr', { raw: qrData });
    }
    broadcast('status', { status: 'awaiting_scan', message: 'Scan QR with the ShamCash app' });
  });

  shamClient.on('ready', (data) => {
    console.log('[Sham] Ready —', data?.message || 'authenticated');
    isReady = true;
    cachedProfile = null;
    broadcast('status', { status: 'ready', message: 'Authenticated successfully' });
  });

  shamClient.on('transaction', async (tx) => {
    console.log('[Sham] Transaction:', tx.id, 'type:', tx.type);
    broadcast('transaction', tx);
    if (tx.id && tx.id > lastKnownTranId) {
      lastKnownTranId = tx.id;
    }
  });

  shamClient.on('error', (err) => {
    console.error('[Sham] Error:', err.message);
    broadcast('error', { message: err.message || String(err) });
  });

  shamClient.on('info', (msg) => {
    console.log('[Sham Info]', msg);
    broadcast('info', { message: msg });
  });
}

// ─── SSE ────────────────────────────────────────────────────────────────────

app.get('/api/events', (req, res) => {
  res.writeHead(200, {
    'Content-Type': 'text/event-stream',
    'Cache-Control': 'no-cache',
    Connection: 'keep-alive',
  });
  res.write('\n');
  sseClients.push(res);
  req.on('close', () => {
    sseClients = sseClients.filter(r => r !== res);
  });
});

// ─── Auth / Init ────────────────────────────────────────────────────────────

app.post('/api/initialize', async (req, res) => {
  try {
    setupShamClient();
    await shamClient.initialize();
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

let cachedProfile = null;

app.get('/api/status', async (req, res) => {
  try {
    if (cachedProfile) {
      res.json({ authenticated: true, profile: cachedProfile });
      return;
    }
    if (!shamClient?.account?.getMyProfile) {
      res.json({ authenticated: false, profile: null });
      return;
    }
    const profile = await withTimeout(shamClient.account.getMyProfile());
    cachedProfile = profile;
    res.json({ authenticated: true, profile });
  } catch (err) {
    res.json({ authenticated: false, profile: null });
  }
});

app.get('/api/profile', async (req, res) => {
  try {
    const profile = await withTimeout(shamClient.account.getMyProfile());
    cachedProfile = profile;
    res.json(profile);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/balances', async (req, res) => {
  try {
    const balances = await withTimeout(shamClient.account.getBalances());
    res.json(balances);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/session', (req, res) => {
  const hasSession = shamClient && (shamClient.token || shamClient.accessToken || cachedProfile);
  res.json({ hasSession: !!hasSession, sessionFile: existsSync(join(sessionsDir, 'session.json')) });
});

app.post('/api/reset', (req, res) => {
  if (shamClient) shamClient.removeAllListeners();
  cachedProfile = null;
  isReady = false;
  shamClient = null;
  const sf = join(sessionsDir, 'session.json');
  try { if (existsSync(sf)) unlinkSync(sf); } catch {}
  res.json({ ok: true });
});

// ─── Transactions ───────────────────────────────────────────────────────────

app.get('/api/transactions', async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const limit = parseInt(req.query.limit) || 20;
    const filters = req.query.filters ? JSON.parse(req.query.filters) : {};
    const logs = await withTimeout(shamClient.history.getLogs(page, limit, filters));
    res.json(logs);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.get('/api/favorites', async (req, res) => {
  try {
    const favorites = await withTimeout(shamClient.account.getFavorites());
    res.json(favorites);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/resolve', async (req, res) => {
  try {
    const { address } = req.body;
    const result = await withTimeout(shamClient.transfer.resolveAccount(address));
    res.json(result);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/transfer', async (req, res) => {
  try {
    const { peerAccount, amount, currencyId, note, pin } = req.body;
    const result = await withTimeout(shamClient.transfer.executeTransaction(peerAccount, amount, currencyId, note, pin));
    res.json(result);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ─── Monitor ────────────────────────────────────────────────────────────────

app.post('/api/monitor/start', async (req, res) => {
  try {
    const interval = parseInt(req.body.interval) || 10000;
    shamClient.history.startMonitoring(interval);
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.post('/api/monitor/stop', async (req, res) => {
  try {
    shamClient.history.stopMonitoring();
    res.json({ ok: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// ─── Verification Endpoints (used by Laravel) ──────────────────────────────

app.get('/health', (req, res) => {
  res.json({
    status: isReady ? 'ready' : shamClient ? 'connecting' : 'stopped',
    ready: isReady,
    lastKnownTranId,
  });
});

app.post('/check', async (req, res) => {
  if (!isReady || !shamClient) {
    return res.status(503).json({ found: false, error: 'client_not_ready' });
  }
  const { tranId, amount, currencyId } = req.body;
  if (!tranId) {
    return res.status(400).json({ found: false, error: 'tranId required' });
  }
  try {
    const logs = await withTimeout(shamClient.history.getLogs(1, 10, { tranID: String(tranId) }));
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
    res.status(500).json({ found: false, error: err.message });
  }
});

app.post('/incoming', async (req, res) => {
  if (!isReady || !shamClient) {
    return res.status(503).json({ transactions: [], error: 'client_not_ready' });
  }
  const { since } = req.body;
  try {
    const allTransactions = [];
    let page = 1;
    let hasMore = true;
    while (hasMore && page <= 3) {
      const logs = await withTimeout(shamClient.history.getLogs(page, 50));
      if (!logs?.log || logs.log.length === 0) break;
      for (const tx of logs.log) {
        if (tx.tranKind !== 1) continue;
        if (since) {
          const txDateTime = `${tx.tranDate}T${tx.tranTime}`;
          if (txDateTime < since) { hasMore = false; break; }
        }
        allTransactions.push({
          tranId: tx.tranId, strTranId: tx.strTranId, amount: tx.amount,
          currencyId: tx.currencyId, currencyName: tx.currencyName,
          peerUserName: tx.peerUserName, peerAccountNumber: tx.peerAccountNumber,
          peerAccountAddress: tx.peerAccountAddress, note: tx.note,
          tranDate: tx.tranDate, tranTime: tx.tranTime, isVerified: tx.isVerified,
        });
      }
      hasMore = logs.haveNext === true;
      page++;
    }
    if (allTransactions.length > 0) {
      const maxId = Math.max(...allTransactions.map(t => t.tranId));
      if (maxId > lastKnownTranId) lastKnownTranId = maxId;
    }
    res.json({ transactions: allTransactions });
  } catch (err) {
    res.status(500).json({ transactions: [], error: err.message });
  }
});

// ─── Static UI ──────────────────────────────────────────────────────────────

const html = readFileSync(join(__dirname, 'public', 'index.html'), 'utf-8');
app.get('/', (req, res) => res.type('html').send(html));

// ─── Start ──────────────────────────────────────────────────────────────────

const PORT = parseInt(process.env.PORT || '3001', 10);
const BIND = process.env.BIND || '127.0.0.1';

server.listen(PORT, BIND, () => {
  console.log(`Shamy Bridge running at http://${BIND}:${PORT}`);
});
