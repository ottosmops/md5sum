# Create and verify m5sum files

[![Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)
[![Latest Stable Version](https://img.shields.io/badge/Version-stable-blue.svg?format=flat-square)](https://packagist.org/packages/ottosmops/md5sum)
[![Build Status](https://img.shields.io/travis/ottosmops/md5sum/master.svg?style=flat-square)](https://travis-ci.org/ottosmops/md5sum)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/248db8b3-4969-48c5-9a61-9c7346832ff0/mini.png)](https://insight.sensiolabs.com/projects/248db8b3-4969-48c5-9a61-9c7346832ff0)
[![Packagist Downloads](https://img.shields.io/packagist/dt/ottosmops/md5sum.svg?style=flat-square)](https://packagist.org/packages/ottosmops/md5sum)

** This package is not maintained any longer. Me, I use the ottosmops/hash package instead **

## Installation

```bash
composer require ottosmops/md5sum
```

## Usage
```php
use Ottosmops\Md5sum\Md5sum;

$md5 = New Md5sum();
$md5->createMd5sums($dir);
if (!$md5->verifyMd5sums($dir . 'md5sums')) {
    print_r($this->messages);
} else {
    echo  sprintf('All files in %s have correct checksums ', $md5->md5sums); 
}
```

You can pass a filename to the ```createMd5sums``` method. The filename must be a path relative to the dir. With the third parameter you can switch off the recursive directory iterator. No subdirectories will be scanned:

```php
$md5 = New Md5sums();
$md5->createMd5sums($dir, "myfilename", false);
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
