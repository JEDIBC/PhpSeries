# JEDIBC/PhpSeries

[![Build Status](https://travis-ci.org/JEDIBC/PhpSeries.png)](https://travis-ci.org/JEDIBC/PhpSeries)

[BetaSeries](http://www.betaseries.com) PHP Library

See [the api documentation](http://www.betaseries.com/api/docs) to see what the library can do .

## Installation

The recommended way to install the library is through [Composer](http://getcomposer.org/). Require the `jedibc/phpseries` package into your `composer.json` file:

```json
{
    "require": {
        "jedibc/phpseries": "@stable"
    }
}
```
## Usage

```php
$client = new PhpSeries\Client('<myApiKey>');

// authenticate using basic login/password to get a token
$authData = $client->post('members/auth', ['login' => 'foo', 'password' => md5('bar')]);

// Get badges list of user 1
$badges = $client->get('members/badges', ['token' => $authData['member']['token'], 'id' => 1]);

```

## License

PhpSeries is released under the MIT License. See the bundled LICENSE file for details.

PhpSeries
=========

[BetaSeries](http://www.betaseries.com) PHP Library

See [the api documentation](http://www.betaseries.com/wiki/Documentation) to see what the library can do .
