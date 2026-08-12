@echo off
REM ponytail: shim, logikanya tetap di stop-ors.sh biar tidak ada dua versi.
"%ProgramFiles%\Git\bin\bash.exe" "%~dp0stop-ors.sh" %*
pause
