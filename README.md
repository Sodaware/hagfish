# HAGFISH - An Ugly PHP Framework


This is Hagfish. It is ugly. 

Hagfish is not intended to be a heavyweight dolution that can handle everything, but rather a small framework that takes care of the common bits and pieces so you can concentrate on getting something built.

Hagfish provides simple routing, templating and database functionality. Everything else is up to you.


## Your first Hagfish App

```php
include 'hagfish/hagfish_core.php';

// Create a controller
$app = new HagfishController();

// Add an action
$app->addAction('default', function() { return "hello world"; });

// Dispatch
$app->dispatch();
```

That's it.


## Adding actions

Actions are added inside the controller using the following:

```php
$this->addAction('route', $handler);
```

For example, the following call "my_function" for a visit to http://myapp/hello 

```php
$this->addAction('hello', 'my_function');
```


Hagish supports the following actions:

```php
// Class => method
$this->addAction('route', array('MyClass', 'someMethod'));

// HagfishAction => method
$this->addAction('route', array('MyHagfishAction', 'someMethod'));

// Object => method
$this->addAction('route', array(&$object, 'someMethod'));

// Function name
$this->addAction('route', 'my_function');

// Closure
$this->addAction('route', function() { });

```

Using a HagfishAction is the recommended method, as it gives access to the request and template functionality. Anything returned from a function will be sent to the browser.


## Requests

Hagfish will optionally pass a HagfishRequest object to any action. Just define your actions as follows:

```php
function myAction(HagfishRequest $request) {
	// Do stuff
}
```


## Template control

First you must set up the template directory from your controller, usually in the
constructor:

```php
$this->setTemplatePath(dirname(__FILE__) . '/../templates/');
```

Then in your action, set the template you wish to use:

```php
$this->setTemplateName('default');
```

Template files should follow the pattern whatever.template.php.

To add a variable to a template, do the following in your action:

```php
$this->registerVariables(array(
	'variable'	=> $variable
));
```

$variable can then be used inside the template.

