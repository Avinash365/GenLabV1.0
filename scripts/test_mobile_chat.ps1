# PowerShell smoke test for GenLab mobile chat API
# Usage: Run from repo root. This script will try to start php artisan serve on port 8000 if not already reachable.

param()

$base = 'http://127.0.0.1:8000'
$adminCred = @{ email = 'superadmin1@example.com'; password = 'password123' }
$userCred = @{ user_code = 'MKT002'; password = '12345678' }
$serverStarted = $false
$phpProcess = $null

function Test-Http($url) {
    try {
        $r = Invoke-WebRequest -Uri $url -Method Head -UseBasicParsing -TimeoutSec 3 -ErrorAction Stop
        return $true
    } catch {
        return $false
    }
}

function Normalize-Response($r) {
    if ($null -eq $r) { return $null }
    if ($r -is [string]) {
        # remove BOM-like junk before the first JSON object/array
        $s = $r -replace "`uFEFF", ''
        $firstBrace = $s.IndexOf('{')
        $firstBracket = $s.IndexOf('[')
        $idx = -1
        if ($firstBrace -ge 0 -and ($firstBrace -le $firstBracket -or $firstBracket -lt 0)) { $idx = $firstBrace }
        elseif ($firstBracket -ge 0) { $idx = $firstBracket }
        if ($idx -gt 0) { $s = $s.Substring($idx) }
        try { return $s | ConvertFrom-Json } catch { return $s }
    }
    return $r
}

Write-Host "Checking if server responds at $base..."
if (-not (Test-Http $base)) {
    Write-Host "Server not responding. Starting php artisan serve on port 8000..."
    $phpProcess = Start-Process -FilePath "php" -ArgumentList "artisan serve --host=127.0.0.1 --port=8000" -WindowStyle Hidden -PassThru
    $serverStarted = $true
    # wait for server to boot
    $tries = 0
    while ($tries -lt 30 -and -not (Test-Http $base)) {
        Start-Sleep -Seconds 1
        $tries++
    }
    if (-not (Test-Http $base)) {
        Write-Error "Server did not start after waiting. Aborting tests."
        if ($phpProcess) { Stop-Process -Id $phpProcess.Id -ErrorAction SilentlyContinue }
        exit 1
    }
}

Write-Host "Server reachable - running tests..."
$results = @{}

# Admin login
try {
    Write-Host "Logging in as admin..."
    $adminRes = Invoke-RestMethod -Uri ($base + '/api/admin/login') -Method Post -Body ($adminCred | ConvertTo-Json) -ContentType 'application/json' -ErrorAction Stop
    $adminRes = Normalize-Response $adminRes
    $results.adminLogin = $adminRes
    $adminToken = $adminRes.access_token
    if ($adminToken) { Write-Host "Admin token obtained (length: $($adminToken.Length))" } else { Write-Warning 'Admin login returned no access_token' }
} catch {
    Write-Warning "Admin login failed: $($_.Exception.Message)"
    $results.adminLogin = $null
}

# User login
try {
    Write-Host "Logging in as user..."
    $userRes = Invoke-RestMethod -Uri ($base + '/api/user/login') -Method Post -Body ($userCred | ConvertTo-Json) -ContentType 'application/json' -ErrorAction Stop
    $userRes = Normalize-Response $userRes
    $results.userLogin = $userRes
    $userToken = $userRes.access_token
    if ($userToken) { Write-Host "User token obtained (length: $($userToken.Length))" } else { Write-Warning 'User login returned no access_token' }
} catch {
    Write-Warning "User login failed: $($_.Exception.Message)"
    $results.userLogin = $null
}

# Call contacts using user token
if ($userToken) {
    try {
        Write-Host "Fetching user contacts..."
        $c = Invoke-RestMethod -Uri ($base + '/api/chat/contacts') -Headers @{ Authorization = "Bearer $userToken" } -ErrorAction Stop
        $results.userContacts = $c
        Write-Host "User contacts returned: $($c.Count) items"
    } catch {
        Write-Warning "User contacts failed: $($_.Exception.Message)"
        $results.userContacts = $null
    }

    # pick a target from contacts
    $target = $null
    try {
        if ($results.userContacts -and $results.userContacts.Count -gt 0) {
            $first = $results.userContacts | Select-Object -First 1
            $target = $first.id
            Write-Host "Using target: $target"
        } else {
            Write-Warning 'No contacts found; fallback to user:1'
            $target = 'user:1'
        }
    } catch {
        $target = 'user:1'
    }

    # Fetch messages
    try {
        Write-Host "Fetching messages with $target (mark_read=1)..."
        $m = Invoke-RestMethod -Uri ($base + "/api/chat/messages/$target?mark_read=1") -Headers @{ Authorization = "Bearer $userToken" } -ErrorAction Stop
        $results.userMessages = $m
        Write-Host "Messages returned: $($m.Count)"
    } catch {
        Write-Warning "Fetching messages failed: $($_.Exception.Message)"
        $results.userMessages = $null
    }

    # Send a test message
    try {
        Write-Host "Sending test message to $target..."
        $payload = @{ receiver_id = $target; content = "Hello from automated PS test at $(Get-Date -Format o)" }
        $sent = Invoke-RestMethod -Uri ($base + '/api/chat/messages') -Method Post -Body ($payload | ConvertTo-Json) -Headers @{ Authorization = "Bearer $userToken" } -ContentType 'application/json' -ErrorAction Stop
        $results.userSent = $sent
        Write-Host "Message sent. id: $($sent.id)"
    } catch {
        Write-Warning "Send failed: $($_.Exception.Message)"
        $results.userSent = $null
    }
}

# Admin calls
if ($adminToken) {
    try {
        Write-Host "Fetching admin contacts..."
        $c2 = Invoke-RestMethod -Uri ($base + '/api/admin/chat/contacts') -Headers @{ Authorization = "Bearer $adminToken" } -ErrorAction Stop
        $results.adminContacts = $c2
        Write-Host "Admin contacts returned: $($c2.Count)"
    } catch {
        Write-Warning "Admin contacts failed: $($_.Exception.Message)"
        $results.adminContacts = $null
    }

    # fetch messages with user if we have user profile
    try {
        if ($results.userLogin -and $results.userLogin.user -and $results.userLogin.user.id) {
            $t = 'user:' + $results.userLogin.user.id
        } elseif ($results.userContacts -and $results.userContacts.Count -gt 0) {
            $t = $results.userContacts[0].id
        } else { $t = 'user:1' }
        Write-Host "Admin fetching messages with $t..."
        $m2 = Invoke-RestMethod -Uri ($base + "/api/admin/chat/messages/$t?mark_read=1") -Headers @{ Authorization = "Bearer $adminToken" } -ErrorAction Stop
        $results.adminMessages = $m2
        Write-Host "Admin messages: $($m2.Count)"
    } catch {
        Write-Warning "Admin fetch messages failed: $($_.Exception.Message)"
        $results.adminMessages = $null
    }
}

# Print summary
Write-Host "\n=== Test Results Summary ==="
$results | ConvertTo-Json -Depth 6 | Write-Host

# Cleanup: stop server if we started it
if ($serverStarted -and $phpProcess) {
    Write-Host "Stopping php artisan serve (pid $($phpProcess.Id))..."
    try { Stop-Process -Id $phpProcess.Id -Force -ErrorAction SilentlyContinue } catch {}
}

Write-Host "Done."
