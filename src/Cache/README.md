# Kasi Cache

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kasi/cache.svg?style=flat)](https://packagist.org/packages/kasi/cache)
[![Total Downloads](https://img.shields.io/packagist/dt/kasi/cache.svg?style=flat)](https://packagist.org/packages/kasi/cache)

The Kasi Cache component provides a unified caching API for PHP applications. This component supports multiple cache stores and offers features like tagging, locking, and rate limiting with a clean and expressive interface.

## ⚠️ Read-Only Repository

**This is a subtree split of the main Kasi Framework repository and is read-only.**

- **Do not submit pull requests here** - they will be closed without review
- **Do not open issues here** - they will be redirected to the main repository
- This repository is automatically updated from the main framework repository

## 📍 Main Repository

For contributions, bug reports, feature requests, and discussions, please visit the main repository:

**🏠 [kasiapps/framework](https://github.com/kasiapps/framework)**

## 📦 Installation

You can install the component via Composer:

```bash
composer require kasi/cache
```

## 🚀 Features

- **Multiple Stores**: Redis, Memcached, Database, File, Array, APC, DynamoDB, and Null stores
- **Cache Tagging**: Group related cache items and invalidate them together
- **Cache Locking**: Prevent cache stampedes with distributed locks
- **Rate Limiting**: Built-in rate limiting functionality
- **Event System**: Comprehensive cache events for monitoring and debugging
- **PSR-6 & PSR-16**: Full PSR compliance for cache interfaces
- **Flexible TTL**: Support for various time formats and expiration strategies
- **Atomic Operations**: Increment, decrement, and remember operations

## 📖 Documentation

For detailed documentation, usage examples, and guides, please visit:

- **[Framework Documentation](https://docs.kasiapp.com)**

## 🔧 Usage

This component is designed to be used as part of the Kasi Framework. For standalone usage or integration examples, please refer to the main repository documentation.

## 🤝 Contributing

We welcome contributions! However, since this is a read-only subtree split, please:

1. Visit the main repository: [kasiapps/framework](https://github.com/kasiapps/framework)
2. Read the contributing guidelines
3. Submit your pull requests there

## 🐛 Bug Reports

If you discover any bugs or issues, please report them in the main repository:

**[Report Issues](https://github.com/kasiapps/framework/issues)**

## 📄 License

The Kasi Cache component is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🏢 About

Kasi Cache is developed and maintained by [Jetstream Labs](https://jetstreamlabs.com) as part of the Kasi Framework.

This component is a port of [illuminate/cache](https://github.com/illuminate/cache), originally created by [Taylor Otwell](https://github.com/taylorotwell) and The Laravel Team.

---

**Remember**: This repository is automatically synchronized from the main framework. All development happens in [kasiapps/framework](https://github.com/kasiapps/framework).
