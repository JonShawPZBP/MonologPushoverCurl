<?php

use MonologPushoverCurl\PushoverCurlHandler;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Processor\IntrospectionProcessor;

require __DIR__ . '/vendor/autoload.php';

$logger = new Logger('logger');

$logger->pushProcessor(new IntrospectionProcessor);

$handler = new PushoverCurlHandler('API_TOKEN','USER_ID','Example Title',null,Level::Critical);
$logger->pushHandler($handler);

$logger->critical('Error has occured!');