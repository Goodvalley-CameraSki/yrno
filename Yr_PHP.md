# Yr PHP library #
[Yr\_Service\_Weatherdata](http://code.google.com/p/yrno/source/browse/trunk/php/library/Yr/Service/Weatherdata.php) fetches [WeatherdataXML](WeatherdataXML.md) and makes the data available as PHP object properties.

The Yr PHP library requires [PHP](http://php.net) 5.1 or newer, PHP 4 will **not** work.
## Forecasts ##
**Iterating over future weather**
```
$yr = new Yr_Service_Weatherdata($iri);
foreach ($yr as $forecast) {
    // $forecast now corresponds to XML in /weatherdata/forecast/tabular/time/*
}
/** Example var_dump($forecast)
object(stdClass)[28]
  public 'from' => string '2008-11-17T18:00:00' (length=19)
  public 'to' => string '2008-11-18T00:00:00' (length=19)
  public 'period' => string '3' (length=1)
  public 'symbolNumber' => string '9' (length=1)
  public 'symbolName' => string 'Regn' (length=4)
  public 'precipitation' => string '3.6' (length=3)
  public 'windDirectionDeg' => string '171.1' (length=5)
  public 'windDirectionCode' => string 'S' (length=1)
  public 'windDirectionName' => string 'Sør' (length=4)
  public 'windSpeedMps' => string '5.8' (length=3)
  public 'windSpeedName' => string 'Laber bris' (length=10)
  public 'temperatureUnit' => string 'Celsius' (length=7)
  public 'temperature' => string '5' (length=1)
  public 'pressureUnit' => string 'hPa' (length=3)
  public 'pressure' => string '1014.8' (length=6)
  public 'fromDate' => string '17.11.2008' (length=10)
  public 'toDate' => string '18.11.2008' (length=10)
  public 'fromDateIso' => string '2008-11-17' (length=10)
  public 'toDateIso' => string '2008-11-18' (length=10)
  public 'fromHour' => string '18' (length=2)
  public 'toHour' => string '00' (length=2)
  public 'hourInterval' => string '18-00' (length=5)
  public 'precipitationText' => string '3.6 mm' (length=6)
  public 'symbol' => string '9' (length=1)
  public 'symbolImage' => string '09.png' (length=6)
  public 'symbolUri' => string 'http://fil.nrk.no/yr/grafikk/sym/b38/09.png' (length=43)
  public 'temperatureText' => string '5&deg;' (length=6)
  public 'windDirection' => float 171.1
  public 'windDirectionUnit' => string '°' (length=2)
  public 'windSpeed' => float 5.8
  public 'windSpeedUnit' => string 'm/s' (length=3)
*/
```

## Text forecast ##
**Text forecast (mostly Norwegian locations)
```
foreach($yr->text as $text) {
  echo "<p><strong>{$text->title}</strong>: {$text->body}</p>";
```
## Links ##**

**Getting link data**
```
<?php
  $link = $yr->link();
  <p class="yr-lenker">
    <a href="<?php echo $link->overview; ?>" >Overview</a>
    <a href="<?php echo $link->hourByHour; ?>" >Hour by hour</a>
    <?php if (isset($link->radar)) { echo '<a href="'. $link->radar .'" >Radar</a>'; } ?>
    <a href="<?php echo $link->weekend; ?>" >Weekend</a>
    <a href="<?php echo $link->longTermForecast; ?>" >Long term</a>
  </p>
```
## Location data ##