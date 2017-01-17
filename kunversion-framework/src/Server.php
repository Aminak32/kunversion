<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/16/17
     * Time: 5:30 PM
     */

    namespace Kunversion;

    use Kunversion\Session;

    class Server extends Model
    {
        const LANDPAGES_TABLE = 'landpages';
        public static function setSettingsBasedOnHost($verify_subdomain=false)
        {
            $server = new Server();
            $session = new Session();
            $host = self::getHost();
            $domain = $host[0];
            $subdomain = $host[1];
            if ( true === $verify_subdomain && !empty($subdomain) && false === self::verifiedSubdomain($domain, $subdomain)) {
                $subdomain = '';
            }

            $settings = $session->settings;
            if(  false === isset($session->settings) || ( isset($session->settings) && ($domain != $settings->domain || $subdomain != $settings->subdomain) ) ) {
                if (!isset($session->referer) && isset($session->HTTP_REFERER) && 0 == substr_count($_SERVER[ 'HTTP_REFERER' ],
                        $settings->domain) && 0 == substr_count($_SERVER[ 'HTTP_REFERER' ], 'kunversion')
                ) {
                    $session->referer = $_SERVER[ 'HTTP_REFERER' ];
                }
                $server->getSettings($domain, $subdomain);
            }
            return $session->get('settings');
        }


        public function getSettings( $domain, $subdomain=false)
        {
            $db = parent::getDb();
            if (!empty($subdomain)) {
                $query = "SELECT location,url FROM " . self::LANDPAGES_TABLE . " WHERE url = ? LIMIT 1";
                $result = $db->getRow($query, $domain);
            } elseif (!empty($subdomain)) {
                $query = "SELECT subdomain, domain FROM " . Agency::AGENCIES_TABLE . " ag, " . Agents::AGENT_TABLE . " a WHERE a.companyid=ag.agencyid AND ag.domain LIKE ? AND a.subdomain=? LIMIT 1";
                $dom = $db->getRow($query, array("%{$domain}", $subdomain));

                $query = "SELECT url FROM " . self::LANDPAGES_TABLE . " WHERE url=?";
                $url = $db->getVal($query, $domain);

                if (!empty($dom) && !empty($url) && !empty($dom->subdomain)) {
                    safe_redirect("Location: http://{$dom->subdomain}.{$dom->domain}" . $_SERVER[ 'REQUEST_URI' ]);
                }
            }

            if ( !empty($result)) {
                if (!empty($result->url) && !empty($result->location)) {
                    safe_redirect("Location: " . $result->location);
                } else {
                    if (!empty($result->url)) {
                        safe_redirect("Location: landing.php");
                    }
                }
            }

            $params = array();
            if ( empty($subdomain) ) {
                $query = "SELECT * FROM " . Agency::AGENCIES_TABLE . " WHERE domain=? LIMIT 1";
                $params[] = $domain;
            }else {
                $query = "SELECT * FROM " . Agency::AGENCIES_TABLE . " WHERE domain=? LIMIT 1";
                $row = $db->getRow($query, "{$subdomain}.{$domain}");
                if (empty($row->name)) {
                    $query = "SELECT * FROM " . Agency::AGENCIES_TABLE . " ag," . Agents::AGENTS_TABLE . " a WHERE a.companyid=ag.agencyid AND ag.domain=? AND a.subdomain=? LIMIT 1";
                    $params[] = $domain;
                    $params[] = $subdomain;
                }
            }
            $row = $db->getRow($query, $params);
            if ( empty($row) || empty($row->name)) {
                $query = "SELECT * FROM " . Agency::AGENCIES_TABLE . " ag," . Server::LANDPAGES_TABLE . " l WHERE l.agencyid=ag.agencyid AND l.othgerurl=? LIMIT 1";
                $row = $db->getRow($query, $domain);

                $query = "SELECT dnschanged FROM " . Agency::AGENCIES_TABLE . " WHERE agencyid=?";
                $dns = $db->getval($query, $row->agencyid);

                if (!empty($row->agencyid) && 0 == $dns) {
                    safe_redirect("Location: " . Kunversion::KV_SITE);
                }

                if (!empty($row->agencyid) && !empty($subdomain)) {
                    $query = "SELECT * FROM " . Agency::AGENCIES_TABLE . " ag, " . Server::LANDPAGES_TABLE . " l, " . Agents::AGENTS_TABLE . " a WHERE l.agencyid=ag.agencyid AND l.otherurl=? AND a.companyid=ag.agencyid AND a.subdomain=? LIMIT 1";
                    $row = $db->getRow($query . array($domain, $subdomain));
                }
            }elseif( !empty($row->name) && 0 == $row->dnschanged && !stristr($domain, 'ecomls.com' ) ) {
                safe_redirect("Location: " . Kunversion::KV_SITE);
            }elseif( isset($row->agentid)) {
                $row->subdomain_agentid = $row->agentid;
            }
            if ( empty($row->name)) {
                safe_redirect("Location: http://www.{$domain}" . $_SERVER[ 'REQUEST_URI' ]);
            }

            $session = new Session();
            $session->set( 'settings', $row );

            $query = "SELECT adata FROM " . Agency::DTYPES_TABLE . " WHERE agencyid=?";
            $adata = $db->getVar($query, $row->agencyid);
            $styles = unserialize( $adata);
            if( isset($styles[0]) && !empty($styles[0]->style) ) {
                $query = "SELECT DISTINCT(style) as style, COUNT(style) as n 
                  FROM " . Listings::LISTINGS_TABLE . " l,
                   " . Agency::AGENCIESMLSSERVICES . " amls,
                   " . Agency::AGENCIESCOUNTIES_TABLE . " ac
                WHERE ac.countyname=l.county AND amls.agencyid=? AND l.type IN (1,2,31) AND ac.agencyid=? 
                   AND l.mls=amls.mlsid AND style!='' AND style NOT LIKE '%remarks%1' 
                   GROUP BY style HAVING n > 10 ORDER BY style ASC";
                $styles = $db->getResults($query, array($row->agencyid, $row->agencyid));

                $query = "INSERT INTO " . Agency::DTYPES_TABLE . " VALUES(?,?,?)";
                $db->query($query, array($row->agencyid, serialize($styles), mktime()));
            }
            $session->set('styles', $styles);
            

        }


        public static function getHost( $address=false )
        {
            $return = array();
            $address = $address ? $address : $_SERVER[ 'HTTP_HOST' ];
            $parseUrl = parse_url(trim(strtolower($address)));
            $host = !empty($parseUrl[ 'host' ]) ? $parseUrl[ 'host' ] : '';
            $path = explode('/', $parseUrl[ 'path' ], 2);
            $path = !empty($path) ? array_shift($path) : '';
            $temp = !empty($path) ? $host : $path;
            $temp = trim(str_replace('www.', '', $temp));
            $temp2 = explode('.', $temp);
            if (count($temp2) > 3) {
                $return = array( "{$temp2[1]}.{$temp2[2]}.{$temp2[3]}", $temp2[0] );
            } elseif (count($temp2) > 2) {
                $return = array(  "{$temp2[1]}.{$temp2[2]}", $temp2[0] );
            } else {
                $return =  array( $temp,'');
            }

            return $return;
        }

        public static function verifiedSubdomain( $domain, $subdomain )
        {
            $db = parent::getDb();
            $query = "SELECT * FROM " . Agents::AGENTS_TABLE . " a, " . Agency::AGENCIES_TABLE . "ag WHERE ag.domain=? AND agents.subdomain=? AND a.companyid=ag.agencyid";
            $result = $db->getRow($query, func_get_args());
            if (!empty($result)) {
                return true;
            }

            $query = "SELECT * FROM " . Agency::AGENCIES_TABLE . " WHERE domain=?";
            $result = $db->getRow($query, "{$domain}.{$subdomain}");
            if (!empty($result)) {
                return true;
            }
        }
    }