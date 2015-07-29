twig-lint - Standalone twig linter
==================================

twig-lint is a lint tool for your twig files.

It can be useful to integrate in your ci setup or as the basis of editor plugins (e.g. [syntastic](https://github.com/scrooloose/syntastic) for Vim).

[![Build Status](https://secure.travis-ci.org/asm89/twig-lint.png?branch=master)](http://travis-ci.org/asm89/twig-lint)

Installation / Usage
--------------------

### As standalone executable

Download the [`twig-lint.phar`](https://asm89.github.io/d/twig-lint.phar) executable. Or as a global composer dependency:

```bash
composer global require "asm89/twig-lint" "@stable"
```

Run `php twig-lint.phar lint <file>` or `~/.composer/vendor/bin/twig-lint lint <file>`.

### As a dev dependency

Add the following to your `composer.json`:

```json
{
    "require-dev": {
        "asm89/twig-lint": "*"
    }
}
```

Run `./bin/twig-lint lint <file>`.

Authors
-------

Alexander <iam.asm89@gmail.com><br />
Marc Weistroff <marc.weistroff@sensiolabs.com> (creator of the original `twig:lint` command in the symfony framework)

License
-------

- twig-lint is licensed under the MIT License - see the LICENSE file for details
- I am providing code in this repository to you under an open source license. Because this is my personal repository, the license you receive to my code is from me and not from my employer (Facebook).
