FROM student-login-app:latest

WORKDIR /var/www/html
COPY src/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html || true

EXPOSE 80
