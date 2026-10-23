@echo off
rem Prueba de Bramajo sin Docker: usa el servidor que trae PHP y una base SQLite con los datos de prueba.
cd /d "%~dp0"
set PHP=php
if exist "C:\xampp\php\php.exe" set PHP=C:\xampp\php\php.exe
if not exist datos.db copy bramajo.db datos.db >nul
set DB_DSN=sqlite:%~dp0datos.db
set CLAVES_DIR=%~dp0claves
echo.
echo  Bramajo esta funcionando en http://localhost:8000
echo  Usuario: admin.prueba@bramajo.test   Contrasena: Bramajo2026
echo  Para apagarlo cerra esta ventana.
echo.
start "" http://localhost:8000
"%PHP%" -S localhost:8000 -t ..\public router.php
pause
