@echo off
setlocal

set "PROJECT_DIR=%cd%"
for %%I in ("%PROJECT_DIR%") do set "PROJECT_NAME=%%~nxI"
for %%I in ("%PROJECT_DIR%\..") do set "PARENT_DIR=%%~fI"

set "ZIP_PATH=%PARENT_DIR%\%PROJECT_NAME%-for-gpt.zip"

echo Membuat ZIP: %ZIP_PATH%
echo.

if exist "%ZIP_PATH%" del "%ZIP_PATH%"

tar ^
--exclude=.env ^
--exclude=.git ^
--exclude=node_modules ^
--exclude=vendor ^
--exclude=storage/logs ^
--exclude=storage/framework/cache ^
--exclude=storage/framework/sessions ^
--exclude=storage/framework/views ^
--exclude=bootstrap/cache ^
--exclude=.idea ^
--exclude=.vscode ^
--exclude=*.log ^
--exclude=*.zip ^
-a -cf "%ZIP_PATH%" *

if exist "%ZIP_PATH%" (
    echo.
    echo Selesai.
    echo File ZIP tersimpan di:
    echo %ZIP_PATH%
) else (
    echo.
    echo Gagal membuat ZIP.
)

pause