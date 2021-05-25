<?php

class VirtualCC extends Model
{
        function __construct() {
                parent::__construct();
        }
        
        function getVCCRouting()
        {
                $sql = "SELECT * FROM vcc_routing";
                $result = $this->getDB()->query($sql);

                if ($this->getDB()->getNumRows() == 1) {
		  return $result[0];
                }            

                return null;
        }
        
        function updateVCCRouting($routing)
        {
	  $sql = "UPDATE vcc_routing SET staffing='$routing->staffing', service_level='$routing->service_level', sl_threshold='$routing->sl_threshold', ".
	    "call_reduce_on_sl='$routing->call_reduce_on_sl', aht='$routing->aht', aht_threshold='$routing->aht_threshold', ".
	    "call_reduce_on_aht='$routing->call_reduce_on_aht', ahq='$routing->ahq', ahq_threshold='$routing->ahq_threshold', ".
	    "call_reduce_on_ahq='$routing->call_reduce_on_ahq' LIMIT 1";
	  return $this->getDB()->query($sql);
        }
        
        function numLocations()
        {
                $cond = '';
                $sql = "SELECT COUNT(vcc_id) AS numrows FROM vcc_id ";
                $result = $this->getDB()->query($sql);

                if($this->getDB()->getNumRows() == 1) {
                        return empty($result[0]->numrows) ? 0 : $result[0]->numrows;
                }

                return 0;
        }

        function getLocations($offset=0, $limit=0)
        {
                $sql = "SELECT * FROM vcc_profile ORDER BY vcc_id ";
                if ($limit > 0) $sql .= "LIMIT $offset, $limit";

                return $this->getDB()->query($sql);
        }
        
        function getLocationById($loc_id)
        {
            $sql = "SELECT * FROM vcc_profile WHERE vcc_id='$loc_id'";
            $result = $this->getDB()->query($sql);

            if ($this->getDB()->getNumRows() == 1) {
                  return $result[0];
            }

            return null;
        }
        
        function addLocation($vcc)
        {
                if (empty($vcc->vcc_id)) return false;

                $sql = "INSERT INTO vcc_profile SET ".
                        "vcc_id='$vcc->vcc_id', ".
                        "name='$vcc->name', ".
                        "call_ratio='$vcc->call_ratio'";

                if ($this->getDB()->query($sql)) {
                        $this->addToAuditLog('OCC Location', 'A', "VCC ID=".$vcc->vcc_id, "Name=$vcc->name");
                        return true;
                }
                return false;
        }
        
        function updateLocation($oldservice, $service)
        {
                if (empty($oldservice->vcc_id)) return false;
                $is_update = false;
                $changed_fields = '';
                $ltext = '';
                if ($service->vcc_id != $oldservice->vcc_id) {
                        $changed_fields .= "vcc_id='$service->vcc_id'";
                        $ltext = "VCC ID=$oldservice->vcc_id to $service->vcc_id";
                }
                if ($service->name != $oldservice->name) {
                        if (!empty($changed_fields)) $changed_fields .= ', ';
                        $changed_fields .= "name='$service->name'";
                        $ltext = $this->addAuditText($ltext, "Name=$oldservice->name to $service->name");
                }
                if ($service->call_ratio != $oldservice->call_ratio) {
                        if (!empty($changed_fields)) $changed_fields .= ', ';
                        $changed_fields .= "call_ratio='$service->call_ratio'";
                        $ltext = $this->addAuditText($ltext, "Call ratio=$oldservice->call_ratio to $service->call_ratio");
                }

                if (!empty($changed_fields)) {
                        $sql = "UPDATE vcc_profile SET $changed_fields WHERE vcc_id='$oldservice->vcc_id'";
                        $is_update = $this->getDB()->query($sql);
                }


                if ($is_update) {
                        $this->addToAuditLog('OCC Location', 'U', "VCC ID=".$oldservice->vcc_id, $ltext);
                }

                return $is_update;
        }

}
