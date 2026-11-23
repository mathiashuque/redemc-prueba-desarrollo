.PHONY: help setup up down logs check test test-integration lint-front fix

DC  := docker compose
# Corre comandos en el contenedor de la app como el usuario local, sin migrar ni levantar la base.
RUN := $(DC) run --rm --no-deps --user $$(id -u):$$(id -g) -e RUN_MIGRATIONS=0 -e COMPOSER_HOME=/tmp/composer app
EXEC := $(DC) exec -T --user $$(id -u):$$(id -g) -e APP_URL=http://localhost:8080 app

help: ## Lista los comandos disponibles
	@grep -E '^[a-z-]+:.*## ' $(MAKEFILE_LIST) | awk -F ':.*## ' '{printf "  %-18s %s\n", $$1, $$2}'

setup: ## Instala dependencias y activa los hooks de git
	git config core.hooksPath .githooks
	$(RUN) composer install
	npm ci

up: ## Levanta app + MySQL en http://localhost:8080
	$(DC) up -d --build --wait
	@echo "App disponible en http://localhost:$${APP_PORT:-8080}"

down: ## Detiene los contenedores
	$(DC) down

logs: ## Muestra los logs de la app
	$(DC) logs -f app

check: ## Lint PHP, PHPCS, PHPStan, tests unitarios, ESLint y Stylelint
	$(RUN) composer check
	npm run lint

test: ## Tests unitarios
	$(RUN) composer test

test-integration: up ## Tests de integración contra la app levantada
	$(EXEC) composer test:integration

lint-front: ## ESLint y Stylelint
	npm run lint

fix: ## Corrige estilo PHP automáticamente (phpcbf)
	$(RUN) composer cs:fix
