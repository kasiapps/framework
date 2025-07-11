# Kasi Queue

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kasi/queue.svg?style=flat)](https://packagist.org/packages/kasi/queue)
[![Total Downloads](https://img.shields.io/packagist/dt/kasi/queue.svg?style=flat)](https://packagist.org/packages/kasi/queue)

The Kasi Queue component provides a unified API for background job processing in PHP applications. This component allows you to defer time-consuming tasks to background workers, dramatically improving application response times and user experience.

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
composer require kasi/queue
```

## 🚀 Features

- **Multiple Drivers**: Database, Redis, Beanstalkd, Amazon SQS, and synchronous drivers
- **Job Processing**: Robust background job processing with retry mechanisms
- **Failed Job Handling**: Comprehensive failed job tracking and retry functionality
- **Queue Workers**: Efficient long-running worker processes with memory management
- **Job Batching**: Group related jobs together and track collective progress
- **Delayed Jobs**: Schedule jobs to run at specific times or after delays
- **Job Middleware**: Process jobs through middleware pipelines
- **Rate Limiting**: Control job processing rates and prevent system overload

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

The Kasi Queue component is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🏢 About

Kasi Queue is developed and maintained by [Jetstream Labs](https://jetstreamlabs.com) as part of the Kasi Framework.

This component is a port of [illuminate/queue](https://github.com/illuminate/queue), originally created by [Taylor Otwell](https://github.com/taylorotwell) and The Laravel Team.

---

**Remember**: This repository is automatically synchronized from the main framework. All development happens in [kasiapps/framework](https://github.com/kasiapps/framework).
