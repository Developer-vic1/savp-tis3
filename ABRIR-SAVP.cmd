@echo off
setlocal
title Iniciador SAVP
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\iniciar-savp.ps1" %*
if errorlevel 1 (
    echo.
    echo SAVP no pudo iniciar. Revisa el mensaje anterior.
    pause
)
endlocal
