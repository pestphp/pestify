# Release Notes for 2.x

## Unreleased

## [v2.6.1 (2024-05-05)](https://github.com/pestphp/pest-plugin-drift/compare/v2.6.0...v2.6.1)

### Changed

- Improve function name conversion to description ([#43](https://github.com/pestphp/pest-plugin-drift/pull/43))

## [v2.6.0 (2024-03-19)](https://github.com/pestphp/pest-plugin-drift/compare/v2.5.0...v2.6.0)

### Dependency Updates

- **php**: Updated from version `^8.1.0` to `^8.2.0`.
- **symfony/finder**: Updated from version `^6.3.0` to `^7.0.0`.
- **pestphp/pest**: Updated from version `^2.16.1` to `^2.34.4`.
- **nikic/php-parser**: Updated from version `^4.17.1` to `^5.0.2`.

## [v2.5.0 (2023-09-26)](https://github.com/pestphp/pest-plugin-drift/compare/v2.4.2...v2.5.0)

### Added

- Add partial support for `#[DataProviderExternal]` attribute ([#37](https://github.com/pestphp/pest-plugin-drift/pull/37))

### Fixed

- Fix incorrect uses when anonymous class extends another class ([#36](https://github.com/pestphp/pest-plugin-drift/pull/36))
- Fix static call conversion ([#35](https://github.com/pestphp/pest-plugin-drift/pull/35))

## [v2.4.2 (2023-09-17)](https://github.com/pestphp/pest-plugin-drift/compare/v2.4.1...v2.4.2)

### Fixed

- Do not convert assertions coming from method calls ([#34](https://github.com/pestphp/pest-plugin-drift/pull/34))

## [v2.4.1 (2023-09-17)](https://github.com/pestphp/pest-plugin-drift/compare/v2.4.0...v2.4.1)

### Fixed

- Only convert PHPUnit assertions ([#30](https://github.com/pestphp/pest-plugin-drift/pull/30))
- Reset extends-to-uses context between each conversion ([#31](https://github.com/pestphp/pest-plugin-drift/pull/31))
- Preserved semicolons in group use after conversion ([#32](https://github.com/pestphp/pest-plugin-drift/pull/32))

## [v2.4.0 (2023-09-03)](https://github.com/pestphp/pest-plugin-drift/compare/v2.3.0...v2.4.0)

### Added

- Add support for handling named arguments in test conversion

## [v2.3.0 (2023-08-15)](https://github.com/pestphp/pest-plugin-drift/compare/v2.2.1...v2.3.0)

### Added

- Add support for `#[Test]`, `#[Group]`, `#[Depends]` and `#[DataProvider]` attributes

## [v2.2.1 (2023-08-02)](https://github.com/pestphp/pest-plugin-drift/compare/v2.2.0...v2.2.1)

### Fixed

- Expectation switched place with message while using a message ([#22](https://github.com/pestphp/pest-plugin-drift/pull/22))

## [v2.2.0 (2023-07-25)](https://github.com/pestphp/pest-plugin-drift/compare/v2.1.0...v2.2.0)

### Added

- Removes annotations ([#14](https://github.com/pestphp/pest-plugin-drift/pull/14))

### Fixed

- Duplicates uses in groups ([#19](https://github.com/pestphp/pest-plugin-drift/pull/19))

## [v2.1.0 (2023-07-23)](https://github.com/pestphp/pest-plugin-drift/compare/v2.0.0...v2.1.0)

### Added

- Support for `@group` tag ([#17](https://github.com/pestphp/pest-plugin-drift/pull/17))

### Fixed

- Anonymous classes being migrated ([#15](https://github.com/pestphp/pest-plugin-drift/pull/15))

## v2.0.0 (2023-07-19)

- First release of the plugin
