.PHONY: help build up down restart install update test rector rector-dry deptrac psalm shell

DOCKER_COMPOSE ?= docker-compose
EXEC_APP ?= $(DOCKER_COMPOSE) exec app

help: ## Show help menu
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker image
	$(DOCKER_COMPOSE) build

up: ## Start Docker container in background
	$(DOCKER_COMPOSE) up -d

down: ## Stop Docker container
	$(DOCKER_COMPOSE) down

restart: down up ## Restart Docker container

install: ## Run composer install
	$(EXEC_APP) composer install

update: ## Run composer update
	$(EXEC_APP) composer update

test: ## Run PHPUnit tests
	$(EXEC_APP) vendor/bin/phpunit --colors=always

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
