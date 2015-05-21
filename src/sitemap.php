<?php namespace Lti\Sitemap;

abstract class XMLSitemap
{
    /**
     * @var \DOMDocument
     */
    protected $XML;
    /**
     * @var \DOMNode
     */
    protected $mainNode;

    private static $instance;

    protected $hasImages = false;
    protected $hasVideos = false;
    protected $hasMobile = false;
    protected $hasNews = false;


    public function __construct()
    {
        $this->XML = $this->getXML();
    }

    public function getXML( $version = '1.0', $encoding = 'UTF-8' )
    {
        if (is_null( self::$instance )) {
            self::$instance = new \DOMDocument( $version, $encoding );
        }

        return self::$instance;
    }

    public function willFormatOutput( $value = true )
    {
        $this->XML->formatOutput = $value;
    }

    public function setAppendTarget( $target )
    {
        $this->mainNode = $target;
    }

    function getNode()
    {
        return $this->mainNode;
    }

    protected function addChild( $attribute, $value = null, $escape=false )
    {
        if ( ! empty( $value )) {

            if($escape===true){
                $node = $this->XML->createElement( $attribute );
                $node->appendChild(new \DOMCdataSection($value));
            }else{
                $node = $this->XML->createElement( $attribute, $value );
            }
            $this->mainNode->appendChild( $node  );
        }
    }

    protected function addChildNode( XMLSitemap $object )
    {
        $this->mainNode->appendChild( $object->getNode() );
    }

    protected function addStylesheet( $url )
    {
        $this->XML->createProcessingInstruction( 'xml-stylesheet', sprintf( 'type="text/xsl" href="%s"', $url ) );
    }

    public function output()
    {
        return $this->XML->saveXML();
    }

    /**
     * @param \DOMElement $nodeset
     */
    protected function addExtraNamespaces( $nodeset )
    {
        $this->addNamespace( 'hasImages', $nodeset, 'xmlns:image', "http://www.google.com/schemas/sitemap-image/1.1" );
        $this->addNamespace( 'hasVideos', $nodeset, 'xmlns:video', "http://www.google.com/schemas/sitemap-video/1.1" );
        $this->addNamespace( 'hasMobile', $nodeset, 'xmlns:mobile',
            "http://www.google.com/schemas/sitemap-mobile/1.0" );
        $this->addNamespace( 'hasNews', $nodeset, 'xmlns:news', "http://www.google.com/schemas/sitemap-news/0.9" );
    }

    /**
     * @param $testedNodeType
     * @param \DOMElement $nodeset
     * @param $attributeName
     * @param $namespaceURL
     */
    private function addNamespace( $testedNodeType, $nodeset, $attributeName, $namespaceURL )
    {
        if ($this->$testedNodeType === true) {
            $nodeset->appendChild( $this->XML->createAttribute( $attributeName ) )->appendChild( $this->XML->createTextNode( $namespaceURL ) );
        }
    }

    protected function escapeData(){

    }
}

class Sitemap extends XMLSitemap
{
    private $location;
    private $lastModification;

    function __construct( $location, $lastModification = '' )
    {
        parent::__construct();

        $this->location         = $location;
        $this->lastModification = $lastModification;
        $this->sitemapXML       = $this->XML->createElement( 'sitemap' );
        $this->setAppendTarget( $this->sitemapXML );
        $this->addChild( 'loc', $this->location );
        $this->addChild( 'lastmod', $this->lastModification );
    }
}

class SiteMapIndex extends XMLSitemap implements \Iterator
{

    public function __construct( $version = '1.0', $encoding = 'UTF-8' )
    {
        parent::__construct( $version, $encoding );
        $this->urlsetXML = $this->XML->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'sitemapindex' );
    }


    private $key = 0;
    private $nodes = array();

    public function current()
    {
        return $this->nodes[$this->key];
    }

    public function next()
    {
        ++ $this->key;
    }

    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        return isset( $this->nodes[$this->key] );
    }

    public function rewind()
    {
        $this->key = 0;
    }

    public function add( Sitemap $sitemap )
    {
        $this->nodes[] = $sitemap;
    }

    public function output()
    {
        /**
         * @var Sitemap $node
         */
        foreach ($this->nodes as $node) {
            $this->urlsetXML->appendChild( $node->getNode() );
        }
        $this->XML->appendChild( $this->urlsetXML );

        return parent::output();
    }

}

class SiteMapUrlSet extends XMLSitemap implements \Iterator
{

    private $key = 0;
    private $nodes = array();

    public function __construct( $version = '1.0', $encoding = 'UTF-8' )
    {
        parent::__construct( $version, $encoding );
        $this->urlsetXML = $this->XML->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset' );
    }

    /**
     * @return SitemapUrl
     */
    public function current()
    {
        return $this->nodes[$this->key];
    }

    public function next()
    {
        ++ $this->key;
    }

    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        return isset( $this->nodes[$this->key] );
    }

    public function rewind()
    {
        $this->key = 0;
    }

    public function add( SitemapUrl $url )
    {
        $this->nodes[] = $url;
    }

    public function output()
    {
        /**
         * @var Sitemap $node
         */
        foreach ($this->nodes as $node) {
            $this->urlsetXML->appendChild( $node->getNode() );
        }
        $this->addExtraNamespaces( $this->urlsetXML );
        $this->XML->appendChild( $this->urlsetXML );

        return parent::output();
    }

    public function addImage( $location, $caption = '', $geolocation = '', $title = '', $license = '' )
    {
        $this->hasImages = true;
        $this->current()->addImage( new SitemapImage( $location, $caption, $geolocation, $title, $license ) );
    }
}

class SitemapUrl extends XMLSitemap
{
    private $location;
    private $lastModification;
    private $changeFrequency;
    private $priority;
    private $urlXML;

    function __construct( $location, $lastModification = '', $changeFrequency = '', $priority = '' )
    {
        parent::__construct();
        $this->location         = $location;
        $this->lastModification = $lastModification;
        $this->changeFrequency  = $changeFrequency;
        $this->priority         = $priority;

        $this->urlXML = $this->XML->createElement( 'url' );
        $this->setAppendTarget( $this->urlXML );
        $this->addChild( 'loc', $this->location );
        $this->addChild( 'lastmod', $this->lastModification );
        $this->addChild( 'changefreq', $this->changeFrequency );
        $this->addChild( 'priority', $this->priority );
    }

    public function addImage( SitemapImage $image )
    {
        $this->addChildNode( $image );
    }

}

class SitemapImage extends XMLSitemap
{
    private $imageXML;
    private $location;
    private $caption;
    private $geolocation;
    private $title;
    private $license;

    public function __construct( $location, $caption = '', $geolocation = '', $title = '', $license = '' )
    {
        parent::__construct();
        $this->location    = $location;
        $this->caption     = $caption;
        $this->geolocation = $geolocation;
        $this->title       = $title;
        $this->license     = $license;

        $this->imageXML = $this->XML->createElement( 'image:image' );

        $this->setAppendTarget( $this->imageXML );
        $this->addChild( 'image:loc', $location );
        $this->addChild( 'image:caption', $caption );
        $this->addChild( 'image:geo_location', $geolocation );
        $this->addChild( 'image:title', $title );
        $this->addChild( 'image:license', $license );
    }
}
