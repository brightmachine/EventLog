# EventLog

<!--
[![Build Status](https://secure.travis-ci.org/brightmachine/eventlog.png?branch=master)](https://travis-ci.org/brightmachine/eventlog)
-->

## What is EventLog?

EventLog is an append-only persistence layer to support Event Sourcing in your PHP project.

EventLog has the following defining characteristics:

- doesn't overly enforce the use of objects, for example, if you want to push an event onto a stream without declaring an object for it, you can.
- stores primitive types (strings, arrays) as JSON, will never resort to PHP serialisation of objects
- will never attempt to rebuild an object itself, but will give the tools to make it easier

## Requirements

PHP 5.5+

## Installation

Install composer in your project:

```
curl -s http://getcomposer.org/installer | php
```

Create a `composer.json` file in your project root:

```json
{
    "require": {
        "brightmachine/eventlog": "*"
    }
}
```

Install via composer:

```
php composer.phar install
```

## License

EventLog is open-sourced software licensed under the MIT License - see the LICENSE file for details

## Documentation

editing...


## Contributing

Checkout master source code from github:

```
hub clone brightmachine/EventLog
```

Install development components via composer:

```
# If you don't have composer.phar
./scripts/bundle-devtools.sh .

# If you have composer.phar
composer.phar install --dev
```

### Unit Testing

We works under test driven development.

Run phpspec:

```
./bin/phpspec run
```
<!--
Run phpunit:

```
./bin/phpunit
```
-->

### Coding Standard

We follows coding standard [PSR-2][].

Check if your codes follows PSR-2 by phpcs:

```
./bin/phpcs --standard=PSR2 src/
```

## Acknowledgement

We would like to acknowledge and thank…

- [Goodby Setup](http://bit.ly/byesetup) for helping to get a package setup
- [EventCentric](https://github.com/event-centric/EventCentric.Core)
- [NEventStore](https://github.com/NEventStore/NEventStore)
- [getevenstore](https://github.com/eventstore/eventstore/wiki)

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md