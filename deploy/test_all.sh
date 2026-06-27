#!/bin/bash
echo "=== Public Pages ==="
for path in "/" "/browse" "/about" "/terms" "/privacy" "/support" "/login" "/register"; do
  code=$(/usr/bin/curl -k -s -o /dev/null -w "%{http_code}" "https://CoreXGaming.duckdns.org$path")
  echo "$path => $code"
done

echo ""
echo "=== API Endpoints ==="
for path in "/api/products?per_page=1" "/api/categories?per_page=1" "/api/products/1"; do
  code=$(/usr/bin/curl -k -s -o /dev/null -w "%{http_code}" "https://CoreXGaming.duckdns.org$path")
  echo "$path => $code"
done

echo ""
echo "=== Login + Authenticated endpoints ==="
rm -f /tmp/cookies5.txt
/usr/bin/curl -k -s -c /tmp/cookies5.txt https://CoreXGaming.duckdns.org/sanctum/csrf-cookie > /dev/null 2>&1
XSRF=$(cat /tmp/cookies5.txt | grep XSRF-TOKEN | awk "{print \$NF}")
XSRF_DECODED=$(python3 -c "import urllib.parse; import sys; print(urllib.parse.unquote(sys.argv[1]))" "$XSRF" 2>/dev/null || echo "$XSRF")
/usr/bin/curl -k -s -b /tmp/cookies5.txt -c /tmp/cookies5.txt -X POST https://CoreXGaming.duckdns.org/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest" -H "X-XSRF-TOKEN: $XSRF_DECODED" \
  -H "Origin: https://CoreXGaming.duckdns.org" \
  -H "Referer: https://CoreXGaming.duckdns.org/login" \
  -d "{\"email\":\"astroid198@gmail.com\",\"password\":\"Admin2483!\"}" > /dev/null 2>&1

echo "Customer endpoints:"
for path in "/api/user" "/api/orders" "/api/transactions" "/api/notifications"; do
  code=$(/usr/bin/curl -k -s -b /tmp/cookies5.txt -o /dev/null -w "%{http_code}" \
    -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" \
    -H "Origin: https://CoreXGaming.duckdns.org" \
    -H "Referer: https://CoreXGaming.duckdns.org/dashboard" \
    "https://CoreXGaming.duckdns.org$path" 2>&1)
  echo "  $path => $code"
done

echo "Admin endpoints:"
for path in "/api/admin/kpi" "/api/admin/categories?per_page=1" "/api/admin/products?per_page=1" "/api/admin/orders" "/api/admin/users?per_page=1"; do
  code=$(/usr/bin/curl -k -s -b /tmp/cookies5.txt -o /dev/null -w "%{http_code}" \
    -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" \
    -H "Origin: https://CoreXGaming.duckdns.org" \
    -H "Referer: https://CoreXGaming.duckdns.org/admin" \
    "https://CoreXGaming.duckdns.org$path" 2>&1)
  echo "  $path => $code"
done

echo ""
echo "=== Inertia Admin Pages ==="
for path in "/admin" "/admin/users" "/admin/products" "/admin/categories" "/admin/orders" "/admin/kpi"; do
  code=$(/usr/bin/curl -k -s -b /tmp/cookies5.txt -o /dev/null -w "%{http_code}" \
    -H "Accept: text/html,application/xhtml+xml" \
    "https://CoreXGaming.duckdns.org$path" 2>&1)
  echo "  $path => $code"
done

echo ""
echo "=== Order pages ==="
for path in "/dashboard" "/orders" "/deposit" "/notifications"; do
  code=$(/usr/bin/curl -k -s -b /tmp/cookies5.txt -o /dev/null -w "%{http_code}" \
    -H "Accept: text/html,application/xhtml+xml" \
    "https://CoreXGaming.duckdns.org$path" 2>&1)
  echo "  $path => $code"
done
