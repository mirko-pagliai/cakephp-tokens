# Tokens plugin

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://travis-ci.org/mirko-pagliai/cakephp-tokens.svg?branch=master)](https://travis-ci.org/mirko-pagliai/cakephp-tokens)
[![Build status](https://ci.appveyor.com/api/projects/status/03gdahoap22rbkkh?svg=true)](https://ci.appveyor.com/project/mirko-pagliai/cakephp-tokens)
[![Coverage Status](https://img.shields.io/codecov/c/github/mirko-pagliai/cakephp-tokens.svg?style=flat-square)](https://codecov.io/github/mirko-pagliai/cakephp-tokens)

*Tokens* is a CakePHP plugin to allows you to handle tokens.

## Installation
You can install the plugin via composer:

    $ composer require --prefer-dist mirko-pagliai/cakephp-tokens
    
Then you have to edit `APP/config/bootstrap.php` to load the plugin:

    Plugin::load('Tokens', ['bootstrap' => true]);

For more information on how to load the plugin, please refer to the 
[Cookbook](http://book.cakephp.org/3.0/en/plugins.html#loading-a-plugin).

## Configuration
The plugin uses some configuration parameters and you can set them using the 
`\Cake\Core\Configure` class, **before** loading 
the plugin.

For example, you can do this at the bottom of the file `APP/config/app.php`
of your application.

## Versioning
For transparency and insight into our release cycle and to maintain backward 
compatibility, *Assets* will be maintained under the 
[Semantic Versioning guidelines](http://semver.org).
