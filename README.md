# Logchecker


A fork version from [OPSnet/Logchecker](https://github.com/OPSnet/Logchecker).

This repo delete `console` usage of logchecker, only left the code what I need.
And it requires PHP 5.5+, instead of PHP 7.0+ in [OPSnet/Logchecker](https://github.com/OPSnet/Logchecker).

------

A CD rip logchecker, used for analyzing the generated logs for any problems that would potentially
indicate a non-perfect rip was produced. Of course, just because a log doesn't score a perfect 100%
does not mean that the produced rip isn't bit perfect, it's just less likely. While this library can
largely run on both Linux and Windows, validating of checksums is only really supported for Linux.

While this library will analyze most parts of a log, unfortunately it cannot properly validate the checksums
for all types of logs. This is due to creators of these programs making their logchecker closed source
and involves some amount of custom mathematical work to produce it. Therefore, we have to fallback on
external methods to validate the checksums of EAC and XLD. If the logchecker detects that we do not have
the necessary programs, then we will just skip this external step and assume the checksum is valid. For
setting up the necessary programs to validate the checksum, see below for the given program you care about.

## Requirements

* PHP 5.5+

## Optional Requirements

* Python 3.5+
* [cchardet](https://github.com/PyYoshi/cChardet) (or [chardet](https://github.com/chardet/chardet))
* [eac_logchecker.py](https://github.com/OPSnet/eac_logchecker.py)
* [xld_logchecker.py](https://github.com/OPSnet/xld_logchecker.py)

```bash
pip3 install cchardet eac-logchecker xld-logchecker
```

## Usage
```php
<?php

require __DIR__ . '/vendor/autoload.php';

use OrpheusNET\Logchecker\Logchecker;

$logchecker = new Logchecker();
$logchecker->newFile('/path/to/log/file');
$logchecker->parse();
print('Ripper   : ' . $logchecker->getRipper() . "\n");
print('Version  : ' . $logchecker->getRipperVersion() . "\n");
print('Score    : ' . $logchecker->getScore() . "\n");
print('Checksum : ' . $logchecker->getChecksumState() . "\n");
print("\nDetails:\n");
foreach ($logchecker->getDetails() as $detail) {
    print("  {$detail}\n");
}
print("\nLog Text:\n\n{$logchecker->getLog()}");
```

## Building

To build your own phar, you can checkout this repository, and then
run the `bin/compile` script. To do this, run the following commands:

```bash
git clone https://github.com/OPSnet/Logchecker
cd Logchecker
composer install
php -d phar.readonly=0 bin/compile
```
