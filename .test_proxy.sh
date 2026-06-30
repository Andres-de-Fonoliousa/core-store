#!/bin/bash
echo "=== Trying free proxies ==="
for proxy in "38.54.116.206:8080" "8.219.97.248:8080" "20.205.61.143:80"; do
  echo -n "$proxy: "
  result=$(curl -s --max-time 8 -x "http://$proxy" -o /dev/null -w "HTTP %{http_code} (%{time_total}s)" "https://api.shamcash.sy/v4/api/Session/check" -X POST -H "Content-Type: application/json" -d "{}" 2>&1)
  echo "$result"
done
echo "=== Done ==="
