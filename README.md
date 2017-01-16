# LazySession

This package allows you to use sessions without having to worry about
whether you have properly called `session_start()` or not.

Instead of using `session_start()` on every request, regardless or whether
said request will actually need sessions or not, this class will automatically
start the session when you try to access it's data in any way. By avoiding
`session_start()` when a request does not need it you improve the usage of
your server's resources, especially since the default method for storing
sessions is using the filesystem, which can be relatively slow.
 
The interface is very similar to the one used natively by PHP, and allows
you to get, create, modify or delete session data via methods, or by using
session keys either as array keys or object properties. See example below
for clarification.

Although the class provides methods for using common session functions,
for example, to change the session storage path, it is not needed that
such functions be called from the instantiated object, nor are you limited
to the methods that have been implemented to access native functions:
since the class uses the native PHP implementation for sessions, any
`session_*` function you call in your code will work nicely
with the class.

## Installation

From the command line:

`composer install alejoluc/lazysession`

Or write manually in `composer.json`:

```json
{
  "require": {
    "alejoluc/lazysession": "*"
  }
}
```

## Instantiating the class: example login usage

```php
<?php
use alejoluc\LazySession\LazySession;

$session = new LazySession();

if ($page === 'login') {
    // Accessing session data using the object oriented interface
    if ($session->get('logged-in') !== true) {
        if (user_exists($sanitized_username, $sanitized_password)) {
            // Accessing session data via array keys
            $session['logged-in'] = true;
            $session['username']  = $sanitized_username;
        }   
    } else {
        echo "You are logged in, this is your data:\n";
        // Accessing session data via object properties
        echo "username: " . $session->username;
    }   
} elseif ($page === '...') {
    // Some page that does not need session data. In this branch of the
    // execution, session_start() will never be called
}
```

## Setting, Getting and Deleting

```php
<?php
// Getting: All of the following are equivalent and valid
$value = $session->get('key');
$value = $session['key'];
$value = $session->key;

$value = $session->get('special&char');
$value = $session['special&char'];
$value = $session->{'special&char'};


// Setting: All of the following are equivalent and valid
$session->set('key', 'value');
$session['key'] = 'value';
$session->key   = 'value';

$session->set('special&char', 'value');
$session['special&char'] = 'value';
$session->{'special&char'} = 'value';

// Deleting: All of the following are equivalent and valid
$session->delete('key');
unset($session['key']);
unset($session->key);
```

## Deleting all session data

```php
$session->clear();
```