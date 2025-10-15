up:
	docker compose up -d --build

down:
	docker compose down

bash:
	docker compose exec app bash

init: up
	./bin/init.sh

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

test:
	docker compose exec app php artisan test
doctor:
	docker compose ps
	docker compose logs --tail=50 web || true
	docker compose logs --tail=50 app || true

reset:
	docker compose down -v
	docker compose up -d --build
	./bin/init.sh

