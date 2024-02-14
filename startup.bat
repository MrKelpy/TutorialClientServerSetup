@echo off
setlocal

taskkill /IM nginx.exe /f
taskkill /IM php-fcgi.bat /f

:: Check if the php cgi exists
if not exist .\nginx\php\php-cgi.exe (
   echo Could not find 'php-cgi.exe'. Please verify the file integrity and make sure that everything is in order.
   exit /b
)

:: Startup the nginx resources
start /B /D ".\nginx" .\nginx.exe
start /B "" php-fcgi.bat

start "" "http://localhost:12345/index.php"
endlocal
