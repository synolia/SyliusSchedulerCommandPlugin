# Upgrade

## from ^2.0 to ^3.0

### Fixtures
* `logFile` field must be renamed to `logFilePrefix` and must not end with the file extension

Also, for old existing schedules in your database, please remove the log file extension in column `logFilePrefix`.

## from 3.8 to 3.9

* The constructors of `Synolia\SyliusSchedulerCommandPlugin\Checker\EveryMinuteIsDueChecker` and `Synolia\SyliusSchedulerCommandPlugin\Checker\SoftLimitThresholdIsDueChecker` has been modified, a new argument has been added :

   ```php
    public function __construct(
        // ...
        private ?DateTimeProviderInterface $dateTimeProvider = null,
    )
    ```

## from 4.0 to 4.1

### Requirements
* Symfony 6.4 is no longer supported: the minimum required version is now Symfony 7.4 (`^7.4 || ^8.0`).
* Sylius 2.0 and 2.1 are no longer supported: the minimum required version is now Sylius 2.2.
