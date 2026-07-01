$projectDir = "C:\Users\Yazan\Desktop\Web\Store-app"
cd $projectDir

Write-Host "=== Deploy bridge fix === (1 password entry only)"

# Upload all 3 scripts
Write-Host "Uploading scripts..."
scp -o StrictHostKeyChecking=accept-new auth-tmp/fix-all.sh root@130.94.47.170:/tmp/fix-all.sh
scp -o StrictHostKeyChecking=accept-new auth-tmp/restart-bridge.sh root@130.94.47.170:/tmp/restart-bridge.sh

# Run fix-all (nginx + shamy patch)
Write-Host "Running Nginx + library patch..."
ssh -o StrictHostKeyChecking=accept-new root@130.94.47.170 "bash /tmp/fix-all.sh"

# Deploy updated server.js via git
Write-Host "Deploying updated bridge code..."
git add sham-bridge/server.js
git commit -m "Add API timeouts and error handling" --allow-empty
git bundle create deploy.bundle HEAD master
scp -o StrictHostKeyChecking=accept-new deploy.bundle root@130.94.47.170:/tmp/deploy.bundle
ssh -o StrictHostKeyChecking=accept-new root@130.94.47.170 "cd /var/www/store-app && git fetch /tmp/deploy.bundle master && git reset --hard FETCH_HEAD"

# Restart bridge
Write-Host "Restarting bridge..."
ssh -o StrictHostKeyChecking=accept-new root@130.94.47.170 "bash /tmp/restart-bridge.sh"

Write-Host "=== Done! Open https://corexgaming.duckdns.org/admin/sham-cash/ ==="
