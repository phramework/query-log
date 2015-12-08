# query-log
query-log environment for phramework

## Usage
(**NOTE** this will work when the repo will become public!)

```bash
composer require phramework/query-log
```

For example:

```php
<?php
use \Phramework\Phramework;
use \Phramework\SystemLog;

$settings = [
    'query-log' => [
        'adapter' => '\\Phramework\\Database\\PostgreSQL'
    ]
];

$phramework = new Phramework(
    $settings,
    new \Phramework\URIStrategy\URITemplate([])
);


$phramework->invoke();
```

## Development
### Install

```bash
composer update
```

### Test and lint code

```bash
composer lint
composer test
```

# License
Copyright 2015 Xenofon Spafaridis

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

```
http://www.apache.org/licenses/LICENSE-2.0
```

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
