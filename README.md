Firewall Authentication Middleware
==================================

A [Stack][0] middleware providing a simple, configurable firewall concept for
[STACK-2 Authentication][1] compatible middlewares.


Installation
------------

Through [Composer][2] as [dflydev/stack-firewall][3].


Usage
-----

The Firewall middleware is a thin layer over [dflydev/stack-authentication][4]
based STACK-2 Authentication middlewares.

A **firewall** is defined as an array of associatve arrays representing paths
for which an authentication middleware should be concerned.

If a requested path does not match a firewalled path, the firewall delegates the
request to the next layer immediately.

If a requested path matches and authentication is missing or invalid and
anonymous requests are allowed, the request is allowed through the firewall
without setting the `stack.authn.token`.

If a requested path matches and authentication is missing or inavlid and
anonymous requests are NOT allowed, the firewall will challenge immediately.

If no firewall is defined, the assumed configuration is:

    [['path' => '/']]

This effectively means that by default the firewall will match all requests
and will not allow anonymous requests resulting in returning a challenge.


```php
<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// The firewall is an array of associative arrays containing the rules for which
// the authentication middleware should be concerned.
$firewall = [
    ['path' => '/', 'anonymous' => true],
    ['path' => '/protected'],
];

$challenge = function (Response $response) {
    // Assumptions that can be made:
    // * 401 status code
    // * WWW-Authenticate header with a value of "Stack"
    //
    // Expectations:
    // * MAY set WWW-Authenticate header to another value
    // * MAY return a brand new response (does not have to be
    //   the original response)
    // * MUST return a response
    return $response;
};

$authenticate = function (HttpKernelInterface $app, $anonymous) {
    // Assumptions that can be made:
    // * The $app can be delegated to at any time
    // * The anonymous boolean indicates whether or not we
    //   SHOULD allow anonymous requests through or if we
    //   should challenge immediately.
    // * Additional state, like $request, $type, and $catch
    //   should be passed via use statement if they are needed.
    //
    // Expectations:
    // * SHOULD set 'stack.authn.token' attribute on the request
    //   when authentication is successful.
    // * MAY delegate to the passed $app
    // * MAY return a custom response of any status (for example
    //   returning a 302 or 400 status response is allowed)
    // * MUST return a response
};

$app = new Firewall($app, [
    'challenge' => $challenge,
    'authenticate' => $authenticate,
    'firewall' => $firewall,
]);
```


License
-------

MIT, see LICENSE.


Community
---------

If you have questions or want to help out, join us in the **#stackphp** or
**#dflydev** channels on **irc.freenode.net**.


[0]: http://stackphp.com/
[1]: http://stackphp.com/specs/STACK-2/
[2]: http://getcomposer.org
[3]: https://packagist.org/packages/dflydev/stack-firewall
