<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" encoding="utf-8" indent="yes" omit-xml-declaration="no" cdata-section-elements="" />
    <!-- params -->
    <xsl:param name="lang" select="'no'"/>
    <xsl:param name="location" select="weatherdata/location/name"/>
    <xsl:param name="country" select="weatherdata/location/country"/>
    <xsl:param name="alternate" select="weatherdata/links/link[@id='overview']/@url"/>
    <xsl:param name="base" select="'http://fil.nrk.no/yr/grafikk/sym/b38/'"/>
    <!-- We use the text forecast dates or? -->
    <xsl:param name="from" select="/weatherdata/forecast/text/location/time/@from" />
    <xsl:param name="to" select="/weatherdata/forecast/text/location/time/@to" />

  <xsl:template match="/">
    <!-- Start of Atom feed -->
    <feed xmlns="http://www.w3.org/2005/Atom">
      <id><xsl:value-of select="$alternate"/></id>

      <title xml:lang="nb"><xsl:value-of select="$location"/> værvarsel fra yr.no</title>
      <title xml:lang="en"><xsl:value-of select="$location"/> weather forecast from yr.no</title>
      <title xml:lang="nn"><xsl:value-of select="$location"/> vêrvarsel frå yr.no</title>

      <icon>http://www.nrk.no/contentfile/web/icons/weather/yr.ico</icon>
      <!--<subtitle xml:lang="nb">Gjelder fra <xsl:value-of select="$from"/> til <xsl:value-of select="$to"/></subtitle>
      <subtitle xml:lang="nn">Gjeld frå <xsl:value-of select="/weatherdata/forecast/text/location/time/@from"/> til <xsl:value-of select="/weatherdata/forecast/text/location/time/@from"/></subtitle>
      -->
      <author><name>yr.no</name><uri>http://yr.no</uri><email>ver[a]yr.no</email></author>
      <contributor><name>Meteorologisk institutt</name></contributor>
      <contributor><name>NRK</name></contributor>

      <updated><xsl:comment>It would be nice if varsel.xml contained timestamp for when the forecast was generated</xsl:comment>
      </updated>

      <link rel="alternate" type="text/html" hreflang="nb" href="{$alternate}"/>
      <link rel="alternate" type="text/html" hreflang="nn" href="{$alternate}"/>
      <link rel="alternate" type="text/html" hreflang="en" href="{$alternate}"/>

      <xsl:comment>It would be nice if the hrefs for each hreflang where in the source varsel.xml</xsl:comment>


      <!--
          <link id="xmlSource" url="http://www.yr.no/sted/Norge/Troms/Tromsø/Tromsø/varsel.xml" />
    <link id="overview" url="http://www.yr.no/sted/Norge/Troms/Tromsø/Tromsø/" />
    <link id="hourByHour" url="http://www.yr.no/sted/Norge/Troms/Tromsø/Tromsø/time_for_time.html" />
    <link id="weekend" url="http://www.yr.no/sted/Norge/Troms/Tromsø/Tromsø/helg.html" />
    <link id="longTermForecast" url="http://www.yr.no/sted/Norge/Troms/Tromsø/Tromsø/langtidsvarsel.html" />
    <link id="radar" url="http://www.yr.no/sted/Norge/Troms/Tromsø/Tromsø/radar.html" />

    -->
      <rights xml:lang="nn">Vêrvarsel frå yr.no, levert av Meteorologisk institutt og NRK</rights>
      <rights xml:lang="nb">Værvarsel fra yr.no, levert av Meteorologisk institutt og NRK</rights>
      <rights xml:lang="en">Weather forecast from yr.no, delivered by the Norwegian Meteorological Institute and the NRK</rights>

      <generator uri="http://where?/yrphp" version="$id: trunk $">Yr PHP library</generator>

      <xsl:apply-templates select="/weatherdata/forecast"/>
    </feed>
  </xsl:template>

  <xsl:template match="text">
    <xsl:variable name="id" select="/weatherdata/links/link[@id='overview']/@url"/>


    <entry xmlns="http://www.w3.org/2005/Atom">
      <title xml:lang="nn">Vêrvarsel for <xsl:value-of select="$location"/> på yr.no</title>
      <id><xsl:value-of select="$id"/></id>
      <summary type="xhtml" xml:lang="{lang}" xml:base="{$base}">
         <div xmlns="http://www.w3.org/1999/xhtml">
          <table summary="Værvarsel for Tromsø fra yr.no">
    <thead>
      <tr><th class="v" colspan="3"><strong>Varsel for <xsl:value-of select="$location"/></strong></th></tr>
    </thead>
    <tbody>

      <xsl:for-each select="/weatherdata/forecast/tabular/time[@period='2']">
      <tr>
        <xsl:variable name="shortdate" select="concat(substring(@from, 9, 2),'.',substring(@from, 6, 2)) " />
        <xsl:variable name="symbol" select="symbol/@number" />
        <td><xsl:value-of select="$shortdate"/></td>
        <td><img src="0{$symbol}d.png" alt="{symbol/@name}" width="38" height="38" /></td>
        <td class="minus"><xsl:value-of select="temperature/@value"/> °C</td>
      </tr>
      </xsl:for-each>
            </tbody>
  </table>
    </div>
      </summary>

      <updated><xsl:value-of select="date[@name='timestamp']"/></updated>

      <link rel="alternate" type="text/html" hreflang="nb" href="{$id}" />
      <link rel="alternate" type="text/html" hreflang="nn" href="{$id}" />
      <link rel="alternate" type="text/html" hreflang="en" href="{$id}" />
    </entry>
  </xsl:template>

</xsl:stylesheet>

<!--

Example Atom feed:

   <?xml version="1.0" encoding="utf-8"?>
   <feed xmlns="http://www.w3.org/2005/Atom">
     <title type="text">dive into mark</title>
     <subtitle type="html">
       A &lt;em&gt;lot&lt;/em&gt; of effort
       went into making this effortless
     </subtitle>
     <updated>2005-07-31T12:29:29Z</updated>
     <id>tag:example.org,2003:3</id>
     <link rel="alternate" type="text/html"
      hreflang="en" href="http://example.org/"/>
     <link rel="self" type="application/atom+xml"
      href="http://example.org/feed.atom"/>
     <rights>Copyright (c) 2003, Mark Pilgrim</rights>
     <generator uri="http://www.example.com/" version="1.0">
       Example Toolkit
     </generator>
     <entry>
       <title>Atom draft-07 snapshot</title>
       <link rel="alternate" type="text/html"
        href="http://example.org/2005/04/02/atom"/>
       <link rel="enclosure" type="audio/mpeg" length="1337"
        href="http://example.org/audio/ph34r_my_podcast.mp3"/>
       <id>tag:example.org,2003:3.2397</id>
       <updated>2005-07-31T12:29:29Z</updated>
       <published>2003-12-13T08:29:29-04:00</published>
       <author>
         <name>Mark Pilgrim</name>
         <uri>http://example.org/</uri>
         <email>f8dy@example.com</email>
       </author>
       <contributor>
         <name>Sam Ruby</name>
       </contributor>
       <contributor>
         <name>Joe Gregorio</name>
       </contributor>
       <content type="xhtml" xml:lang="en" xml:base="http://diveintomark.org/">
         <div xmlns="http://www.w3.org/1999/xhtml">
           <p><i>[Update: The Atom draft is finished.]</i></p>
         </div>
       </content>
     </entry>
   </feed>

 -->
