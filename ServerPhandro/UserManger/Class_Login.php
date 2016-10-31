<?php

if (getcwd() == dirname(__FILE__)) {
    require '../System/ErrorPage.php';
    die(ShowError());
}

/**
 * Description of Class_Login
 *
 * @author abaza
 */
class Class_Login {

    var $UserMang = null;

    //put your code here
    //Function 1 : Log With Password if IMEI is Exist but SIM is diferant
    //Function 2 : Log With Password if SIM is Exist but IMEI is diferant

    function __construct() {


        $this->UserMang = new UserMangment();
    }

    public function Normal_Login() {
        $UserInfor = array();

        $Data = array(
            FILED_PHONE_IMEI => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_IMEI),
            FILED_PHONE_SIM_SERIAL => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SIM_SERIAL)
        );

        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($Data);

        if (!is_null($Data)) {
            if ($GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_PHONE_IMEI, $Data)) {
                $UserInfor = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $Data);
                if (is_null($UserInfor)) {
                    $GLOBALS[CLASS_TOOLS]->System_Log("NOT Login for IMEI : " . $Data[FILED_PHONE_IMEI] . " MYSQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__, Tools::ERROR);
                    return FAIL;
                } else {
                    $Retern = $this->UserMang->Get_User_Information($UserInfor[FILED_PHONE_UID]);
                    if (!is_null($Retern)) {
                        $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], ON);
                        $this->SaveUserIP_Address($UserInfor[FILED_PHONE_UID]);
                        return $Retern;
                    } else {
                        $GLOBALS[CLASS_TOOLS]->System_Log("Function Get_User_Information NOT retern data for user id " . $UserInfor[FILED_PHONE_UID], __FUNCTION__, __LINE__, Tools::ERROR);
                        $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], OFF);
                        return FAIL . USER_NOT_ACTIVATION;
                    }
                }
            } else if ($GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_PHONE_SIM_SERIAL, $Data)) {
                $UserInfor = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $Data);
                if (is_null($UserInfor)) {
                    $GLOBALS[CLASS_TOOLS]->System_Log("NOT Login for SIM Serial : " . $Data[FILED_PHONE_SIM_SERIAL] . " MYSQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__, Tools::ERROR);
                    return FAIL;
                } else {
                    $Retern = $this->UserMang->Get_User_Information($UserInfor[FILED_PHONE_UID]);
                    if (!is_null($Retern)) {
                        $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], ON);
                        $this->SaveUserIP_Address($UserInfor[FILED_PHONE_UID]);
                        return $Retern;
                    } else {
                        $GLOBALS[CLASS_TOOLS]->System_Log("Function Get_User_Information NOT retern data for user id " . $UserInfor[FILED_PHONE_UID], __FUNCTION__, __LINE__, Tools::ERROR);
                        return FAIL . USER_NOT_ACTIVATION;
                    }
                }
            }
        } else {
            $GLOBALS[CLASS_TOOLS]->System_Log("Login Data is EMPTY !!!!", __FUNCTION__, __LINE__, Tools::NOTICE);
            return FAIL;
        }
    }

    public function Password_Login() {
        $UserInfor = array();
        //TODO: Must update this function for case (if user change his phone and sim) 
        $Password = array(FILED_USER_PASSWORD => md5($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PASSWORD)));

        $Data = array(
            FILED_PHONE_IMEI => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_IMEI),
            FILED_PHONE_SIM_SERIAL => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SIM_SERIAL)
        );

        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($Data);
        $Password = $GLOBALS[CLASS_TOOLS]->removeNull($Password);

        if (is_null($Password)) {
            return FAIL . KEY_USER_PASSWORD;
        }

        if (!is_null($Data)) {
            if ($GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_PHONE_IMEI, $Data)) {
                $UserInfor = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $Data);
                if (is_null($UserInfor)) {
                    $GLOBALS[CLASS_TOOLS]->System_Log("NOT Login for IMEI : " . $Data[FILED_PHONE_IMEI] . " MYSQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__, Tools::ERROR);
                    return FAIL;
                } else {
                    $Retern = $this->UserMang->Get_User_Information($UserInfor[FILED_PHONE_UID]);
                    if (!is_null($Retern)) {
                        if (strstr(trim($Retern[FILED_USER_PASSWORD]), trim($Password[FILED_USER_PASSWORD]))) {
                            $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], OFF);
                            return FAIL . KEY_USER_PASSWORD;
                        } else {
                            $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], ON);
                            $this->SaveUserIP_Address($UserInfor[FILED_PHONE_UID]);
                            return $Retern;
                        }
                    } else {
                        $GLOBALS[CLASS_TOOLS]->System_Log("Function Get_User_Information NOT retern data for user id " . $UserInfor[FILED_PHONE_UID], __FUNCTION__, __LINE__, Tools::ERROR);
                        $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], OFF);
                        return FAIL . USER_NOT_ACTIVATION;
                    }
                }
            } else if ($GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_PHONE_SIM_SERIAL, $Data)) {
                $UserInfor = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $Data);
                if (is_null($UserInfor)) {
                    $GLOBALS[CLASS_TOOLS]->System_Log("NOT Login for SIM Serial : " . $Data[FILED_PHONE_SIM_SERIAL] . " MYSQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__, Tools::ERROR);
                    return FAIL;
                } else {
                    $Retern = $this->UserMang->Get_User_Information($UserInfor[FILED_PHONE_UID]);
                    if (!is_null($Retern)) {
                        if (strstr(trim($Retern[FILED_USER_PASSWORD]), trim($Password[FILED_USER_PASSWORD]))) {
                            $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], OFF);
                            return FAIL . KEY_USER_PASSWORD;
                        } else {
                            $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], ON);
                            $this->SaveUserIP_Address($UserInfor[FILED_PHONE_UID]);
                            return $Retern;
                        }
                    } else {
                        $GLOBALS[CLASS_TOOLS]->System_Log("Function Get_User_Information NOT retern data for user id " . $UserInfor[FILED_PHONE_UID], __FUNCTION__, __LINE__, Tools::ERROR);
                        $this->UserMang->Change_Status_Line($UserInfor[FILED_PHONE_UID], OFF);
                        return FAIL . USER_NOT_ACTIVATION;
                    }
                }
            }
        } else {
            $GLOBALS[CLASS_TOOLS]->System_Log("Login Data is EMPTY !!!!", __FUNCTION__, __LINE__, Tools::NOTICE);
            return FAIL;
        }
    }

    private function SaveUserIP_Address($USER_ID) {
        $CountrySIM = $CountryPhone = '';

        $Data = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, [FILED_PHONE_UID => $USER_ID]);
        if (!is_null($Data)) {
            $Network = $Data[FILED_PHONE_NETWORK];
            $Country = $Data[FILED_PHONE_COUNTRY];
            $NetworkCountry = $GLOBALS[CLASS_DATABASE]->select(TABLE_MOBILE_NETWORK, [FILED_MOBILE_NETWORK_ID => $Network]);
            $CountrySIM = $NetworkCountry[FILED_MOBILE_NETWORK_COUNTRY_NAME];

            $CountryName = $GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY, [FILED_COUNTRY_ID => $Country]);
            $CountryPhone = $CountryName[FILED_COUNTRY_NAME];
        }
        $DataLocation = $GLOBALS[CLASS_FILTER]->GetCountry();
        //" SIM : " . $CountrySIM . " PHONE : " . $CountryPhone
        $DataLocation['SIM'] = $CountrySIM;
        $DataLocation['PHONE'] = $CountryPhone;
        $DataLogin = [
            FILED_USER_IP_ADDRESS_UID => $USER_ID,
            FILED_USER_IP_ADDRESS_IP_ADDR => $GLOBALS[CLASS_FILTER]->GetIP(),
            FILED_USER_IP_ADDRESS_LOCATION => print_r($DataLocation, true),
        ];

        if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_IP_ADDRESS, $DataLogin)) {
            return true;
        } else {
            $GLOBALS[CLASS_TOOLS]->System_Log("ERROR MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __CLASS__ . "::" . __FUNCTION__, __LINE__, Tools::ERROR);
            return false;
        }
    }

}