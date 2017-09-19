# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/).

## [Unreleased]
### Added
- Laravel 5.5 support
- Package Auto-Discovery

### Changed
- Travis build matrix

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

[Unreleased]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/kronthto/laravel-oauth2-login/compare/v1.0.0...v1.1.0
