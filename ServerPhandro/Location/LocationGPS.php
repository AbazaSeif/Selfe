<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LocationGPS
 *
 * @author abaza
 */
class LocationGPS {

    public function Set_Location() {
        $tmp_locatoin = [
            FILED_GPS_LOCATIONS_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_UID),
            FILED_GPS_LOCATIONS_ACCURACY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_ACCURACY),
            FILED_GPS_LOCATIONS_DIRECTION => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_DIRECTION),
            FILED_GPS_LOCATIONS_DISTANCE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_DISTANCE),
            FILED_GPS_LOCATIONS_EVENTTYPE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_EVENTTYPE),
            FILED_GPS_LOCATIONS_EXTRAINFO => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_EXTRAINFO),
            FILED_GPS_LOCATIONS_GPSTIME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_GPSTIME),
            FILED_GPS_LOCATIONS_LASTUPDATE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_LASTUPDATE),
            FILED_GPS_LOCATIONS_LATITUDE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_LATITUDE),
            FILED_GPS_LOCATIONS_LOCATIONMETHOD => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_LOCATIONMETHOD),
            FILED_GPS_LOCATIONS_LONGITUDE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_LONGITUDE),
            FILED_GPS_LOCATIONS_SPEED => $GLOBALS[CLASS_FILTER]->FilterData(KEY_GPS_LOCATIONS_SPEED)
        ];

        $Location = $GLOBALS[CLASS_TOOLS]->removeNull($tmp_locatoin);
        if($GLOBALS[CLASS_DATABASE]->insert(TABLE_GPS_LOCATIONS,$Location)){
            
        }
        
    }

    public function Get_Location($User_ID) {
        
    }

    public function Neer_User() {
        
    }

    public function Where_is_this_Place() {
        
    }

}
