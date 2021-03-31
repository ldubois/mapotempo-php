# Mapotempo for PHP

Basic SDK to deal with mapotempo records

## Installation

Tell composer to require this bundle by running:

``` bash
composer require ldubois/php-mapotempo
```

## Usage

```php
$apikey   = "API_KEY"; // Your Api key
$url  = "URL"; // Your url application mapotempo

$mapotempo = new Mapotempo($url, $apikey);

$records = $mapotempo->findRecords($table);
```


## License

Fork : This library is under the MIT license. [See the complete license](https://github.com/ldubois/php-mapotempo/blob/master/LICENSE).

