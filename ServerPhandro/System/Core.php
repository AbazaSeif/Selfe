<?php

if (getcwd() == dirname(__FILE__)) {
    require '../System/ErrorPage.php';
    die(ShowError());
}

/**
 * Description of AndroServer
 *
 * @author abaza
 */
class Core {

    var $Interface = null;
    var $UserGroup = null;
    var $UserMangment = null;
    var $Friends = null;
    var $Multimedia = null;
    var $PostComment = null;
    var $Syncronization = null;
    var $Message = null;
    var $Groups = null;
    var $Searching = null;
    var $Login = null;
    var $Post = null;
    var $Zone = null;
    var $SMS = null;
    var $Radio = null;
    var $Buffer = null;

    public function __construct($Interface) {


        $this->Interface = $Interface;
        if (is_null($this->UserGroup)) {
            if (class_exists(Creating_New)) {
                $this->UserGroup = new Creating_New();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Zone)) {
            if (class_exists(Zone)) {
                $this->Zone = new Zone();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->UserMangment)) {
            if (class_exists(UserMangment)) {
                $this->UserMangment = new UserMangment();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Login)) {
            if (class_exists(Class_Login)) {
                $this->Login = new Class_Login();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Multimedia)) {
            if (class_exists(Maltimedia_module)) {
                $this->Multimedia = new Maltimedia_module();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Searching)) {
            if (class_exists(Search_Class)) {
                $this->Searching = new Search_Class();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Message)) {
            if (class_exists(Message_Class)) {
                $this->Message = new Message_Class();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Syncronization)) {
            if (class_exists(Syncronization)) {
                $this->Syncronization = new Syncronization();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Friends)) {
            if (class_exists(Friend_Class)) {
                $this->Friends = new Friend_Class();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Post)) {
            if (class_exists(Post)) {
                $this->Post = new Post();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->PostComment)) {
            if (class_exists(Comment)) {
                $this->PostComment = new Comment();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->Groups)) {
            if (class_exists(Class_Group)) {
                $this->Groups = new Class_Group();
            } else {
                die(ShowError());
            }
        }

        if (is_null($this->SMS)) {
            if (class_exists(SMS)) {
                $this->SMS = new SMS();
            } else {
                die(ShowError());
            }
        }
    }

    function __destruct() {
        if (!is_null($this->Buffer)) {
            unset($this->Buffer);
        }
        if (!is_null($this->Interface)) {
            unset($this->Interface);
        }
        if (!is_null($this->UserGroup)) {
            unset($this->UserGroup);
        }
        if (!is_null($this->UserMangment)) {
            unset($this->UserMangment);
        }
        if (!is_null($this->Multimedia)) {
            unset($this->Multimedia);
        }
        if (!is_null($this->Searching)) {
            unset($this->Searching);
        }
        if (!is_null($this->Message)) {
            unset($this->Message);
        }
        if (!is_null($this->Syncronization)) {
            unset($this->Syncronization);
        }
        if (!is_null($this->Friends)) {
            unset($this->Friends);
        }
        if (!is_null($this->Post)) {
            unset($this->Post);
        }
        if (!is_null($this->PostComment)) {
            unset($this->PostComment);
        }
        if (!is_null($this->Groups)) {
            unset($this->Groups);
        }
        if (!is_null($this->SMS)) {
            unset($this->SMS);
        }
    }

    public function Android_Phone() {
        $Information = $GLOBALS[CLASS_TOOLS]->Show(SystemVariable(FILED_SYSTEM_APPLICATION_NAME) . ',Server Version:' . SystemVariable(FILED_SYSTEM_VERSION));
        $this->Buffer = $Information;
    }

    public function Test() {
//        $this->UserMangment->Calculat_Total_Of_Ponans_User();
        $GLOBALS[CLASS_TOOLS]->Show(SystemVariable(FILED_SYSTEM_APPLICATION_NAME) . ' Server Version ' . SystemVariable(FILED_SYSTEM_VERSION) . '<br>');
        $this->Buffer = SUCCESS;
        $this->Outdata();
    }

    public function RadioOnline($Type) {
        switch ($Type) {
            case ONLINE_RADIO:
                $this->Radio = new Radio_Online();
                $this->Radio->Start();
                break;
        }
    }

