# Script de test de création d'article
Write-Host "=== TEST CRÉATION ARTICLE ===" -ForegroundColor Cyan

# 1. Se connecter
Write-Host "`n1. Connexion..." -ForegroundColor Yellow
$loginBody = @{
    email = "journaliste@redacgabon.com"
    password = "password123"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/auth/login' `
        -Method POST `
        -Headers @{'Content-Type'='application/json'; 'Accept'='application/json'} `
        -Body $loginBody

    $token = $loginResponse.data.access_token
    Write-Host "✅ Connecté ! Token: $($token.Substring(0,20))..." -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur de connexion: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host $_.Exception.Response.StatusCode
    exit 1
}

# 2. Créer un article
Write-Host "`n2. Création d'article..." -ForegroundColor Yellow
$articleBody = @{
    title = "Mon Premier Article de Test"
    content = "<h1>Titre</h1><p>Ceci est un contenu de test créé le $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')</p>"
    category = "Actualités"
    tags = @("test", "api")
    excerpt = "Ceci est un extrait de test"
    status = "draft"
    workflow_status = "draft"
    seo_title = "SEO Titre Test"
    seo_description = "Description SEO de test"
    seo_keywords = @("test", "article")
} | ConvertTo-Json

try {
    $articleResponse = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/articles' `
        -Method POST `
        -Headers @{
            'Authorization' = "Bearer $token"
            'Content-Type' = 'application/json'
            'Accept' = 'application/json'
        } `
        -Body $articleBody

    Write-Host "✅ Article créé avec succès !" -ForegroundColor Green
    Write-Host "   ID: $($articleResponse.data.id)" -ForegroundColor Cyan
    Write-Host "   Titre: $($articleResponse.data.titre)" -ForegroundColor Cyan
    Write-Host "   Statut: $($articleResponse.data.statut)" -ForegroundColor Cyan
    Write-Host "   Slug: $($articleResponse.data.slug)" -ForegroundColor Cyan

    # Afficher la réponse complète
    Write-Host "`n📄 Réponse complète:" -ForegroundColor Magenta
    $articleResponse | ConvertTo-Json -Depth 5

} catch {
    Write-Host "❌ Erreur de création d'article:" -ForegroundColor Red
    Write-Host "   Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "   Message: $($_.Exception.Message)" -ForegroundColor Red

    # Lire la réponse d'erreur
    $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
    $errorBody = $reader.ReadToEnd()
    Write-Host "   Détails: $errorBody" -ForegroundColor Red
    exit 1
}

Write-Host "`n=== TEST TERMINÉ AVEC SUCCÈS ===" -ForegroundColor Green










