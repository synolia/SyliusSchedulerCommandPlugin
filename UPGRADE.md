# Upgrade

## from ^2.0 to ^3.0

### Fixtures
* `logFile` field must be renamed to `logFilePrefix` and must not end with the file extension

Also, for old existing schedules in your database, please remove the log file extension in column `logFilePrefix`.
