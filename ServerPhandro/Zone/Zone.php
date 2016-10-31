<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Zone
 *
 * @author abaza
 */
class Zone {

    var $UserManger;
    var $Firends;

    public function Maybe_this_your_Friends($UID) {
        if ($UID == 0) {
            return null;
        }
        $UserNetwork = 0;
        if ((!is_null($UID))) {
            $this->UserManger = new UserMangment();
            $this->Firends = new Friend_Class();

            $Users = $CountryTable = $NetworkTable = [];

            $Countrys = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_COUNTRY_ZONE, [FILED_USER_COUNTRY_ZONE_UID => $UID]);
            if (!is_null($Countrys)) {
                if (is_array($Countrys[0])) {
                    foreach ($Countrys as $CountryCode) {
                        if ($this->isThisZoneBlock($CountryCode[FILED_USER_COUNTRY_ZONE_COUNTRY])) {
                            $PhoneUsers = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_COUNTRY => $CountryCode[FILED_USER_COUNTRY_ZONE_COUNTRY]]);
                            if (!is_null($PhoneUsers)) {
                                if (is_array($PhoneUsers[0])) {
                                    foreach ($PhoneUsers as $UserUID) {
                                        if ($UserUID[FILED_PHONE_UID] == $UID) {
                                            $UserNetwork = $UserUID[FILED_PHONE_NETWORK];
                                            continue;
                                        }
                                        $User = $this->UserManger->WhoIs($UserUID[FILED_PHONE_UID], null, KEY_TABLE_USER);
                                        if (!$this->Firends->isFriends($UID, $User[FILED_USER_ID])) {
                                            if (!in_array($User, $CountryTable)) {
                                                array_push($CountryTable, $User);
                                            }
                                        }
                                    }
                                } else {
                                    if ($PhoneUsers[FILED_PHONE_UID] == $UID) {
                                        $UserNetwork = $UserUID[FILED_PHONE_NETWORK];
                                        continue;
                                    }
                                    $User = $this->UserManger->WhoIs($PhoneUsers[FILED_PHONE_UID], null, KEY_TABLE_USER);
                                    if (!$this->Firends->isFriends($UID, $User[FILED_USER_ID])) {
                                        if (!in_array($User, $CountryTable)) {
                                            array_push($CountryTable, $User);
                                        }
                                    }
                                }
                            } else {
                                continue;
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
#=========================================+
#======= Get Friend in One Network =======|
#=========================================+
            if ($UserNetwork == 0) {
                $tmp = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $UID]);
                if (!is_null($tmp)) {
                    $UserNetwork = $tmp[FILED_PHONE_NETWORK];
                }
            }

            if ($UserNetwork !== 0) {
                $UserNetworkQuiry = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_NETWORK => $UserNetwork]);
                if (!is_null($UserNetworkQuiry)) {
                    if (is_array($UserNetworkQuiry[0])) {
                        foreach ($UserNetworkQuiry as $Network) {
                            if ($Network[FILED_PHONE_UID] == $UID) {
                                continue;
                            }
                            $User = $this->UserManger->WhoIs($Network[FILED_PHONE_UID], null, KEY_TABLE_USER);
                            if (!$this->Firends->isFriends($UID, $User[FILED_USER_ID])) {
                                if (!in_array($User, $NetworkTable)) {
                                    array_push($NetworkTable, $User);
                                }
                            }
                        }
                    } else {
                        if ($UserNetworkQuiry[FILED_PHONE_UID] != $UID) {
                            $User = $this->UserManger->WhoIs($UserNetworkQuiry[FILED_PHONE_UID], null, KEY_TABLE_USER);
                            if (!$this->Firends->isFriends($UID, $User[FILED_USER_ID])) {
                                if (!in_array($User, $NetworkTable)) {
                                    array_push($NetworkTable, $User);
                                }
                            }
                        }
                    }
                }
            }