    public function User_Group($Type) {
        if (is_string($Type)) {
            switch ($Type) {
                case NEW_USER://Create new user
                    $this->Buffer = $this->UserGroup->Create_New_User();
                    break;
                case EDIT_USER://Edit Data user
                    $this->Buffer = $this->UserGroup->Edite_User();
                    break;
                case DELETE_USER://Delete user account
                    $this->Buffer = $this->UserGroup->Delete_User();
                    break;
            }
        }
        $this->Outdata();
    }

    public function Multimedia_Group($Type) {
        switch ($Type) {
            case UPLOAD_MALTIMEDIA://Upload image
                $this->Buffer = $this->Multimedia->Maltimedia();
                break;
            case UPLOAD_FROM_URL:
                $this->Buffer = $this->Multimedia->Get_From_URL();
                break;
            case GET_IMAGE://download image
                $this->Buffer = $this->Multimedia->Get_Multimedia(PHOTO);
                break;
            case GET_VIDEO://get video
                $this->Buffer = $this->Multimedia->Get_Multimedia(VIDEO);
                break;
            case GET_AUDIO:
                $this->Buffer = $this->Multimedia->Get_Multimedia(AUDIO);
                break;
        }
        $this->Outdata();
    }

    public function Search($Type) {
        $this->Buffer = $this->Searching->GetResult();
        $this->Outdata();
    }

    public function sms_active($Type) {
        switch ($Type) {
            case SMS_ACTIVE_CODE:
                $this->Buffer = $this->SMS->SMS_Active();
                break;
        }

        $this->Outdata();
    }

    public function User_Mangment_Group($Type) {
        switch ($Type) {
            case GET_USER_LIST://Get all user list in the zone
                $this->Buffer = $this->UserMangment->Get_User_List();
                break;
            case GET_USER_INFO: //Get Information
                $this->Buffer = $this->UserMangment->Get_User_Information();
                break;
            case GET_ALBUMES://Get Photo Album for user
                $this->Buffer = $this->UserMangment->Get_User_Albume();
                break;
            case SET_COMMENT_IN_PHOTO: //Set Comment in Photo
                $this->Buffer = $this->UserMangment->Set_Comment_in_photo();
                break;
            case DELETE_COMMENT_IN_PHOTO: //Delete Comment in Photo
                $this->Buffer = $this->UserMangment->Delet_Comment_in_Photo();
                break;
            case EDIT_COMMENT_IN_PHOTO: //Edite Comment in Photo
                $this->Buffer = $this->UserMangment->Edit_Comment_in_Photo();
                break;
            case SET_COMMENT_IN_AUDIO: //Set Comment in Audio
                $this->Buffer = $this->UserMangment->Set_Comment_in_Audio();
                break;
            case DELETE_COMMENT_IN_AUDIO: //Delete Comment in Audio
                $this->Buffer = $this->UserMangment->Delet_Comment_in_Audio();
                break;
            case EDIT_COMMENT_IN_AUDIO : //Edite Comment in Audio
                $this->Buffer = $this->UserMangment->Edit_Comment_in_Audio();
                break;
            case SET_COMMENT_IN_VIDEO: //Set Comment in Video
                $this->Buffer = $this->UserMangment->Set_Comment_in_Video();
                break;
            case DELETE_COMMENT_IN_VIDEO: //Delete Comment in Video
                $this->Buffer = $this->UserMangment->Delet_Comment_in_Video();
                break;
            case EDIT_COMMENT_IN_VIDEO : //Edite Comment in Video
                $this->Buffer = $this->UserMangment->Edit_Comment_in_Video();
                break;
            case GET_NOTIFICATION: //Get Notification 
                $this->Buffer = $this->UserMangment->getNotification();
                break;
            case STATUS_CHANGE: //Change Status (Online , Offline)
                $this->Buffer = $this->UserMangment->Change_Status_Line();
                break;
            case SET_PONANS: //set Ponas for User
                $this->Buffer = $this->UserMangment->Set_Ponans_For_User();
                break;
            case GET_PONANS: //get Ponas for User
                $this->Buffer = $this->UserMangment->Get_Ponans_For_User();
                break;
        }
        $this->Outdata();
    }

