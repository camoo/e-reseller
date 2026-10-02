.PHONY: help build up down restart install update clear-cache test tinker rector rector-dry deptrac psalm shell restapi-up restapi-down restapi-restart restapi-logs up-all

DOCKER_COMPOSE ?= docker compose
EXEC_APP ?= $(DOCKER_COMPOSE) exec app
RESTAPI_COMPOSE_FILE ?= $(if $(wildcard ../restapi/compose.yaml),../restapi/compose.yaml,/Users/epiphane/Projects/restapi/compose.yaml)
RESTAPI_COMPOSE ?= $(DOCKER_COMPOSE) -f $(RESTAPI_COMPOSE_FILE)

help: ## Show help menu
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker image
	$(DOCKER_COMPOSE) build

up: ## Start Docker container in background
	$(DOCKER_COMPOSE) up -d

up-all: restapi-up up ## Start both REST API gateway and web-framework containers

down: ## Stop Docker container
	$(DOCKER_COMPOSE) down

restart: down up ## Restart Docker container

restapi-up: ## Start REST API gateway container in background
	$(RESTAPI_COMPOSE) up -d

restapi-down: ## Stop REST API gateway container
	$(RESTAPI_COMPOSE) down

restapi-restart: ## Restart REST API gateway container
	$(RESTAPI_COMPOSE) restart

restapi-logs: ## Tail REST API gateway logs
	$(RESTAPI_COMPOSE) logs -f restapi

install: ## Run composer install
	$(EXEC_APP) composer install

update: ## Run composer update
	$(EXEC_APP) composer update

clear-cache: ## Clear all application caches
	$(DOCKER_COMPOSE) run --rm app ./bin/camoo cleanup:all

test: ## Run PHPUnit tests
	$(EXEC_APP) vendor/bin/phpunit --colors=always

tinker: ## Open the development Tinker REPL
	docker compose run --rm app ./bin/camoo tinker

rector: ## Run Rector to upgrade/refactor code to PHP 8.4+
	$(EXEC_APP) vendor/bin/rector process

rector-dry: ## Run Rector in dry-run mode (check only)
	$(EXEC_APP) vendor/bin/rector process --dry-run

deptrac: ## Run Deptrac architecture check
	$(EXEC_APP) vendor/bin/deptrac

psalm: ## Run Psalm static analysis
	$(EXEC_APP) vendor/bin/psalm

shell: ## Open shell in app container
	$(EXEC_APP) sh
