FROM eclipse-temurin:8-jre

WORKDIR /app

COPY gateway-sdk-service.jar /app/gateway-sdk-service.jar

RUN mkdir -p /app/logs

EXPOSE 8190 10010 10011

CMD ["java", "-server", "-Xmx1g", "-XX:MetaspaceSize=128m", "-Dfile.encoding=UTF-8", "-Dlogging.file=/app/logs/stdout.log", "-jar", "/app/gateway-sdk-service.jar"]
