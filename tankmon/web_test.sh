#!/bin/bash

# Start a web servelet in the tankmon webroot for testing
php -S localhost:1234 &

# Fire up the default web browser and browse to the php server running in the web root
xdg-open http://localhost:1234/index.php


