# PHP Toolkit

PHP Toolkit is a standalone fork/copy of Toolkit, MIT licensed parts of [Kirby CMS](https://getkirby.com). Additionally, it also provides a single file version for usage in contexts where packagist/composer aren't available/advisable, like scripting, Alfred plugins etc.

## Installation

```bash
composer require adamkiss/toolkit
```

or download the single file build from [Github Releases](https://github.com/adamkiss/toolkit/releases)

## Version

Current release is based on [Kirby CMS 5.1.3](https://github.com/getkirby/kirby/commit/b6fd967f0744c83dedac3f424f4cd6981bcf862e).

## Changes

This is a running list of changes implemented/maintained in the fork

**November 2025**
- based on Kirby 5.1.3
- changed namespace to `Adamkiss\Toolkit`
- written a build action that:
	- includes dependencies
	- includes mocks for proprietary parts of Kirby called from toolkit (those toolkit parts won't work)
	- outputs `./dist/toolkit-{version}.php`
- modified `Adamkiss\Toolkit\A::map` to provide keys along with values to the mapping closure

## License

MIT License, originally written by Bastian Allgeier + Kirby contributors, maintained by Adam Kiss
