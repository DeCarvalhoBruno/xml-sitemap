<?php namespace Lti\Sitemap;

/**
 * Class SitemapNews
 * @package Lti\Sitemap
 */
class SitemapNews extends XMLSitemap
{
    /**
     * Name of the news publication. It must exactly match
     * the name as it appears on your articles in news.google.com,
     * omitting any trailing parentheticals.
     * For example, if the name appears in Google News as
     * "The Example Times (subscription)", you should use
     * "The Example Times".  Required.
     * @var string
     */
    private $name;
    /**
     * Language of the publication.  It should be an
     * ISO 639 Language Code (either 2 or 3 letters)
     *
     * Exception: For Chinese, please use zh-cn for Simplified
     * Chinese or zh-tw for Traditional Chinese.  Required.
     *
     * @see http://www.loc.gov/standards/iso639-2/php/code_list.php
     * @var string
     */
    private $language;
    /**
     *
     * Accessibility of the article.  Required if access is not open,
     * otherwise this tag should be omitted.
     * @var string
     */
    private $access;
    /**
     * A comma-separated list of properties characterizing the content
     * of the article, such as "PressRelease" or "UserGenerated".
     * Required if any genres apply to the article, otherwise this tag
     * should be omitted.
     *
     * @see http://www.google.com/support/news_pub/bin/answer.py?answer=93992
     * @var string
     */
    private $genres;
    /**
     * Article publication date in W3C format, specifying the complete
     * date (YYYY-MM-DD) with optional timestamp.
     * Please ensure that you give the original date and time at which
     * the article was published on your site; do not give the time
     * at which the article was added to your Sitemap.  Required.
     *
     * @see http://www.w3.org/TR/NOTE-datetime
     * @var string
     */
    private $publication_date;
    /**
     *
     * Title of the news article.  Required.
     * Note: The title may be truncated for space reasons when shown
     * on Google News.
     *
     * @var string
     */
    private $title;
    /**
     * Comma-separated list of keywords describing the topic of
     * the article.  Keywords may be drawn from, but are not limited to,
     * the list of existing Google News keywords.
     * Optional.
     *
     * @see http://www.google.com/support/news_pub/bin/answer.py?answer=116037
     * @var string
     */
    private $keywords;
    /**
     * Comma-separated list of up to 5 stock tickers of the companies,
     * mutual funds, or other financial entities that are the main subject
     * of the article.  Relevant primarily for business articles.
     * Each ticker must be prefixed by the name of its stock exchange,
     * and must match its entry in Google Finance.
     * For example, "NASDAQ:AMAT" (but not "NASD:AMAT"),
     * or "BOM:500325" (but not "BOM:RIL").  Optional.
     * @var string
     */
    private $stock_tickers;

    /**
     * @param string $name
     * @param string $language
     */
    public function __construct( $name, $language )
    {
        parent::__construct();
        $this->name     = $name;
        $this->language = $language;

        $this->mainNode = $this->XML->createElement( 'news:news' );

        $node  = $this->XML->createElement( 'news:publication' );
        $child = $this->XML->createElement( 'news:name' );
        $child->appendChild( new \DOMCdataSection( $name ) );
        $node->appendChild( $child );
        $child = $this->XML->createElement( 'news:language', $language );
        $node->appendChild( $child );
        $this->mainNode->appendChild( $node );
    }

    /**
     * @param string $value
     */
    public function set_access( $value )
    {
        $this->access = $value;
    }

    /**
     * @param string $value
     */
    public function set_genres( $value )
    {
        $this->genres = $value;
    }

    /**
     * @param string $value
     */
    public function set_publication_date( $value )
    {
        $this->publication_date = $value;
    }

    /**
     * @param string $value
     */
    public function set_title( $value )
    {
        $this->title = $value;
    }

    /**
     * @param string $value
     */
    public function set_keywords( $value )
    {
        $this->keywords = $value;
    }

    /**
     * @param string $value
     */
    public function set_stock_tickers( $value )
    {
        $this->stock_tickers = $value;
    }

    /**
     * Adds all the nodes in our news sitemap, with some values being escaped.
     */
    public function build()
    {
        $values = array( 'access'           => false,
                         'genres'           => false,
                         'publication_date' => false,
                         'title'            => true,
                         'keywords'         => true,
                         'stock_tickers'    => true
        );
        foreach ($values as $value => $isEscaped) {
            if ( ! is_null( $this->{$value} )) {
                $this->addChild( sprintf( 'news:%s', $value ), $this->{$value}, $isEscaped );
            }
        }
    }


}
