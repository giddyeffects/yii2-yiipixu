Yii2 Extension for the Apixu Weather API
========================================
Access weather and geo data via the JSON/XML RESTful Apixu API directly in your Yii2 project

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist "giddyeffects/yii2-yiipixu":"@dev"
```

or add

```
"giddyeffects/yii2-yiipixu": "@dev"
```

to the require section of your `composer.json` file.


Usage
-----
First get an Apixu API key [here](https://www.apixu.com/signup.aspx).

Once the extension is installed, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        //...
        'apixu' => [
            'class' => 'giddyeffects\yiipixu\Apixu',
            'api_key' => 'YOUR_APIXU_API_KEY',
        ],
    ],
];
```
You can now access the extension via \Yii::$app->apixu;

For more details refer to the [Apixu Documentation](https://www.apixu.com/doc/).
Example
-------
```
    $weather = \Yii::$app->apixu;
    $weather->query = 'Nairobi';
    $weather->request();
    if(!$weather->response->error){
        echo "<h1>Current Weather</h1>";
        echo "<h2>Location</h2>";
        echo "City: ". $weather->response->location->name;
        echo "<br>";
        echo "Region: ".$weather->response->location->region;
        echo "<br>";
        echo "Country: ".$weather->response->location->country;
        echo "<br>";
        echo "Lat: ".$weather->response->location->lat." , Long:".$weather->response->location->lon;
        echo "<h2>Temperature</h2>";
        echo "<br>";
        echo "Temperature (&deg;C): " . $weather->response->current->temp_c; echo "<br>";
        echo "Feels like (&deg;C)". $weather->response->current->feelslike_c;
                echo "<br>";
        echo "<br>";
        echo "Temperature (&deg;F): " . $weather->response->current->temp_f; echo "<br>";
        echo "Feels like (&deg;F)". $weather->response->current->feelslike_f;
        echo "<br>";
        echo "Condition: <img src='" . $weather->response->current->condition->icon ."'>" . $weather->response->current->condition->text;
        echo "<h2>Wind</h2>";
        echo $weather->response->current->wind_mph." mph <br>";
        echo $weather->response->current->wind_kph." kph <br>"; 
        echo $weather->response->current->wind_degree."&deg;  " . $weather->response->current->wind_dir."<br>";   
        echo "Humidity: ".$weather->response->current->humidity;
        echo "<br><br><br>";
        echo "Updated On: ".$weather->response->current->last_updated."<br/>";
    }
    else {
        echo $weather->response->error->message;
    }
    $weather->api_method = 'forecast';
    $weather->query = "Mombasa";
    $weather->days = 3;
    echo "<h1>Weather forecast for the next $weather->days days for $weather->query </h1><br/>";
    $weather->request();
    if(!$weather->response->error){
        foreach ($weather->response->forecast->forecastday as $day) {
            echo "<table>";    
                echo "<tr><td colspan='4' border='0'><h2>{$day->date}</h2> Sunrise: {$day->astro->sunrise} <br> Sunset: {$day->astro->sunset}"
                . "<br> condition: {$day->day->condition->text} <img src=' {$day->day->condition->icon}'/></td></tr>";
                echo "<tr><td>&nbsp;</td><td>Max.<br>Temperature</td><td>Min.<br>Temperature</td><td>Avg.<br>Temperature</td></tr>";
                echo "<tr><td>&deg;C</td><td>{$day->day->maxtemp_c}</td><td>{$day->day->mintemp_c}</td><td>{$day->day->avgtemp_c}</td></tr>";
                echo "<tr><td>&deg;F</td><td>{$day->day->maxtemp_f}</td><td>{$day->day->mintemp_f}</td><td>{$day->day->avgtemp_f}</td></tr>";
                echo "<tr><td><h4>Wind</h4></td><td colspan='3'>{$day->day->maxwind_mph}Mph <br> {$day->day->maxwind_kph}kph </td></tr>";
                foreach ($day->hour as $hr){
                    echo "<tr><td colspan='4' border='0'>";
                    echo "<table style='width:100%;'>";    
                    echo "<tr><td>Time</td><td>Temperature</td><td>Wind</td><td>Humidity</td></tr>";
                    echo "<tr><td><div>{$hr->time}<img src=' {$hr->condition->icon}'/></div></td><td>{$hr->temp_c}&deg;C<br>{$hr->temp_f}&deg;F</td><td>{$hr->wind_mph}Mph <br> {$hr->wind_kph}kph</td><td>$hr->humidity</td></tr>";
                    echo "</table></tr></td>";
                }
            echo "</table> <br>";
        }
    }
    else {
        echo $weather->response->error->message;
    }
```

Live Demo
---------
Go to the [Interactive API Explorer](https://www.apixu.com/api-explorer.aspx) to test the API. 