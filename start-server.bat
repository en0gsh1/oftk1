@echo off
title OFTK - Server
echo Duke nisur serverin PHP ne http://localhost:8000
echo.
echo Hapni ne shfletues:
echo   http://localhost:8000/health.php  - kontroll
echo   http://localhost:8000/install.php - krijo llogarine e pare
echo   http://localhost:8000/login.php  - hyrje
echo   http://localhost:8000/index.html - ballina
echo.
echo Shtypni Ctrl+C per te ndalur serverin.
echo.
php -S localhost:8000
pause
