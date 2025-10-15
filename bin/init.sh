#!/usr/bin/env bash
set -euo pipefail

run_in_app () { docker compose exec -T app bash -lc "$*"; }

# 1) .env 준비
[ -f .env ] || cp .env.example .env

# 2) Laravel 설치 or 의존성 설치
if [ ! -f artisan ]; then
  echo ">> Installing fresh Laravel into temp and copying..."
  run_in_app "rm -rf /tmp/laravel && composer create-project laravel/laravel /tmp/laravel"
  # dotfiles 포함 복사
  run_in_app "shopt -s dotglob; cp -r /tmp/laravel/* /var/www/; rm -rf /tmp/laravel"
else
  echo ">> composer install..."
  run_in_app "composer install"
fi

# 3) 기본 세팅
run_in_app "php artisan key:generate"
run_in_app "php artisan storage:link || true"
# 마이그레이션/시더는 스키마 붙인 후 유효
run_in_app "php artisan migrate --force || true"
run_in_app "php artisan db:seed --force || true"

echo ">> Init done. Open http://localhost:8080"
