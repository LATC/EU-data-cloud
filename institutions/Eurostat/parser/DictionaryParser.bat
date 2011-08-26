@echo off
set EuroStat=%~p0
set CP="%EuroStat%build"
call :findjars "%EuroStat%lib"
java -cp %CP% -Xmx256M com.ontologycentral.estatwrap.DictionaryParser %*
exit /B

:findjars
for %%j in (%1\*.jar) do call :addjar "%%j"
for /D %%d in (%1\*) do call :findjars "%%d"
exit /B

:addjar
set CP=%CP%;%1