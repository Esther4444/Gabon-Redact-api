@echo off
echo ========================================
echo DEMARRAGE SERVEUR LARAVEL
echo ========================================
echo.
echo Configuration:
echo - Host: 127.0.0.1
echo - Port: 8000
echo - CORS: Active pour localhost:8081
echo.
echo Effacement des caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo.
echo Demarrage du serveur...
echo.
echo ========================================
echo SERVEUR EN COURS D'EXECUTION
echo ========================================
echo.
echo Acces:
echo   - API: http://127.0.0.1:8000/api/v1
echo   - Docs: http://127.0.0.1:8000/api/documentation
echo.
echo Appuyez sur Ctrl+C pour arreter le serveur
echo ========================================
echo.
php artisan serve --host=127.0.0.1 --port=8000









