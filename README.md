<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Scheduler Command Plugin</h1>

<p align="center">Schedule Symfony Commands in your Sylius admin panel.</p>

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

## Installation

Run `$ composer require synolia/sylius-scheduler-command-plugin`.
Register `SchedulerCommandPlugin\SynoliaSchedulerCommandPlugin::class => ['all' => true],` in your `config/bundles.php` file.
Import required config in your `config/packages/_sylius.yaml` file:

```yaml
# config/packages/_sylius.yaml

imports:
    - { resource: "@SynoliaSyliusSchedulerCommandPlugin/Resources/config/config.yml" }
```

Import routing in your `config/routes.yaml` file:

```yaml
# config/routes.yaml

synolia_scheduled_command:
    resource: "@SynoliaSyliusSchedulerCommandPlugin/Resources/config/admin_routing.yml"
    prefix: /admin
```

## Usage

* Log into admin panel
* Click on `Scheduled commands` in the Configuration section in main menu
* Manage your Scheduled commands

## Development

See [How to contribute](CONTRIBUTING.md).

License
-------
This library is under the MIT license.

Credits
-------
Developed by [Synolia](https://synolia.com/).