    /**
     * BufferCountrys
     * it is only buffer the country in the Database table without change anything
     * only it convert flag name to URL fot flag
     */
    public function BufferCountrys() {

        $this->Buffer = array();
        $BufferCountry = $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_COUNTRY));
        foreach ($BufferCountry as $Country) {
            $Flag = DIR_FLAGS . $Country[FILED_COUNTRY_PATH_FLAG];
            $URL = $GLOBALS[CLASS_TOOLS]->CreateURL($Flag);
            $Country = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_COUNTRY_PATH_FLAG, $URL, $Country);
            $Country = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_COUNTRY_NAME, ucfirst($Country[FILED_COUNTRY_NAME]), $Country);
            $this->Buffer[$Country[FILED_COUNTRY_NAME]] = $Country;
        }
        $this->Outdata();
    }

    /**
     * BufferNetwork
     * it is not change anything only get Networks name in Table database 
     * to array and also not duplicat the network name
     */
    public function BufferNetworks() {
        $this->Buffer = array();
        $BufferNetwork = $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_MOBILE_NETWORK));
        foreach ($BufferNetwork as $Network) {
            if (!in_array($Network[FILED_MOBILE_NETWORK_NETWORK_NAME], $this->Buffer)) {
                $NetworkDitels = array(
                    $Network[FILED_MOBILE_NETWORK_CODE],
                    $Network[FILED_MOBILE_NETWORK_COUNTRY_NAME]
                );
                if (array_key_exists($Network[FILED_MOBILE_NETWORK_NETWORK_NAME], $this->Buffer)) {
                    array_push($this->Buffer[$Network[FILED_MOBILE_NETWORK_NETWORK_NAME]], $NetworkDitels);
                } else {
                    $this->Buffer[$Network[FILED_MOBILE_NETWORK_NETWORK_NAME]] = [$NetworkDitels];
                }
            }
        }
        if (is_null($BufferNetwork)) {
            $this->Buffer = NOT_FOUND;
        }
        $this->Outdata();
    }

    public function Message_Unit($Type) {
        switch ($Type) {
            case SET_MESSAGE:
                $this->Buffer = $this->Message->set_message();
                break;
            case GET_MESSAGE:
                $this->Buffer = $this->Message->get_new_message();
                break;
            case GET_ALL_MESSAGE:
                $this->Buffer = $this->Message->get_all_message();
                break;
            case DEL_MESSAGE:
                $this->Buffer = $this->Message->delete_message();
                break;
        }
        $this->Outdata();
    }

    public function Gift_Unit($Type) {
        $this->Outdata();
    }

    public function Groups($Type) {
        switch ($Type) {
            case CREATE_GROUP:
                $this->Buffer = $this->Groups->Create_New_Group();
                break;
            case DELETE_GROUP:
                $this->Buffer = $this->Groups->Delete_Group();
                break;
            case EDIT_GROUP:
                $this->Buffer = $this->Groups->Edite_Group();
                break;
            case ADDTO_GROUP:
                $this->Buffer = $this->Groups->Add_to_Group();
                break;
            case REVFROM_GROUP:
                $this->Buffer = $this->Groups->Remove_from_Group();
                break;
            case UPDATELEVEL_GROUP:
                $this->Buffer = $this->Groups->Update_Level();
                break;
        }
        $this->Outdata();
    }

    public function Icon_User_Unit($Type) {
        $this->Outdata();
    }

    public function Money_Unit($Type) {
        $this->Outdata();
    }

    public function Conntact_Unit($Type) {
        $this->Outdata();
    }

    public function Login_Unit($Type) {

        switch ($Type) {
            case NORMAL_LOGIN : //normal login for Android Users
                $this->Buffer = $this->Login->Normal_Login();
                break;
            case PASSWORD_LOGIN: //Login if IMEI or SIM Card Serial Number is Exist in Database
                $this->Buffer = $this->Login->Password_Login();
                break;
        }
        $this->Outdata();
    }

    public function Post($Type) {
        switch ($Type) {
            case POST_SET:
                $this->Buffer = $this->Post->Set_Post();
                break;
            case POST_GET_USER_POST:
                $this->Buffer = $this->Post->Get_Posts_User();
                break;
            case POST_DEL:
                $this->Buffer = $this->Post->Del_Post();
                break;
            case POST_EDI:
                $this->Buffer = $this->Post->Edit_Post();
                break;
            case POST_SHEAR:
                $this->Buffer = $this->Post->Shear();
                break;
            case SET_POST_COMMENT:
                $this->Buffer = $this->PostComment->setPostComment();
                break;
            case GET_POST_COMMENT:
                $this->Buffer = $this->PostComment->getPostsComment();
                break;
            case SET_POST_RECOMMENT:
                $this->Buffer = $this->PostComment->setPostRecomment();
                break;
            case GET_POST_RECOMMENT:
                $this->Buffer = $this->PostComment->getPostsRecommentComment();
                break;
        }
        $this->Outdata();
    }

    private function Outdata() {
        if ((isset($this->Buffer)) || (!is_null($this->Buffer))) {
            if (is_array($this->Buffer)) {
                $tmp = $this->Buffer;
                $this->Buffer = [];
                $this->Buffer = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
            }
            $this->Interface->Buffer($this->Buffer);
        } else {
            $this->Interface->Buffer(DONE);
        }
        exit();
    }

    public function Sync_Unit($type) {
        switch ($type) {
            case SYNC_ALL:
                $Data = serialize($this->Syncronization);
                $this->Buffer = unserialize($Data);
//                $this->Buffer = $this->Syncronization->SyncAll();
                break;
            case SYNC_COUNTRY:
                $this->Buffer = $this->Syncronization->SyncCountrys();
                break;
            case SYNC_MEMBER_IN_COUNTRY:
                $this->Buffer = $this->Syncronization->SyncMemberInCountry();
                break;
            case SYNC_COMMENT:
                $this->Buffer = $this->Syncronization->SyncComment();
                break;
            case SYNC_FRINDS:
                $this->Buffer = $this->Syncronization->SyncFriend();
                break;
            case SYNC_MUSIC:
                $this->Buffer = $this->Syncronization->SyncMusic();
                break;
            case SYNC_GIFTS:
                $this->Buffer = $this->Syncronization->SyncGifts();
                break;
            case SYNC_MESSAGE:
                $this->Buffer = $this->Syncronization->SyncMessage();
                break;
            case SYNC_NEWS:
                $this->Buffer = $this->Syncronization->SyncNews();
                break;
            case SYNC_NOTIFICATION:
                $this->Buffer = $this->Syncronization->SyncNotification();
                break;
            case SYNC_PONANS:
                $this->Buffer = $this->Syncronization->SyncPonans();
                break;
            case SYNC_STOCK:
                $this->Buffer = $this->Syncronization->SyncStock();
                break;
            case SYNC_POST:
                $this->Buffer = $this->Syncronization->SyncPost();
                break;
        }
        $this->Outdata();
    }

    public function Friends($type) {
        switch ($type) {
            case GET_USER_FRIENDS: //Get User Friends
                $this->Buffer = $this->Friends->Get_User_friends();
                break;
            case MAKE_FRIEND: //set firend for user
                $this->Buffer = $this->Friends->Set_Friend();
                break;
            case MAKE_UNFRIEND: //set unfirend for user
                $this->Buffer = $this->Friends->Set_Unfriend();
                break;
            case SET_FRIEND_FAVORIT: //set favort friend
                $this->Buffer = $this->Friends->Set_Friend_Favorit();
                break;
            case SET_FRIEND_UNFAVORIT: //set unfavort friend
                $this->Buffer = $this->Friends->Set_Friend_Unfavorit();
                break;
            case SET_FRIEND_ACCEPT:
                $this->Buffer = $this->Friends->Accept_friend();
                break;
            case SET_FRIEND_REFUSED:
                $this->Buffer = $this->Friends->Refused_friend();
                break;
            case SET_FRIEND_FOLLOWER:
                $this->Buffer = $this->Friends->Follower_friend();
                break;
            case SET_FRIEND_BLOCK:
                $this->Buffer = $this->Friends->Block_friend();
                break;
            case SET_FRIEND_UNBLOCK:
                $this->Buffer = $this->Friends->Unblock_friend();
                break;
        }
        $this->Outdata();
    }

}