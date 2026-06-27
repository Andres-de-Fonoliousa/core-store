#!/bin/bash
# Login
rm -f /tmp/cookies6.txt
/usr/bin/curl -k -s -c /tmp/cookies6.txt https://CoreXGaming.duckdns.org/sanctum/csrf-cookie > /dev/null 2>&1
XSRF=$(cat /tmp/cookies6.txt | grep XSRF-TOKEN | awk "{print \$NF}")
XSRF_DECODED=$(python3 -c "import urllib.parse; import sys; print(urllib.parse.unquote(sys.argv[1]))" "$XSRF" 2>/dev/null || echo "$XSRF")
/usr/bin/curl -k -s -b /tmp/cookies6.txt -c /tmp/cookies6.txt -X POST https://CoreXGaming.duckdns.org/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest" -H "X-XSRF-TOKEN: $XSRF_DECODED" \
  -H "Origin: https://CoreXGaming.duckdns.org" \
  -H "Referer: https://CoreXGaming.duckdns.org/login" \
  -d "{\"email\":\"astroid198@gmail.com\",\"password\":\"Admin2483!\"}" > /dev/null 2>&1

# Call KPI
echo "=== KPI Response ==="
/usr/bin/curl -k -s -b /tmp/cookies6.txt -w "\nHTTP: %{http_code}" \
  -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" \
  -H "Origin: https://CoreXGaming.duckdns.org" \
  -H "Referer: https://CoreXGaming.duckdns.org/admin" \
  https://CoreXGaming.duckdns.org/api/admin/kpi 2>&1 | tail -10

echo ""
echo "=== Recent log ==="
tail -40 /var/www/store-app/storage/logs/laravel.log 2>&1 | grep -iE "kpi|error|exception|Query" | tail -10
