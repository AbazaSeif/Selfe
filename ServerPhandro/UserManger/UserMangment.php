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
 * Description of UserMangment
 *
 * @author abaza
 */
class UserMangment extends Zone {

    var $UUID = null;
    var $Multimedia = null;
    var $Sync = null;

    function __construct() {

        if (is_null($this->UUID)) {
            $this->UUID = new UUID();
        }
        if (is_null($this->Multimedia)) {
            $this->Multimedia = new Maltimedia_module();
        }
    }

    function __destruct() {
        unset($this->UUID);
        unset($this->Multimedia);
        unset($this->Sync);
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

    public function setNotification($From, $To, $Action, $Label) {
        $Serial = $this->UUID->generate_license();
        $Data = array(
            FILED_NOTIFICATIONS_FROM => $From,
            FILED_NOTIFICATIONS_TO => $To,
            FILED_NOTIFICATIONS_ACTION => $Action,
            FILED_NOTIFICATIONS_ISNEW => ON,
            FILED_NOTIFICATIONS_LABEL => $Label,
            FILED_NOTIFICATIONS_SERIAL => $Serial
        );

        $Notifcation = $GLOBALS[CLASS_TOOLS]->removeNull($Data);
        if (!is_null($Notifcation)) {
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_NOTIFICATIONS, $Notifcation)) {
                return $Serial;
            } else {
                $this->LogERROR("ERROR MySQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return null;
            }
        } else {
            return null;
        }
    }

