# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/).

## [Unreleased]


## [1.7.1] - 2019-03-23
### Fixed
- No longer attempt refresh_token grant without token
- Handle errors during refresh_token (same as invalid access-token -> Redirect to IP authorize)

## [1.7.0] - 2019-03-10
### Added
- Laravel 5.8 support

## [1.6.0] - 2018-12-30
### Added
- Possibility to specify an "AuthWrapperFactory" allowing custom creation of the User object

## [1.5.0] - 2018-11-03
### Added
- Laravel 5.7 support

### Changed
- Tests: Assign the middleware as route-middleware

### Fixed
- Readme: Config publish command quotes (otherwise would need to use `\\` in namespace)

## [1.4.0] - 2018-04-19
### Added
- Laravel 5.6 support

### Fixed
- Readme: composer require package name

## [1.3.0] - 2017-09-19
### Added
- Laravel 5.5 support
- Package Auto-Discovery

### Changed
- Travis build matrix
- Improved dependency version constraints
- Allow `league/oauth2-client` also at v1 - even though I don't recommend it a short test showed no problems/incompatibilities

### Fixed
- PHP 5.6 install stuck because of (loose) testbench constraints

## [1.2.0] - 2017-06-29
### Added
- `Auth`-driver *viaRequest* & `Authenticatable` wrapper

## [1.1.0] - 2017-05-25
### Added
- Configuration option to specify a custom OAuth2 provider class

## 1.0.0 - 2017-05-21
### Added
- Basic Project setup
- OAuth2 client middleware & callback
- Keeps token in session
- Refreshes expired tokens
- (Cached) resource owner info

[Unreleased]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.7.1...HEAD
[1.7.1]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.7.0...v1.7.1
[1.7.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.6.0...v1.7.0
[1.6.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.5.0...v1.6.0
[1.5.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.0.0...v1.1.0
