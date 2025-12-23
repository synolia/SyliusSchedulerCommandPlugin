## Installation

From the plugin root directory, run the following commands:

```bash
$ make install -e SYLIUS_VERSION=XX SYMFONY_VERSION=YY
```

Default values : XX=2.1 and YY=6.4

To be able to setup the plugin database, remember to configure you database credentials
in `tests/TestApplication/.env.local` and `tests/TestApplication/.env.test.local`.

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
    $ symfony server:start -d -e test
    ```

- Using `dev` environment:

    ```bash
    $ symfony server:start -d -e dev
    ```
