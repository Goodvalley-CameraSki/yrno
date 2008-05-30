<?php
/** Configuration */

$config = array("library" => realpath(dirname(__FILE__)."/../library"));

$configfile = realpath(dirname(__FILE__)."/../config/config.php");
if (file_exists($configfile)) {
   $config = array_merge(require_once $configfile);
}
ini_set("include_path", $config["library"] . PATH_SEPARATOR . ini_get("include_path"));

/** Yr_Service_Weatherdata */
require_once "Yr/Service/Weatherdata.php";

/** Params */
$uri = (isset($config["uri"])) ? $config["uri"] : null ;
$uri = (empty($_GET["uri"]) === false) ? $_GET["uri"] : $uri ; // GET override for service URI
$lang = (isset($config["lang"])) ? $config["lang"] : "no";
$base = (isset($config["base"])) ? $config["base"] : null;

try {
    $yr = new Yr_Service_Weatherdata($uri);
    $yr->checkUri(null, "<p>Mangler URI til datakilde. Sett URI til XML-varsel i konfigurasjonsfil eller bruk GET parameter 'uri'.</p>
	<p>Eksempel: http://eksempel.no/varsel.php?uri=http://www.yr.no/sted/Norge/Rogaland/Stavanger/Stavanger/varsel.xml</p>");

    header("Content-Type: application/xml; charset=utf-8");
    $yr->setXslUri(dirname(__FILE__)."/../../xslt/weatherdata_atom_simple.xsl");
    echo $yr->transform();

} catch (Exception $e) {
    echo $e->getMessage();
}
