<?php

class Yr_SimpleXml
{
    static public function factory($string, $libXmlOptions = LIBXML_NOERROR, $stringIsUri=false, $namespace = null, $isPrefix = null)
    {
        if (empty($string)) {
            throw new RuntimeException("You need to supply a \$string containing XML or an HTTP URI");
        }

        if (strpos(trim($string), "http") === 0) {
            $stringIsUri = true;
            $uri = $string;
            $stream = @fopen($uri, "r");
            if ($stream === false) {
                throw new RuntimeException("Feil URI eller manglende forbindelse med internett.");
            }
            fclose($stream);
        }

        libxml_use_internal_errors(true);

        // @ to suppress warnings on 404 or 5xx
        $sx =  @new SimpleXMLElement($string, $libXmlOptions, $stringIsUri, $namespace, $isPrefix);


        $errors = libxml_get_errors();
        $errorCount = count($errors);

        if ($errorCount === 0) {
            return $sx;
        } else {
            $i = 0;
            $msg = __METHOD__ ." failed with $errorCount libxml errors: " .PHP_EOL;
            foreach ($errors as $error) {
                $msg .= "  [". ++$i ."/$errorCount] ". $error->message;
                if ($error->file) {
 	                $msg .= " in file ". $error->file ." line:".$error->line;
 	            }
            }
            libxml_clear_errors();
            throw new RuntimeException($msg);
        }
    }
}