            //Final
            if (count($CountryTable) > 0) {
                $Users[KEY_TABLE_COUNTRY] = $GLOBALS[CLASS_TOOLS]->removeNull($CountryTable);
            }
            if (count($NetworkTable) > 0) {
                $Users[KEY_TABLE_MOBILE_NETWORK] = $GLOBALS[CLASS_TOOLS]->removeNull($NetworkTable);
            }
            //Delete not important fileds
            if (count($Users) > 0) {
                return $Users;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function Country_Zone($Country) {
        $Users_List = array();
        $this->UserManger = new UserMangment();

        $CountryCode = $GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY, $Country);
        if (!is_null($CountryCode)) {

            $ID = intval($CountryCode[FILED_COUNTRY_ID]);

            $Users = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_COUNTRY => $ID]);
            if (!is_null($Users)) {
                if (is_array($Users[0])) {
                    foreach ($Users as $UserUID) {

                        $User = $this->UserManger->WhoIs($UserUID[FILED_PHONE_UID]);
                        if (!in_array($User, $Users_List)) {
                            array_push($Users_List, $User);
                        }
                    }
                    if (count($Users_List) > 0) {
                        return $Users_List;
                    } else {
                        return NOT_FOUND;
                    }
                } else {
                    $User = $this->UserManger->WhoIs($UserUID[FILED_PHONE_UID]);
                    if (!in_array($User, $Users_List)) {
                        array_push($Users_List, $User);
                    }
                    if (count($Users_List) > 0) {
                        return $Users_List;
                    } else {
                        return NOT_FOUND;
                    }
                }
            }
        } else {
            return NOT_FOUND;
        }
    }

    public function Network_Zone($PhoneNetwork_Code) {
        $Users_List = array();
        $this->UserManger = new UserMangment();
        $Retern = '';
        if (array_key_exists(FILED_MOBILE_NETWORK_COUNTRY_NAME, $PhoneNetwork_Code)) {
            $Retern = $GLOBALS[CLASS_DATABASE]->select(TABLE_MOBILE_NETWORK, $PhoneNetwork_Code, FILED_MOBILE_NETWORK_ID, '', true);
        } else {
            $Retern = $GLOBALS[CLASS_DATABASE]->select(TABLE_MOBILE_NETWORK, $PhoneNetwork_Code, FILED_MOBILE_NETWORK_ID);
        }
        if (!is_null($Retern)) {
            if (is_array($Retern[0])) {

                foreach ($Retern as $Element_Retern) {

                    $NetworkID = [FILED_PHONE_NETWORK => $Element_Retern[FILED_MOBILE_NETWORK_ID]];

                    $Users = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $NetworkID);

                    if (!is_null($Users)) {

                        if (is_array($Users[0])) {

                            foreach ($Users as $element_user) {

                                $User = $this->UserManger->WhoIs($element_user[FILED_PHONE_UID]);
                                if (!in_array($User, $Users_List)) {
                                    array_push($Users_List, $User);
                                }

                                if (!in_array($User, $Users_List)) {
                                    array_push($Users_List, $User);
                                }
                            }
                            $Users = null;
                        } else {
                            $User = $this->UserManger->WhoIs($Users[FILED_PHONE_UID]);
                            if (!in_array($User, $Users_List)) {
                                array_push($Users_List, $User);
                            }

                            if (!in_array($User, $Users_List)) {
                                array_push($Users_List, $User);
                            }
                        }
                    } else {
                        return NOT_FOUND;
                    }
                }
                if (count($Users_List) > 0) {
                    return $Users_List;
                } else {
                    return NOT_FOUND;
                }
            } else {

                $NetworkID = [FILED_PHONE_NETWORK => $Retern[FILED_MOBILE_NETWORK_ID]];

                $Users = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $NetworkID);

                if (!is_null($Users)) {

                    if (is_array($Users[0])) {

                        foreach ($Users as $element_user) {

                            $User = $this->UserManger->WhoIs($element_user[FILED_PHONE_UID]);
                            if (!in_array($User, $Users_List)) {
                                array_push($Users_List, $User);
                            }

                            if (!in_array($User, $Users_List)) {
                                array_push($Users_List, $User);
                            }
                        }
                        $Users = null;
                    } else {
                        $User = $this->UserManger->WhoIs($Users[FILED_PHONE_UID]);
                        if (!in_array($User, $Users_List)) {
                            array_push($Users_List, $User);
                        }

                        if (!in_array($User, $Users_List)) {
                            array_push($Users_List, $User);
                        }
                    }
                } else {
                    return NOT_FOUND;
                }
                if (count($Users_List) > 0) {
                    return $Users_List;
                } else {
                    return NOT_FOUND;
                }
            }
        } else {
            return NOT_FOUND;
        }
    }

    private function isThisZoneBlock($Country_ID) {
        $Quiry = [FILED_COUNTRY_ID => $Country_ID, FILED_COUNTRY_BLOCK => OFF];
        $Country = $GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY, $Quiry);
        if (!is_null($Country)) {
            return false;
        } else {
            return true;
        }
    }

    private function isThisUserInCountactList() {
        //From Phone Number
    }

    public function isTheSimeZone($User1, $User2) {
        $Country_User_1 = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $User1]);
        $Country_User_2 = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $User2]);
        if ($Country_User_1[FILED_PHONE_COUNTRY] == $Country_User_2[FILED_PHONE_COUNTRY]) {
            return true;
        } else {
            return false;
        }
    }

    public function isTheSimeNetwork($User1, $User2) {
        $Country_User_1 = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $User1]);
        $Country_User_2 = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $User2]);
        if ($Country_User_1[FILED_PHONE_NETWORK] == $Country_User_2[FILED_PHONE_NETWORK]) {
            return true;
        } else {
            return false;
        }
    }

    ############################################
    ############ FOR FUNCTION WHOIS ############
    ############################################

    public function CountryTable($ID) { //Related to UserManger WhoIs
        $Country = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $ID]);
        if (!is_null($Country)) {
            $CountryCode = $Country[FILED_PHONE_COUNTRY];
            if (!$this->isThisZoneBlock($CountryCode)) {
                $tmpCountry = $GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY, [FILED_COUNTRY_ID => $CountryCode, FILED_COUNTRY_BLOCK => OFF]);
                if (!is_null($tmpCountry)) {
                    $Flag = DIR_FLAGS . $tmpCountry[FILED_COUNTRY_PATH_FLAG];
                    $URL = $GLOBALS[CLASS_TOOLS]->CreateURL($Flag);
                    $CountryTable[FILED_COUNTRY_ID] = $CountryCode;
                    $CountryTable[FILED_COUNTRY_NAME] = $tmpCountry[FILED_COUNTRY_NAME];
                    $CountryTable[FILED_COUNTRY_PATH_FLAG] = $URL;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
        return $GLOBALS[CLASS_TOOLS]->removeNull($CountryTable);
    }

    public function NetworkTable($ID) {//Related to UserManger WhoIs
        $net = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $ID]);
        $NetworkCode = $net[FILED_PHONE_NETWORK];
        if ($NetworkCode != 0) {
            $tmpnet = $GLOBALS[CLASS_DATABASE]->select(TABLE_MOBILE_NETWORK, [FILED_MOBILE_NETWORK_ID => $NetworkCode]);
            if (!is_null($tmpnet)) {
                $NetworkTable[FILED_MOBILE_NETWORK_COUNTRY_NAME] = $tmpnet[FILED_MOBILE_NETWORK_COUNTRY_NAME];
                $NetworkTable[FILED_MOBILE_NETWORK_NETWORK_NAME] = $tmpnet[FILED_MOBILE_NETWORK_NETWORK_NAME];
            } else {
                return null;
            }
        } else {
            return null;
        }
        return $GLOBALS[CLASS_TOOLS]->removeNull($NetworkTable);
    }

}
