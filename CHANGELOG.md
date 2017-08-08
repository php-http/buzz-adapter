# Change Log

## 1.0.0

### Changed

- We throw a `RequestException` instead of a `\InvalidExcpetion` when using Buzz' curl client and trying to send GET request with body.
- We throw a `NetworkException` on network timeout.
- We require version 0.15.1 of `kriswallsmith/buzz`.  

## 0.3.0 - 2016-07-18

### Changed

- Client now requires a Response factory instead of a Message factory


## 0.2.0 - 2016-06-28

### Changed

- Updated discovery dependency


## 0.1.0 - 2016-03-10

### Added

- Initial release
