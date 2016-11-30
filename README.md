## Onphp Extensions Package

### Installation

In `composer.json` add:

```
    "require": {
        ...
        "bis-gmbh/onphp-extensions": "dev-master",
        ...
    },
```
 and run `composer install`.
 
### Running tests

Setup phpunit config for local environment:

```
cp phpunit.dist.xml phpunit.xml
```

edit `phpunit.xml` if needed and run tests:

```
vendor/bin/phpunit
```
