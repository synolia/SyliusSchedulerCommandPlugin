.DEFAULT_GOAL := help
SHELL=/bin/bash
COMPOSER_BIN=composer
TEST_APP_DIR=vendor/sylius/test-application
APP_ENV ?= test
CONSOLE=vendor/bin/console -e $(APP_ENV)
# sylius/test-application frontend requires Yarn classic (1.x): Yarn Berry (PnP, strict peer dependencies) fails to build it
YARN_BIN ?= $(shell yarn --version 2>/dev/null | grep -q '^1\.' && echo yarn || echo npx --yes yarn@1.22.22)
YARN=$(YARN_BIN) --cwd $(TEST_APP_DIR)

# Leave empty to install the latest versions allowed by composer.json (local development).
# Set them to restrict the versions (CI), e.g. `make install SYLIUS_VERSION=2.3.0 SYMFONY_VERSION=8.1`.
SYLIUS_VERSION ?=
SYMFONY_VERSION ?=
# Fixtures suite loaded by `make install` (empty: the "default" suite)
FIXTURES_SUITE ?=

###
### DEVELOPMENT
### ¯¯¯¯¯¯¯¯¯¯¯

install: dependencies frontend database ## Install Plugin with Sylius Test Application [SYLIUS_VERSION=2.3.0] [SYMFONY_VERSION=8.1]
	$(CONSOLE) cache:clear
.PHONY: install

reset: ## Remove dependencies and database
ifneq ("$(wildcard vendor/bin/console)","")
	$(CONSOLE) doctrine:database:drop --force --if-exists || true
endif
	rm -rf var/ vendor/ node_modules composer.lock
.PHONY: reset

phpunit: ## Run PHPUnit
	vendor/bin/phpunit
.PHONY: phpunit

serve: ## Start the Symfony local web server (Sylius Test Application) [APP_ENV=test]
	APP_ENV=$(APP_ENV) symfony server:start -d
.PHONY: serve

grumphp: ## Run GrumPHP
	vendor/bin/grumphp run
.PHONY: grumphp

###
### OTHER
### ¯¯¯¯¯¯

dependencies:
ifneq ($(SYLIUS_VERSION),)
	$(COMPOSER_BIN) require --no-update --no-scripts sylius/sylius:"~$(SYLIUS_VERSION)"
	$(COMPOSER_BIN) require --dev --no-update --no-scripts sylius/test-application:"~$(SYLIUS_VERSION)@alpha"
endif
ifneq ($(SYMFONY_VERSION),)
	# extra.symfony.require is only applied by Symfony Flex when it is loaded before the dependency resolution
	$(COMPOSER_BIN) global show symfony/flex > /dev/null 2>&1 || ($(COMPOSER_BIN) global config --no-plugins allow-plugins.symfony/flex true && $(COMPOSER_BIN) global require --no-progress --no-scripts --no-plugins symfony/flex)
	# Pin the minor version: "~8.0" would also allow 8.1
	$(COMPOSER_BIN) config extra.symfony.require "$(SYMFONY_VERSION).*"
endif
	$(COMPOSER_BIN) update --no-progress -n
.PHONY: dependencies

frontend:
	php bin/create_node_symlink.php
	$(YARN) install
	$(YARN) build
	$(CONSOLE) assets:install
.PHONY: frontend

database:
	$(CONSOLE) doctrine:database:drop --if-exists --force
	$(CONSOLE) doctrine:database:create
	$(CONSOLE) doctrine:migrations:migrate -n
	$(CONSOLE) sylius:fixtures:load -n $(FIXTURES_SUITE)
.PHONY: database

help: SHELL=/bin/bash
help: ## Display this help
	@IFS=$$'\n'; for line in `grep -h -E '^[a-zA-Z_#-]+:?.*?##.*$$' $(MAKEFILE_LIST)`; do if [ "$${line:0:2}" = "##" ]; then \
	echo $$line | awk 'BEGIN {FS = "## "}; {printf "\033[33m    %s\033[0m\n", $$2}'; else \
	echo $$line | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m%s\n", $$1, $$2}'; fi; \
	done; unset IFS;
.PHONY: help
