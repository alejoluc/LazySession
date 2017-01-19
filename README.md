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

`composer require alejoluc/lazysession`

Or write manually in `composer.json`:

```json
{
  "require": {
    "alejoluc/lazysession": "*"
  }
}
```

## Example usage: Instantiating the class and accessing session data in a crude login example

```php
<?php
require __DIR__ . '/vendor/autoload.php';
use alejoluc\LazySession\LazySession;

$session = new LazySession();

$page = isset($_GET['page']) ? $_GET['page'] : 'login';
if ($page === 'login') {
    // Accessing session data using the object oriented interface
    if ($session->get('logged-in') !== true) {
        // For clarity and space purposes, we assume that here a function
        // checking for the existence of an user with request data would be
        // called
        if (true) {
            // Accessing session data via array keys
            $session['logged-in'] = true;
            $session['username']  = 'Test_Username';
            $session['email']     = 'test@email.com';
            
            echo 'You have logged in. Please refresh the page';
        }   
    } else {
        echo "You are logged in, this is your data:<br />";
        // Accessing session data via object properties
        echo "username: " . $session->username . "<br />";
        echo "e-mail: " . $session->email;
    }   
} elseif ($page === '...') {
    // Some page that does not need session data. In this branch of the
    // execution, session_start() will never be called

    // The following code will output the integer 1, which is the value of
    // the constant PHP_SESSION_NONE. That means sessions are enabled
    // but no session has been started
    var_dump(session_status());
} elseif ($page === 'logout') {
    $session->clear();
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
<?php
// [...instantiation, code...]
$session->clear();
```

## Changing the session save path

```php
<?php
// [...instantiation, code...]
$session->savePath(__DIR__ . '/tmp/sessions/');
$session->start(); // will create a session file in ./tmp/sessions/

// The following is equivalent, and the class will behave
// as expected after it
session_save_path(__DIR__ . '/tmp/sessions/');
$session->start(); // will create a session file in ./tmp/sessions/
```