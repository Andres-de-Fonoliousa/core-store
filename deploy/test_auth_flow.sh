#!/bin/bash
rm -f /tmp/cookies4.txt 2>/dev/null

echo "=== Step 1: CSRF Cookie ==="
/usr/bin/curl -k -s -c /tmp/cookies4.txt https://CoreXGaming.duckdns.org/sanctum/csrf-cookie

echo "=== Extract XSRF-TOKEN ==="
XSRF=$(cat /tmp/cookies4.txt | grep XSRF-TOKEN | awk "{print \$NF}")
echo "XSRF: $XSRF"

# URL decode using python3
XSRF_DECODED=$(python3 -c "import urllib.parse; import sys; print(urllib.parse.unquote(sys.argv[1]))" "$XSRF" 2>/dev/null || echo "$XSRF")

echo "=== Step 2: Login ==="
/usr/bin/curl -k -s -b /tmp/cookies4.txt -c /tmp/cookies4.txt -D - -X POST https://CoreXGaming.duckdns.org/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -H "X-XSRF-TOKEN: $XSRF_DECODED" \
  -H "Origin: https://CoreXGaming.duckdns.org" \
  -H "Referer: https://CoreXGaming.duckdns.org/login" \
  -d "{\"email\":\"astroid198@gmail.com\",\"password\":\"Admin2483!\"}" 2>&1 | head -15

echo "=== Cookies after login ==="
cat /tmp/cookies4.txt | grep -iE "session|cores"

echo "=== Step 3: API /orders ==="
/usr/bin/curl -k -s -b /tmp/cookies4.txt -w "\nHTTP_CODE: %{http_code}" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -H "Origin: https://CoreXGaming.duckdns.org" \
  -H "Referer: https://CoreXGaming.duckdns.org/dashboard" \
  https://CoreXGaming.duckdns.org/api/orders 2>&1 | head -10
