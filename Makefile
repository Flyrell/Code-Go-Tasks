composer:
	docker-compose run --rm --user=www-data php composer install

migrate:
	docker-compose run --rm --user=www-data php bin/console doctrine:migrations:migrate

elastic-setup-mapping:
	docker-compose run --rm --user=www-data php bin/console app:elastic:index --all

fixtures:
	docker-compose run --rm --user=www-data php bin/console doctrine:fixtures:load -n

export-employees-to-elastic:
	docker-compose run --rm --user=www-data php bin/console app:elastic:export-employees

php-cs-fixer:
	docker-compose run --rm --user=www-data php vendor/bin/php-cs-fixer fix src

eslint:
	docker-compose run --rm react yarn --cwd /app/core lint

eslint-fix:
	docker-compose run --rm react yarn --cwd /app/core fix

test:
	docker-compose run --rm -e SYMFONY_DEPRECATIONS_HELPER=disabled --user=www-data php php bin/phpunit

react-install:
	docker-compose run --rm react yarn install

react-dev:
	docker-compose run --rm react yarn --cwd /app/core dev || true

react-watch:
	docker-compose run --rm react yarn --cwd /app/core watch || true

react-build:
	docker-compose run --rm react yarn --cwd /app/core build || true

cache-clear:
	docker-compose run --rm --user=www-data php bin/console cache:clear

recalculate-all-skills:
	docker-compose run --rm --user=www-data php bin/console app:skills:recalculate

restart-consumer:
	docker-compose restart php.consumer.skill_stats_recalculation
