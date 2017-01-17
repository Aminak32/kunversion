<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/11/17
     * Time: 5:09 PM
     */

    namespace Kunversion;

    use Kunversion\Kunversion,
        Kunversion\Db,
        Kunversion\Cache;


    class Mls
    {

        public function AnyMlsServices($agencyId) {
            $cache_key = "get_mlsservices-" . $agencyId; // name from old KV code '__FUNCTION__'




    }