SalesMachine.IO PHP Client Library
===================

salesmachine-php is a library which allows you to easily send data from your PHP aplication to **SalesMachine.IO**. The library can be used right away without a complicated setup as shown in the examples below.

When using this library in a high traffic production environment you can use different options to optimize the behavior. See the chapter *Usage in Production Environments* and *Options* for more details.

## Requirements
First off, you need to have an account at SalesMachine.IO and be in possession of valid API credentials.
The requirements regarding your PHP environment are quite basic:
* PHP 5.x
* CURL module
* JSON module

## Quick Guide and Code Examples
### Installation
The salesmachine-php client library comes as a [Composer package](https://getcomposer.org/) and implements the PSR-0 autoload definition. This way it should be straight forward to use the library in the most modern PHP applications. All you need to do is to add the *salesmachine/salesmachine-php : "dev-master"* to the composer.json file of your project and update your dependencies with the command "composer update".

If you do not use the Composer package manager or PSR-0 autoloading in your project, just include the library file with a simple *require('Salesmachine.php');* and you should be ready to go.

### Init Salesmachine
salesmachine-php is a static class which makes the usage very easy. Once initialized, the class and its methods can be called at different places without re-initializing. For a quick start you can use the default values and initialize the class as followed:

    use \SalesmachinePhp\Salesmachine as Salesmachine;
    Salesmachine::init($api_token, $api_secret);

For an usage in a production environment you might want to tweak some parameters. This can be done by adding an array with parameters while initiliazing the class. A list of all options is described later in this document. An example call with parameters would look this:

    Salesmachine::init($api_key, $api_secret, array('use_buffer' => true));

### Create or Update a Contact
    Salesmachine::set_contact($unique_contact_id, array('name' => 'John Doe', 'email' => 'john@doe.com'));

### Track Pageview of a Contact
    Salesmachine::track_pageview($unique_contact_id, array('/dashboard'));

### Track Events of a Contact
    Salesmachine::track_event($unique_contact_id, 'your event name');

## Using salesmachine-php in Production Environments
While the default settings get you going right away, it is recommend to change some settings for production environments.

By default, all requests to SalesMachine.IO are with CURL in real time. On a high traffic environment this can eventually lead to reduced performance on the host site. It is therefore recommended to enable the option "use_buffer" which will buffer all requests to a local file first.

The local buffer can then be sent with a cron job in regular intervals by using the code below. This method ensures that the SalesMachine.IO library won't degrade the performance of the host environment.

### Store a requests in local buffer

    Salesmachine::init($api_key, $api_secret, array('use_buffer' => true));
    Salesmachine::set_contact($unique_contact_id, array('name' => 'John Doe', 'email' => 'john@doe.com'));
    ...

### Process local buffer in cron job

    Salesmachine::init($api_key, $api_secret);
    Salesmachine::flush();

## Options
When calling Salesmachine::init($api_key, $api_secret) an array of options can be passed as a third parameter.
If this parameter is not present or option keys are missing, the default values are taken.

|Option | Default | Description
|:------------:|:-------------:| ----- |
|use_https | true | Whether or not to send the data to SalesMachine SSL encrypted
|use_buffer | false | If set to true, requests will be written to a local file instead of sent directly to SalesMachine.io. The buffer of stored requests can then later be sent by a cron job.
|debug | false | If activated, debug information will be written to the log file

## Additional Information

For more information please visit

* http://salesmachine.io
* https://github.com/salesmachine-io/salesmachine-php


