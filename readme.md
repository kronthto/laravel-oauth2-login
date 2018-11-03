# Laravel OAuth2 Login

[![Software License][ico-license]](LICENSE.md)
[![Latest Stable Version][ico-githubversion]][link-releases]
[![Build Status][ico-build]][link-build]

This is a Laravel5 package that provides a middleware to protect routes requiring an OAuth2 login.

You could describe it as a bridge between Laravel and [league/oauth2-client](https://github.com/thephpleague/oauth2-client).

## Features

* OAuth2 client middleware
* Keeps token in session
* Refreshes expired tokens
* (Cached) resource owner info
* Driver to allow use of `Auth` facade

## Install

* Using composer: `$ composer require kronthto/laravel-oauth2-login`
* Register the service provider (Auto-Discovery enabled): `Kronthto\LaravelOAuth2Login\ServiceProvider`
* Publish the config file: `$ artisan vendor:publish --provider="Kronthto\LaravelOAuth2Login\ServiceProvider"`
* Put the credentials of your OAuth Provider in the published config

## Usage

Add the `Kronthto\LaravelOAuth2Login\CheckOAuth2` middleware to the routes (-groups) you want to protect.

**Bear in mind that this only ensures that some user is logged in**, if you require further authorization checks those will still have to be implemented. This package stores the resource owner info as an Request-attribute to enable you to do so.

### `Auth` guard

This is optional, as adding the middleware redirects the client anyways if not authenticated. If you want to utilize Policies however you will need to define a custom guard. A driver for it is provided by this package.

In your auth config, add the new guard like this:
``` php
  'oauth2' => [
    'driver' => 'oauth2', // Config: oauth2login.auth_driver_key
  ]
```

**You will need to assign a higher priority to `CheckOAuth2` than `\Illuminate\Auth\Middleware\Authenticate`**, do this by overriding `$middlewarePriority` in your Http-Kernel.

## Changelog

Please see the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-githubversion]: https://badge.fury.io/gh/kronthto%2Flaravel-oauth2-login.svg
[ico-build]: https://travis-ci.org/kronthto/laravel-oauth2-login.svg?branch=master

[link-releases]: https://github.com/kronthto/laravel-oauth2-login/releases
[link-contributors]: ../../contributors
[link-build]: https://travis-ci.org/kronthto/laravel-oauth2-login
