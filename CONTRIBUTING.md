## Installation

The plugin is developed and tested against the official [Sylius Test Application](https://github.com/Sylius/TestApplication)
(`sylius/test-application`), installed as a Composer dev dependency. The plugin-specific configuration
of the test application lives in `tests/TestApplication/`.

To be able to setup the plugin database, remember to configure your database credentials
in `tests/TestApplication/.env.local` and `tests/TestApplication/.env.test.local`
(default: `mysql://root@127.0.0.1/synolia_scheduler_command_plugin_%kernel.environment%`).

From the plugin root directory, run the following command:

```bash
$ make install
```

To test against specific Sylius and Symfony versions (as the CI does), run:

```bash
$ make install -e SYLIUS_VERSION=XX SYMFONY_VERSION=YY
```

e.g. `SYLIUS_VERSION=2.2.0 SYMFONY_VERSION=7.4`. Symfony 8 (YY=8.1) requires XX=2.3.0 and PHP 8.4.

It installs Composer dependencies and the frontend assets of the test application,
then creates the database, runs the migrations and loads the fixtures (`test` environment by default).

To reset test environment:
```bash
$ make reset
```

## Usage

### Running code analyse and tests

- GrumPHP (see configuration [grumphp.yml](grumphp.yml).)

  GrumPHP is executed by the Git pre-commit hook, but you can launch it manualy with :

  ```bash
  $ make grumphp
  ```

- PHPUnit

  ```bash
  $ make phpunit
  ```

### Opening Sylius with your plugin

The console of the test application is available with `vendor/bin/console`:

```bash
$ vendor/bin/console debug:router -e test
```

- Using `test` environment (from the plugin root directory):

    ```bash
    $ make serve
    ```

    or

    ```bash
    $ APP_ENV=test symfony server:start -d
    ```

- Using `dev` environment:

    ```bash
    $ APP_ENV=dev symfony server:start -d
    ```

    The `dev` database has to be created first, e.g. `vendor/bin/console doctrine:database:create -e dev`,
    `vendor/bin/console doctrine:migrations:migrate -n -e dev` and `vendor/bin/console sylius:fixtures:load -n -e dev`.
