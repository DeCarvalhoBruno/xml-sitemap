<?php namespace Lti\Sitemap;

abstract class SiteMapIterator extends XMLSitemap implements \Iterator
{
    protected $key = 0;
    protected $nodes = array();

    /**
     * @return \Lti\Sitemap\XMLSitemap
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

    public function get( $key )
    {
        if (isset( $this->nodes[$key] )) {
            return $this->nodes[$key];
        }

        return null;
    }

    public function output()
    {
        /**
         * @var Sitemap $node
         */
        foreach ($this->nodes as $node) {
            $this->mainNode->appendChild( $node->getNode() );
        }
        $this->addExtraNamespaces( $this->mainNode );
        $this->XML->appendChild( $this->mainNode );

        return parent::output();
    }
}


class SiteMapIndex extends SiteMapIterator
{

    public function __construct( $version = '1.0', $encoding = 'UTF-8', $willFormatOutput = true )
    {
        XMLSitemap::__construct( $version, $encoding, $willFormatOutput );
        $this->mainNode = $this->XML->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'sitemapindex' );
    }

    public function add( Sitemap $sitemap )
    {
        $this->nodes[] = $sitemap;
    }
}

class SiteMapUrlSet extends SiteMapIterator
{

    public function __construct( $version = '1.0', $encoding = 'UTF-8', $willFormatOutput = true )
    {
        XMLSitemap::__construct( $version, $encoding, $willFormatOutput );
        $this->mainNode = $this->XML->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset' );
    }


    public function add( SiteMapUrl $sitemap )
    {
        $key           = $this->key();
        $this->nodes[] = $sitemap;
        $this->next();

        return $key;
    }

    public function addImage( $key, $location, $caption = '', $geolocation = '', $title = '', $license = '' )
    {
        /**
         * @var \Lti\Sitemap\SitemapUrl $node
         */
        $node = $this->get( $key );
        if ( ! is_null( $node )) {
            $node->addImage( new SitemapImage( $location, $caption, $geolocation, $title, $license ) );
            $this->hasImages = true;
        }
    }

    public function addImageNode( $key, SitemapImage $image )
    {
        /**
         * @var \Lti\Sitemap\SitemapUrl $node
         */
        $node = $this->get( $key );
        if ( ! is_null( $node )) {
            $node->addImage( $image );
            $this->hasImages = true;
        }
    }

    public function addNewsNode( $key, SitemapNews $news )
    {
        /**
         * @var \Lti\Sitemap\SitemapUrl $node
         */
        $node = $this->get( $key );
        if ( ! is_null( $node )) {
            $news->build();
            $node->addNews( $news );
            $this->hasImages = true;
        }
    }
}