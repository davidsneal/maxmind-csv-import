MaxMind CSV Import
==================

An automated import from MaxMind's GeoIP Legacy Country CSV, from CSV to MySQL, inspired by [Vincent de Lau](http://vincent.delau.nl/artikelen/geoip_howto.html)

Screenshots
--------------

Initial welcome screen

![Image](/assets/img/import-ready.png)

Progress window

![Image](/assets/img/import-complete.png)


Setup
--------------
Place the repository files in the root folder of a local environment.


Change the __$config__ settings in _/inc/import_csv.php_ to match your requirements.

```php
// config settings for the import
public $config = array(
						'host' 		=> 'localhost',
						'user' 		=> 'root',
						'password' 	=> '',
						'database' 	=> 'geoip',
						'file' 		=> '/pathto/csv.csv',
					);
```