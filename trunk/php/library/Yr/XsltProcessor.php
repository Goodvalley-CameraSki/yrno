<?php

class Yr_XsltProcessor extends XSLTProcessor
{
    protected $_dom;
    
    protected $_xsl;
    
    public function __construct($xmlSource = null, $xslUri = null)
    {
        $this->setSource($xmlSource);
        $this->setStylesheet($xslUri);
    }


    public function setSource($xmlString)
    {
        if ($xmlString != '') {
            $this->_dom = new DOMDocument();
            $this->_dom->loadXML($xmlString);
        }
    }

    public function setStylesheet($xslUri)
    {
        if ($xslUri != '') {
            $this->_xsl = new DOMDocument();
            $this->_xsl->load($xslUri);
            $this->importStylesheet($this->_xsl);
        }
    }

    public function __toString()
    {
        return $this->transform();
    }

    public function transform()
    {
        return $this->transformToXml($this->_dom);
    }

    public function send()
    {
        header("Content-Type: application/xml; charset=utf-8");
        echo $this->__toString();
    }
}
