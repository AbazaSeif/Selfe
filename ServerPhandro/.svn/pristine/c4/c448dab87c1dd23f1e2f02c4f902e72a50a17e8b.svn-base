<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SMS
 *
 * @author abaza
 */
class SMS {

    var $ActivCode = '';
    var $User_ID = 0;

    public function SendSMS($UID, $MessageSMS, $Password = null) {
        if (isset($UID)) {
            $this->User_ID = $UID;
        } else {
            return null;
        }
        $UserManger = new UserMangment();
        $Phone = $UserManger->WhoIs($this->User_ID, FILED_USER_PHONE, KEY_TABLE_USER, true);
        if (is_null($Phone)) {
            return FAIL;
        }
        $this->ActivCode = $this->CreateActiveCode();
        $ApplicationName = SystemVariable(FILED_SYSTEM_APPLICATION_NAME);
        if (is_null($Password)) {
            $Message = urlencode($GLOBALS[CLASS_TOOLS]->Language(MessagesSystem($MessageSMS)));
        } else {
            $Message = urlencode($GLOBALS[CLASS_TOOLS]->Language(sprintf(MessagesSystem($MessageSMS), $this->ActivCode, $Password)));
        }


//Prepare you post parameters
        $postData = array(
            'authkey' => SystemVariable(FILED_SYSTEM_SMS_AUTH_KEY),
            'mobiles' => $Phone,
            'message' => $Message,
            'sender' => SystemVariable(FILED_SYSTEM_SMS_SENDER_ID),
            'route' => SystemVariable(FILED_SYSTEM_SMS_ROUTE),
            'response' => SystemVariable(FILED_SYSTEM_SMS_RESPONSE_TYPE),
            'unicode' => 0, //unicode=1 (for unicode SMS) or 0 if English
            'campaign' => $ApplicationName //Campaign name you wish to create.
        );

// init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => SystemVariable(FILED_SYSTEM_SMS_API_URL),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
                //,CURLOPT_FOLLOWLOCATION => true
        ));


//Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


//get response
        $Json = curl_exec($ch); //{"message":"3661676f3237323238393231","type":"success"}

        $Data = [
            FILED_SMS_ACTIVE_CODE => $this->ActivCode,
            FILED_SMS_STATUS => SMS_WAITING,
            FILED_SMS_APIKEY => $GLOBALS[CLASS_TOOLS]->GenerateApiKey(),
            FILED_SMS_UID => $this->User_ID
        ];

//Print error if any
        if (curl_errno($ch)) {
            return sprintf(MessagesSystem(ERROR_MESSAGE), curl_error($ch));
        }

        curl_close($ch);

        if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_SMS, $Data)) {

            $SMS_ID = $GLOBALS[CLASS_DATABASE]->lastInsertID();

            if ($this->Implimantation($Json, $SMS_ID)) {
                $this->LogSUCCESS("Done Send SMS Active Code " . print_r($Data, true), __FUNCTION__, __LINE__);
                return true;
            } else {
                $this->LogNOTICE("Can't Send SMS Active Code " . "Parameter : " . print_r($Data, true) . " Respons : " . print_r(json_decode($json), true), __FUNCTION__, __LINE__);
                return false;
            }
        } else {
            $this->LogERROR("MySQL ERROR : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
            return false;
        }
    }

    private function Implimantation($JSON, $SMS_ID) {
        $Data = array();

        $Data[FILED_SMS_RESEVED_SMS_ID] = $SMS_ID;

        $Output = json_decode($JSON, true);
        if (is_array($Output)) {
            foreach ($Output as $Key => $Value) {
                if ($Key == 'message') {
                    $Data[FILED_SMS_RESEVED_CODE] = $Value;
                }
                if ($Key == 'type') {
                    if ($Value == 'success') {
                        $Data[FILED_SMS_RESEVED_STATUS] = $Value;
                    } else {
                        $Data[FILED_SMS_RESEVED_STATUS] = $Value;
                    }
                }
            }
        }

        if (count($Data) > 0) {
            return $this->SetSMSHistory($Data);
        } else {
            return false;
        }
    }

    private function SetSMSHistory($Data = null) {
        if (!is_null($Data)) {
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_SMS_RESEVED_ACTIVE, $Data)) {
                return true;
            } else {
                return false;
            }
        } else {
            return null;
        }
    }

    public function GetSMSHistory($Where = null, $Filed = null) {
        if (!is_null($Where)) {
            $Data = $GLOBALS[CLASS_DATABASE]->select(TABLE_SMS, $Where, FILED_SMS_DATE_TIME);
            if (!is_null($Data)) {
                if (!is_null($Filed)) {
                    return $Data[$Filed];
                } else {
                    return $Data;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private function CreateActiveCode() {
        $Code = $GLOBALS[CLASS_TOOLS]->generateRandomString(true);
        $GLOBALS[CLASS_DATABASE]->rollback();
        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_SMS, [FILED_SMS_ACTIVE_CODE => $Code])) {
            $this->CreateActiveCode();
        } else {
            return $Code;
        }
    }

    public function SMS_Active() {
        $tmpSMS = [
            FILED_SMS_ACTIVE_CODE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SMS_ACTIVE_CODE),
        ];
        $tmpPHONE = [
            FILED_PHONE_IMEI => $GLOBALS[CLASS_TOOLS]->Serial_Number_Implimntation($GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_IMEI)),
            FILED_PHONE_SIM_SERIAL => $GLOBALS[CLASS_TOOLS]->Serial_Number_Implimntation($GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SIM_SERIAL))
        ];

        $SMS = $GLOBALS[CLASS_TOOLS]->removeNull($tmpSMS);
        $PHONE = $GLOBALS[CLASS_TOOLS]->removeNull($tmpPHONE);

        $Phone = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $PHONE);
        if (!is_null($Phone)) {
            $SMS[FILED_SMS_UID] = $UserID = $Phone[FILED_PHONE_UID];
            if (!is_null($GLOBALS[CLASS_DATABASE]->select(TABLE_SMS, $SMS))) {
                if ($GLOBALS[CLASS_DATABASE]->update(TABLE_USER, [FILED_USER_ACTIVATION => ON], [FILED_USER_ID => $UserID])) {
                    $UserMang = new UserMangment();
                    if (($UserMang->Change_Status_Line($UserID, ON, MessagesSystem(WELCOME_MESSAGE_IN_STATUS))) == SUCCESS) {
                        $Sync = new Syncronization();
                        return $Sync->SyncAll();
                    } else {
                        $this->LogERROR("Status Can't Change !!!", __FUNCTION__, __LINE__);
                        return FAIL;
                    }
                } else {
                    $this->LogERROR("Can't Update User Activation MySQL Error : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                    return FAIL;
                }
            } else {
                $this->LogERROR("Active Code Not Fund " . print_r($SMS, true), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("Table Phone Serial Number is Empty SMS Active Code :" . print_r($SMS, true) . " Phone : " . print_r($PHONE, true), __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    private function LogERROR($Message, $Function, $Line) {
        $GLOBALS[CLASS_TOOLS]->System_Log($Message, __CLASS__ . "::" . $Function, $Line, Tools::ERROR);
    }

    private function LogNOTICE($Message, $Function, $Line) {
        $GLOBALS[CLASS_TOOLS]->System_Log($Message, __CLASS__ . "::" . $Function, $Line, Tools::NOTICE);
    }

    private function LogSUCCESS($Message, $Function, $Line) {
        $GLOBALS[CLASS_TOOLS]->System_Log($Message, __CLASS__ . "::" . $Function, $Line, Tools::SUCCESS);
    }

}
