# Kasi Broadcasting

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kasi/broadcasting.svg?style=flat)](https://packagist.org/packages/kasi/broadcasting)
[![Total Downloads](https://img.shields.io/packagist/dt/kasi/broadcasting.svg?style=flat)](https://packagist.org/packages/kasi/broadcasting)

The Kasi Broadcasting component provides a comprehensive real-time event broadcasting system for PHP applications. This component enables you to broadcast events to various channels and services, supporting WebSocket connections, push notifications, and real-time communication.

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
composer require kasi/broadcasting
```

## 🚀 Features

- **Multiple Broadcasters**: Pusher, Ably, Redis, Log, and Null broadcasters
- **Channel Types**: Public, private, presence, and encrypted private channels
- **Event Broadcasting**: Queue-based and real-time event broadcasting
- **Authentication**: Channel authentication and authorization
- **Socket Management**: Socket ID tracking and user exclusion
- **Anonymous Events**: Send events without creating event classes
- **Unique Broadcasting**: Prevent duplicate broadcasts with unique locks
- **Middleware Support**: Broadcasting middleware and route handling

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

The Kasi Broadcasting component is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🏢 About

Kasi Broadcasting is developed and maintained by [Jetstream Labs](https://jetstreamlabs.com) as part of the Kasi Framework.

This component is a port of [illuminate/broadcasting](https://github.com/illuminate/broadcasting), originally created by [Taylor Otwell](https://github.com/taylorotwell) and The Laravel Team.

---

**Remember**: This repository is automatically synchronized from the main framework. All development happens in [kasiapps/framework](https://github.com/kasiapps/framework).
