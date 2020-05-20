# Slim restful

[![License](https://poser.pugx.org/adasilva/slim-restful/license)](https://packagist.org/packages/adasilva/slim-restful)
[![Latest Stable Version](https://poser.pugx.org/adasilva/slim-restful/version)](https://packagist.org/packages/adasilva/slim-restful)
[![Latest Unstable Version](https://poser.pugx.org/adasilva/slim-restful/v/unstable)](//packagist.org/packages/adasilva/slim-restful)

This library provides useful classes to simplify the use of Slim framework to create REST APIs.

## Getting Started

Install via [Composer](http://getcomposer.org)
```bash
$ composer require adasilva/slim-restful:^v1.0-beta
```

This will install Slim restful and all required dependencies (Including slim framework). Slim restful requires PHP 7.4 or newer.

## Hello world

### Slim restful skeleton

To start a new project, you can use the [skeleton](https://github.com/antodasil/slim-restful-skeleton).

### On an existing project

Use the SettingsManager to load settings file (json or ini):
 * load(string filepath): void - Load settings into the SettingsManager
 * getContainer(): Container - Get container with settings

Use RestAppFactory instead of AppFactory:
 * create(): RestApp

RestApp define new methods:
 * addSlimMiddlewares(): RestApp - Call Slim methods AddRoutingMiddleware() and AddErrorMiddleware(true, true, true).
 * loadRoutes(string filepath): RestApp - Load routes and middlewares from file (json or xml)

A controller MAY extend BaseController and SHOULD contains get,post,put,patch or delete methods with traditional slim parameters (request, response and arguments).
It SHOULD return the response.

A middleware MAY extend BaseMiddleware.

To add a controller, you just have to add it on your routes file.

## Routes file

The routes file is used to declare routes but also middlewares 

```json
{
    "middlewares": {
        "namespace": "Routes\\",
        "list": [
            { "middleware": "TestMiddleware", "annotation": "test" }
        ]
    },
    "routes" : {
        "namespace": "Controllers\\",
        "list": [
            { "name": "hello",   "pattern": "/hello",   "controller": "HelloController" },
            { "name": "home",   "pattern": "/home",   "controller": "HomeController" }
        ]
    }
}
```

## Settings file

These files are juste for syntax example. Only "environment" setting is necessary. Use "containerSettings" to defined container settings.

### JSON

```json
{
    "application": {
        "environment": "development",
        "name": "Slim-restful Skeleton"
    },

    "containerSettings": {
        "determineRouteBeforeAppMiddleware": true
    }

}
```

## Authors

* **Antoine Da Silva**

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details
