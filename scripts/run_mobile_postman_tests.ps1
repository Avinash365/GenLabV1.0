param(
  [string]$BaseUrl = "http://127.0.0.1:8000",
  [string]$UserCode = "MKT002",
  [string]$Password = "12345678",
  [string]$UserId = "36",
  [string]$UserContact = "user:36"
)

Write-Host "Running trimmed Postman collection via newman..."

$newman = Get-Command newman -ErrorAction SilentlyContinue
if (-not $newman) {
  Write-Host "newman not found. Install with: npm install -g newman" -ForegroundColor Yellow
  exit 2
}

$collection = "postman/Mobile_Chat_API.trimmed.postman_collection.json"
if (-not (Test-Path $collection)) { Write-Host "Collection not found: $collection" -ForegroundColor Red; exit 3 }

mkdir -Force results | Out-Null

$envVars = @( 
  "base_url=$BaseUrl",
  "user_code=$UserCode",
  "password=$Password",
  "user_id=$UserId",
  "user_contact=$UserContact"
)

$envArgs = $envVars | ForEach-Object { "--env-var `"$_`"" }

$cmd = "newman run `"$collection`" $($envArgs -join ' ') --reporters cli,junit --reporter-junit-export results/mobile-chat-tests.xml"
Write-Host "Executing: $cmd"

Invoke-Expression $cmd

if ($LASTEXITCODE -eq 0) { Write-Host "Postman tests completed successfully." -ForegroundColor Green } else { Write-Host "Postman tests failed. See output above and results/mobile-chat-tests.xml" -ForegroundColor Red }
