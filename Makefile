.DEFAULT_GOAL := help
SHELL=/bin/bash
COMPOSER_ROOT=composer
TEST_DIRECTORY=tests/Application
CONSOLE=cd tests/Application && symfony console -e test
COMPOSER=cd tests/Application && composer
YARN=cd tests/Application && yarn

SYLIUS_VERSION=1.10.0
SYMFONY_VERSION=5.2
PLUGIN_NAME=synolia/sylius-scheduler-command-plugin

###
### INSTALL
### ¯¯¯¯¯¯¯

install: sylius ## Install Plugin on Sylius [SyliusVersion=1.9] [SymfonyVersion=5.2]
.PHONY: install

reset: ## Remove dependencies
	rm -rf tests/Application
.PHONY: reset

###
### DEVELOPMENT
### ¯¯¯¯¯¯¯¯¯¯¯

phpunit: phpunit-configure phpunit-run ## Run PHPUnit
.PHONY: phpunit


sylius: sylius-standard install-plugin update-dependencies install-sylius configure-sylius
.PHONY: sylius

sylius-standard:
	${COMPOSER_ROOT} create-project sylius/sylius-standard ${TEST_DIRECTORY} "~${SYLIUS_VERSION}"

install-plugin:
	${COMPOSER} config repositories.plugin '{"type": "path", "url": "../../"}'
	${COMPOSER} config extra.symfony.allow-contrib true
	${COMPOSER} config minimum-stability "dev"
	${COMPOSER} config prefer-stable true
	${COMPOSER} req "${PLUGIN_NAME}:*" --prefer-source --no-scripts
	cp -r install/Application tests
	cp -r tests/data/* ${TEST_DIRECTORY}/

update-dependencies:
	${COMPOSER} config extra.symfony.require "^${SYMFONY_VERSION}"
# FIX since https://github.com/Sylius/Sylius/pull/13215 is not merged
	${COMPOSER} require doctrine/dbal:"^2.6" doctrine/orm:"^2.9" --no-scripts --no-update

ifeq ($(SYLIUS_VERSION), 1.9.0)
ifeq ($(SYMFONY_VERSION), 4.4)
	${COMPOSER} require sylius/admin-api-bundle --no-scripts --no-update
endif
endif
	${COMPOSER} update --no-progress -n

install-sylius:
	${CONSOLE} sylius:install -n -s scheduler_command
	${YARN} install
	${YARN} build
	${CONSOLE} cache:clear

configure-sylius:
	cd ${TEST_DIRECTORY} && echo '    Synolia\SyliusSchedulerCommandPlugin\Checker\SoftLimitThresholdIsDueChecker:' >> config/services.yaml
	cd ${TEST_DIRECTORY} && echo '        tags:' >> config/services.yaml
	cd ${TEST_DIRECTORY} && echo '            - { name: !php/const Synolia\SyliusSchedulerCommandPlugin\Checker\IsDueCheckerInterface::TAG_ID }' >> config/services.yaml

phpunit-configure:
	cp phpunit.xml.dist ${TEST_DIRECTORY}/phpunit.xml

phpunit-run:
	cd ${TEST_DIRECTORY} && ./vendor/bin/phpunit

behat-configure: ## Configure Behat
	(cd ${TEST_DIRECTORY} && cp behat.yml.dist behat.yml)
	(cd ${TEST_DIRECTORY} && sed -i "s#vendor/sylius/sylius/src/Sylius/Behat/Resources/config/suites.yml#vendor/${PLUGIN_NAME}/tests/Behat/Resources/suites.yml#g" behat.yml)
	(cd ${TEST_DIRECTORY} && sed -i "s#vendor/sylius/sylius/features#vendor/${PLUGIN_NAME}/features#g" behat.yml)
	(cd ${TEST_DIRECTORY} && sed -i "s#@cli#@javascript#g" behat.yml)
	(cd ${TEST_DIRECTORY} && echo '    - { resource: "../vendor/${PLUGIN_NAME}/tests/Behat/Resources/services.yml" }' >> config/services_test.yaml)

grumphp: ## Run GrumPHP
	vendor/bin/grumphp run

###
### OTHER
### ¯¯¯¯¯¯

help: SHELL=/bin/bash
help: ## Dislay this help
	@IFS=$$'\n'; for line in `grep -h -E '^[a-zA-Z_#-]+:?.*?##.*$$' $(MAKEFILE_LIST)`; do if [ "$${line:0:2}" = "##" ]; then \
	echo $$line | awk 'BEGIN {FS = "## "}; {printf "\033[33m    %s\033[0m\n", $$2}'; else \
	echo $$line | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m%s\n", $$1, $$2}'; fi; \
	done; unset IFS;
.PHONY: help
