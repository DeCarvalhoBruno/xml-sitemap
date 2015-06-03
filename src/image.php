<?php namespace Lti\Sitemap;

/**
 * Class SitemapImage
 * @package Lti\Sitemap
 */
class SitemapImage extends XMLSitemap
{
    private $location;
    private $caption;
    private $geolocation;
    private $title;
    private $license;

    /**
     * @param string $location
     * @param string $caption
     * @param string $geolocation
     * @param string $title
     * @param string $license
     */
    public function __construct( $location, $caption = '', $geolocation = '', $title = '', $license = '' )
    {
        parent::__construct();
        $this->location    = $location;
        $this->caption     = $caption;
        $this->geolocation = $geolocation;
        $this->title       = $title;
        $this->license     = $license;

        $this->mainNode = $this->XML->createElement( 'image:image' );

        $this->addChild( 'image:loc', $location, true );
        $this->addChild( 'image:caption', $caption, true );
        $this->addChild( 'image:geo_location', $geolocation, true );
        $this->addChild( 'image:title', $title, true );
        $this->addChild( 'image:license', $license );
    }
}
