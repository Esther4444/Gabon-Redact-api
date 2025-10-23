@echo off
echo ========================================
echo  DEMARRAGE SERVEUR LARAVEL
echo ========================================
echo.
echo Nettoyage des caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo.
echo Demarrage du serveur sur http://127.0.0.1:8000
echo Appuyez sur Ctrl+C pour arreter
echo.
php artisan serve --host=127.0.0.1 --port=8000












