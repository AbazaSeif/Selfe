<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Syncronization
 * This Class for Sync Profile User
 * @author Seif Abaza
 */
class Syncronization implements Serializable {

    private $User_ID = 0;
    private $Sync = array();

    private function getID($full = false) {
        $IMEIData = $GLOBALS[CLASS_TOOLS]->removeNull([FILED_PHONE_IMEI => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_IMEI), FILED_PHONE_SIM_SERIAL => $GLOBALS[CLASS_FILTER]->FilterData(KEY_PHONE_SIM_SERIAL)]);
        if(is_null($IMEIData)){
            return null;
        }
        
        $IMEI = $GLOBALS[CLASS_DATABASE]->select(TABLE_PHONE, $IMEIData);
        if (is_null($IMEI)) {
            return null;
        } else {
            if ($full) {
                $User = new UserMangment();
                return $User->WhoIs($this->User_ID);
            } else {
                $this->User_ID = intval($IMEI[FILED_PHONE_UID]);
                if ($this->User_ID == 0) {
                    die(ShowError());
                }
                return $this->User_ID;
            }
        }
    }

    public function SyncAll($User_ID = 0) {
        if ($User_ID == 0) {
            $this->getID();
        }
        $Sync[KEY_TABLE_NOTIFICATIONS] = $this->SyncNotification();
        $Sync[KEY_TABLE_POST] = $this->SyncPost();
        $Sync[KEY_TABLE_POST_COMMENT] = $this->SyncComment();
        $Sync[KEY_TABLE_CATIGORY_MUSIC] = $this->SyncMusic();
        $Sync[KEY_TABLE_FRIENDS] = $this->SyncFriend();
        $Sync[KEY_TABLE_GIFTES] = $this->SyncGifts();
        $Sync[KEY_TABLE_MESSAGE] = $this->SyncMessage();
        $Sync[KEY_TABLE_USER_POINT] = $this->SyncPonans();
        $Sync[KEY_TABLE_USER_STOCK] = $this->SyncStock();
        $Sync[KEY_TABLE_GROUPS]=$this->SyncGroups();
        
        return $Sync;
    }
    
    public function SyncMessage() {
        $this->getID();
        $MessageClass = new Message_Class();

        $Message1 = $MessageClass->get_new_message($this->User_ID);
        $Message2 = $MessageClass->get_new_message('', $this->User_ID);
        if (($Message1 == FAIL) || ($Message1 == NO_NEW)) {
            $Message1 = null;
        }
        if (($Message2 == FAIL) || ($Message2 == NO_NEW)) {
            $Message2 = null;
        }
        if ((!is_null($Message1)) && (!is_null($Message2))) {
            return array_merge($Message1, $Message2);
        } elseif ((!is_null($Message1)) && (is_null($Message2))) {
            return $Message1;
        } elseif ((is_null($Message1)) && (!is_null($Message2))) {
            return $Message2;
        } elseif ((is_null($Message1)) && (is_null($Message2))) {
            return NO_NEW;
        }
    }

    public function SyncGroups() {
        $Buffer = array();
        $Data = $GLOBALS[CLASS_DATABASE]->select(TABLE_GROUPS, [FILED_GROUPS_HIDE => OFF]);
        if (!is_null($Data)) {
            if (is_array($Data[0])) {
                foreach ($Data as $Group) {
                    $tmp = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_GROUPS_HIDE, $Group);
                    array_push($Buffer, $tmp);
                }
            } else {
                $tmp = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_GROUPS_HIDE, $Data);
                array_push($Buffer, $tmp);
            }
            return $GLOBALS[CLASS_TOOLS]->Sort_Up_to_Down($Buffer);
        } else {
            return null;
        }
    }

    public function SyncUserGroups($USER_ID = null) {
        $Oner = $MemberIn = array();

        if (is_null($USER_ID)) {
            $this->getID();
        } else {
            $this->User_ID = $USER_ID;
        }

        $tmp = [
            FILED_GROUPS_ADMIN => $this->User_ID,
            FILED_GROUPS_HIDE => OFF
        ];

        $Data = $GLOBALS[CLASS_DATABASE]->select(TABLE_GROUPS, $tmp);
        if (!is_null($Data)) {
            if (is_array($Data[0])) {
                foreach ($Data as $Group) {
                    $tmp = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_GROUPS_HIDE, $Group);
                    array_push($Oner, $tmp);
                }
            } else {
                $tmp = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_GROUPS_HIDE, $Data);
                array_push($Oner, $tmp);
            }
        } else {
            $Oner = null;
        }

        $WhereMember = [
            FILED_MEMBER_GROUP_ID => $this->User_ID
        ];

        $Member = $GLOBALS[CLASS_DATABASE]->select(TABLE_MEMBER_GROUPS, $WhereMember);
        if (!is_null($Member)) {
            if (is_array($Member[0])) {
                foreach ($Member as $mem) {
                    $GroupID = $mem[FILED_MEMBER_GROUP_IDG];
                    $GroupName = $GLOBALS[CLASS_DATABASE]->select(TABLE_GROUPS, [FILED_GROUPS_ID => $GroupID, FILED_GROUPS_HIDE => OFF]);
                    if (!is_null($GroupName)) {
                        $tmp = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_GROUPS_HIDE, $GroupName);
                        array_push($MemberIn, $tmp);
                    }
                }
            } else {
                $GroupID = $Member[FILED_MEMBER_GROUP_IDG];
                $GroupName = $GLOBALS[CLASS_DATABASE]->select(TABLE_GROUPS, [FILED_GROUPS_ID => $GroupID, FILED_GROUPS_HIDE => OFF]);
                if (!is_null($GroupName)) {
                    $tmp = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_GROUPS_HIDE, $GroupName);
                    array_push($MemberIn, $tmp);
                }
            }
        } else {
            $MemberIn = null;
        }

        $Buffer = [];
        if ((!is_null($Oner)) && (count($Oner) > 0)) {
            $Buffer[KEY_TABLE_GROUPS] = $GLOBALS[CLASS_TOOLS]->Sort_Up_to_Down($Oner);
        }
        if ((!is_null($MemberIn)) && (count($MemberIn) > 0)) {
            $Buffer[KEY_TABLE_MEMBER_GROUPS] = $GLOBALS[CLASS_TOOLS]->Sort_Up_to_Down($MemberIn);
        }
        if (count($Buffer) > 0) {
            return $Buffer;
        } else {
            return NOT_FOUND;
        }
    }
    
    public function SyncCountrys() {
        $BufferCountry = $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY));
        foreach ($BufferCountry as $Country) {
            $CountNetwork = $GLOBALS[CLASS_DATABASE]->countRows(TABLE_MOBILE_NETWORK, [FILED_MOBILE_NETWORK_COUNTRY_NAME => $Country[FILED_COUNTRY_NAME]], true);
            $Country = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_COUNTRY_NUMBER_OF_NETWORK, $CountNetwork, $Country);
            $GLOBALS[CLASS_DATABASE]->update(TABLE_COUNTRY, [FILED_COUNTRY_NUMBER_OF_NETWORK => $CountNetwork], [FILED_COUNTRY_ID => $Country[FILED_COUNTRY_ID]]);
        }
    }

    public function SyncMemberInCountry() {
        $INCRIMENT_AFTER = intval(SystemVariable(FILED_SYSTEM_INCRIMENT_PRICE_AFTER));
        $INCRIMENT_PRICE = intval(SystemVariable(FILED_SYSTEM_INCRIMENT_PRICE));
        $BufferMember = $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY));
        foreach ($BufferMember as $Member) {
            $CountMember = $GLOBALS[CLASS_DATABASE]->countRows(TABLE_PHONE, [FILED_PHONE_COUNTRY => $Member[FILED_COUNTRY_ID]]);
            if ($CountMember >= $INCRIMENT_AFTER) {
                $Price = $Member[FILED_COUNTRY_PRICE] + $INCRIMENT_PRICE;
                $GLOBALS[CLASS_DATABASE]->update(TABLE_COUNTRY, [FILED_COUNTRY_MEMBERS => $CountMember, FILED_COUNTRY_PRICE => $Price], [FILED_COUNTRY_ID => $Member[FILED_COUNTRY_ID]]);
            } else {
                $GLOBALS[CLASS_DATABASE]->update(TABLE_COUNTRY, [FILED_COUNTRY_MEMBERS => $CountMember], [FILED_COUNTRY_ID => $Member[FILED_COUNTRY_ID]]);
            }
        }
    }

    public function SyncMusic(){
        $this->getID();
        //All Music in server
    }

    public function SyncNews() {
        $this->getID();
        //All News Related with the section or user
        //Section for News Papar in the futcher
    }

    public function SyncNotification($USER_ID = null) {
        if (is_null($User_ID)) {
            $this->getID();
        }
    }

    public function SyncFriend($USER_ID = null) {
        if (is_null($User_ID)) {
            $this->getID();
        }
    }

    public function SyncPonans($USER_ID = null) {
        if (is_null($User_ID)) {
            $this->getID();
        }
    }

    public function SyncStock($USER_ID = null) {
        if (is_null($User_ID)) {
            $this->getID();
        }
    }

    public function SyncPost() {
//in Friend Class
    }

    public function SyncComment() {
        $this->getID();
    }

    public function SyncGifts($USER_ID = null) {
        if (is_null($User_ID)) {
            $this->getID();
        }
    }

    public function serialize() {
        return serialize($this->SyncAll());
    }

    public function unserialize($serialized) {
        $this->Sync = unserialize($serialized);
    }

}
