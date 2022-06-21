# **Lev** is a true Grav multisite

	* Grav supports multisites in a special dir `user/env` only.
	* `Lev`:
		- supports Lev sites anywhere on disk, not in a `user/env` dir only
		- is a small Grav extension
		- does not affects Grav installation (except a single small fix)
		- is a site configurator, does not contain any source codes or classes
		- compatible with all Grav versions (tested up to 1.7.24)
	* `Lev` makes strict separation between host and site, namely:
		- host and site is absolutally different entities, host manages site or is a service for a site.
		- one host may control many sites.
		- CMS and host is the same, or host is CMS implementation.
		- site is a page's configurator according to some host rules.
		- and so on...

# QuickStart

## Downloading a Lev Package

You can download a **ready-built** package from the [Downloads page on https://getlev.org/lev](https://getlev.org/Lev/downloads)

# Contributing
We appreciate any contribution to Lev, whether it is related to bugs, grammar, or simply a suggestion or improvement! Please refer to the [Contributing guide](CONTRIBUTING.md) for more guidance on this topic.

## Security issues
If you discover a possible security issue related to Grav or one of its plugins, please email the core team at contact@getgrav.org and we'll address it as soon as possible.

# License

See [LICENSE](LICENSE)
