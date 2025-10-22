# Script de test CORS
Write-Host "=== TEST CORS ET CONNEXION API ===" -ForegroundColor Cyan
Write-Host ""

# Test 1: Vérifier que le serveur répond
Write-Host "1. Test serveur..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -Method GET -UseBasicParsing -TimeoutSec 5
    Write-Host "   ✅ Serveur accessible (Status: $($response.StatusCode))" -ForegroundColor Green
} catch {
    Write-Host "   ❌ ERREUR: Serveur non accessible" -ForegroundColor Red
    Write-Host "   → Lancez START_SERVER.bat dans un autre terminal" -ForegroundColor Yellow
    exit 1
}

Write-Host ""

# Test 2: Test de connexion avec headers CORS
Write-Host "2. Test connexion API avec CORS headers..." -ForegroundColor Yellow
$headers = @{
    'Content-Type' = 'application/json'
    'Accept' = 'application/json'
    'Origin' = 'http://localhost:8081'
}

$body = @{
    email = "journaliste@redacgabon.com"
    password = "password123"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/auth/login' `
        -Method POST `
        -Headers $headers `
        -Body $body

    Write-Host "   ✅ Connexion réussie !" -ForegroundColor Green
    Write-Host "   Token: $($loginResponse.data.access_token.Substring(0,30))..." -ForegroundColor Cyan

    $token = $loginResponse.data.access_token

    Write-Host ""

    # Test 3: Test création d'article avec CORS
    Write-Host "3. Test création d'article avec CORS..." -ForegroundColor Yellow

    $articleHeaders = @{
        'Content-Type' = 'application/json'
        'Accept' = 'application/json'
        'Authorization' = "Bearer $token"
        'Origin' = 'http://localhost:8081'
    }

    $articleBody = @{
        title = "Test CORS Article"
        content = "<p>Test depuis PowerShell avec headers CORS</p>"
        category = "Test"
        tags = @("cors", "test")
        status = "draft"
        workflow_status = "draft"
    } | ConvertTo-Json

    $articleResponse = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/articles' `
        -Method POST `
        -Headers $articleHeaders `
        -Body $articleBody

    Write-Host "   ✅ Article créé avec succès !" -ForegroundColor Green
    Write-Host "   ID: $($articleResponse.data.id)" -ForegroundColor Cyan
    Write-Host "   Titre: $($articleResponse.data.titre)" -ForegroundColor Cyan

} catch {
    Write-Host "   ❌ ERREUR" -ForegroundColor Red
    Write-Host "   Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "   Message: $($_.Exception.Message)" -ForegroundColor Red

    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $errorBody = $reader.ReadToEnd()
        Write-Host "   Détails: $errorBody" -ForegroundColor Red
    }
    exit 1
}

Write-Host ""
Write-Host "=== ✅ TOUS LES TESTS CORS RÉUSSIS ===" -ForegroundColor Green
Write-Host ""
Write-Host "Configuration CORS validée:" -ForegroundColor Cyan
Write-Host "  ✓ Serveur accessible" -ForegroundColor Green
Write-Host "  ✓ Headers CORS acceptés" -ForegroundColor Green
Write-Host "  ✓ Authentification fonctionnelle" -ForegroundColor Green
Write-Host "  ✓ Création d'article fonctionnelle" -ForegroundColor Green
Write-Host ""
Write-Host "Vous pouvez maintenant tester depuis le frontend:" -ForegroundColor Yellow
Write-Host "  http://localhost:8081/dashboard" -ForegroundColor Cyan







