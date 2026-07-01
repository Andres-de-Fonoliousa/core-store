set -e
cd /var/www/store-app
cp .env /tmp/deploy.env 2>/dev/null || true
git fetch /tmp/deploy.bundle master
git reset --hard FETCH_HEAD
cp /tmp/deploy.env .env 2>/dev/null || true
echo "Deployed. No rebuild needed."
