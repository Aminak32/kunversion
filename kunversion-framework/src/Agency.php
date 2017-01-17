<?php


    namespace Kunversion;

    use Kunversion\Cache;

    class Agency extends Model
    {

        const AGENCIES_TABLE = "agencies";
        const RESOURCES_TABLE = 'resources';
        const SERVICEDAREAS_TABLE = 'servicedareas';
        const LISTINGTYPESAGENCIES_TABLE = 'listingtypesagencies';
        const LISTINGTYPES_TABLE = 'listingtypes';
        const AGENCIESCOUNTIES_TABLE = 'agenciescounties';
        const AGENCIESMLSSERVICES = "agenciesmlsservices";
        const DTYPES_TABLE = 'dtypes';

        public function getResources( $id )
        {
            $cache = "get_resources-{$id}";
            $results = Cache::instance()->get($cache);
            if (empty($results)) {

                $db = parent::getDb();
                $query = "SELECT title, slug, url, category FROM " . self::RESOURCES_TABLE .
                    " WHERE agencyid=? AND showinnav=1 ORDER BY category DESC, title ASC";
                $results = $db->getResults($query, $id);
                Cache::instance()->set($cache, $results, 30);
            }
            return $results;
        }

        public function getServicedAreas($id )
        {
            $cache = "get_servicedareas-{$id}";
            $results = Cache::instance()->get($cache);
            if (empty($results)) {
                $db = parent::getDb();
                $query = "SELECT * FROM " . self::SERVICEDAREAS_TABLE . " WHERE agencyid=?";
                $results = $db->getResults($query, $id);
                Cache::instance()->set($cache, $results, 2);
            }
            return $results;
        }

        public function getTypes( $id )
        {
            $cache = "get_types-{$id}";
            $results = Cache::instance()->get($cache);
            if (empty($results)) {
                $db = parent::getDb();
                $query = "SELECT * FROM " . self::LISTINGTYPESAGENCIES_TABLE . " lta," . self::LISTINGTYPES_TABLE . " lt 
                    WHERE lta.listingtypeid=lt.agencyid AND lta.agencyid=? ORDER BY lta.order ASC";
                $results = $db->getResults($query, $id);
                Cache::instance()->set($cache, $results, 2);
            }
            return $results;
        }

        public function getAgencyCountiesServed( $id )
        {
            $cache = "get_agentcounties_served-{$id}";
            $results = Cache::instance()->get($cache);
            if (empty($results)) {
                $db = parent::getDb();
                $query = "SELECT countyname,statename FROM " . self::AGENCIESCOUNTIES_TABLE . " WHERE agencyid=?";
                $results = $db->getResults($query, $id);
                Cache::instance()->set($cache, $results, 60);
            }
            return $results;
        }

        public function getAgencyCounties( $id, $coverageOnly=false)
        {
            if (!$coverageOnly) {
                return array();
            } else {
                return $this->getAgencyCountiesServed($id);
            }
        }
    }