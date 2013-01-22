twig-lint - Standalone twig linter
==================================

twig-lint is a lint tool for your twig files.

It can be useful to integrate in your ci setup or as the basis of editor plugins (e.g. [syntastic](https://github.com/scrooloose/syntastic) for Vim).

Installation / Usage
--------------------

1. Download the [`twig-lint.phar`](http://asm89.github.com/d/twig-lint.phar) executable. Or require the package in your `composer.json`:

    ``` json
    {
        "require": {
            "asm89/twig-lint": "*"
        }
    }
    ```

2. Run `php twig-lint.phar lint <file>`

Authors
-------

Alexander <iam.asm89@gmail.com>
Marc Weistroff <marc.weistroff@sensiolabs.com> (creator of the original `twig:lint` command in the symfony framework)

License
-------

twig-lint is licensed under the MIT License - see the LICENSE file for details
