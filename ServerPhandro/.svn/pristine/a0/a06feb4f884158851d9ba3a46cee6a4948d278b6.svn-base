<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Message_Class
 *
 * @author abaza
 */
class Message_Class extends Friend_Class {

    private $UserMange = null;

    function __construct() {
        if (is_null($this->UserMange)) {
            $this->UserMange = new UserMangment();
        }
    }

    function __destruct() {
        if (!is_null($this->UserManger)) {
            unset($this->UserManger);
        }
    }

    public function set_message() {
        $MES = [
            FILED_MESSAGE_FROM => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_FROM),
            FILED_MESSAGE_TO => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_TO),
            FILED_MESSAGE_MESSAGE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_MESSAGE),
            FILED_MESSAGE_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_FROM),
            FILED_MESSAGE_HIDE => OFF
        ];
        $MES = $GLOBALS[CLASS_TOOLS]->removeNull($MES);

        if (!array_key_exists(FILED_MESSAGE_MESSAGE, $MES)) {
            $MultiMedia = new Maltimedia_module();
            $MES = array();
            $MES = $MultiMedia->Maltimedia(true);
        }
        return $this->Implimntation_Save_Message($MES);
    }

    private function Implimntation_Save_Message($MES) {
        if (!is_null($MES)) {

            if ($this->UserMange->isThisUserExist($MES[FILED_MESSAGE_FROM])) {
                if (!$this->UserMange->isThisUserExist($MES[FILED_MESSAGE_TO])) {
                    return FAIL . KEY_MESSAGE_TO;
                }
            } else {
                return FAIL . KEY_MESSAGE_FROM;
            }

            $User_ID = $MES[FILED_MESSAGE_UID];
            $User_Friend_ID = $MES[FILED_MESSAGE_TO];
            if (!$this->isFriends($User_ID, $User_Friend_ID)) {
                return FAIL . NOT_FRINDS;
            }

            if (!empty($MES[FILED_MESSAGE_MESSAGE])) {
                $MessageClear = $this->filter_message($MES[FILED_MESSAGE_MESSAGE]);
                $MES = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_MESSAGE_MESSAGE, $MessageClear, $MES);
                if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_MESSAGE, $MES)) {
                    $ID = $GLOBALS[CLASS_DATABASE]->lastInsertID();
                    $this->set_as_unread($ID);
                    $From = $MES[FILED_MESSAGE_FROM];
                    $To = $MES[FILED_MESSAGE_TO];

                    $Label = sprintf(MessagesSystem(YOU_HAVE_A_NEW_MESSAGE), $this->UserMange->WhoIs($To, FILED_USER_NAME, KEY_TABLE_USER));

                    $Serial = $this->UserMange->setNotification($From, $To, NOTIF_MESSAGE, $Label);
                    return [KEY_NOTIFICATIONS_SERIAL => $Serial];
                } else {
                    $this->User_Log("Error in Database MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                    return FAIL;
                }
            }
        } else {
            $this->User_Log("Error MES is Empty", __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    public function get_new_message($From = '', $To = '') {
        $Buffer = array();
        $MSG = [
            FILED_MESSAGE_FROM => (empty($From) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_FROM) : $From),
            FILED_MESSAGE_TO => (empty($To) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_TO) : $To),
            FILED_MESSAGE_NEW => ON,
            FILED_MESSAGE_HIDE => OFF
        ];
        $MES = $GLOBALS[CLASS_TOOLS]->removeNull($MSG);
        if (!is_null($MES)) {
            $Message = $GLOBALS[CLASS_DATABASE]->select(TABLE_MESSAGE, $MES, FILED_MESSAGE_DATE_TIME);
            if (!is_null($Message)) {
                if (is_array($Message[0])) {
                    foreach ($Message as $element) {
                        $To = $this->UserMange->WhoIs($element[FILED_MESSAGE_TO], FILED_USER_NAME, KEY_TABLE_USER);
                        $From = $this->UserMange->WhoIs($element[FILED_MESSAGE_FROM], FILED_USER_NAME, KEY_TABLE_USER);
                        $Text = $this->get_message_text($element[FILED_MESSAGE_MESSAGE]);
                        $Attach = $element[FILED_MESSAGE_ATTACH];
                        $MID = $element[FILED_MESSAGE_ID];
                        $this->set_as_read($MID);
                        $this->UserMange->set_Notification_Old($element[FILED_MESSAGE_TO]);
                        $New = [FILED_MESSAGE_ID => $MID, FILED_MESSAGE_FROM => $From, FILED_MESSAGE_TO => $To, FILED_MESSAGE_MESSAGE => $Text, FILED_MESSAGE_ATTACH => $Attach];
                        array_push($Buffer, $New);
                    }
                } else {
                    $To = $this->UserMange->WhoIs($Message[FILED_MESSAGE_TO], FILED_USER_NAME, KEY_TABLE_USER);
                    $From = $this->UserMange->WhoIs($Message[FILED_MESSAGE_FROM], FILED_USER_NAME, KEY_TABLE_USER);
                    $Text = $this->get_message_text($Message[FILED_MESSAGE_MESSAGE]);
                    $Attach = $element[FILED_MESSAGE_ATTACH];
                    $MID = $Message[FILED_MESSAGE_ID];
                    $this->set_as_read($MID);
                    $this->UserMange->set_Notification_Old($Message[FILED_MESSAGE_TO]);

                    $New = [FILED_MESSAGE_ID => $MID, FILED_MESSAGE_FROM => $From, FILED_MESSAGE_TO => $To, FILED_MESSAGE_MESSAGE => $Text, FILED_MESSAGE_ATTACH => $Attach];
                    array_push($Buffer, $New);
                }
                return $Buffer;
            } else {
                return NO_NEW;
            }
        } else {
            $this->User_Log("Error MSG is Empty", __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    public function get_all_message($From = '', $To = '') {
        $Buffer = array();
        $MSG = [
            FILED_MESSAGE_FROM => (empty($From) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_FROM) : $From),
            FILED_MESSAGE_TO => (empty($To) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_TO) : $To),
            FILED_MESSAGE_HIDE => OFF
        ];
        $MES = $GLOBALS[CLASS_TOOLS]->removeNull($MSG);
        if (!is_null($MES)) {
            $Message = $GLOBALS[CLASS_DATABASE]->select(TABLE_MESSAGE, $MES);
            if (!is_null($Message)) {
                if (is_array($Message[0])) {
                    foreach ($Message as $element) {
                        $To = $this->UserMange->WhoIs($element[FILED_MESSAGE_TO], FILED_USER_NAME, KEY_TABLE_USER);
                        $From = $this->UserMange->WhoIs($element[FILED_MESSAGE_FROM], FILED_USER_NAME, KEY_TABLE_USER);
                        $Text = $this->get_message_text($element[FILED_MESSAGE_MESSAGE]);
                        $Attach = $element[FILED_MESSAGE_ATTACH];
                        $MID = $element[FILED_MESSAGE_ID];
                        $this->set_as_read($MID);
                        $this->UserMange->set_Notification_Old($element[FILED_MESSAGE_TO]);
                        $New = [FILED_MESSAGE_ID => $MID, FILED_MESSAGE_FROM => $From, FILED_MESSAGE_TO => $To, FILED_MESSAGE_MESSAGE => $Text, FILED_MESSAGE_ATTACH => $Attach];
                        array_push($Buffer, $New);
                    }
                } else {
                    $To = $this->UserMange->WhoIs($Message[FILED_MESSAGE_TO], FILED_USER_NAME, KEY_TABLE_USER);
                    $From = $this->UserMange->WhoIs($Message[FILED_MESSAGE_FROM], FILED_USER_NAME, KEY_TABLE_USER);
                    $Text = $this->get_message_text($Message[FILED_MESSAGE_MESSAGE]);
                    $Attach = $element[FILED_MESSAGE_ATTACH];
                    $MID = $Message[FILED_MESSAGE_ID];
                    $this->set_as_read($MID);
                    $this->UserMange->set_Notification_Old($Message[FILED_MESSAGE_TO]);
                    $New = [FILED_MESSAGE_ID => $MID, FILED_MESSAGE_FROM => $From, FILED_MESSAGE_TO => $To, FILED_MESSAGE_MESSAGE => $Text, FILED_MESSAGE_ATTACH => $Attach];
                    array_push($Buffer, $New);
                }
                return $Buffer;
            } else {
                return NO_NEW;
            }
        } else {
            $this->User_Log("Error MSG is Empty", __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    public function delete_message($From = '', $To = '') {
        $MSG = [
            FILED_MESSAGE_FROM => (empty($From) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_FROM) : $From),
            FILED_MESSAGE_TO => (empty($To) ? $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_TO) : $To),
            FILED_MESSAGE_TO => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_TO),
            FILED_MESSAGE_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_MESSAGE_UID)
        ];

        $MES = $GLOBALS[CLASS_TOOLS]->removeNull($MSG);

        if (!array_key_exists(FILED_MESSAGE_UID, $MES)) {
            return FAIL . KEY_MESSAGE_UID;
        }

        if (array_key_exists(FILED_MESSAGE_ID, $MES)) {
            if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_MESSAGE, [FILED_MESSAGE_ID => $MES[FILED_MESSAGE_ID]])) {
                if ($GLOBALS[CLASS_DATABASE]->update(TABLE_MESSAGE, [FILED_MESSAGE_HIDE => ON], [FILED_MESSAGE_ID => $MES[FILED_MESSAGE_ID]])) {
                    $this->User_Log("MESSAGE ID : " . $MES[FILED_MESSAGE_ID] . " is Deleted", __FUNCTION__, __LINE__);
                    return SUCCESS;
                } else {
                    $this->User_Log("Error in Database MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                    return FAIL;
                }
            }
        } else {
            if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_MESSAGE, $MES)) {
                if ($GLOBALS[CLASS_DATABASE]->update(TABLE_MESSAGE, [FILED_MESSAGE_HIDE => ON], $MES)) {
                    $this->User_Log("MESSAGE ID : " . $MES[FILED_MESSAGE_ID] . " is Deleted", __FUNCTION__, __LINE__);
                    return SUCCESS;
                } else {
                    $this->User_Log("Error in Database MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                    return FAIL;
                }
            } else {
                $this->User_Log("Not Found", __FUNCTION__, __LINE__);
                return FAIL;
            }
        }
    }

    private function filter_message($message) {
        $GLOBALS[CLASS_TOOLS]->forString($message);
//        $str = utf8_decode($message);
        return trim($message);
    }

    private function get_message_text($message) {
//        $str = utf8_encode($message);
        return trim($message);
    }

    public function set_as_unread($message_id) {
        $Message = [
            FILED_MESSAGE_ID => $message_id
        ];
        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_MESSAGE, $Message)) {
            if ($GLOBALS[CLASS_DATABASE]->update(TABLE_MESSAGE, [FILED_MESSAGE_NEW => ON], $Message)) {
                $this->User_Log("MESSAGE ID : " . $MES[FILED_MESSAGE_ID] . " is Deleted", __FUNCTION__, __LINE__);
                return SUCCESS;
            } else {
                $this->User_Log("Error in Database MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->User_Log("Not Found", __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    public function set_as_read($message_id) {
        $Message = [
            FILED_MESSAGE_ID => $message_id
        ];
        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_MESSAGE, $Message)) {
            if ($GLOBALS[CLASS_DATABASE]->update(TABLE_MESSAGE, [FILED_MESSAGE_NEW => OFF], $Message)) {
                $this->User_Log("MESSAGE ID : " . $MES[FILED_MESSAGE_ID] . " is Deleted", __FUNCTION__, __LINE__);
                return SUCCESS;
            } else {
                $this->User_Log("Error in Database MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->User_Log("Not Found", __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    private function User_Log($Message, $FunctionName, $LINE) {
        if (SystemVariable(FILED_SYSTEM_LOG_SYSTEM)) {
            $ClassName = __CLASS__;

            $Log = "(MESSAGE) " . $ClassName . "::" . $FunctionName . "::" . $LINE . " = " . $Message;

            $MessageLog = array(
                FILED_USER_LOG_ACTION => $Log
            );

            $GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_LOG, $MessageLog);
            $GLOBALS[CLASS_TOOLS]->System_Log($MessageLog, $ClassName . "::" . $FunctionName, $LINE, Tools::NOTICE, true);

            if ($GLOBALS[CLASS_TOOLS]->isDebug()) {
                $GLOBALS[CLASS_TOOLS]->Show($MessageLog);
            }
        }
    }

}
