@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

echo ========================================
echo    INICIADOR RÁPIDO DE XAMPP
echo ========================================
echo.

REM Solicitar permisos de administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Solicitando permisos de administrador...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

REM Definir rutas
set "XAMPP_PATH=C:\xampp"
set "HTDOCS_PATH=%XAMPP_PATH%\htdocs"
set "ERROR_URL=file:///%HTDOCS_PATH%\error.html"
set "SUCCESS_URL=http://localhost"
set "TIMEOUT_SECONDS=30"

REM Verificar si XAMPP está instalado
if not exist "%XAMPP_PATH%\xampp-control.exe" (
    echo ERROR: XAMPP no está instalado en %XAMPP_PATH%
    echo Redirigiendo a página de error...
    start msedge "%ERROR_URL%" --start-fullscreen
    timeout /t 3 >nul
    exit /b 1
)

echo Iniciando servicios de XAMPP...
echo Tiempo límite: %TIMEOUT_SECONDS% segundos
echo.

REM Iniciar XAMPP Control Panel en segundo plano
start "" "%XAMPP_PATH%\xampp-control.exe"
timeout /t 2 >nul

REM Intentar iniciar Apache
echo [1/2] Iniciando Apache...
"%XAMPP_PATH%\apache\bin\httpd.exe" -k start >nul 2>&1

REM Intentar iniciar MySQL
echo [2/2] Iniciando MySQL...
net start mysql >nul 2>&1

REM Bucle de verificación por 30 segundos
set /a "END_TIME=%TIMEOUT_SECONDS%"
set /a "CURRENT_TIME=0"
set "APACHE_RUNNING=false"
set "MYSQL_RUNNING=false"

echo.
echo Verificando servicios...

:CHECK_SERVICES_LOOP
if %CURRENT_TIME% geq %END_TIME% goto TIMEOUT_REACHED

REM Verificar Apache
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
if %ERRORLEVEL% equ 0 (
    set "APACHE_RUNNING=true"
) else (
    set "APACHE_RUNNING=false"
    REM Intentar reiniciar Apache
    "%XAMPP_PATH%\apache\bin\httpd.exe" -k start >nul 2>&1
)

REM Verificar MySQL
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if %ERRORLEVEL% equ 0 (
    set "MYSQL_RUNNING=true"
) else (
    set "MYSQL_RUNNING=false"
    REM Intentar reiniciar MySQL
    net start mysql >nul 2>&1
)

REM Mostrar estado actual
echo [%CURRENT_TIME%s/%TIMEOUT_SECONDS%s] Apache: !APACHE_RUNNING! ^| MySQL: !MYSQL_RUNNING!

REM Verificar si ambos servicios están funcionando
if "!APACHE_RUNNING!"=="true" if "!MYSQL_RUNNING!"=="true" (
    echo.
    echo ✓ ¡Servicios iniciados correctamente!
    echo ✓ Abriendo localhost en Microsoft Edge...
    echo.
    
    REM Abrir localhost en Microsoft Edge en pantalla completa
    start msedge "%SUCCESS_URL%" --start-fullscreen
    
    echo ✓ ¡Listo! XAMPP está funcionando.
    timeout /t 3 >nul
    exit /b 0
)

REM Esperar 1 segundo antes de la siguiente verificación
timeout /t 1 >nul
set /a "CURRENT_TIME+=1"
goto CHECK_SERVICES_LOOP

:TIMEOUT_REACHED
echo.
echo ✗ TIMEOUT: No se pudieron iniciar los servicios en %TIMEOUT_SECONDS% segundos
echo ✗ Estado final:
echo   - Apache: !APACHE_RUNNING!
echo   - MySQL: !MYSQL_RUNNING!
echo.
echo Redirigiendo a página de error...

REM Abrir error.html en Microsoft Edge en pantalla completa
start msedge "%ERROR_URL%" --start-fullscreen

timeout /t 3 >nul
exit /b 1