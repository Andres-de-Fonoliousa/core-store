$password = 'Qrt157154@'
$pwFile = "$env:TEMP\sshpass.ps1"
@"
Write-Host -NoNewline '$password'
"@ | Set-Content -Path $pwFile -Force

$env:SSH_ASKPASS = "powershell.exe -NoProfile -NonInteractive -File `"$pwFile`""
$env:SSH_ASKPASS_REQUIRE = "force"
$env:DISPLAY = "dummy"

Write-Host "=== Deploy fix v2 ==="

git bundle create deploy.bundle HEAD master
scp -o StrictHostKeyChecking=accept-new deploy.bundle root@130.94.47.170:/tmp/deploy.bundle

$cmds = @'
set -e
cd /var/www/store-app
cp .env /tmp/deploy.env 2>/dev/null || true
git fetch /tmp/deploy.bundle master
git reset --hard FETCH_HEAD
cp /tmp/deploy.env .env 2>/dev/null || true

echo "Bridge code updated"
'@

$cmdFile = "$env:TEMP\deploy_fix_v2.sh"
$cmds | Set-Content -Path $cmdFile -Force

scp -o StrictHostKeyChecking=accept-new "$cmdFile" root@130.94.47.170:/tmp/deploy_fix_v2.sh
ssh -o StrictHostKeyChecking=accept-new root@130.94.47.170 "bash /tmp/deploy_fix_v2.sh"

Write-Host "=== Done ==="
