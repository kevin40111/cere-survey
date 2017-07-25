## Installation

Set local repositories, Modify composer/config.js
```
{
    "config": {
        "secure-http": false
    },
    "repositories": {
        "packagist": false
    }
}
```

Require this package
```
$ composer require cere/survey
```

After updating composer, add the ServiceProvider to the providers array in config/app.php
```
'Cere\Survey\SurveyServiceProvider',
```

## Develop with workbench

```
php artisan view:publish --path workbench/cere/survey/resources/views cere/survey
php artisan asset:publish --bench="cere/survey"
```