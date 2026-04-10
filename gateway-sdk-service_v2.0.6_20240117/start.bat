@echo off
echo [Pre-Requirement] Makesure install JDK 8.0+ and set the JRE_HOME.

set JAVA_EXE=java
if not "%JAVA_HOME%"=="" set JAVA_EXE=%JAVA_HOME%\bin\java.exe
set JAVA_OPTS=%JAVA_OPTS% -server -Xmx2g -XX:MetaspaceSize=128m -Dfile.encoding=UTF-8
set SERVER_NAME=gateway-sdk-service.jar

echo [Step] start application.
echo "%JAVA_EXE% %JAVA_OPTS% -jar %SERVER_NAME%"
start "TdxGatewaySdkService" %JAVA_EXE% %JAVA_OPTS% -Dlogging.file=logs\stdout.%date:~0,4%%date:~5,2%%date:~8,2%.log -jar %SERVER_NAME%
if errorlevel 1 goto error

echo [INFO] Please wait a moment. When you see "[INFO] Started GatewaySdkApplication in xxx seconds" in console, you can access below api:
echo [INFO] http://localhost:8190/api/xxx

goto end
:error
echo Error Happen!!
:end
pause
