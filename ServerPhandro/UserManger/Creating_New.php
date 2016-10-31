<?php

if (getcwd() == dirname(__FILE__)) {
    require '../System/ErrorPage.php';
    die(ShowError());
}
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Creating_New
 *
 * @author seif abaza
 */
class Creating_New {

    var $Filter = null;
    var $UUID = null;
    var $DIR_OPRATION = null;
    var $PasswordUser = '';
    var $Image = null;

    function initCreateNew() {

        if (is_null($this->DIR_OPRATION)) {
            if (class_exists(FileSystem)) {
                $this->DIR_OPRATION = new FileSystem();
            }
        }
        if (is_null($this->UUID)) {
            if (class_exists(UUID)) {
                $this->UUID = new UUID();
            }
        }
    }

    public function Create_New_User() {
        $this->initCreateNew();
        $UUID = null;
        $this->PasswordUser = $GLOBALS[CLASS_TOOLS]->generateRandomString();
        //Creating UUID
        do {
            $UUID = $this->UUID->v4();
        } while (!$this->UUID->is_valid($UUID));

        //Set Data
        $User = [
            FILED_USER_UUID => $UUID,
            FILED_USER_NAME => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_NAME)),
            FILED_USER_PHONE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHONE),
            FILED_USER_AGE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AGE),
            FILED_USER_EMAIL_ADDRESS => filter_var($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_EMAIL_ADDRESS), FILTER_VALIDATE_EMAIL),
            FILED_USER_SEX => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_SEX),
            FILED_USER_PASSWORD => md5($this->PasswordUser),
            FILED_USER_LANGUAGE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_LANGUAGE),
            FILED_USER_IS_FAMOUS => 0,
            FILED_USER_IS_VIP => 0,
            FILED_USER_ACTIVATION => OFF
        ];

        $Phone = [
            FILED_PHONE_IMEI => $GLOBALS[CLASS_TOOLS]->Serial_Number_Implimntation($GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_IMEI)),
            FILED_PHONE_SIM_SERIAL => $GLOBALS[CLASS_TOOLS]->Serial_Number_Implimntation($GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SIM_SERIAL)),
            FILED_PHONE_SUBSCRIBERID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SUBSCRIBERID),
            FILED_PHONE_NETWORK => 0,
            FILED_PHONE_COUNTRY => 0
        ];

        $Network = [
            FILED_MOBILE_NETWORK_NETWORK_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_NETWORK_NAME), //AQUAFON
            FILED_MOBILE_NETWORK_COUNTRY_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_COUNTRY_NAME), //ge
            FILED_MOBILE_NETWORK_CODE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_CODE) //28967
        ];

        $Country = [
            FILED_COUNTRY_ISO => $GLOBALS[CLASS_FILTER]->FilterData(KEY_COUNTRY_ISO) //ge
        ];

        //Filter Null values
        $User = $GLOBALS[CLASS_TOOLS]->removeNull($User);
        $Phone = $GLOBALS[CLASS_TOOLS]->removeNull($Phone);
        $Network = $GLOBALS[CLASS_TOOLS]->removeNull($Network);
        $Country = $GLOBALS[CLASS_TOOLS]->removeNull($Country);

        //Make sure about some keys
        if (!$GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_USER_NAME, $User)) {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "User Name Not Found");
            return FAIL . KEY_USER_NAME;
        }
        if (!$GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_PHONE_IMEI, $Phone)) {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Phone IMEI Not Found");
            return FAIL . PHONE_IMEI;
        }
        if (!$GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_PHONE_SIM_SERIAL, $Phone)) {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Phone SIM Serial Number Not Found");
            return FAIL . PHONE_SIM_SERIAL_NUMBER;
        }

        $SearchisExist = array(
            FILED_PHONE_IMEI => $Phone[FILED_PHONE_IMEI],
            FILED_PHONE_SIM_SERIAL => $Phone[FILED_PHONE_SIM_SERIAL]
        );
        $SearchisExistPhone = array(
            FILED_USER_PHONE => $User[FILED_USER_PHONE],
            FILED_USER_EMAIL_ADDRESS => $User[FILED_USER_EMAIL_ADDRESS]
        );

        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_USER, $SearchisExistPhone, '', '', false, 'OR')) {
            return FAIL . PHONE_EXIST;
        }
        //get ID for Country
        $COUNTRYRetern = $GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY, $Country);
        if (!is_null($COUNTRYRetern)) {
            $COUNTRY_ID = $COUNTRYRetern[FILED_COUNTRY_ID];
        } else {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Found Country not in Database");
            die(ShowError());
            exit();
        }

        $Phone = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_PHONE_COUNTRY, $COUNTRY_ID, $Phone);

        //get ID for Network
        $NetworkCode = array(FILED_MOBILE_NETWORK_CODE => $Network[FILED_MOBILE_NETWORK_CODE]);

        $NetworkRetern = $GLOBALS[CLASS_DATABASE]->select(TABLE_MOBILE_NETWORK, $NetworkCode, FILED_MOBILE_NETWORK_ID, 1);

        if (!is_null($NetworkRetern)) {
            $NETWORK_ID = $NetworkRetern[FILED_MOBILE_NETWORK_ID];
        } else {
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_MOBILE_NETWORK, $Network)) {
                $NETWORK_ID = $GLOBALS[CLASS_DATABASE]->lastInsertID();
            }
        }
        $Phone = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_PHONE_NETWORK, $NETWORK_ID, $Phone);


        //Install Data in Database
        if (!$GLOBALS[CLASS_DATABASE]->isExist(TABLE_PHONE, $SearchisExist, '', 1, false, 'OR')) {
            //Create User Dir    
            $User[FILED_USER_DIR_PATH] = $this->Create_Dir($User[FILED_USER_UUID]);

            if ($User[FILED_USER_DIR_PATH] == false) {
                $this->User_Log(__FUNCTION__, __LINE__, "ERROR", "Can not Create User Dir");
                return FAIL . FILED_USER_DIR_PATH;
            }

            //Inser New User
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER, $User)) {
                //Get ID and insert it in Phone Table
                $Phone[FILED_UID] = $UserID = $GLOBALS[CLASS_DATABASE]->lastInsertID();

                if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_PHONE, $Phone)) {
                    //Save in Country Zone
                    $Zone = array(
                        FILED_USER_COUNTRY_ZONE_UID => $UserID,
                        FILED_USER_COUNTRY_ZONE_COUNTRY => $Phone[FILED_PHONE_COUNTRY]
                    );
                    if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_COUNTRY_ZONE, $Zone)) {
                        if (SystemVariable(FILED_SYSTEM_SWITCH_MAIL) == true) {
                            //Send Email to User
                            if ($this->MailToUser($User[FILED_USER_NAME], $User[FILED_USER_EMAIL_ADDRESS], $this->PasswordUser)) {
                                $this->User_Log(__FUNCTION__, __LINE__, "OK", "Create New User is Done : UUID : " . $UUID);
                            } else {
                                $this->User_Log(__FUNCTION__, __LINE__, "OK", "Create New User is Done : UUID : " . $UUID . " But SMS Not Send");
                            }
                        }
                        if (SystemVariable(FILED_SYSTEM_SWITCH_SMS) == true) {
                            //Send SMS
                            $SMS = new SMS();
                            if ($SMS->SendSMS($UserID, ACTIVE_CODE, $this->PasswordUser)) {
                                $this->User_Log(__FUNCTION__, __LINE__, "OK", "Create New User is Done : UUID : " . $UUID);
                            } else {
                                $this->User_Log(__FUNCTION__, __LINE__, "OK", "Create New User is Done : UUID : " . $UUID . " But SMS Not Send");
                            }
                        }
                        return SUCCESS;
                    } else {
                        $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "When Save Country Zone: " . print_r($Zone, true));
                        return FAIL . DATABASE;
                    }
                }
            }
        } else {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "This Phone IMEI is Exist : " . $User[FILED_USER_NAME] .
                    "-" . $User[FILED_USER_PHONE] . "-" . $User[FILED_PHONE_IMEI] . "-" . $User[FILED_PHONE_SIM_SERIAL]);
            return FAIL . PHONE_EXIST;
        }
        return USER_EXIST;
    }

    public function Edite_User() {
        $USER_UUID = array(
            FILED_USER_UUID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_UUID)
        );

        if (is_null($GLOBALS[CLASS_TOOLS]->getValue(FILED_USER_UUID, $USER_UUID))) {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "ERROR Not Found UUID");
            return FAIL . KEY_USER_UUID;
        }

        $User = array(
            FILED_USER_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_NAME),
            FILED_USER_PHONE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHONE),
            FILED_USER_AGE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AGE),
            FILED_USER_EMAIL_ADDRESS => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_EMAIL_ADDRESS),
            FILED_USER_SEX => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_SEX),
            FILED_USER_PASSWORD => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PASSWORD),
            FILED_USER_IS_FAMOUS => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_IS_FAMOUS),
            FILED_USER_IS_VIP => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_IS_VIP),
            FILED_USER_LANGUAGE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_LANGUAGE)
        );

        $Phone = array(
            FILED_PHONE_IMEI => $GLOBALS[CLASS_TOOLS]->Serial_Number_Implimntation($GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_IMEI)),
            FILED_PHONE_SIM_SERIAL => $GLOBALS[CLASS_TOOLS]->Serial_Number_Implimntation($GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SIM_SERIAL)),
            FILED_PHONE_SUBSCRIBERID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SUBSCRIBERID),
            FILED_PHONE_NETWORK => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_NETWORK),
            FILED_PHONE_COUNTRY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_COUNTRY)
        );

        $Network = array(
            FILED_MOBILE_NETWORK_NETWORK_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_NETWORK_NAME), //AQUAFON
            FILED_MOBILE_NETWORK_COUNTRY_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_COUNTRY_NAME), //ge
            FILED_MOBILE_NETWORK_CODE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_CODE) //28967
        );
        $Country = array(
            FILED_COUNTRY_ISO => $GLOBALS[CLASS_FILTER]->FilterData(KEY_COUNTRY_ISO) //ge
        );

        //Filter Null values
        $User = $GLOBALS[CLASS_TOOLS]->removeNull($User);
        $Phone = $GLOBALS[CLASS_TOOLS]->removeNull($Phone);
        $Network = $GLOBALS[CLASS_TOOLS]->removeNull($Network);
        $Country = $GLOBALS[CLASS_TOOLS]->removeNull($Country);


        //get ID for Country
        $COUNTRYRetern = $GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY, $Country);
        if (!is_null($COUNTRYRetern)) {
            $COUNTRY_ID = $COUNTRYRetern[FILED_COUNTRY_ID];
        } else {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Found Country not in Database");
            die(ShowError());
            exit();
        }

        $Phone = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_PHONE_COUNTRY, $COUNTRY_ID, $Phone);

        //get ID for Network
        $NetworkCode = array(FILED_MOBILE_NETWORK_CODE => $Network[FILED_MOBILE_NETWORK_CODE]);

        $NetworkRetern = $GLOBALS[CLASS_DATABASE]->select(TABLE_MOBILE_NETWORK, $NetworkCode, FILED_MOBILE_NETWORK_ID, 1);
        if (!is_null($NetworkRetern)) {
            $NETWORK_ID = $NetworkRetern[FILED_MOBILE_NETWORK_ID];
        } else {
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_MOBILE_NETWORK, $Network)) {
                $NETWORK_ID = $GLOBALS[CLASS_DATABASE]->lastInsertID();
            }
        }
        $Phone = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_PHONE_NETWORK, $NETWORK_ID, $Phone);

        //Make sure this UUID in Database or not
        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_USER, $USER_UUID)) {
            //Yes in Database
            //Get ID
            $UID = null;

            $Ret = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $USER_UUID);
            if (!is_null($Ret)) {
                $UID = $Ret[FILED_USER_ID];
                $Where = array(FILED_UID => $UID);
                if (count($User) > 0) {
                    if (!$GLOBALS[CLASS_DATABASE]->update(TABLE_USER, $User, $USER_UUID)) {
                        $this->User_Log(__FUNCTION__, __LINE__, "ERROR", $GLOBALS[CLASS_DATABASE]->ReturnError());
                        return FAIL . DATABASE;
                    }
                }
                if (count($Phone) > 0) {
                    if (!$GLOBALS[CLASS_DATABASE]->update(TABLE_PHONE, $Phone, $Where)) {
                        $this->User_Log(__FUNCTION__, __LINE__, "ERROR", $GLOBALS[CLASS_DATABASE]->ReturnError());
                        return FAIL . DATABASE;
                    }
                }
                $this->User_Log(__FUNCTION__, __LINE__, "OK", "Update is Done for User UUID " . $USER_UUID[FILED_USER_UUID]);
                $Sync = new Syncronization();
                return $Sync->SyncAll();
            } else {
                $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Not found User UUID");
                return FAIL . KEY_USER_UUID;
            }
        } else {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Not found User UUID");
            return FAIL . KEY_USER_UUID;
        }
    }

    public function Delete_User() {
        $DataUser = array(
            FILED_USER_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_ID),
            FILED_USER_PASSWORD => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PASSWORD)
        );

        if (is_null($GLOBALS[CLASS_TOOLS]->getValue(FILED_USER_ID, $DataUser))) {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Not found User UUID");
            return FAIL . KEY_USER_UUID;
        }
        if (is_null($GLOBALS[CLASS_TOOLS]->getValue(FILED_USER_PASSWORD, $DataUser))) {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Not found User Password");
            return FAIL . KEY_USER_PASSWORD;
        }

        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_USER, $DataUser)) {
            //Yes in Database
            //Get ID

            $Ret = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $DataUser);
            if (!is_null($Ret)) {
                $SwitchOFFUser = array(
                    FILED_USER_ACTIVATION => OFF
                );

                if ($GLOBALS[CLASS_DATABASE]->update(TABLE_USER, $SwitchOFFUser, $Ret)) {
                    $this->User_Log(__FUNCTION__, __LINE__, "OK", "Delete User " . $DataUser[FILED_USER_UUID] . " is removed done");
                    return SUCCESS;
                } else {
                    $this->User_Log(__FUNCTION__, __LINE__, "ERROR", "Delete User " . $DataUser[FILED_USER_UUID] . " is NOT removed ");
                    return FAIL;
                }
            } else {
                $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Not found User UUID");
                return FAIL . KEY_USER_UUID;
            }
        } else {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR USER", "Not found User UUID");
            return FAIL . KEY_USER_UUID;
        }
    }

    private function Create_Dir($UUID) {
        if ($this->UUID->is_valid($UUID)) {
            $Path = opendir(PATH_IMAGE_USERS);
            if (!$Path) {
                $this->User_Log(__FUNCTION__, __LINE__, "ERROR", "Can't Open File " . PATH_IMAGE_USERS);
                die($GLOBALS[CLASS_TOOLS]->ShowDie($GLOBALS[CLASS_TOOLS]->ShowDie("Can't Open File " . PATH_IMAGE_USERS)));
                closedir();
            } else {
                closedir();
                $New_Dir = PATH_IMAGE_USERS . DIRECTORY_SEPARATOR . $UUID;
                if (!$this->DIR_OPRATION->exists($New_Dir)) {
                    $Retern = $this->DIR_OPRATION->mkdir($New_Dir);
                    $this->User_Log(__FUNCTION__, __LINE__, "OK", "Creating Done Dir " . $New_Dir);
                } else {
                    $this->DIR_OPRATION->move($New_Dir, $New_Dir . '_bk');
                    $this->Create_Dir($UUID);
                }

                $QrBarcode = PATH_IMAGE_USERS . DIRECTORY_SEPARATOR . $UUID . DIRECTORY_SEPARATOR . BARCODE;
                if (!$this->DIR_OPRATION->exists($QrBarcode)) {
                    if ($this->DIR_OPRATION->mkdir($QrBarcode)) {
                        $filename = $QrBarcode . DIRECTORY_SEPARATOR . $UUID . '.png';
                        QRCode::png($UUID, $filename, 'H', 5);
                    }
                }

                return $Retern;
            }
        } else {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR", "UUID Not Valid" . $UUID);
            die(ShowError());
        }
    }

    private function User_Log($FunctionName, $LINE, $Categury, $Message) {
        if (SystemVariable(FILED_SYSTEM_LOG_SYSTEM)) {
            $ClassName = __CLASS__;

            $Log = "(" . $Categury . ") " . $ClassName . "::" . $FunctionName . "::" . $LINE . " = " . $Message;

            $MessageLog = array(
                FILED_USER_LOG_ACTION => $Log
            );

            $GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_LOG, $MessageLog);
            $GLOBALS[CLASS_TOOLS]->System_Log($MessageLog, $FunctionName, $LINE, Tools::NOTICE, true);

            if ($GLOBALS[CLASS_TOOLS]->isDebug()) {
                $GLOBALS[CLASS_TOOLS]->Show($MessageLog);
            }
        }
    }

    private function MailToUser($Name, $Mail, $Password) {
        $Message = "Dear " . $Name . PHP_EOL .
                " Thank you for make account in " . SystemVariable(FILED_SYSTEM_APPLICATION_NAME) . " we are " . PHP_EOL .
                "hope to be happy with us ." . PHP_EOL .
                "Your Password : " . $Password;

        $header = "From:" . SystemVariable(FILED_SYSTEM_SERVER_MAIL) . " \r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html\r\n";

        try {
            if (mail($Mail, "Welcome to " . SystemVariable(FILED_SYSTEM_APPLICATION_NAME), $GLOBALS[CLASS_TOOLS]->Language($Message), $header)) {
                $this->User_Log(__FUNCTION__, __LINE__, "SUCCESS", "The Message is Send done to " . $Mail);
                return true;
            } else {
                $this->User_Log(__FUNCTION__, __LINE__, "ERROR", "The Message not send");
                return false;
            }
        } catch (Exception $ex) {
            $this->User_Log(__FUNCTION__, __LINE__, "ERROR", "The Message not send Error : " . $ex->getMessage());
        }
    }

    function __destruct() {
        if (!is_null($this->DIR_OPRATION)) {
            unset($this->DIR_OPRATION);
        }
        if (!is_null($this->UUID)) {
            unset($this->UUID);
        }
    }

}