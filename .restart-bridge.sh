#!/bin/bash
set -e
cd /var/www/store-app/sham-bridge
OLD_PID=$(ps aux | grep 'node server.js' | grep -v grep | awk '{print $2}')
if [ -n "$OLD_PID" ]; then kill $OLD_PID 2>/dev/null; sleep 1; fi
nohup node server.js > /tmp/bridge.log 2>&1 &
sleep 2
curl -s http://127.0.0.1:3001/health
echo ""