    public function getNotification() {
        $Retern = array();

        $tmpData = array(
            FILED_NOTIFICATIONS_TO => $GLOBALS[CLASS_FILTER]->FilterData(KEY_NOTIFICATIONS_TO),
            FILED_NOTIFICATIONS_SERIAL => $GLOBALS[CLASS_FILTER]->FilterData(KEY_NOTIFICATIONS_SERIAL),
            FILED_NOTIFICATIONS_ISNEW => ON
        );

        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($tmpData);
        if (!is_null($Data)) {
            $FilteringArray = $GLOBALS[CLASS_DATABASE]->select(TABLE_NOTIFICATIONS, $Data, FILED_NOTIFICATIONS_DATE_TIME);
            if (count($FilteringArray) > 0) {
                foreach ($FilteringArray as $element) {
                    $element = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_NOTIFICATIONS_ACTION, $element);
                    $element = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_NOTIFICATIONS_ID, $element);
                    $element = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_NOTIFICATIONS_ISNEW, $element);
                    $element = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_NOTIFICATIONS_TO, $element);
                    array_push($Retern, $element);
                }

                //Make all New Notification is old
                $this->set_Notification_Old($Data[FILED_NOTIFICATIONS_TO]);
                return $Retern;
            } else {
                return NO_NEW;
            }
        }
    }

    public function set_Notification_Old($To) {
        $Update = array(
            FILED_NOTIFICATIONS_TO => $To,
            FILED_NOTIFICATIONS_ISNEW => OFF
        );
        if ($GLOBALS[CLASS_DATABASE]->update(TABLE_NOTIFICATIONS, $Update, $Update)) {
            return true;
        } else {
            $this->LogERROR("Error In Set Notification Old " . $To, __FUNCTION__, __LINE__);
            return false;
        }
    }

    public function isThisUserExist($User_ID) {
        $UserCheck1 = array(
            FILED_USER_ID => $User_ID
        );
        return $GLOBALS[CLASS_DATABASE]->isExist(TABLE_USER, $UserCheck1);
    }

    private function Basic($Data) {
        $Basic = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $Data);
        if (is_null($Basic)) {
            return null;
        }
        return $GLOBALS[CLASS_TOOLS]->removeNull($Basic);
    }

    private function Photo($ID) {
        $photo = $this->Get_User_Profile_Photo($ID);
        if (!is_null($photo)) {
            $PhotoProfile = [FILED_USER_PHOTO_PHOTO_PATH => $photo];
        } else {
            return null;
        }
        return $GLOBALS[CLASS_TOOLS]->removeNull($PhotoProfile);
    }

    private function Status($ID) {
        return $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_USER_ONLINE, [FILED_USER_ONLINE_ID => $ID], FILED_USER_ONLINE_DATE_TIME, 1));
    }

    public function WhoIs($ID, $Filed = null, $Table = null, $Information = false) {
        if (isset($ID)) {
            $Data = array();
            if ($Information) {
                $Data = array(
                    FILED_USER_ID => $ID
                );
            } else {
                $Data = array(
                    FILED_USER_ID => $ID,
                    FILED_USER_ACTIVATION => ON
                );
            }
            $DataUser = $Basic = $PhotoProfile = $CountryTable = $NetworkTable = $Status = [];
            if (!is_null($Table)) {
                switch ($Table) {
                    case KEY_TABLE_USER:
                        if (is_null($Filed)) {
                            return $this->Basic($Data);
                        } else {
                            return ($this->Basic($Data)[$Filed]);
                        }
                        break;
                    case KEY_TABLE_USER_PHOTO:
                        if (is_null($Filed)) {
                            return $this->Photo($ID);
                        } else {
                            return ($this->Photo($ID)[$Filed]);
                        }
                        break;
                    case KEY_TABLE_COUNTRY:
                        if (is_null($Filed)) {
                            return $this->CountryTable($ID); //TODO: Move to Zone Class
                        } else {
                            return ($this->CountryTable($ID)[$Filed]);
                        }
                        break;
                    case KEY_TABLE_MOBILE_NETWORK:
                        if (is_null($Filed)) {
                            return $this->NetworkTable($ID); //TODO: Move to Zone Class
                        } else {
                            return ($this->NetworkTable($ID)[$Filed]); //TODO: Move to Zone Class
                        }
                        break;
                    case KEY_TABLE_USER_ONLINE:
                        if (is_null($Filed)) {
                            return $this->Status($ID);
                        } else {
                            return $this->Status($ID)[$Filed];
                        }
                        break;
                    case KEY_TABLE_USER_POINT:
                        if (is_null($Filed)) {
                            return $this->GetUserPoint($ID);
                        } else {
                            return $this->GetUserPoint($ID)[$Filed];
                        }
                        break;
                    case KEY_TABLE_USER_STOCK:
                        if (is_null($Filed)) {
                            return $this->GetUserPoint($ID);
                        } else {
                            return $this->GetUserPoint($ID)[$Filed];
                        }
                        break;
                }
            } else {
                # Basic Information [KEY_TABLE_USER]
                $Basic = $this->Basic($Data);
                # Profile Photo [KEY_TABLE_USER_PHOTO]
                $PhotoProfile = $this->Photo($ID);
                # Country Name and Code [KEY_TABLE_COUNTRY]
                $CountryTable = $this->CountryTable($ID);     //TODO:Move to Zone Class
                # Network Name and Code [KEY_TABLE_MOBILE_NETWORK]
                $NetworkTable = $this->NetworkTable($ID); //TODO: Move to Zone Class
                # Status User Now [KEY_TABLE_USER_ONLINE]
                $Status = $this->Status($ID);
                # Points User Now [KEY_TABLE_USER_POINT]
                $Points = $this->GetUserPoint($ID);
                # Stock User Now [KEY_TABLE_USER_STOCK]
                $Stock = $this->GetUserPoint($ID);
            }

#Insert In Main Array
            $DataUser[KEY_TABLE_USER] = $GLOBALS[CLASS_TOOLS]->removeNull($Basic);
            $DataUser[KEY_TABLE_USER_PHOTO] = $GLOBALS[CLASS_TOOLS]->removeNull($PhotoProfile);
            $DataUser[KEY_TABLE_COUNTRY] = $GLOBALS[CLASS_TOOLS]->removeNull($CountryTable);
            $DataUser[KEY_TABLE_MOBILE_NETWORK] = $GLOBALS[CLASS_TOOLS]->removeNull($NetworkTable);
            $DataUser[KEY_TABLE_USER_ONLINE] = $GLOBALS[CLASS_TOOLS]->removeNull($Status);
            $DataUser[KEY_TABLE_USER_POINT] = $GLOBALS[CLASS_TOOLS]->removeNull($Points);
            $DataUser[KEY_TABLE_USER_STOCK] = $GLOBALS[CLASS_TOOLS]->removeNull($Stock);

            return $DataUser;
        } else {
            return null;
        }
    }

    public function Get_User_Status_Now($ID, $Filed = null) {
        if (isset($ID)) {
            $Data = array(
                FILED_USER_ONLINE_ID => $ID
            );

            $tmp = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_ONLINE, $Data, FILED_USER_ONLINE_DATE_TIME);
            $User = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
            if (!is_null($User)) {
                if (is_null($Filed)) {
                    return $User;
                } else {
                    return $User[$Filed];
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function Get_User_Profile_Photo($ID) {
        $Photo = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_PHOTO, [FILED_USER_PHOTO_UID => $ID, FILED_USER_PHOTO_IS_PROFILE => ON]);
        if (!is_null($Photo)) {
            $Data = $this->Multimedia->Send_Image_User($Photo[FILED_USER_PHOTO_PHOTO_PATH], PHOTO, $ID);
            if (!empty($Data)) {
                return $Data;
            }
        } else {
            return null;
        }
    }

    public function Get_User_Stock_Now($ID) {
        if (isset($ID)) {
            $Data = array(
                FILED_USER_STOCK_UID => $ID
            );

            $tmp = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_STOCK, $Data, FILED_USER_STOCK_DATE_CHARGE_MONEY_STOK);
            $User = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
            if (!is_null($User)) {
                return $User;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private function GetUserPoint($UserID) {
        $Quiry = array(
            FILED_USER_POINT_UID => $UserID
        );

        $UserPoint = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_POINT, $Quiry);
        if (!is_null($UserPoint)) {
            return $UserPoint[FILED_USER_POINT_POINT_STOCK];
        } else {
            return null;
        }
    }

    public function Get_User_Albume() {
        $Images = array();
        $Commands = array();
        $RetarnImage = array();

        $temp = array(
            FILED_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_UID)
        );

        $UserUID = $GLOBALS[CLASS_TOOLS]->removeNull($temp);
        if (!is_null($UserUID)) {
            $Images = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_PHOTO, $UserUID);
        }
        if (!is_null($Images)) {
            if (is_array($Images)) {
                foreach ($Images as $RecImage) {
                    $ID = $RecImage[FILED_USER_PHOTO_ID];
                    $ImagePath = $RecImage[FILED_USER_PHOTO_PHOTO_PATH];
                    $RecImage[FILED_USER_PHOTO_PHOTO_PATH] = $this->Multimedia->Send_Image_User($ImagePath, PHOTO);
                    $ImageCommand = array(FILED_USER_PHOTO_COMMENT_UPID => $ID);
                    $Commands = $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_USER_PHOTO_COMMENT, $ImageCommand));
                    $element = array(
                        [$RecImage, $Commands]
                    );
                    if (is_null($Commands)) {
                        array_push($RetarnImage, $RecImage);
                    } else {
                        array_push($RetarnImage, $element);
                    }
                }
                return $RetarnImage;
            }
        } else {
            return NO_NEW;
        }
    }

    /**
     * Get_User_List
     * it is maybe geting with (Zone only) or (Network and Zone) or 
     * By (Country) or (Name) or (Barcode from UUID)
     */
    public function Get_User_List() {
        $Option = 0;
        $TableName = '';

        $tUser = array(
            FILED_PHONE_IMEI => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_IMEI)
        );
        $tPhoneNetwork = array(
            FILED_MOBILE_NETWORK_CODE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_CODE),
            FILED_MOBILE_NETWORK_NETWORK_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_NETWORK_NAME),
            FILED_MOBILE_NETWORK_COUNTRY_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MOBILE_NETWORK_COUNTRY_NAME)
        );
        $tCountry = array(
            FILED_COUNTRY_NAME => strtolower($GLOBALS[CLASS_FILTER]->FilterData(KEY_COUNTRY_NAME)),
            FILED_COUNTRY_ISO => strtolower($GLOBALS[CLASS_FILTER]->FilterData(KEY_COUNTRY_ISO)),
            FILED_COUNTRY_BLOCK => OFF
        );
        $tUserName = array(
            FILED_USER_NAME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_NAME),
            FILED_USER_PHONE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHONE),
            FILED_USER_UUID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_UUID)
        );
        //For Get Users List depending on the $User ID Zone
        $User = $GLOBALS[CLASS_TOOLS]->removeNull($tUser);
        //For Ger Users List depending on the Network name in User Zone
        $PhoneNetwork = $GLOBALS[CLASS_TOOLS]->removeNull($tPhoneNetwork);
        //For Get Users List depending on the Country which available to the user
        $Country = $GLOBALS[CLASS_TOOLS]->removeNull($tCountry);
        //For Get User from his Name or Phone or UUID
        $UserName = $GLOBALS[CLASS_TOOLS]->removeNull($tUserName);

        if (!is_null($User)) {
            $tmp = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $User);
            $User = $tmp[FILED_PHONE_UID];
            $Option = GET_BY_ZONE_ONLY;
        }

        if (!is_null($PhoneNetwork)) {
            $Option = GET_BY_NETWORK;
        }

        if (!is_null($Country)) {
            $Option = GET_BY_COUNTRY;
        }

        if (!is_null($UserName)) {
            $Option = GET_BY_USER;
        }

        switch ($Option) {
            case GET_BY_ZONE_ONLY:
                return $this->Maybe_this_your_Friends($User); //TODO: Move to Zone Class
                break;
            case GET_BY_NETWORK:
                return $this->Network_Zone($PhoneNetwork); //TODO: Move to Zone Class
                break;
            case GET_BY_COUNTRY:
                return $this->Country_Zone($Country); //TODO: Move to Zone Class
                break;
            case GET_BY_USER:
                //Get By Name
                break;
            case GET_BY_PHONE_TYPE:
                //Get By Phone Type ( Samsung , Sony , LG ,...etc)
                break;
            default :
                return NOT_FOUND;
                break;
        }
    }

    public function Get_User_Information($User_ID = null) {
        $UserID = array(
            FILED_USER_ID => isset($User_ID) ? $User_ID : $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_ID)
        );

        $Tables = array(
            FILED_UID => $UserID[FILED_USER_ID]
        );

        $Notification = array(
            FILED_NOTIFICATIONS_TO => $UserID[FILED_USER_ID]
        );

        $isActiv = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $UserID);
        if (!is_null($isActiv)) {
            $Status = $GLOBALS[CLASS_TOOLS]->getValue(FILED_USER_ACTIVATION, $isActiv);
            if ($Status == OFF) {
                return NOT_ACTIVE_USER;
            }
        }


        $UserID = $GLOBALS[CLASS_TOOLS]->removeNull($UserID);
        if (is_null($UserID)) {
            die(ShowError());
        } else {
            $this->Sync = new Syncronization();
            $UserData = $this->Sync->SyncAll($UserID[FILED_USER_ID]);
//            $UserData = array(
//                KEY_TABLE_USER => $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $UserID),
//                KEY_TABLE_PHONE => $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $Tables),
//                KEY_TABLE_USER_STOCK => $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_STOCK, $Tables),
//                KEY_TABLE_USER_GIFT => $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_GIFT, $Tables),
//                KEY_TABLE_MESSAGE => $GLOBALS[CLASS_DATABASE]->select(TABLE_MESSAGE, $Tables),
//                KEY_TABLE_FRIENDS => $GLOBALS[CLASS_DATABASE]->select(TABLE_FRIENDS, $Tables),
//                KEY_TABLE_NOTIFICATIONS => $GLOBALS[CLASS_DATABASE]->select(TABLE_NOTIFICATIONS, $Notification)
//            );
            //ICON For If Famuse or VIP
            if ($UserData[KEY_TABLE_USER][FILED_USER_IS_FAMOUS] == ON) {
                $FamusIconURL = $GLOBALS[CLASS_TOOLS]->CreateURL(SystemVariable(FILED_SYSTEM_ICON_FOR_FAMUS));
                $UserData[KEY_TABLE_USER] = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_USER_IS_FAMOUS, $FamusIconURL, $UserData[KEY_TABLE_USER]);
            } else {
                $UserData[KEY_TABLE_USER] = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_USER_IS_FAMOUS, $UserData[KEY_TABLE_USER]);
            }

            if ($UserData[KEY_TABLE_USER][FILED_USER_IS_VIP] == ON) {
                $FamusIconURL = $GLOBALS[CLASS_TOOLS]->CreateURL(SystemVariable(FILED_SYSTEM_ICON_FOR_VIP));
                $UserData[KEY_TABLE_USER] = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_USER_IS_VIP, $FamusIconURL, $UserData[KEY_TABLE_USER]);
            } else {
                $UserData[KEY_TABLE_USER] = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_USER_IS_VIP, $UserData[KEY_TABLE_USER]);
            }

            $UserInfo = $GLOBALS[CLASS_TOOLS]->removeNull($UserData);
            $this->set_Notification_Old($Notification[FILED_NOTIFICATIONS_TO]);

            return $UserInfo;
        }
    }

