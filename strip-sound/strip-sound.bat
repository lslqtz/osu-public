@echo off

rem strip-sound.bat
rem Removes the audio tracks of the given input files.
rem http://strager.net/blag/remove-sound-video

rem             DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
rem 
rem  Copyright (C) 2010 <strager.nds@gmail.com>
rem 
rem  Everyone is permitted to copy and distribute verbatim or modified 
rem  copies of this license document, and changing it is allowed as long 
rem  as the name is changed. 
rem 
rem             DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
rem    TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION 
rem 
rem   0. You just DO WHAT THE FUCK YOU WANT TO.

rem http://stackoverflow.com/questions/377407/detecting-how-a-batch-file-was-executed

setlocal enableextensions enabledelayedexpansion

set SCRIPT=%0
set DQUOTE="

echo %SCRIPT:~0,1% | findstr /l %DQUOTE% > NUL
if %ERRORLEVEL% EQU 0 set RUN_FROM_EXPLORER=1

rem Check if any arguments were passed
if "%~1" == "" (
	if defined RUN_FROM_EXPLORER (
		echo Drag-and-drop one or more files into %~nx0
	) else (
		echo You must specify at least one filename
	)

	call :end
	exit /B 1
)

rem http://stackoverflow.com/questions/3416457/spaces-in-batch-script-arguments

rem Parse each file

set "args=%*"
set "args=%args:,=:comma:%"
set "args=%args:;=:semicolon:%"

for %%g in (%args%) do (
	set "infile=%%~g"
	set "infile=!infile::comma:=,!"
	set "infile=!infile::semicolon:=;!"

	set "outfile=%%~dpng-out.avi"
	set "outfile=!outfile::comma:=,!"
	set "outfile=!outfile::semicolon:=;!"

	echo !infile!
	del /Q "!outfile!" 2> NUL

	%~dp0mencoder.exe -msglevel "all=3:statusline=0" -nosound -ovc copy -of avi -o "!outfile!" -- "!infile!"

	rem For some reason, mencoder doesn't set the error level.
	rem To detect if the encode worked or not, we check if the
	rem output file exists.  If a file is reencoded, the old
	rem output file may still exist, so we need to delete it.

	if not exist !outfile! (
		echo.
		echo Encoding !infile! failed; see above >&2
		call :end
	)
)

:end
if defined RUN_FROM_EXPLORER pause
