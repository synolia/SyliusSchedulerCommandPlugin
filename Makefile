.DEFAULT_GOAL := help
SHELL=/bin/bash
COMPOSER_ROOT=composer
TEST_DIRECTORY=tests/Application
INSTALL_DIRECTORY=install/Application
CONSOLE=cd ${TEST_DIRECTORY} && php bin/console -e test
COMPOSER=cd ${TEST_DIRECTORY} && composer
YARN=cd ${TEST_DIRECTORY} && yarn

SYLIUS_VERSION=1.14.0
SYMFONY_VERSION=6.4
PHP_VERSION=8.2
PLUGIN_NAME=synolia/sylius-scheduler-command-plugin

###
### DEVELOPMENT
### ¯¯¯¯¯¯¯¯¯¯¯

install: sylius ## Install Plugin on Sylius [SYLIUS_VERSION=1.12.0] [SYMFONY_VERSION=6.1] [PHP_VERSION=8.1]
.PHONY: install

reset: ## Remove dependencies
ifneq ("$(wildcard ${TEST_DIRECTORY}/bin/console)","")
	${CONSOLE} doctrine:database:drop --force --if-exists || true
endif
	rm -rf ${TEST_DIRECTORY}
.PHONY: reset

phpunit: phpunit-configure phpunit-run ## Run PHPUnit
.PHONY: phpunit

###
### OTHER
### ¯¯¯¯¯¯

sylius: sylius-standard update-dependencies install-plugin install-sylius
.PHONY: sylius

sylius-standard:
ifeq ($(shell [[ $(SYLIUS_VERSION) == *dev ]] && echo true ),true)
	${COMPOSER_ROOT} create-project sylius/sylius-standard:${SYLIUS_VERSION} ${TEST_DIRECTORY} --no-install --no-scripts
else
	${COMPOSER_ROOT} create-project sylius/sylius-standard ${TEST_DIRECTORY} "~${SYLIUS_VERSION}" --no-install --no-scripts
endif
	${COMPOSER} config allow-plugins true
ifeq ($(shell [[ $(SYLIUS_VERSION) == *dev ]] && echo true ),true)
	${COMPOSER} require --no-update sylius/sylius:"${SYLIUS_VERSION}"
else
	${COMPOSER} require --no-update sylius/sylius:"~${SYLIUS_VERSION}"
endif

update-dependencies:
	${COMPOSER} config extra.symfony.require "~${SYMFONY_VERSION}"
	${COMPOSER} require symfony/asset:~${SYMFONY_VERSION} --no-scripts --no-update
	${COMPOSER} update --no-progress -n

install-plugin:
	${COMPOSER} config repositories.plugin '{"type": "path", "url": "../../"}'
	${COMPOSER} config extra.symfony.allow-contrib true
	${COMPOSER} config minimum-stability "dev"
	${COMPOSER} config prefer-stable true
	${COMPOSER} req ${PLUGIN_NAME}:* --prefer-source --no-scripts
	cp -r ${INSTALL_DIRECTORY} tests

install-sylius:
	${CONSOLE} doctrine:database:create -n
	${CONSOLE} doctrine:migrations:migrate -n
	${CONSOLE} messenger:setup-transports -n
	${CONSOLE} sylius:fixtures:load default -n
	${YARN} install
	${YARN} build
	${CONSOLE} cache:clear

phpunit-configure:
	cp phpunit.xml.dist ${TEST_DIRECTORY}/phpunit.xml

phpunit-run:
	cd ${TEST_DIRECTORY} && ./vendor/bin/phpunit --testdox

behat-configure: ## Configure Behat
	(cd ${TEST_DIRECTORY} && cp behat.yml.dist behat.yml)
	(cd ${TEST_DIRECTORY} && sed -i "s#vendor/sylius/sylius/src/Sylius/Behat/Resources/config/suites.yml#vendor/${PLUGIN_NAME}/tests/Behat/Resources/suites.yml#g" behat.yml)
	(cd ${TEST_DIRECTORY} && sed -i "s#vendor/sylius/sylius/features#vendor/${PLUGIN_NAME}/features#g" behat.yml)
	(cd ${TEST_DIRECTORY} && sed -i "s#@cli#@javascript#g" behat.yml)
	(cd ${TEST_DIRECTORY} && sed -i '2i \ \ \ \ - { resource: "../vendor/${PLUGIN_NAME}/tests/Behat/Resources/services.yml\" }' config/services_test.yaml)
	${CONSOLE} cache:clear

grumphp: ## Run GrumPHP
	vendor/bin/grumphp run

help: SHELL=/bin/bash
help: ## Display this help
	@IFS=$$'\n'; for line in `grep -h -E '^[a-zA-Z_#-]+:?.*?##.*$$' $(MAKEFILE_LIST)`; do if [ "$${line:0:2}" = "##" ]; then \
	echo $$line | awk 'BEGIN {FS = "## "}; {printf "\033[33m    %s\033[0m\n", $$2}'; else \
	echo $$line | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m%s\n", $$1, $$2}'; fi; \
	done; unset IFS;
.PHONY: help
