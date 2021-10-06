# cakephp-tokens

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![CI](https://github.com/mirko-pagliai/cakephp-tokens/actions/workflows/ci.yml/badge.svg)](https://github.com/mirko-pagliai/cakephp-tokens/actions/workflows/ci.yml)
[![Coverage Status](https://img.shields.io/codecov/c/github/mirko-pagliai/cakephp-tokens.svg?style=flat-square)](https://codecov.io/github/mirko-pagliai/cakephp-tokens)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/3285af154b94452ab5927747579b6bf7)](https://www.codacy.com/gh/mirko-pagliai/cakephp-tokens/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mirko-pagliai/cakephp-tokens&amp;utm_campaign=Badge_Grade)
[![CodeFactor](https://www.codefactor.io/repository/github/mirko-pagliai/cakephp-tokens/badge)](https://www.codefactor.io/repository/github/mirko-pagliai/cakephp-tokens)

*Tokens* is a CakePHP plugin to allows you to handle tokens.

Did you like this plugin? Its development requires a lot of time for me.
Please consider the possibility of making [a donation](//paypal.me/mirkopagliai): even a coffee is enough! Thank you.

[![Make a donation](https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_carte.jpg)](//paypal.me/mirkopagliai)

## Installation
You can install the plugin via composer:

```bash
$ composer require --prefer-dist mirko-pagliai/cakephp-tokens
```

Then you have to load the plugin. For more information on how to load the plugin,
please refer to the [Cookbook](//book.cakephp.org/4.0/en/plugins.html#loading-a-plugin).

Simply, you can execute the shell command to enable the plugin:
```bash
bin/cake plugin load Tokens
```
This would update your application's bootstrap method.

## Configuration
The plugin uses some configuration parameters and you can set them using the
`\Cake\Core\Configure` class, **before** loading the plugin.

For example, you can do this at the bottom of the file `APP/config/app.php`
of your application.

## Versioning
For transparency and insight into our release cycle and to maintain backward
compatibility, *Tokens* will be maintained under the
[Semantic Versioning guidelines](http://semver.org).
