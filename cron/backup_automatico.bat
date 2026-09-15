@echo off
REM ============================================================
REM Ejecuta el respaldo automático diario de BD_Dengue_SIGuppy.
REM Este .bat es lo que se programa en el Programador de tareas
REM de Windows (Task Scheduler), NO el .php directamente.
REM
REM Antes de programarlo:
REM   1) Ajusta la ruta de php.exe abajo si es distinta en tu equipo
REM      (en Laragon suele ser algo como
REM      C:\laragon\bin\php\php-8.x\php.exe).
REM   2) Ajusta la ruta de backup_automatico.php si moviste la carpeta.
REM ============================================================

"C:\laragon\bin\php\php-8.2.0-Win32-vs16-x64\php.exe" "C:\laragon\www\SIGuppy\SIGuppy\cron\backup_automatico.php" >> "C:\laragon\www\SIGuppy\SIGuppy\cron\backup_automatico.log" 2>&1
