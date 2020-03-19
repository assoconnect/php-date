# The PHP AbsoluteDate object

[![Build Status](https://travis-ci.org/assoconnect/php-date.svg?branch=master)](https://travis-ci.org/assoconnect/php-date)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=assoconnect_php-date&metric=alert_status)](https://sonarcloud.io/dashboard?id=assoconnect_php-date)

## Why this object?

PHP has the `DateTime` class to handle date and time but there is no object to just handle a date.

A `DateTime` instance represents a precise point in time and embeds a `DateTimeZone` instance to format the underlying timestamp to a more readable format.

On the other hand, a date may have two meanings:
1. A period of 24h starting at 00:00:00 in the morning and ending at 23:59:59 in the evening. Actually a day may last less or more than 24 hours, for instance think about the day you advance your clock for the daylight saving time, but you get the idea: this date is actually an interval between two different `DateTime` instances. This interval changes when you travel the world: for instance a *date* starts about 8 hours later in Los Angeles than in Paris.
2. A simple reference to a day with no critical care about the timezone. Think about your birthday, you won't change it wherever you travel on Earth: you keep only the current one from the place you were born at.

This PHP `AbsoluteDate` object represents a date according to this second use case.

## Why not use the `DateTime`, then?

`DateTime` is an amazing class, and actually this `AbsoluteDate` class relies on it.

But you don't care about the time nor the timezone when you deal with this second use case.

Using `DateTime` may then lead to issues in case it is not handled properly.

It also helps to clearly identify if you are dealing with a real point in time or just a loose date.

## How to use it?

This classes exposes two ways to instanciate a `AbsoluteDate` object:
1. Using the constructor, you get a `AbsoluteDate` instance using the default date format `Y-m-d`. If none is given, then the current date in the UTC timezone is used.
2. Using `AbsoluteDate::createInTimezone(\DateTimeZone $timezone, \DateTimeInterface $datetime = null)`, you get the current date of the given `DateTime` instance in the given timezone.

The `AbsoluteDate::format(string $format)` method can help you format the date as you want. It relies on the format method of the `DateTime` class thus it supports the same formats as the PHP [date()]([https://www.php.net/manual/en/function.date.php) function.

 ## Examples
 
 ```php
<?php

// A given point in time
$datetime = new \DateTime('@0'); // 1970-01-01 00:00:00+00:00

// The date was 1970-01-01 in Paris at the Epoch time
\AbsoluteDate::createInTimezone(new \DateTimeZone('Europe/Paris'), $datetime); // 1970-01-01

// The date was still 1969-12-31 in Los Angeles at the same point in time
\AbsoluteDate::createInTimezone(new \DateTimeZone('America/Los_Angeles'), $datetime); // 1969-12-31

// You can also instanciate an AbsoluteDate by calling the constructor with an `Y-m-d` like string
new \AbsoluteDate('2020-01-01'); // eq. to this \AbsoluteDate::createInTimezone(new \DateTimeZone('UTC'), new \DateTime('2020-01-01'))

// The format is optional, but you can provide one if you need
// For example:
new \AbsoluteDate('01/01/2020', 'd/m/Y'); 
```

## Roadmap

1. Doctrine type for `AbsoluteDate`
2. Symfony normalizer for `AbsoluteDate`
3. Create the `RelativeDate` object for the first use case exposing `startsAt` and `endsAt` methods
 
