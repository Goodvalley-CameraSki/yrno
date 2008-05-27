<?php
/**
 * text.php renders a yr.no text forecast in XHTML
 *
 * @author       Conrad Helgeland
 * @contributor  yr.no (XHTML and CSS)
 * @license
 * @id
 * @link
 *
 */

/**
 * URI for forecast.xml / varsel.xml
 *
 * Change the HTTP URI below to your desired forecast location
 */
$uri = "http://www.yr.no/sted/Norge/Troms/Tromsø/Tromsø/varsel.xml";

// The following line lets you specify forecast location via GET parameter "uri", e.g.:
// http://example.com/text.php?uri=http://www.yr.no/place/Norway/Oslo/Oslo/Oslo/forecast.xml
$uri = (empty($_GET["uri"]) === false) ? $_GET["uri"] :  $uri ;

try {
    if (strpos($uri, "http://www.yr.no") === false || strpos($uri, ".xml") === false) {
        throw new RuntimeException("Forecast URI '$uri' is invalid");

    }
    libxml_use_internal_errors(true);
    var_dump($uri);
    $sx =  new SimpleXMLElement($uri, LIBXML_NOERROR, true);

    date_default_timezone_set( (string) $sx->location->timezone["id"]);

    // Get all text forecasts
    $texts = $sx->xpath("/weatherdata/forecast/text/location/time");

    if ($texts === false || count($texts) === 0) {
        throw new RuntimeException("Found no text forecast for ".$sx->location->name);
    }

    /**
     * Start output
     **/
    $location = (string) $sx->forecast->text->location["name"];
    header("Content-Type: text/html; charset=utf-8");
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .PHP_EOL; ?>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>Tekstvarsel for <?php echo $location; ?> fra yr.no</title>
      <link href="http://www12.nrk.no/yr.no/yr-php.css" rel="stylesheet" type="text/css" />
  </head>
<body>
<div id="yr-varsel">
  <h2><a href="<?php echo dirname($uri).'/" target="_top">Meteorologens varsel for '. $location .'';?></a></h2>
    <?php
    // Loop all text forecasts
    foreach($texts as $text) {
        // Create human-readable dates
        $from = date("d.m", strtotime($text["from"])); // "25.05"
        $to   = date("d.m", strtotime($text["to"]));   // "01.06"
        $year = date("Y", strtotime($text["to"]));     // "2008"

        $from_time = date("H", strtotime($text["from"])); // "00"
        $to_time = date("H", strtotime($text["to"]));     // "14"
        $fromto = ($from != $to) ? "$from kl. $from_time tom. $to.$year kl. $to_time" : "for $from til $to_time" ;

        // Adjust "to" date at 00 (midnight) to the previous day at 24
        if ($to_time == "00") {
            $day_before_to = mktime(0, 0, 0, date("m", strtotime($text["to"])), date("d", strtotime($text["to"]))-1,   date("Y", strtotime($text["to"])));
            $to = date("d.m", $day_before_to);
            $to_time = "24";
            $fromto = ($from != $to) ? "$from kl. $from_time til midnatt $to.$year" : "til midnatt $from" ;

        }
        echo "<p><strong>{$text->title}</strong>: {$text->body}<br/>(Gjelder fra $fromto.)</p>";
        echo "<!-- from ". $text["from"] ." to ". $text["to"] ."-->";
    }

    ?>
  <p>Værvarsel fra <a href="http://www.yr.no/" target="_top">yr.no</a> er levert av Meteorologisk institutt og NRK.</p>
</div>
</body>
</html>
<?php
    /**
     * Error handling
    */
} catch (Exception $e) {

    echo "<h1>Teknisk feil</h1>";
    echo "<p><strong>". get_class($e) ."</strong>: ".$e->getMessage() ."</p>";

    $libxmlerrors = libxml_get_errors();
    echo "<pre>";
    foreach ($libxmlerrors as $err) {
        echo $err->message;
    }
    echo "</pre>";
}
