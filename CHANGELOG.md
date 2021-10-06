# 1.x branch
## 1.3 branch
### 1.3.3
* improvement of function descriptions and tags;
* migration to github actions.

### 1.3.2
* ready for `php` 8;
* added `phpstan`, so fixed some code.

### 1.3.1
* small fixes to standardize to `cakephp` 4.

### 1.3.0
* updated for `cakephp` 4 and `phpunit` 8.

## 1.2 branch
### 1.2.6
* little fixes.

### 1.2.5
* updated for `me-tools` 2.18.11;
* added tests for lower dependencies.

### 1.2.4
* little fixes.

### 1.2.3
* now you can set the `Users` class options (`className` and `foreignKey`)
    using the `usersClassOptions` key of the `TokensTable` configuration;
* small code fixes;
* requires `me-tools` package for dev;
* updated for `php-tools` 1.1.12.

### 1.2.2
* `TokenTrait::find()`  method is now public;
* updated for CakePHP 3.7.

### 1.2.1
* updated again for CakePHP 3.6.

### 1.2.0
* updated for CakePHP 3.6;
* now it uses the `mirko-pagliai/php-tools` package. This also replaces
    `mirko-pagliai/reflection`.

## 1.1 branch
### 1.1.1
* some changes to be ready for CakePHP 3.6;
* added config for AppVeyor tests.

### 1.1.0
* updated for CakePHP 3.5.

## 1.0 branch
### 1.0.4
* added initial schema of the plugin database;
* the MIT license has been applied.

### 1.0.3
* fixed bug on `deleteExpired()` and `findExpired()` methods.

### 1.0.2
* the `TokenTrait::create()` method throws an exception on failure.

### 1.0.1
* methods that have been deprecated with CakePHP 3.4 have been replaced;
* updated for CakePHP 3.4.

### 1.0.0
* first release.
