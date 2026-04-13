#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

cd "$ROOT_DIR"

if [[ ! -f ".env" ]]; then
  echo "Missing .env in $ROOT_DIR"
  echo "Create it from .env.vps.example first."
  exit 1
fi

read_env_value() {
  local key="$1"
  sed -n "s/^${key}=//p" .env | tail -n 1 | tr -d '\r'
}

require_value() {
  local key="$1"
  local value
  value="$(read_env_value "$key")"

  if [[ -z "$value" ]]; then
    echo "Missing required $key in $ROOT_DIR/.env"
    exit 1
  fi
}

require_non_example_value() {
  local key="$1"
  local value
  value="$(read_env_value "$key")"

  if [[ -z "$value" || "$value" == *"example.com"* ]]; then
    echo "$key still uses a placeholder value in $ROOT_DIR/.env"
    exit 1
  fi
}

require_value "LARAVEL_APP_KEY"
require_non_example_value "FACEAPP_DOMAIN"
require_non_example_value "FACEAPP_API_DOMAIN"
require_non_example_value "FACEAPP_API_ORIGIN"

if [[ "$(read_env_value "LARAVEL_APP_KEY")" == *"replace-with-your-real-app-key"* ]]; then
  echo "LARAVEL_APP_KEY is still using the placeholder value in $ROOT_DIR/.env"
  echo "Generate one with:"
  echo "php -r \"echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;\""
  exit 1
fi

echo "Deploying FaceApp from $ROOT_DIR"

if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "Pulling latest Git changes..."
  git pull --ff-only
fi

echo "Building and starting Docker services..."
docker compose -f docker-compose.yml -f docker-compose.vps.yml up -d --build
echo "Reloading FaceApp Caddy with latest mounted config..."
docker compose -f docker-compose.yml -f docker-compose.vps.yml exec -T caddy caddy validate --config /etc/caddy/Caddyfile
docker compose -f docker-compose.yml -f docker-compose.vps.yml exec -T caddy caddy reload --config /etc/caddy/Caddyfile || docker compose -f docker-compose.yml -f docker-compose.vps.yml restart caddy

echo "FaceApp deployment complete."
