# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

No unreleased changes.

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

[unreleased]: https://github.com/danbettles/marigold/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/danbettles/marigold/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/danbettles/marigold/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/danbettles/marigold/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/danbettles/marigold/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/danbettles/marigold/releases/tag/v1.0.0
