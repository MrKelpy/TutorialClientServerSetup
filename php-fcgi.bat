@echo off
set PATH=nginx/php;%PATH%
.\nginx\php\php-cgi.exe -b 127.0.0.1:9123 -c .\nginx\php\php.ini