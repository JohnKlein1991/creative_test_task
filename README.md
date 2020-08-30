Чтобы развернуть приложение, необходимо в корне проекта выполнить

`docker-compose up`

После этого, оно будет доступно по адресу localhost:80

Запустите миграции:

` docker-compose exec app /var/www/app/vendor/bin/doctrine-migrations migrations:migrate`

Чтобы импортировать данные о трейлерах выполните:

`bin/console fetch:trailers`