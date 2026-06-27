$pwFile = "$env:TEMP\sshpass.ps1"
@"
Write-Host -NoNewline 'Qrt157154@'
"@ | Set-Content -Path $pwFile -Force

$env:SSH_ASKPASS = "powershell.exe -NoProfile -NonInteractive -File `"$pwFile`""
$env:SSH_ASKPASS_REQUIRE = "force"
$env:DISPLAY = "dummy"

ssh -o StrictHostKeyChecking=accept-new -o ConnectTimeout=10 root@130.94.47.170 "curl -s -L -o /dev/null -w 'Final: HTTP %{http_code}\n' 'https://CoreXGaming.duckdns.org/admin/sham-cash/' 2>&1 && echo '---' && curl -s -o /dev/null -w 'HTTP %{http_code}' 'https://CoreXGaming.duckdns.org/admin/sham-cash/api/health' 2>&1 && echo '' && echo '---health---' && curl -s -m 5 http://127.0.0.1:3001/health" 2>&1