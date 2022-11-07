## Installation

From the plugin root directory, run the following commands:

```bash
$ make install -e SYLIUS_VERSION=XX SYMFONY_VERSION=YY PHP_VERSION=ZZ
```

Default values : XX=1.12.0 and YY=6.1 and ZZ=8.1

To be able to setup the plugin database, remember to configure you database credentials
in `install/Application/.env.local` and `install/Application/.env.test.local`.

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

- Using `test` environment:

    ```bash
    $ (cd tests/Application && bin/console server:run -d public -e test)
    ```

- Using `dev` environment:

    ```bash
    $ (cd tests/Application && bin/console server:run -d public -e dev)
    ```
