# Kasi Bus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kasi/bus.svg?style=flat)](https://packagist.org/packages/kasi/bus)
[![Total Downloads](https://img.shields.io/packagist/dt/kasi/bus.svg?style=flat)](https://packagist.org/packages/kasi/bus)

The Kasi Bus component provides a command bus implementation for PHP applications. This component enables you to dispatch commands and jobs, handle batching, and manage command pipelines with a clean and expressive API.

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
composer require kasi/bus
```

## 🚀 Features

- **Command Dispatching**: Dispatch commands synchronously or asynchronously
- **Job Batching**: Group jobs together and track their collective progress
- **Command Chains**: Chain multiple jobs to run sequentially
- **Pipeline Support**: Process commands through middleware pipelines
- **Queue Integration**: Seamless integration with queue systems
- **Batch Management**: Database and DynamoDB batch repositories
- **Unique Locks**: Prevent duplicate job execution with unique locks
- **Testing Support**: Comprehensive testing utilities and fakes

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

The Kasi Bus component is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🏢 About

Kasi Bus is developed and maintained by [Jetstream Labs](https://jetstreamlabs.com) as part of the Kasi Framework.

This component is a port of [illuminate/bus](https://github.com/illuminate/bus), originally created by [Taylor Otwell](https://github.com/taylorotwell) and The Laravel Team.

---

**Remember**: This repository is automatically synchronized from the main framework. All development happens in [kasiapps/framework](https://github.com/kasiapps/framework).
