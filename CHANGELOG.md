# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

No unreleased changes.

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

- Updated `HttpResponse::send()` to accept an `HttpRequest`.  This should have been done previously :facepalm:

## [1.0.0] - 2022-10-22

First stable release.

[unreleased]: https://github.com/danbettles/marigold/compare/v2.3.3...HEAD
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
