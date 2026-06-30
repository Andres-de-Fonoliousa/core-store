#!/bin/bash
echo "=== Testing Turkey proxies ==="
for proxy in \
  "95.9.211.33:3310" \
  "31.145.106.120:1080" \
  "217.195.203.28:3130" \
  "212.175.174.172:80" \
  "91.220.182.134:80"; do

  echo -n "Testing $proxy ... "
  result=$(curl -s --max-time 10 -x "http://$proxy" -o /tmp/proxy_test.txt -w "%{http_code}" "https://api.shamcash.sy/v4/api/Session/check" -X POST -H "Content-Type: application/json" -d '{}' 2>&1)
  if [ "$result" = "000" ]; then
    echo "TIMEOUT"
  elif [ "$result" = "405" ]; then
    echo "WORKS! (405 - expects POST)"
  elif [ "$result" = "200" ]; then
    echo "WORKS! (200)"
    cat /tmp/proxy_test.txt
    echo ""
  else
    echo "HTTP $result"
  fi
done

echo ""
echo "=== Check if Node.js can use HTTP_PROXY ==="
echo "If above works, we set HTTPS_PROXY env var for the bridge"
