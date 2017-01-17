<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/16/17
     * Time: 5:43 PM
     */

    namespace Kunversion;

    use Kunversion\Cache;

    class Agents extends Model
    {
        const AGENTS_TABLE = "agents";

        public function getAgents($agencyid)
        {
            $fields = array(
                'agentid',
                'companyid',
                'fname',
                'lname',
                'officephone',
                'cellphone',
                'companyemail',
                'gmail',
                'facebook',
                'twitter',
                'youtube',
                'gtalk',
                'bambuser',
                'asslender',
                'description',
                'agentcode',
                'startdate',
                'lastlogin',
                'photo',
                'subdomain',
                'active',
                'weight',
                'minprice',
                'maxprice',
                'badtowns',
                'inrotation',
                'primadonna',
                'roster',
                'listingmachine'
            );
            $cache_key = "get_agents-{$agencyid}";
            $results = Cache::instance()->get($cache_key);
            $db = parent::getDb();
            if (!$results) {
                $query = "SELECT a." . join(",a.",
                        $fields) . ", COUNT(b.blogid) as Total FROM " . self::AGENTS_TABLE . " a LEFT JOIN " . Blog::BLOG_TABLE . " b WHERE companyid=? AND active=1 ORDER BY lname,fname DESC";
                $results = $db->getResults($query, $agencyid);
                Cache::instance()->set($cache_key, $results);
            }
            return $results;
        }

            
    }