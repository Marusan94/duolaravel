@echo off
set "PATH=C:\Users\USUARIO\tools\php;C:\Users\USUARIO\tools;%PATH%"
cd /d "C:\Users\USUARIO\Desktop\laravel-prep"
echo Servidor Laravel: http://localhost:8000
php artisan serve

