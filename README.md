salesmachine-php: The official SalesMachine.IO PHP Client Library
====================================================================

salesmachine-php is a library for sending user related data to **SalesMachine.IO**.
The library can be used right away without a complicated setup as shown in the examples below.

When using this library in a high traffic production environment you can use different options to optimize the behavior. See the chapter *Usage in Production Environments* and *Options* for more details.

## Requirements
First off, you need to have an account at SalesMachine.IO and be in possession of valid API credentials.
The requirements regarding your PHP environment are the following
* PHP 5.x 
* CURL module
* JSON module

## Quick Guide and Code Examples
### Init Salesmachine and Identify User
An API token is issued each time you create an application inside your SalesMachine.IO interface. You need to init the Salesmachine class just once, all following function calls will use the provided credentials.

    require_once('Salesmachine.php');
    Salesmachine::init($api_key, $api_secret);    

### Identify a User

    Salesmachine::identify($your_unique_user_id);

### Create or Update a User

    Salesmachine::set(array('name' => 'John Doe', 'email' => 'john@doe.com'));

### Track Pageview of a User
    Salesmachine::pageview('/dashboard');

### Tack Events of a User
    Salesmachine::event('your event name');

### Create or Update a Data Element
Elements are arrays which belong to a certain set of data. An element can also be associated to a user by adding user_id to the parameters.

    Salesmachine::element($unique_id, $dataset, array('a_key' => 'A value', 'another_key' => 'another value'));

## Using salesmachine-php in Production Environments
While the default settings get you going right away, it is recommend to change some settings for production environments.

By default, all requests to SalesMachine.IO are directly executed with CURL. On a high traffic environment this can eventually lead to reduced performance on the host site. 
It is therefore recommended to enable the option "use_buffer" which will buffer all requests to a local file.

The local buffer can then be sent with a cron job in regular intervals by using the code below. This method ensures that the SalesMachine.IO library won't degrade the performance of the host environment.
  
### Store a requests in local buffer

    require_once('Salesmachine.php');
    Salesmachine::init($api_key, $api_secret, array('use_buffer' => true, 'prod_env' => true));    

    Salesmachine::identify($your_unique_user_id);
    Salesmachine::set(array('name' => 'John Doe', 'email' => 'john@doe.com'));
    Salesmachine::pageview('/dashboard');
    ...

### Process local buffer in cron job

    require_once('Salesmachine.php');
    Salesmachine::init($api_key, $api_secret);    
    Salesmachine::send_buffer();

## Options
When calling Salesmachine::init($api_key, $api_secret) an array of options can be passed as a third parameter. 
If this parameter is not present or option keys are missing, the default values are taken. While the default values will get you going right away it is recommend to adjust the settings based on environment in which you're using the SalesMachine.IO library

|Option | Default | Description
|:------------:|:-------------:| ----- |
|use_https | true | Whether or not to send the data to SalesMachine SSL encrypted
|use_buffer | false | If set to true, requests will be written to a local file instead of sent directly to SalesMachine.io. The buffer of stored requests can then later be sent by a cron job.
|log_dir | /logs/ |A writeable directory where the buffer and the debug file can be stored. Trailing slash is mandatory.
|log_file_buffer | salesmachine_buffer.log | The file which will be used for the local buffer of requests.
|log_file_debug | salesmachine_debug.log | The log file in which can be used for debugging.
|debug | false | If activated, debug information will be written to the log file
|prod_env |false | If set to true, errors will be silently routed to the default stderr error file
|epoch | null | By default, time() will be used as the time of the request. It's also possible to provide a different unix timestamp for the request.

## Additional Information

For more information please visit 

* http://salesmachine.io
* https://github.com/salesmachine-io/salesmachine-php



