@echo off
REM ponytail: shim, logikanya tetap di start-ors.sh biar tidak ada dua versi.
"%ProgramFiles%\Git\bin\bash.exe" "%~dp0start-ors.sh" %*
pause
