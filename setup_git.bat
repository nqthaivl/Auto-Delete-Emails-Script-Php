@echo off
echo Checking for Git...
git --version
if %errorlevel% neq 0 (
    echo [ERROR] Git command not found.
    echo Please ensure Git is installed and you have restarted your terminal/computer if needed.
    pause
    exit /b
)

echo.
echo === Initializing Git Repository ===
git init

echo.
echo === Adding files ===
git add .

echo.
echo === Committing ===
git commit -m "Initial commit"

echo.
echo === Success! ===
pause
