@echo off
title SMSOnline Server
setlocal

:: Tentative de trouver le chemin de PHP
set PHP_PATH=php
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set PHP_PATH="C:\xampp\php\php.exe"
    ) else if exist "C:\wamp64\bin\php\php8.1.13\php.exe" (
        set PHP_PATH="C:\wamp64\bin\php\php8.1.13\php.exe"
    ) else (
        echo [ERREUR] PHP n'a pas ete trouve dans votre PATH ni dans C:\xampp\php.
        echo Veuillez installer PHP ou l'ajouter au PATH, ou modifier ce fichier .bat.
        pause
        exit /b
    )
)

echo Lancement de SMSOnline sur http://localhost:8000...
echo Serveur PHP utilise : %PHP_PATH%
echo.
echo [!] Assurez-vous que MySQL est actif dans XAMPP/WAMP.
echo.

:: Ouvrir le navigateur
start http://localhost:8000

:: Demarrer le serveur PHP interne dans le dossier public
cd /d "%~dp0public"
%PHP_PATH% -S localhost:8000

pause
