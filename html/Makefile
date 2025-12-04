.PHONY: artisan

artisan:
	docker compose -f docker-compose.yml exec php php artisan $(filter-out $@,$(MAKECMDGOALS))

bash:
	docker compose -f docker-compose.yml exec bash

local:
	docker compose -f docker-compose.local.yml up -d --build

prod:
	docker compose up -d --build

error:
	tail -f -n 2000000 storage/logs/laravel.log | grep "production.ERROR"

grep:
	cat  storage/logs/laravel.log | grep "$(filter-out $@,$(MAKECMDGOALS))"

%:
	@:
