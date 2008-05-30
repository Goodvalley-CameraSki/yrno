<?php

/**
 * PHP 5.1
 * http://api.yr.no/weatherapi/locationforecast/1.4/?lat=60.10;lon=9.58
 *http://api.yr.no/weatherapi/locationforecast/1.4/schema
 */


/** Yr_SimpleXml */
require_once "Yr/SimpleXml.php";

/**
 *
 *
 */
class Yr_Service_Weatherdata implements IteratorAggregate
{

    protected $_config = array(
        "country"            => "Norge",
        "dateFormat"         => "d.m.Y",
        "hourFormat"         => "H", // Whole hour; two digits; [00, 01, 02, 03 .. 23]
        "lang"               => "nb",
        "mode"               => "prod",
        "symbolLocation"     => "http://fil.nrk.no/yr/grafikk/sym/b38",
        "timeFormat"         => "H:i",
        "celsiusCharacter"   => "°", // The Celsius Unicode character is U+2103 ℃
    );


    protected $_imported = false;

    protected $_i18n = array();

    protected $_location;

    protected $_link = array();

    protected $_sx;

    protected $_tabular = array();

    protected $_text = array();

    protected $_textFrom;

    protected $_textTo;

    protected $_textLocationName;

    protected $_uri; // forecast URI (http location of varsel.xml)

    protected $_xslUri;


    public function __construct($uri = null, $config = null)
    {

        if ($uri !== null) {
            $this->setUri($uri);
            $this->import($uri);
        }
        //setConfig()

    }

    public function __get($key)
    {
        switch ($key) {

            case "links":
            case "link":
                return $this->link();
                break;

            case "location":
                return $this->location();
                break;

            case "text":
                return $this->text();
                break;

            case "forecast":
            case "tabular":
                return $this->forecast();
                break;

            case "textFrom":
                return $this->_textFrom;
                break;

            case "textLocationName":
                return $this->_textLocationName;
                break;

            case "textTo":
                return $this->_textTo;
                break;

            case "_uri":
            case "uri":
                return $this->getUri();
                break;

            default:
                throw new UnexpectedValueException(__CLASS__."; unknown __get() key '$key'");
                break;
        }
    }

