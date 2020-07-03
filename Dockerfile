FROM forceedge01/php56cli-composer:latest

RUN apt-get update && apt-get install -y git-all

WORKDIR '/app'
COPY composer.json .
COPY composer.lock .
RUN composer install --prefer-source
COPY . .

CMD ["composer", "run-script", "tests"]