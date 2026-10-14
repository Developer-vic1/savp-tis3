@echo off
setlocal
title Iniciador SAVP
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\iniciar-savp.ps1" %*
set "SAVP_EXIT=%errorlevel%"
if not "%SAVP_EXIT%"=="0" (
    echo.
    echo SAVP no pudo iniciar. Revisa el mensaje anterior.
    pause
)
endlocal & exit /b %SAVP_EXIT%