    public function checkUri($uri = null, $errMessage = "Mangler URI til datakilde")
    {
        $uri = ($uri === null) ? $this->getUri() : $uri ;
        if  (empty($uri)) {
            require_once "Yr/Exception.php";
            throw new Yr_Exception($errMessage);
        }
        return true;
    }

    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->_config;
        } else if (isset($this->_config[$key])) {
            return $this->_config[$key];
        } else {
            throw new UnexpectedValueException("Unknown configuration key '$key'");
        }
    }

    public function getForecast()
    {
        return $this->forecast();
    }


    /**
     * Method implements interface IteratorAggregate
     *
     * Allows object iteration of the tabular forecast data
     * <code>
     * $yr = new Yr_Service_Weatherdata($uri);
     *    foreach ($yr as $period) {
     *      // do stuff
     * }
     * </code>
     *
     *
     */
    public function getIterator()
    {
        return new ArrayObject($this->forecast());
    }

    public function getLocation()
    {
        return $this->location();
    }

    public function getLink($id = null)
    {
        return $this->link($id);
    }


    public function getSimpleXml()
    {
        return $this->_sx;
    }

        /*    echo "<p><strong>Minimum og maksimum</strong></p>";
    $temp = $yr->getTimeseries("temperature");
    $press =($yr->getTimeseries("pressure"));
    $wind = $yr->getTimeseries("windSpeed");
    $prec = $yr->getTimeseries("precipitation");

    echo "<p>Temperatur. Fra ". min($temp). "° - ". max($temp). "°.</p>";
    echo "<p>Vind. Fra ". min($wind). " m/s - ". max($wind). " m/s.</p>";
    echo "<p>Lufttrykk. Fra ". min($press). " hPa - ". max($press). " hPa.</p>";
    echo "<p>Nedbør. Fra ". min($prec). " mm - ". max($prec). " mm.</p>";
    */
    public function getTimeseries($key)
    {
        $t = array();
        foreach ($this as $f) {
            //todo isset;
            $t[$f->from] = $f->$key;
        }
        return $t;
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function getXslUri()
    {
        if (empty($this->_xslUri)) {
            $xsl = dirname(__FILE__)."/_/weatherdata_atom.xsl";
            $this->_xslUri= $xsl;
        }
        return $this->_xslUri;
    }


    public function forecast($offset = null)
    {
        if (count($this->_tabular) === 0) {
            throw new UnexpectedValueException("No data has been imported");
        }

        if (is_int($offset) && isset($this->_tabular[$offset])) {
            return $this->_tabular[$offset];
        } else {
            return $this->_tabular;
        }
    }

    public function imageForSymbol($symbolNumber, $fromHour = 14, $toHour = 20)
    {
        $file = str_pad($symbolNumber, 2, "0", STR_PAD_LEFT);

        if ((int) $symbolNumber <= 8 && $symbolNumber != 4) {
            $file .= ( ($fromHour >= 00 || $fromHour == 24) && ($fromHour <= 6 && $toHour <= 6) ) ? "n" : "d";
        }
        $file .= ".png";
        return $file;
    }

    public function import($uri)
    {
        // $this->fetch($uri);
        // Expires: Mon, 12 May 2008 11:28:08 GMT
        // Last-Modified: Mon, 12 May 2008 11:27:08 GMT
        //Cache-Control: public, max-age=60

        $this->setUri($uri);
        try {
            $this->_sx = Yr_SimpleXml::factory($uri);

            $this->_importLocation();
            $this->_importLinks();
            $this->_importText();
            $this->_importTabular();

        } catch (Exception $e) {
            require_once "Yr/Exception.php";
            Yr_Exception::setMode($this->getConfig("mode"));
            Yr_Exception::handle($e);
        }
    }

    public function lang()
    {
        $path = parse_url($this->getUri(), PHP_URL_PATH);
        $lang = "nb";
        if (strpos($path, "stad") === 1) {
            $lang ="nn";
        } else if (strpos($path, "place") === 1) {
            $lang = "en";
        }
        return $lang;
    }

    public function location()
    {
        return $this->_location;
    }

    public function link($id = null)
    {
        if ($id === null) {
            return $this->_link;
        } else if (isset($this->_link[$id])) {
            return $this->_link[$id];
        } else {
            throw new RuntimeException("Unknown link id '$id'");
        }

    }


    public function setConfig(Array $config)
    {
        $this->_config = array_merge($config);
    }

    public function setUri($uri)
    {
        $this->_uri = $uri;
    }

    public function setXslUri($name)
    {
        $this->_xslUri = $name;
    }

    public function transform($xsl = null)
    {
        if ($xsl === null) {
            $xsl = $this->getXslUri();
        }
        $xml = $this->getSimpleXml()->saveXML();

        require_once "Yr/XsltProcessor.php";
        $proc = new Yr_XsltProcessor($xml, $xsl);
        return $proc->transform();
    }

    public function text()
    {
        return $this->_text;
    }

    public function xpath($xp)
    {
        return $this->_sx->xpath($xp);
    }

    /**
     *   <location>
     *    <name>Tromsø</name>
     *    <type>By</type>
     *    <country>Norge</country>
     *    <timezone id="Europe/Oslo" utcoffsetMinutes="120" />
     *  </location>
     *
     *
     *
     *
     **/
    protected function _importLocation()
    {
        $l = new stdClass;
        $location = $this->_sx->location;
        $l->name = (string) $location->name;
        $l->type = (string) $location->type;
        $l->timezone = (string) $location->timezone["id"];
        $l->timezoneUtcOffsetMinutes = (int) $location->timezone["utcoffsetMinutes"];

        $this->_location = $l;
        // set the default timezone to use. Available since PHP 5.1
        date_default_timezone_set($l->timezone);
    }

    protected function _importTabular()
    {

        $res = (array) $this->_sx->xpath("/weatherdata/forecast/tabular/time");

        foreach ($res as $time) {
            $period = $this->_objectFromForecastTime($time);
            $this->_tabular[] = $period;
        }

    }

    protected function _importLinks()
    {
        $res = (array) $this->_sx->xpath("/weatherdata/links/link[@id]");
        foreach ($res as $link) {
            $id = (string) $link["id"];
            $this->_link[$id] = (string) $link["url"];
        }
    }

    protected function _importText()
    {
        $res = (array) $this->_sx->xpath("/weatherdata/forecast/text/location/time");
        $textLocationName = (string) $this->_sx->forecast->text->location["name"];
        // id?

        $this->_textFrom = (string) $this->_sx->forecast->text->location->time[0]["from"];

        $this->_textLocationName = $textLocationName;

        foreach ($res as $time) {
            $object = $this->_objectFromSxe($time);
            $this->_text[] = $object;
        }
        $this->_textTo = $object->to;
    }

    protected function _objectFromSxe(SimpleXMLElement $sx)
    {
        $object = new stdClass;

        $attrSx = $sx->attributes();
        if (count($attrSx) > 0) {
            foreach ($attrSx as $aname => $avalue) {
                // Set property
                $object->$aname = (string) $avalue;
            }
        }

        // Loop all subelements of XML element
        foreach ($sx as $name => $elmt) {
            // text content
            $elmtText = (string) $elmt;
            if (empty($elmtText) === false) {
                $object->{$name} = $elmtText;
            }

            // attributes
            $attrs = $elmt->attributes();
            if (count($attrs) > 0) {
                // Loop alle attributes of each subelement
                foreach ($attrs as $aname => $avalue) {
                    // Create object property name
                    $suffix = ($aname !== "value") ? ucfirst($aname) : "";
                    $prop = $name . $suffix;
                    // Set property
                    $object->$prop = (string) $avalue;
                }

            }
        }
        return $object;

    }


        /**
     * Create object of a "<time>" piece of weatherdata XML
     *
     *       <time from="2008-05-08T18:00:00" to="2008-05-09T00:00:00" period="3">
        <!-- Valid from 2008-05-08T18:00:00 to 2008-05-09T00:00:00 -->
        <symbol number="3" name="Delvis skyet" />

        <precipitation value="0.2" />
        <!-- Valid at 2008-05-08T18:00:00 -->
        <windDirection deg="259.5" code="W" name="Vest" />
        <windSpeed mps="3.7" name="Lett bris" />
        <temperature unit="celcius" value="6" />
        <pressure unit="hPa" value="1010.2" />
      </time>
     *
     * @param SimpleXMLElement $time
     * @return stdClass $period Returns object with "time" data
    */
    protected function _objectFromForecastTime(SimpleXMLElement $time)
    {
        $forecast = $this->_objectFromSxe($time);

        $fromUnix = (string) strtotime($forecast->from);
        $toUnix = (string) strtotime($forecast->to);

        $forecast->fromDate = date($this->_config["dateFormat"], $fromUnix);
        $forecast->toDate = date($this->_config["dateFormat"], $toUnix);

        $forecast->fromDateIso = date("Y-m-d", $fromUnix);
        $forecast->toDateIso = date("Y-m-d", $toUnix);

        $forecast->fromHour = date($this->_config["hourFormat"], $fromUnix);
        $forecast->toHour = date($this->_config["hourFormat"], $toUnix);
        $forecast->hourInterval = $forecast->fromHour . "-" . $forecast->toHour;

        $forecast->precipitationText = $forecast->precipitation ." mm";

        $forecast->symbol = $forecast->symbolNumber;
        $forecast->symbolImage = $this->imageForSymbol($forecast->symbolNumber, $forecast->fromHour, $forecast->toHour);
        $forecast->symbolUri = $this->_config["symbolLocation"]."/". $forecast->symbolImage;

        $forecast->temperatureUnit = "Celsius"; // fix
        $forecast->temperatureText = $forecast->temperature . $this->_config["celsiusCharacter"];

        $forecast->windDirection = (float) $forecast->windDirectionDeg;
        $forecast->windDirectionUnit = "°";
        $forecast->windSpeed = (float) $forecast->windSpeedMps;
        $forecast->windSpeedUnit = "m/s";
        $forecast->windSpeedText = $forecast->windSpeed ." ". $forecast->windSpeedUnit;

        return $forecast;
    }
}

//// xml->json json client ?format= server getTempearture (as time series) get Pressure getMaxTemp getMinTemp etc. graph libs? search?
// http://www.yr.no/sted/Norxgsxe/Trxxoms/Troxms%C3%B8/Trxomxs/varsel.xml?r=3 gir 200 ikke 404
// absoluttkrav med caching?

// utstedt mangler i wd
//
// xml:lang mangler i wd

// flau vind wsw sol etc på en no ny?
