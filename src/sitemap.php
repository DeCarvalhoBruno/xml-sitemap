<?php namespace Lti\Sitemap;

abstract class XMLSitemap
{
    /**
     * @var \DOMDocument
     */
    protected $XML;
    /**
     * @var \DOMElement
     */
    protected $mainNode;

    private static $instance;

    protected $hasImages = false;
    protected $hasVideos = false;
    protected $hasMobile = false;
    protected $hasNews = false;

    /**
     * All inherited classes call this constructor because we need all DOMElements to to added to the same instance of DOMDocument
     * They call the constructor, they get handed the same DOMDocument, and we can put it together later with the output() method.
     */
    public function __construct()
    {
        $this->XML = $this->getXML();
    }

    /**
     * Creates the instance of DOMDocument all classes will be working with.
     *
     * @param string $version
     * @param string $encoding
     * @param bool $willFormatOutput
     *
     * @return \DOMDocument
     */
    public function getXML( $version = '1.0', $encoding = 'UTF-8', $willFormatOutput = true )
    {
        if (is_null( self::$instance )) {
            self::$instance               = new \DOMDocument( $version, $encoding );
            self::$instance->formatOutput = $willFormatOutput;
        }

        return self::$instance;
    }

    /**
     * When we unit test, we may output multiple XML files within the same run,
     * so we need to nullify the DOMDocument instance at the opportune moment, like after an output
     *
     */
    public static function reset()
    {
        self::$instance = null;
    }

    public function willFormatOutput( $value = true )
    {
        $this->XML->formatOutput = $value;
    }

    function getNode()
    {
        return $this->mainNode;
    }

    /**
     * Adds a child node to the main node that each inherited class creates when it instantiates itself.
     *
     * @param $attribute
     * @param null $value
     * @param bool $escape Whether to escape the value, we usually escape any field that might contain special characters
     *  to avoid using special char handler methods
     */
    protected function addChild( $attribute, $value = null, $escape = false )
    {
        if ( ! empty( $value )) {
            if ($escape === true) {
                $node = $this->XML->createElement( $attribute );
                $node->appendChild( new \DOMCdataSection( $value ) );
            } else {
                $node = $this->XML->createElement( $attribute, $value );
            }
            $this->mainNode->appendChild( $node );
        }
    }

    /**
     * Sometimes we want to add a ready-made node
     *
     * @param XMLSitemap $object
     */
    protected function addChildNode( XMLSitemap $object )
    {
        $this->mainNode->appendChild( $object->getNode() );
    }

    /**
     * Adds the XSL stylesheet to our document
     *
     * @param $url
     */
    public function addStylesheet( $url )
    {
        $this->XML->appendChild( $this->XML->createProcessingInstruction( 'xml-stylesheet',
            sprintf( 'type="text/xsl" href="%s"', $url ) ) );
    }

    public function output()
    {
        return $this->XML->saveXML();
    }

    /**
     * If we add nodes with special namespaces, we setup a boolean in our iterator classes that switch to true
     * so we can add the appropriate namespaces to our document.
     *
     * Ex: an image is added to the document, we set "hasImages" to true so that it's evaluated to true here
     * and the image namespace is added.
     *
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
     *
     * @param string $testedNodeType The boolean in our class that we're going to assess
     * @param \DOMElement $nodeset
     * @param $attributeName
     * @param $namespaceURL
     * @see XMLSitemap::addExtraNamespaces
     */
    private function addNamespace( $testedNodeType, $nodeset, $attributeName, $namespaceURL )
    {
        if ($this->$testedNodeType === true) {
            $nodeset->appendChild( $this->XML->createAttribute( $attributeName ) )->appendChild( $this->XML->createTextNode( $namespaceURL ) );
        }
    }

    protected function hasChild( $childName )
    {
        $children = $this->mainNode->getElementsByTagName( $childName );
        if ($children->length > 0) {
            return true;
        }

        return false;
    }

    /**
     * @TODO: add the replace bit
     *
     * @param $attribute
     * @param null $value
     * @param bool $escape
     */
    protected function addOrReplaceChild( $attribute, $value = null, $escape = false )
    {
        if ($this->hasChild( $attribute ) === false) {
            $this->addChild( $attribute, $value, $escape );
        }
    }

}

/**
 * Class Sitemap
 * @package Lti\Sitemap
 */
class Sitemap extends XMLSitemap
{
    private $location;
    private $lastModification;

    /**
     * @param string $location URL, creates a <loc> node
     * @param string $lastModification creates a <lastmod> node
     */
    function __construct( $location, $lastModification = '' )
    {
        parent::__construct();

        $this->location         = $location;
        $this->lastModification = $lastModification;
        $this->mainNode         = $this->XML->createElement( 'sitemap' );
        $this->addChild( 'loc', $this->location, true );
        $this->addChild( 'lastmod', $this->lastModification );
    }


}

/**
 * Class SitemapUrl
 * @package Lti\Sitemap
 */
class SitemapUrl extends XMLSitemap
{
    private $location;
    private $lastModification;
    private $changeFrequency;
    private $priority;

    /**
     * @param string $location
     * @param string $lastModification
     * @param string $changeFrequency
     * @param string $priority
     */
    function __construct( $location, $lastModification = '', $changeFrequency = '', $priority = '' )
    {
        parent::__construct();
        $this->location         = $location;
        $this->lastModification = $lastModification;
        $this->changeFrequency  = $changeFrequency;
        $this->priority         = $priority;

        $this->mainNode = $this->XML->createElement( 'url' );
        $this->addChild( 'loc', $this->location, true );
        $this->addChild( 'lastmod', $this->lastModification );
        $this->addChild( 'changefreq', $this->changeFrequency );
        $this->addChild( 'priority', $this->priority );
    }

    /**
     * @param SitemapImage $image
     */
    public function addImage( SitemapImage $image )
    {
        $this->addChildNode( $image );
    }

    /**
     * @param SitemapNews $news
     */
    public function addNews( SitemapNews $news )
    {
        $this->addChildNode( $news );
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation( $location )
    {
        $this->location = $location;
        $this->addOrReplaceChild( 'loc', $this->location, true );
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     */
    public function setPriority( $priority )
    {
        $this->priority = $priority;
        $this->addOrReplaceChild( 'priority', $this->priority );
    }

    /**
     * @return string
     */
    public function getChangeFrequency()
    {
        return $this->changeFrequency;
    }

    /**
     * @param string $changeFrequency
     */
    public function setChangeFrequency( $changeFrequency )
    {
        $this->changeFrequency = $changeFrequency;
        $this->addOrReplaceChild( 'changefreq', $this->changeFrequency );
    }

    /**
     * @return string
     */
    public function getLastModification()
    {
        return $this->lastModification;
    }

    /**
     * @param string $lastModification
     */
    public function setLastModification( $lastModification )
    {
        $this->lastModification = $lastModification;
        $this->addOrReplaceChild( 'lastmod', $this->lastModification );
    }

}
