#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

cd "$ROOT_DIR"

if [[ ! -f ".env" ]]; then
  echo "Missing .env in $ROOT_DIR"
  echo "Create it from .env.vps.example first."
  exit 1
fi

echo "Deploying FaceApp from $ROOT_DIR"

if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "Pulling latest Git changes..."
  git pull --ff-only
fi

echo "Building and starting Docker services..."
docker compose -f docker-compose.yml -f docker-compose.vps.yml up -d --build

echo "FaceApp deployment complete."