#############################################################################
#############################################################################
#############################################################################
#############################################################################
#############################################################################

    public function Set_Comment_in_photo() {
        $Image = array();
        $ImageTemp = array(
            FILED_USER_PHOTO_COMMENT_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_FROM),
            FILED_USER_PHOTO_COMMENT_UPID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_UPID),
            FILED_USER_PHOTO_COMMENT_COMMENT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_COMMENT)),
            FILED_USER_PHOTO_COMMENT_LIKE => 0,
            FILED_USER_PHOTO_COMMENT_UNLIKE => 0
        );

        $Image = $GLOBALS[CLASS_TOOLS]->removeNull($ImageTemp);

        if (!is_null($Image)) {
            if (empty($Image[FILED_USER_PHOTO_COMMENT_COMMENT]) || strlen($Image[FILED_USER_PHOTO_COMMENT_COMMENT]) == 0) {
                $this->LogERROR("ERROR : Comment is Empty !!!", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_PHOTO_COMMENT_COMMENT;
            }
            if (empty($Image[FILED_USER_PHOTO_COMMENT_UPID])) {
                $this->LogERROR("ERROR : Not found UPID ?", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_PHOTO_COMMENT_UPID;
            }

            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_PHOTO_COMMENT, $Image)) {
                $Comment_ID = $GLOBALS[CLASS_DATABASE]->lastInsertID();
                $this->LogSUCCESS("Insert new comment to image " . $Image[FILED_USER_PHOTO_COMMENT_UPID] . " is DONE.", __FUNCTION__, __LINE__);

                $Name = $this->WhoIs($Image[FILED_USER_PHOTO_COMMENT_FROM], KEY_TABLE_USER);
                $SerialNumber = $this->setNotification($Image[FILED_USER_PHOTO_COMMENT_FROM], $Name[FILED_USER_ID], NOTIF_COMMENT, $GLOBALS[CLASS_TOOLS]->Language(sprintf(MessagesSystem(LABEL_USER_COMMENT), $Name[FILED_USER_NAME])));

                $Retern = array(
                    KEY_USER_PHOTO_COMMENT_ID => $Comment_ID,
                    KEY_NOTIFICATIONS_SERIAL => (is_null($SerialNumber) ? "NULL" : $SerialNumber)
                );

                return $Retern;
            }
        } else {
            $this->LogERROR("No Data is set .... i think it is Attack", __FUNCTION__, __LINE__);
            return FAIL . SET_COMMENT_IN_PHOTO;
        }
    }

    public function Edit_Comment_in_Photo() {
        $Image = array();
        $Where = array(
            FILED_USER_PHOTO_COMMENT_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_ID)
        );
        $ImageTemp = array(
            FILED_USER_PHOTO_COMMENT_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_FROM),
            FILED_USER_PHOTO_COMMENT_UPID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_UPID),
            FILED_USER_PHOTO_COMMENT_COMMENT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_COMMENT)),
            FILED_USER_PHOTO_COMMENT_LIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_LIKE),
            FILED_USER_PHOTO_COMMENT_UNLIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_UNLIKE)
        );

        $Image = $GLOBALS[CLASS_TOOLS]->removeNull($ImageTemp);

        if (!is_null($Image)) {
            if (empty($Image[FILED_USER_PHOTO_COMMENT_COMMENT]) || strlen($Image[FILED_USER_PHOTO_COMMENT_COMMENT]) == 0) {
                $this->LogERROR("ERROR : Comment is Empty !!!", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_PHOTO_COMMENT_COMMENT;
            }
            if (empty($Image[FILED_USER_PHOTO_COMMENT_UPID])) {
                $this->LogERROR("ERROR : Not found UPID ?", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_PHOTO_COMMENT_UPID;
            }

            if ($GLOBALS[CLASS_DATABASE]->update(TABLE_USER_PHOTO_COMMENT, $Image, $Where)) {
                $Comment_ID = $Where[FILED_USER_PHOTO_COMMENT_ID];
                $this->LogSUCCESS("Insert new comment to image " . $Image[FILED_USER_PHOTO_COMMENT_UPID] . " is DONE.", __FUNCTION__, __LINE__);

                $Name = $this->WhoIs($Image[FILED_USER_PHOTO_COMMENT_FROM], KEY_TABLE_USER);
                $SerialNumber = $this->setNotification($Image[FILED_USER_PHOTO_COMMENT_FROM], $Name[FILED_USER_ID], NOTIF_COMMENT, $GLOBALS[CLASS_TOOLS]->Language(sprintf(MessagesSystem(LABEL_USER_COMMENT), $Name[FILED_USER_NAME])));

                $Retern = array(
                    KEY_USER_PHOTO_COMMENT_ID => $Comment_ID,
                    KEY_NOTIFICATIONS_SERIAL => (is_null($SerialNumber) ? "NULL" : $SerialNumber)
                );

                return $Retern;
            }
        } else {
            $this->LogERROR("No Data is set .... i think it is Attack", __FUNCTION__, __LINE__);
            return FAIL . SET_COMMENT_IN_PHOTO;
        }
    }

    public function Delet_Comment_in_Photo() {
        $Where = array(
            FILED_USER_PHOTO_COMMENT_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT_ID)
        );

        $Where = $GLOBALS[CLASS_TOOLS]->removeNull($Where);

        if (!is_null($Where)) {
            if ($GLOBALS[CLASS_DATABASE]->delete(TABLE_USER_PHOTO_COMMENT, $Where)) {
                $this->LogSUCCESS("Done Delete Comment " . $Where[FILED_USER_PHOTO_COMMENT_ID], __FUNCTION__, __LINE__);
                return SUCCESS;
            } else {
                $this->LogERROR("Can not Delete Comment " . $Where[FILED_USER_PHOTO_COMMENT_ID] . " MYSQL ERROR : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("Not Found ID For Delete Comment!!", __FUNCTION__, __LINE__);
            return FAIL . KEY_USER_PHOTO_COMMENT_ID;
        }
    }

    public function Set_Comment_in_Audio() {
        $Audio = array();
        $AudioTemp = array(
            FILED_USER_AUDIO_COMMENT_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_FROM),
            FILED_USER_AUDIO_COMMENT_UAID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_UAID),
            FILED_USER_AUDIO_COMMENT_COMMENT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_COMMENT)),
            FILED_USER_AUDIO_COMMENT_LIKE => 0,
            FILED_USER_AUDIO_COMMENT_UNLIKE => 0
        );

        $Audio = $GLOBALS[CLASS_TOOLS]->removeNull($AudioTemp);

        if (!is_null($Audio)) {
            if (empty($Audio[FILED_USER_AUDIO_COMMENT_COMMENT]) || strlen($Audio[FILED_USER_AUDIO_COMMENT_COMMENT]) == 0) {
                $this->LogERROR("ERROR : Comment is Empty !!!", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_AUDIO_COMMENT_COMMENT;
            }
            if (empty($Audio[FILED_USER_AUDIO_COMMENT_UAID])) {
                $this->LogERROR("ERROR : Not found UAID ?", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_AUDIO_COMMENT_UAID;
            }

            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_AUDIO_COMMENT, $Audio)) {
                $Comment_ID = $GLOBALS[CLASS_DATABASE]->lastInsertID();
                $this->LogSUCCESS("Insert new comment to Audio " . $Audio[FILED_USER_AUDIO_COMMENT_UAID] . " is DONE.", __FUNCTION__, __LINE__);

                $Name = $this->WhoIs($Audio[FILED_USER_AUDIO_COMMENT_FROM], KEY_TABLE_USER);
                $SerialNumber = $this->setNotification($Audio[FILED_USER_AUDIO_COMMENT_FROM], $Name[FILED_USER_ID], NOTIF_COMMENT, $GLOBALS[CLASS_TOOLS]->Language(sprintf(MessagesSystem(LABEL_USER_COMMENT), $Name[FILED_USER_NAME])));

                $Retern = array(
                    KEY_USER_AUDIO_COMMENT_ID => $Comment_ID,
                    KEY_NOTIFICATIONS_SERIAL => (is_null($SerialNumber) ? "NULL" : $SerialNumber)
                );

                return $Retern;
            }
        } else {
            $this->LogERROR("No Data is set .... i think it is Attack", __FUNCTION__, __LINE__);
            return FAIL . SET_COMMENT_IN_AUDIO;
        }
    }

    public function Edit_Comment_in_Audio() {
        $Audio = array();
        $Where = array(
            FILED_USER_AUDIO_COMMENT_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_ID)
        );
        $AudioTemp = array(
            FILED_USER_AUDIO_COMMENT_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_FROM),
            FILED_USER_AUDIO_COMMENT_UAID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_UAID),
            FILED_USER_AUDIO_COMMENT_COMMENT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_COMMENT)),
            FILED_USER_AUDIO_COMMENT_LIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_LIKE),
            FILED_USER_AUDIO_COMMENT_UNLIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_UNLIKE)
        );

        $Audio = $GLOBALS[CLASS_TOOLS]->removeNull($AudioTemp);

        if (!is_null($Audio)) {
            if (empty($Audio[FILED_USER_AUDIO_COMMENT_COMMENT]) || strlen($Audio[FILED_USER_AUDIO_COMMENT_COMMENT]) == 0) {
                $this->LogERROR("ERROR : Comment is Empty !!!", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_AUDIO_COMMENT_COMMENT;
            }
            if (empty($Audio[FILED_USER_AUDIO_COMMENT_UAID])) {
                $this->LogERROR("ERROR : Not found UAID ?", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_AUDIO_COMMENT_UAID;
            }

            if ($GLOBALS[CLASS_DATABASE]->update(TABLE_USER_AUDIO_COMMENT, $Audio, $Where)) {
                $Comment_ID = $Where[FILED_USER_AUDIO_COMMENT_ID];
                $this->LogSUCCESS("Insert new comment to Audio " . $Audio[FILED_USER_AUDIO_COMMENT_UAID] . " is DONE.", __FUNCTION__, __LINE__);

                $Name = $this->WhoIs($Audio[FILED_USER_AUDIO_COMMENT_FROM], KEY_TABLE_USER);
                $SerialNumber = $this->setNotification($Audio[FILED_USER_AUDIO_COMMENT_FROM], $Name[FILED_USER_ID], NOTIF_COMMENT, $GLOBALS[CLASS_TOOLS]->Language(sprintf(MessagesSystem(LABEL_USER_COMMENT), $Name[FILED_USER_NAME])));

                $Retern = array(
                    KEY_USER_AUDIO_COMMENT_ID => $Comment_ID,
                    KEY_NOTIFICATIONS_SERIAL => (is_null($SerialNumber) ? "NULL" : $SerialNumber)
                );

                return $Retern;
            }
        } else {
            $this->LogERROR("No Data is set .... i think it is Attack", __FUNCTION__, __LINE__);
            return FAIL . SET_COMMENT_IN_AUDIO;
        }
    }

    public function Delet_Comment_in_Audio() {
        $Where = array(
            FILED_USER_AUDIO_COMMENT_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_COMMENT_ID)
        );

        $Where = $GLOBALS[CLASS_TOOLS]->removeNull($Where);

        if (!is_null($Where)) {
            if ($GLOBALS[CLASS_DATABASE]->delete(TABLE_USER_AUDIO_COMMENT, $Where)) {
                $this->LogERROR("Done Delete Comment " . $Where[FILED_USER_AUDIO_COMMENT_ID], __FUNCTION__, __LINE__);
                return SUCCESS;
            } else {
                $this->LogERROR("Can not Delete Comment " . $Where[FILED_USER_AUDIO_COMMENT_ID] . " MYSQL ERROR : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("Not Found ID For Delete Comment!!", __FUNCTION__, __LINE__);
            return FAIL . KEY_USER_AUDIO_COMMENT_ID;
        }
    }

    public function Set_Comment_in_Video() {
        $Video = array();
        $VideoTemp = array(
            FILED_USER_VIDEO_COMMENT_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_FROM),
            FILED_USER_VIDEO_COMMENT_UVID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_UVID),
            FILED_USER_VIDEO_COMMENT_COMMENT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_COMMENT)),
            FILED_USER_VIDEO_COMMENT_LIKE => 0,
            FILED_USER_VIDEO_COMMENT_UNLIKE => 0
        );

        $Video = $GLOBALS[CLASS_TOOLS]->removeNull($VideoTemp);

        if (!is_null($Video)) {
            if (empty($Video[FILED_USER_VIDEO_COMMENT_COMMENT]) || strlen($Video[FILED_USER_VIDEO_COMMENT_COMMENT]) == 0) {
                $this->LogERROR("ERROR : Comment is Empty !!!", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_VIDEO_COMMENT_COMMENT;
            }
            if (empty($Video[FILED_USER_VIDEO_COMMENT_UVID])) {
                $this->LogERROR("ERROR : Not found UVID ?", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_VIDEO_COMMENT_UVID;
            }

            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_VIDEO_COMMENT, $Video)) {
                $Comment_ID = $GLOBALS[CLASS_DATABASE]->lastInsertID();
                $this->LogSUCCESS("Insert new comment to Video " . $Video[FILED_USER_VIDEO_COMMENT_UVID] . " is DONE.", __FUNCTION__, __LINE__);

                $Name = $this->WhoIs($Video[FILED_USER_VIDEO_COMMENT_FROM], KEY_TABLE_USER);
                $SerialNumber = $this->setNotification($Video[FILED_USER_VIDEO_COMMENT_FROM], $Name[FILED_USER_ID], NOTIF_COMMENT, $GLOBALS[CLASS_TOOLS]->Language(sprintf(MessagesSystem(LABEL_USER_COMMENT), $Name[FILED_USER_NAME])));

                $Retern = array(
                    KEY_USER_VIDEO_COMMENT_ID => $Comment_ID,
                    KEY_NOTIFICATIONS_SERIAL => (is_null($SerialNumber) ? "NULL" : $SerialNumber)
                );

                return $Retern;
            }
        } else {
            $this->LogERROR("No Data is set .... i think it is Attack", __FUNCTION__, __LINE__);
            return FAIL . SET_COMMENT_IN_VIDEO;
        }
    }

    public function Edit_Comment_in_Video() {
        $Video = array();
        $Where = array(
            FILED_USER_VIDEO_COMMENT_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_ID)
        );
        $VideoTemp = array(
            FILED_USER_VIDEO_COMMENT_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_FROM),
            FILED_USER_VIDEO_COMMENT_UVID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_UVID),
            FILED_USER_VIDEO_COMMENT_COMMENT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_COMMENT)),
            FILED_USER_VIDEO_COMMENT_LIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_LIKE),
            FILED_USER_VIDEO_COMMENT_UNLIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_UNLIKE)
        );

        $Video = $GLOBALS[CLASS_TOOLS]->removeNull($VideoTemp);

        if (!is_null($Video)) {
            if (empty($Video[FILED_USER_VIDEO_COMMENT_COMMENT]) || strlen($Video[FILED_USER_VIDEO_COMMENT_COMMENT]) == 0) {
                $this->LogERROR("ERROR : Comment is Empty !!!", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_VIDEO_COMMENT_COMMENT;
            }
            if (empty($Video[FILED_USER_VIDEO_COMMENT_UVID])) {
                $this->LogERROR("ERROR : Not found UVID ?", __FUNCTION__, __LINE__);
                return FAIL . FILED_USER_VIDEO_COMMENT_UVID;
            }

            if ($GLOBALS[CLASS_DATABASE]->update(TABLE_USER_VIDEO_COMMENT, $Video, $Where)) {
                $Comment_ID = $Where[FILED_USER_VIDEO_COMMENT_ID];
                $this->LogSUCCESS("Insert new comment to Audio " . $Video[FILED_USER_VIDEO_COMMENT_UVID] . " is DONE.", __FUNCTION__, __LINE__);

                $Name = $this->WhoIs($Video[FILED_USER_VIDEO_COMMENT_FROM], KEY_TABLE_USER);
                $SerialNumber = $this->setNotification($Video[FILED_USER_VIDEO_COMMENT_FROM], $Name[FILED_USER_ID], NOTIF_COMMENT, $GLOBALS[CLASS_TOOLS]->Language(sprintf(MessagesSystem(LABEL_USER_COMMENT), $Name[FILED_USER_NAME])));

                $Retern = array(
                    KEY_USER_VIDEO_COMMENT_ID => $Comment_ID,
                    KEY_NOTIFICATIONS_SERIAL => (is_null($SerialNumber) ? "NULL" : $SerialNumber)
                );

                return $Retern;
            }
        } else {
            $this->LogERROR("No Data is set .... i think it is Attack", __FUNCTION__, __LINE__);
            return FAIL . SET_COMMENT_IN_VIDEO;
        }
    }

    public function Delet_Comment_in_Video() {
        $Where = array(
            FILED_USER_VIDEO_COMMENT_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_COMMENT_ID)
        );

        $Where = $GLOBALS[CLASS_TOOLS]->removeNull($Where);

        if (!is_null($Where)) {
            if ($GLOBALS[CLASS_DATABASE]->delete(TABLE_USER_VIDEO_COMMENT, $Where)) {
                $this->LogSUCCESS("Done Delete Comment " . $Where[FILED_USER_VIDEO_COMMENT_ID], __FUNCTION__, __LINE__);
                return SUCCESS;
            } else {
                $this->LogERROR("Can not Delete Comment " . $Where[FILED_USER_VIDEO_COMMENT_ID] . " MYSQL ERROR : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("Not Found ID For Delete Comment!!", __FUNCTION__, __LINE__);
            return FAIL . KEY_USER_VIDEO_COMMENT_ID;
        }
    }

#############################################################################
#############################################################################
#############################################################################
#############################################################################
#############################################################################

    public function Get_Status_Line($UserID) {
        $USER_ID = (empty($UserID) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_ONLINE_ID) : $UserID);

        $Status = $this->Get_User_Status_Now($USER_ID);
        if (!is_null($Status)) {
            if ($Status[FILED_USER_ONLINE_STATUS] == ON) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function Change_Status_Line($UserID = '', $Status = '', $WhatInMind = '') {
        $USER_ID = (empty($UserID) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_ONLINE_ID) : $UserID);

        $Data = array(
            FILED_USER_ONLINE_ID => (empty($UserID) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_ONLINE_ID) : $UserID),
            FILED_USER_ONLINE_STATUS => (empty($Status) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_ONLINE_STATUS) : $Status),
            FILED_USER_ONLINE_WHAT_IS_IN_YOUR_MIND => (empty($WhatInMind) ? $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_ONLINE_WHAT_IS_IN_YOUR_MIND)) : $WhatInMind)
        );
        $Database = $this->Get_User_Status_Now($USER_ID);
        if (is_null($Database)) {
            //Insert
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_ONLINE, $Data)) {
                return SUCCESS;
            } else {
                $this->LogERROR("Filed to Insert data in MySQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            //Update
            if ($GLOBALS[CLASS_DATABASE]->update(TABLE_USER_ONLINE, $Data, $Where)) {
                return SUCCESS;
            } else {
                $this->LogERROR("Filed to Update data in MySQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        }
    }

#############################################################################
#############################################################################
#############################################################################
#############################################################################
#############################################################################

    public function Set_Ponans_For_User() {
        $where = array(FILED_USER_POINT_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_POINT_UID));
        $Data = array(
            FILED_USER_POINT_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_POINT_UID),
            FILED_USER_POINT_POINT_STOCK => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_POINT_POINT_STOCK),
            FILED_USER_POINT_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_POINT_FROM)
        );

        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($Data);
        if (!$GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_USER_POINT_UID, $Data)) {
            $this->LogERROR("Not found User ID", __FUNCTION__, __LINE__);
            return FAIL;
        }

        if (!$this->isThisUserExist($Data[FILED_USER_POINT_UID])) {
            return FAIL;
        }

        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_USER_POINT, $where)) {
            if ($GLOBALS[CLASS_DATABASE]->update(TABLE_USER_POINT, $Data, $where)) {
                return SUCCESS;
            } else {
                $this->LogERROR("Can not Set ponans to table . MySQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_POINT, $Data)) {
                return SUCCESS;
            } else {
                $this->LogERROR("Can not Set ponans to table . MySQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        }
    }

    public function Get_Ponans_For_User() {
        $where = array(FILED_USER_POINT_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_POINT_UID));
        $where = $GLOBALS[CLASS_TOOLS]->removeNull($where);
        if ($GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_USER_POINT_UID, $where)) {
            if (!$this->isThisUserExist($where[FILED_USER_POINT_UID])) {
                return FAIL;
            }
            $Retern = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_POINT, $where);
            if (!is_null($Retern)) {
                $Retern = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_USER_POINT_UID, $Retern);
                return $Retern;
            } else {
                $this->LogERROR("Can not Get ponans from table . MySQL : " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("Not found User ID", __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    public function Calculat_Total_Of_Ponans_User($User_ID = null) {
        $Like = 0;
        $Unlike = 0;

        $TableArray = array(
            TABLE_USER_PHOTO => array(FILED_USER_PHOTO_TIME_OF_LIKE, FILED_USER_PHOTO_TIME_OF_UNLIKE),
            TABLE_USER_VIDEO => array(FILED_USER_VIDEO_TIME_OF_LIKE, FILED_USER_VIDEO_TIME_OF_UNLIKE),
        );

        $Data = array();
        if (is_null($User_ID)) {
            $Data = array(
                FILED_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_UID)
            );
        } else {
            $Data = array(
                FILED_UID => $User_ID
            );
        }
        $Keys = $GLOBALS[CLASS_TOOLS]->GetKeys($TableArray);

        for ($i = 0; $i <= count($Keys) - 1; $i++) {
            $User = $GLOBALS[CLASS_DATABASE]->select($Keys[$i], $Data);
            if (!is_null($User)) {
                foreach ($User as $element) {
                    $tempL = $element[$TableArray[$Keys[$i]][0]];
                    $tempU = $element[$TableArray[$Keys[$i]][1]];
                    $Like += (int) $tempL;
                    $Unlike +=(int) $tempU;
                }
            }

            //Set Ponans for User
        }
    }

}
