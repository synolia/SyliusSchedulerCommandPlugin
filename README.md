[![License](https://img.shields.io/packagist/l/synolia/sylius-scheduler-command-plugin.svg)](https://github.com/synolia/SyliusSchedulerCommandPlugin/blob/master/LICENSE)
![Tests](https://github.com/synolia/SyliusSchedulerCommandPlugin/workflows/CI/badge.svg?branch=master)
[![Version](https://img.shields.io/packagist/v/synolia/sylius-scheduler-command-plugin.svg)](https://packagist.org/packages/synolia/sylius-scheduler-command-plugin)
[![Total Downloads](https://poser.pugx.org/synolia/sylius-scheduler-command-plugin/downloads)](https://packagist.org/packages/synolia/sylius-scheduler-command-plugin)

<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Scheduler Command Plugin</h1>
<p align="center">
    <img src="https://sylius.com/assets/badge-approved-by-sylius.png" width="85">
</p>
<p align="center">Schedule Symfony Commands in your Sylius admin panel.</p>

![Capture](/etc/capture.png "Capture")

## Features

* See the list of planned command
* Add, edit, enable/disable or delete scheduled commands
* For each command, you have to define :
  * Name
  * Selected Command from the list of Symfony commands
  * Based on Cron schedule expression see [Cron formats](https://abunchofutils.com/u/computing/cron-format-helper/)
  * Output Log file (optional)
  * Priority (highest is priority)
* Run the Command immediately
* Download, show file size, empty log files directly from the admin panel
* Define commands with a Factory (from a Doctrine migration, for example)

## Requirements

| | Version |
| :--- | :--- |
| PHP  | 7.4, 8.0 |
| Sylius | 1.8, 1.9, 1.10 |

## Installation

1. Add the bundle and dependencies in your composer.json :

        composer config extra.symfony.allow-contrib true
        composer req synolia/sylius-scheduler-command-plugin

2. Apply migrations to your database:
   
        bin/console doctrine:migrations:migrate

3. Launch Run command in your Crontab

        * * * * * /_PROJECT_DIRECTORY_/bin/console synolia:scheduler-run

4. (optional) Showing humanized cron expression

        composer require sivaschenko/utility-cron

## Usage

* Log into admin panel
* Click on `Scheduled commands` in the Configuration section in main menu
* Manage your Scheduled commands

## Fixtures
Inside sylius fixture file `config/packages/sylius_fixtures.yaml` you can add scheduled command fixtures to your suite.
```yaml
sylius_fixtures:
    suites:
        my_fixture_suite:
            fixtures:
                scheduler_command:
                    options:
                        scheduled_commands:
                            -
                                name: 'Reset Sylius'
                                command: 'sylius:fixtures:load'
                                cronExpression: '0 0 * * *'
                                logFile: 'reset.log'
                                priority: 0
                                enabled: true
                            -
                                name: 'Cancel Unpaid Orders'
                                command: 'sylius:cancel-unpaid-orders'
                                cronExpression: '0 0 * * *'
                                priority: 1
                                enabled: false
```

## Development

See [How to contribute](CONTRIBUTING.md).

## License

This library is under the MIT license.

## Credits

Developed by [Synolia](https://synolia.com/).
