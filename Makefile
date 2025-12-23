.DEFAULT_GOAL := help
SHELL=/bin/bash
COMPOSER_ROOT=composer
CONSOLE=vendor/bin/console
COMPOSER=composer
SYLIUS_VERSION=2.1.0
SYMFONY_VERSION=6.4
PLUGIN_NAME=synolia/sylius-scheduler-command-plugin

###
### DEVELOPMENT
### ¯¯¯¯¯¯¯¯¯¯¯

install: sylius ## Install all dependencies with [SYLIUS_VERSION=2.1.0] [SYMFONY_VERSION=6.4]
.PHONY: install

reset: ## Remove dependencies
	${CONSOLE} doctrine:database:drop --force --if-exists || true
	rm -rf vendor
.PHONY: reset

phpunit: ## Run PHPUnit tests
	./vendor/bin/phpunit --testdox
.PHONY: phpunit

###
### OTHER
### ¯¯¯¯¯¯

sylius: install-sylius
.PHONY: sylius

install-sylius:
	@echo "Installing Sylius ${SYLIUS_VERSION} using TestApplication"
	${COMPOSER} config extra.symfony.require "^${SYMFONY_VERSION}"
	${COMPOSER} install
	${COMPOSER} require --dev sylius/test-application:"^${SYLIUS_VERSION}@alpha" -n -W # TODO: Remove alpha when stable
	${COMPOSER} test-application:install

grumphp: ## Run GrumPHP
	vendor/bin/grumphp run

help: SHELL=/bin/bash
help: ## Display this help
	@IFS=$$'\n'; for line in `grep -h -E '^[a-zA-Z_#-]+:?.*?##.*$$' $(MAKEFILE_LIST)`; do if [ "$${line:0:2}" = "##" ]; then \
	echo $$line | awk 'BEGIN {FS = "## "}; {printf "\033[33m    %s\033[0m\n", $$2}'; else \
	echo $$line | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m%s\n", $$1, $$2}'; fi; \
	done; unset IFS;
.PHONY: help
