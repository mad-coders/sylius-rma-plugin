.PHONY: help \
        install update setup init app \
        docker-up docker-up-all docker-down docker-logs \
        backend backend-test frontend cache-clean db-reset fixtures fixtures-test serve serve-test \
        test phpunit behat behat-js \
        static rector rector-fix phpstan ecs fix \
        verify pre-commit install-hooks \
        ci

TEST_APP = tests/Application
BEHAT    = APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -f progress

# Default target: list the available commands.
help:
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| sort \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

## --- Dependencies -----------------------------------------------------------

install: ## Install composer dependencies
	composer install --no-interaction

update: ## Update composer dependencies
	composer update --no-interaction

## --- Docker -----------------------------------------------------------------

docker-up: ## Start the MySQL container (host port 3307)
	docker compose up -d mysql

docker-up-all: ## Start MySQL + headless Chrome (for @javascript Behat)
	docker compose up -d

docker-down: ## Stop and remove the containers
	docker compose down

docker-logs: ## Tail the container logs
	docker compose logs -f

## --- Test application -------------------------------------------------------

backend: ## Create the dev database and schema (APP_ENV=dev)
	APP_ENV=dev $(TEST_APP)/bin/console doctrine:database:create --if-not-exists --no-interaction
	APP_ENV=dev $(TEST_APP)/bin/console doctrine:schema:create --no-interaction

backend-test: ## Create the test database and schema (APP_ENV=test; used by Behat and CI)
	APP_ENV=test $(TEST_APP)/bin/console doctrine:database:create --if-not-exists --no-interaction
	APP_ENV=test $(TEST_APP)/bin/console doctrine:schema:create --no-interaction

frontend: ## Install and build the test application assets
	(cd $(TEST_APP) && yarn install --ignore-engines)
	(cd $(TEST_APP) && yarn encore dev)
	$(TEST_APP)/bin/console assets:install public --no-interaction

cache-clean: ## Remove the test application cache (all environments)
	rm -rf $(TEST_APP)/var/cache/*

db-reset: ## Drop and recreate the database schema
	$(TEST_APP)/bin/console doctrine:database:create --if-not-exists --no-interaction
	$(TEST_APP)/bin/console doctrine:schema:drop --force --full-database --no-interaction
	$(TEST_APP)/bin/console doctrine:schema:create --no-interaction

fixtures: ## (Re)load the default Sylius + RMA fixtures
	$(TEST_APP)/bin/console sylius:fixtures:load default --no-interaction

fixtures-test: ## Load the default Sylius + RMA fixtures into the test database (used by CI)
	APP_ENV=test $(TEST_APP)/bin/console sylius:fixtures:load default --no-interaction

serve: ## Serve the local DEV application on https://127.0.0.1:8080 (dev database)
	APP_ENV=dev $(TEST_APP)/bin/console cache:clear
	(cd $(TEST_APP) && APP_ENV=dev symfony serve --port=8080)

serve-test: ## Serve the TEST application on https://127.0.0.1:8081 (test database; for @javascript Behat)
	APP_ENV=test $(TEST_APP)/bin/console cache:clear
	(cd $(TEST_APP) && APP_ENV=test symfony serve --port=8081)

## --- Setup ------------------------------------------------------------------

app: docker-up frontend db-reset fixtures ## Spin up the local app: docker + assets + fresh schema + fixtures
	@echo "Local app ready. Start it with 'make serve' (https://127.0.0.1:8080)."

setup: install docker-up frontend backend ## One-shot local setup (deps + docker + assets + db)
	@echo "Setup complete. Run 'make test' to run the suite."

init: install backend frontend ## Install deps, db and assets (no docker)

## --- Tests ------------------------------------------------------------------

test: phpunit behat ## Run PHPUnit and the non-JS Behat suite

phpunit: ## Run PHPUnit (unit/component) tests
	vendor/bin/phpunit -c phpunit.xml.dist

behat: ## Run the non-JavaScript Behat suite
	$(BEHAT) --tags="~@javascript"

behat-js: ## Run the JavaScript Behat suite (needs docker-up-all + a running server)
	$(BEHAT) --tags="@javascript"

## --- Static analysis & code style -------------------------------------------

static: phpstan ecs ## Run all static analysis and code style checks

rector: ## Report pending Rector changes (no writes)
	vendor/bin/rector process --dry-run

rector-fix: ## Apply Rector (PHP 8.2 modernization of src)
	vendor/bin/rector process

phpstan: ## Run PHPStan
	vendor/bin/phpstan analyse -c phpstan.neon

ecs: ## Check code style (no changes)
	vendor/bin/ecs check src

fix: ## Auto-fix code style with ECS
	vendor/bin/ecs check src --fix

## --- Quality gate / hooks ---------------------------------------------------

verify: static phpunit ## Run the full quality gate (static + unit), no fixes

pre-commit: fix static phpunit ## Auto-fix style, then verify static analysis + unit tests

install-hooks: ## Point git at the repo hooks (.githooks) and set the commit template
	git config core.hooksPath .githooks
	git config commit.template .gitmessage
	@echo "Git hooks installed. 'make pre-commit' will run on every commit."
	@echo "Commit template set (.gitmessage); commits follow Conventional Commits."

## --- CI ---------------------------------------------------------------------

ci: init static phpunit behat ## Full CI pipeline
