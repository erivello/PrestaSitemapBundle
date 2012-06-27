<?php

namespace Presta\SitemapBundle\Sitemap;

use Presta\SitemapBundle\Sitemap\UrlInterface;

/**
 * Description of Urlset
 *
 * @author depely
 */
class Urlset extends XmlConstraint
{
    const TAG = 'sitemap';
    
    protected $loc;
    protected $lastmod;
    
    protected $urlsXml = '';

    protected $customNamespaces = array();
    
    public function __construct($loc)
    {
        $this->loc = $loc;
        $this->lastmod = new \DateTime;
    }
    
    public function getLoc()
    {
        return $this->loc;
    }
    
    public function getLastmod()
    {
        return $this->lastmod;
    }


//    public function getUrls()
//    {
//        return $this->urls;
//    }
//    
//    public function setUrls(array $urls)
//    {
//        $this->urls = $urls;
//    }
    
    
    /**
     * add url to pool and check limits
     * 
     * @param Url\Url $url
     * @throws \RuntimeException 
     */
    public function addUrl(Url\Url $url)
    {
        if ($this->isFull()) {
            throw new \RuntimeException('The urlset limit has been exceeded');
        }
        
        $urlXml = $url->toXml();
        $this->urlsXml .= $urlXml;
        
        //---------------------
        //Check limits 
        if ($this->countItems++ >= self::LIMIT_ITEMS) {
           $this->limitItemsReached = true;
        }
        
        $urlLength = strlen($urlXml);
        $this->countBytes += $urlLength;
        
        if ($this->countBytes + $urlLength + strlen($this->getStructureXml()) > self::LIMIT_BYTES ) {
            //we suppose the next url is almost the same length and cannot be added
            //plus we keep 500kB (@see self::LIMIT_BYTES)
            //... beware of numerous images set in url
            $this->limitBytesReached = true;
        }
        //---------------------
    }
    
    
    /**
     * get the xml structure of the current urlset 
     * @return type 
     */
    protected function getStructureXml()
    {
        $struct = '<?xml version="1.0" encoding="UTF-8"?>';
        $struct .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" NAMESPACES>URLS</urlset>';
        
        $namespaces = '';
        foreach ($this->customNamespaces as $key => $location) {
            $namespaces .= ' xmlns:' . $key . '="' . $location .'"';
        }
        
        $struct = str_replace('NAMESPACES', $namespaces, $struct);
        
        return $struct;
    }
    
    
    public function toXml() 
    {
        return str_replace('URLS', $this->urlsXml, $this->getStructureXml());
    }
}