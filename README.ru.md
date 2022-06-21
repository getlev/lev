# **Lev** - это настоящий мультисайт

	* `Lev` - это реакция на слабую и ограниченную реализацию мультисайта в Grav.
	* `Lev`:
		- позволяет создавать сайты Lev где угодно на диске, а не только в дире `user/env` Grav
		- является расширением Grav, причем небольшим и достаточно простым
	* `Lev` привел к строгому разделению движка (engine, host) и сайта (site), а именно:
		- host и site - `абсолютно разные сущности`, хост управляет сайтом и является сервисом для сайта.
		- хост один, а сайтов много.
		- CMS и хост - это или одно и то же, или хост является реализацией CMS.
		- сайт - это конфигуратор страниц (по правилам хоста).
		- и т.д. и т.п...

# QuickStart

## Downloading a Lev Package

You can download a **ready-built** package from the [Downloads page on https://getlev.org/Lev](https://getlev.org/Lev/downloads)

# Contributing
We appreciate any contribution to Lev, whether it is related to bugs, grammar, or simply a suggestion or improvement! Please refer to the [Contributing guide](CONTRIBUTING.md) for more guidance on this topic.

## Security issues
If you discover a possible security issue related to Grav or one of its plugins, please email the core team at contact@getgrav.org and we'll address it as soon as possible.

# License

See [LICENSE](LICENSE)
