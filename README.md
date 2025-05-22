# BaksDev Api Megamarket

[![Version](https://img.shields.io/badge/version-7.2.11-blue)](https://github.com/baks-dev/megamarket/releases)
![php 8.4+](https://img.shields.io/badge/php-min%208.4-red.svg)
[![packagist](https://img.shields.io/badge/packagist-green)](https://packagist.org/packages/baks-dev/megamarket)

Модуль Megamarket Api

## Установка

``` bash
$ composer require baks-dev/megamarket
```

## Дополнительно

Установка конфигурации и файловых ресурсов:

``` bash
$ php bin/console baks:assets:install
```

Каждому токену добавляем свой транспорт очереди

``` php
$messenger
->transport('<UUID>')
->dsn('%env(MESSENGER_TRANSPORT_DSN)%')
->options(['queue_name' => 'profile_name'])
->retryStrategy()
->maxRetries(3)
->delay(1000)
->maxDelay(0)
->multiplier(2)
->service(null);
```

## Тестирование

``` bash
$ php bin/phpunit --group=megamarket
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.
