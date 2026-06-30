#!/bin/bash
curl -sN http://127.0.0.1:3001/api/events &
SSE_PID=$!
sleep 1
echo '--- INIT ---'
curl -s -X POST http://127.0.0.1:3001/api/initialize
echo ''
echo '--- WAITING (5s) ---'
sleep 5
kill $SSE_PID 2>/dev/null
wait $SSE_PID 2>/dev/null
echo '--- DONE ---'
