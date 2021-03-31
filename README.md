# Mapotempo for PHP

Basic SDK to deal with mapotempo records

## Installation

Tell composer to require this bundle by running:

```bash
composer require ldubois/php-mapotempo
```

## Usage

```php
$apikey   = "API_KEY"; // Your Api key
$url  = "URL"; // Your url application mapotempo
$table ="TABLE"; // A table to interact

$mapotempo = new Mapotempo($url, $apikey);

$records = $mapotempo->findRecords($table);
```

## Exemple

````php


    $mapotempo = new Mapotempo($url, $apikey);
    $fields =
            [
                "ref" => "TEST",
                "name" => "new client",
                "street" => "12 avenue Thiers",
                "postalcode" => 33100,
                "city" => "Bordeaux",
                "country" => "France",
                "detail" => "2ème étage",
                "tag_ids" => [],
                "visits" => []
            ];
    $criteria = ["ref" => $fields["ref"]];
    $des = $mapotempo->createTableManipulator('destinations');

    if ($des->containsRecord($criteria)) {
        
        $des->updateRecord($criteria, $fields);
    } else {

        $des->createRecord($fields);
    }
```

## License

Fork : This library is under the MIT license. [See the complete license](https://github.com/ldubois/php-mapotempo/blob/master/LICENSE).

````
