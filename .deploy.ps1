$ErrorActionPreference = "Stop"
$password = "Qrt157154@"

$pwFile = "$env:TEMP\sshpass_pw.ps1"
"Write-Host -NoNewline '$password'" | Set-Content -Path $pwFile -Force
$env:SSH_ASKPASS = "powershell.exe -NoProfile -NonInteractive -File `"$pwFile`""
$env:SSH_ASKPASS_REQUIRE = "force"
$env:DISPLAY = "dirty"

$server = "root@130.94.47.170"
$opts = "-o StrictHostKeyChecking=accept-new"
$app = "/var/www/store-app"

Write-Host "=== Uploading files ==="
scp $opts "C:\Users\Yazan\Desktop\Web\Store-app\app\Http\Controllers\Admin\ShamCashProxyController.php" "${server}:${app}/app/Http/Controllers/Admin/ShamCashProxyController.php" 2>&1
scp $opts "C:\Users\Yazan\Desktop\Web\Store-app\sham-bridge\public\index.html" "${server}:${app}/sham-bridge/public/index.html" 2>&1

Write-Host "=== Setting permissions ==="
ssh $opts $server "chown www-data:www-data ${app}/app/Http/Controllers/Admin/ShamCashProxyController.php ${app}/sham-bridge/public/index.html" 2>&1

Write-Host "=== Clearing Laravel caches ==="
ssh $opts $server "cd ${app} && php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan optimize:clear" 2>&1

Write-Host "=== Uploading restart script ==="
scp $opts "C:\Users\Yazan\Desktop\Web\Store-app\.restart-bridge.sh" "${server}:/tmp/restart-bridge.sh" 2>&1

Write-Host "=== Restarting bridge ==="
ssh $opts $server "chmod +x /tmp/restart-bridge.sh && bash /tmp/restart-bridge.sh" 2>&1

Write-Host "=== Testing debug endpoint ==="
ssh $opts $server "curl -s --max-time 5 https://CoreXGaming.duckdns.org/admin/sham-cash/_debug" 2>&1

Write-Host "=== Done ==="
