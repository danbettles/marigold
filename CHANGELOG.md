# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

No unreleased changes.

## [4.0.1] - 2023-04-08

### Changed

- The UTF-8 BOM is removed from output from `Php::executeFile()` because it's more trouble than it's worth.

## [4.0.0] - 2023-03-26

### Added

- 'Default' route parameters will be processed, and passed through to, the action.

### Removed

- `AbstractAction::createNotFoundException()` because it was a bit pointless 🤦‍♂️

## [3.0.0] - 2023-03-11

### Added

- Added `RedirectHttpResponse`.
- Added a couple more oft-used HTTP-response status codes.

### Changed

- Restructured exception classes.

## [2.5.0] - 2023-03-08

### Added

- Added headers to `HTTPResponse`.
- Added the 303 (See Other) and 400 (Bad Request) response codes to `HTTPResponse`.

### Fixed

- An `HTTPResponse` can safely be created from a response code unknown to the class&mdash;which is most of them at present.

## [2.4.0] - 2023-01-21

### Added

- Added `AbstractTestCase::getFixtureContents()`, which returns the contents of a fixture file.

### Changed

- Simplified some code.

### Fixed

- Got `AbstractTestCaseTest` working 🤦‍♂️

## [2.3.3] - 2023-01-17

### Fixed

- In the CSS minifier, fixed a bug that was breaking selectors containing a pseudo-class.

## [2.3.2] - 2022-12-19

### Changed

- Improved tests.

## [2.3.1] - 2022-12-03

### Fixed

- In `HttpRequest`, use `setContent()` in the constructor, duh!

## [2.3.0] - 2022-12-03

### Added

- Added dumb `content` property to `HttpRequest`.

## [2.2.1] - 2022-11-30

### Fixed

- Quickly corrected some PHPDocs.

## [2.2.0] - 2022-11-26

### Added

- In `Registry`, factories can be any kind of callable.

## [2.1.0] - 2022-11-24

### Added

- In `Registry`, factory closures are passed the `Registry` instance.

## [2.0.0] - 2022-11-23

### Changed

- Changed the structure of the route array: `id` must now be included in the array.

## [1.0.1] - 2022-10-22

### Added

- This change-log file.

### Fixed

- Updated `HttpResponse::send()` to accept an `HttpRequest`.  This should have been done previously 🤦‍♂️

## [1.0.0] - 2022-10-22

First stable release.

[unreleased]: https://github.com/danbettles/marigold/compare/v4.0.1...HEAD
[4.0.1]: https://github.com/danbettles/marigold/compare/v4.0.0...v4.0.1
[4.0.0]: https://github.com/danbettles/marigold/compare/v3.0.0...v4.0.0
[3.0.0]: https://github.com/danbettles/marigold/compare/v2.5.0...v3.0.0
[2.5.0]: https://github.com/danbettles/marigold/compare/v2.4.0...v2.5.0
[2.4.0]: https://github.com/danbettles/marigold/compare/v2.3.3...v2.4.0
[2.3.3]: https://github.com/danbettles/marigold/compare/v2.3.2...v2.3.3
[2.3.2]: https://github.com/danbettles/marigold/compare/v2.3.1...v2.3.2
[2.3.1]: https://github.com/danbettles/marigold/compare/v2.3.0...v2.3.1
[2.3.0]: https://github.com/danbettles/marigold/compare/v2.2.1...v2.3.0
[2.2.1]: https://github.com/danbettles/marigold/compare/v2.2.0...v2.2.1
[2.2.0]: https://github.com/danbettles/marigold/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/danbettles/marigold/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/danbettles/marigold/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/danbettles/marigold/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/danbettles/marigold/releases/tag/v1.0.0
