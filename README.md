# cakephp-tokens

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://travis-ci.org/mirko-pagliai/cakephp-tokens.svg?branch=master)](https://travis-ci.org/mirko-pagliai/cakephp-tokens)
[![Build status](https://ci.appveyor.com/api/projects/status/03gdahoap22rbkkh?svg=true)](https://ci.appveyor.com/project/mirko-pagliai/cakephp-tokens)
[![Coverage Status](https://img.shields.io/codecov/c/github/mirko-pagliai/cakephp-tokens.svg?style=flat-square)](https://codecov.io/github/mirko-pagliai/cakephp-tokens)
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
