#!/bin/bash
echo "=== Check alternative hosts ==="
for host in "shamcash.com" "www.shamcash.com" "api.shamcash.com" "pay.shamcash.sy"; do
  echo -n "$host: "
  curl -s --max-time 5 -o /dev/null -w "HTTP %{http_code} (%{time_total}s)\n" "https://$host/" 2>&1
done
echo "=== Check alternate ports ==="
for port in 80 443 8080 8443; do
  echo -n "api.shamcash.sy:$port: "
  curl -s --max-time 5 -o /dev/null -w "HTTP %{http_code} (%{time_total}s)\n" "http://api.shamcash.sy:$port/" 2>&1
done
