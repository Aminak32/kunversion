<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/16/17
     * Time: 4:56 PM
     */

    namespace Kunversion;

    use Kunversion\Kunversion as KV,
        Kunversion\Agency;

    class Lead extends Model
    {

        const LEADS_TABLE = 'leads';

        public function getLead($agencyId, $email, $phone='')
        {
            $db = parent::getDb();
            $email = KV::sanitizeEmail($email);
            $phone = !empty($phone) ? $db->quote($phone) : '';
            if (empty($agencyId) && empty(trim($email))) {
                return array();
            }

            $result = $this->getLeadBy('email', $email, $agencyId);
            if (!empty(trim($phone)) && !empty($result) && empty(trim($result->phone))) {
                $leadId = $result->leadid;
                $query = "UPDATE " . self::LEADS_TABLE . " SET phone=? WHERE leadid=?";
                $db->query($query, $phone, $leadId);
            }
            return $result;
        }

        public function getLeadBy($type, $value, $agencyId)
        {

            $db = parent::getDb();
            $query = "SELECT * FROM " . self::LEADS_TABLE . " l, " . Agents::AGENTS_TABLE . " a WHERE agencyid=? l.agentid=a.agentid ";
            $params = array();
            $params[] = $agencyId;
            switch ($type) {
                case 'email':
                    $query .= " AND email = ?";
                    $params[] = $value;
                    break;
                case 'id':
                    $query .= " AND l.leadid=?";
                    $params[] = $value;
                    break;
            }

            $result = $db->getRow($query, $params);
            return $result;
        }

    }