<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Maltimedia_module
 *
 * @author abaza
 */
class Maltimedia_module {

    var $UID = null;
    var $To = null;
    var $IsProfile = null;
    var $Privace = null;
    var $Commant = null;
    var $FileType = null;
    var $Message = null;
    var $FileImagePath = null;
    var $FilePath = null;
    var $Dir = null;
    var $FileAllow = null;
    var $DIR_OPRATION = null;
    var $UserMang = null;
    var $IsTheAttach = false;

    function __construct() {
        if (is_null($this->DIR_OPRATION)) {
            if (class_exists(FileSystem)) {
                $this->DIR_OPRATION = new FileSystem();
            }
        }

        $this->FileAllow = FileAllow();
    }

    public function Maltimedia($WithMessage = false) {
        $Data = $_SERVER['HTTP_INFOR']; //UID , IS Profile , Privcey , Comment
        if (!$WithMessage) {
            $this->IsTheAttach = false;
            list($this->UID, $this->IsProfile, $this->Privace, $this->Commant, $this->FileType) = explode(":", $Data);
        } else {
            $this->IsTheAttach = true;
            list($this->UID, $this->To, $this->Message, $this->FileType) = explode(":", $Data);
            if (is_null($this->To)) {
                die(ShowError());
            }
        }
        if (empty($this->UID)) {
            die(ShowError());
        }

        if ($this->Filtration()) {
            $Dir = $this->FileType();
            if (!$this->IsTheAttach) {
                return $this->RoutingDir();
            } else {
                if (is_null($this->UserMang)) {
                    if (class_exists(UserMangment)) {
                        $this->UserMang = new UserMangment();
                    }
                }
                $Array = $this->RoutingDir();
                $DataID = 0;
                $DataLink = '';
                $URL = '';
                switch ($Dir) {
                    case AUDIO:
                        $Data = $Array[KEY_TABLE_USER_AUDIO];
                        $tmpDataLink = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_AUDIO, [FILED_USER_AUDIO_ID => $Data], '', '', false, 'AND', FILED_USER_AUDIO_AUDIO_PATH);
                        $DataLink = $tmpDataLink[FILED_USER_AUDIO_AUDIO_PATH];
                        $UUID = $this->UserMang->WhoIs($this->UID, FILED_USER_UUID, KEY_TABLE_USER);
                        $URL = $this->Send_Image_User($DataLink, AUDIO, $UUID);
                        break;
                    case VIDEO:
                        $Data = $Array[KEY_TABLE_USER_VIDEO];
                        $tmpDataLink = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_VIDEO, [FILED_USER_VIDEO_ID => $Data], '', '', false, 'AND', FILED_USER_VIDEO_VIDEO_PATH);
                        $DataLink = $tmpDataLink[FILED_USER_VIDEO_VIDEO_PATH];
                        $UUID = $this->UserMang->WhoIs($this->UID, FILED_USER_UUID, KEY_TABLE_USER);
                        $URL = $this->Send_Image_User($DataLink, VIDEO, $UUID);
                        break;
                    case PHOTO:
                        $Data = $Array[KEY_TABLE_USER_PHOTO];
                        $tmpDataLink = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_PHOTO, [FILED_USER_PHOTO_ID => $Data], '', '', false, 'AND', FILED_USER_PHOTO_PHOTO_PATH);
                        $DataLink = $tmpDataLink[FILED_USER_PHOTO_PHOTO_PATH];
                        $UUID = $this->UserMang->WhoIs($this->UID, FILED_USER_UUID, KEY_TABLE_USER);
                        $URL = $this->Send_Image_User($DataLink, PHOTO, $UUID);
                        break;
                }

                return [
                    FILED_MESSAGE_ATTACH => $URL,
                    FILED_MESSAGE_TO => $this->To,
                    FILED_MESSAGE_FROM => $this->UID,
                    FILED_MESSAGE_UID => $this->UID,
                    FILED_MESSAGE_MESSAGE => $this->Message,
                    FILED_MESSAGE_NEW => ON,
                    FILED_MESSAGE_HIDE => OFF,
                ];
            }
        }
        exit();
    }

    private function Filtration() {
        if (!$this->IsTheAttach) {
            if (!isset($this->UID)) {
                return FAIL . KEY_UID;
            } else {
                $this->UID = intval($this->UID);
            }
            if (!isset($this->IsProfile)) {
                return FAIL . KEY_USER_PHOTO_IS_PROFILE;
            }
            if (!isset($this->Privace)) {
                return FAIL . KEY_USER_PHOTO_PRIVACY;
            }

            if (!is_int($this->IsProfile)) {
                $this->IsProfile = intval($this->IsProfile);
            }

            if (!is_int($this->Privace)) {
                $this->Privace = intval($this->Privace);
            }

            if (is_string($this->Commant)) {
                $this->Commant = $GLOBALS[CLASS_TOOLS]->forString(mysql_real_escape_string($this->Commant));
            } else {
                $this->Commant = "";
            }

            if (is_string($this->FileType)) {
                $this->FileType = strtolower($this->FileType);
            }
        } else {
            if (!isset($this->UID)) {
                return FAIL . KEY_UID;
            } else {
                $this->UID = intval($this->UID);
            }

            if (is_string($this->Message)) {
                $this->Message = $GLOBALS[CLASS_TOOLS]->forString(mysql_real_escape_string($this->Message));
            } else {
                $this->Message = "";
            }
        }
        return true;
    }

    private function FileType() {
        if (is_array($this->FileAllow)) {
            foreach ($this->FileAllow as $Array => $file) {
                if (in_array($this->FileType, $file)) {
                    $this->Dir = $Array;
                    return $this->Dir;
                }
            }
        } else {
            $GLOBALS[CLASS_TOOLS]->System_Log("ERROR Not found File Allow Array", __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
            $this->Dir = null;
        }
    }

    private function RoutingDir() {
        if (!empty($this->Dir)) {
            $SearchForUser = array(
                FILED_USER_ID => $this->UID
            );
            if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_USER, $SearchForUser)) {
                $UserInfor = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $SearchForUser);
                $ImagePath = $UserInfor[FILED_USER_DIR_PATH];

                if ($this->Dir == PHOTO && $this->IsProfile == ON) {
                    $ProfileImage = array(
                        FILED_UID => $this->UID,
                        FILED_USER_PHOTO_IS_PROFILE => ON
                    );

                    $RetImage = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER_PHOTO, $ProfileImage, '', 1);
                    if (!is_null($RetImage)) {
                        $RetImage = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_USER_PHOTO_IS_PROFILE, OFF, $RetImage);
                        $RetImage = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_USER_PHOTO_ID, $RetImage);
                        $GLOBALS[CLASS_DATABASE]->update(TABLE_USER_PHOTO, $RetImage, $ProfileImage);
                    }
                }

                $this->FilePath = $ImagePath . DIRECTORY_SEPARATOR . $this->Dir;
                if (!$this->DIR_OPRATION->exists($this->FilePath)) {
                    if (!$this->DIR_OPRATION->mkdir($this->FilePath)) {
                        $GLOBALS[CLASS_TOOLS]->System_Log("Can not Make Dir " . $this->FilePath, __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
                        return FAIL;
                    }
                }

                if ($this->SavingFile()) {
                    if (!is_null($this->FileImagePath)) {
                        $DataImage = $GLOBALS[CLASS_TOOLS]->removeNull($this->GetDatabaseData());

                        $Ret = false;

                        switch ($this->Dir) {
                            case AUDIO:
                                if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_AUDIO, $DataImage)) {
                                    return [KEY_TABLE_USER_AUDIO => $GLOBALS[CLASS_DATABASE]->lastInsertID()];
                                }
                                break;
                            case VIDEO:
                                if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_VIDEO, $DataImage)) {
                                    return [KEY_TABLE_USER_VIDEO => $GLOBALS[CLASS_DATABASE]->lastInsertID()];
                                }
                                break;
                            case PHOTO:
                                if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_PHOTO, $DataImage)) {
                                    return [KEY_TABLE_USER_PHOTO => $GLOBALS[CLASS_DATABASE]->lastInsertID()];
                                }
                                break;
                        }
                    } else {
                        $GLOBALS[CLASS_TOOLS]->System_Log("Error in Write image file", __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
                        return FAIL . FILE_IMAGE;
                    }
                }
            }
        }
    }

    private function SavingFile() {
        $file = $tempFileImagePath = '';

        $name = basename($_FILES['uploaded_file']['name']);

        $file = $this->FilePath . DIRECTORY_SEPARATOR . $name;
        $tempFileImagePath = $this->FilePath . DIRECTORY_SEPARATOR . 'tmp_' . $name;

        if ($this->DIR_OPRATION->exists($file)) {
            $name = $GLOBALS[CLASS_TOOLS]->generateRandomString() . '.' . $this->FileType;
            $file = $this->FilePath . DIRECTORY_SEPARATOR . $name;
            $tempFileImagePath = $this->FilePath . DIRECTORY_SEPARATOR . 'tmp_' . $name;
        }

        $this->FileImagePath = $file;

        if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $tempFileImagePath)) {
            $this->Watermark_Open($tempFileImagePath, $this->FileImagePath);
            return true;
        } else {
            return false;
        }
    }

    private function GetDatabaseData() {
        $Data = array();
        switch ($this->Dir) {
            case AUDIO:
                $Data = array(
                    FILED_UID => $this->UID,
                    FILED_USER_AUDIO_AUDIO_PATH => $this->FileImagePath,
                    FILED_USER_AUDIO_COMMENT => ((!$this->IsTheAttach) ? $this->Commant : "MESSAGE"),
                    FILED_USER_AUDIO_TIME_OF_LIKE => 0,
                    FILED_USER_AUDIO_TIME_OF_UNLIKE => 0,
                    FILED_USER_AUDIO_PRIVACY => $this->Privace, //0=public 1=me only 2=friends 3=friends of friends 4=all world
                    FILED_USER_AUDIO_INATTACH => (($this->IsTheAttach) ? ON : OFF)
                );
                break;
            case VIDEO:
                $Data = array(
                    FILED_UID => $this->UID,
                    FILED_USER_VIDEO_VIDEO_PATH => $this->FileImagePath,
                    FILED_USER_VIDEO_COMMENT => ((!$this->IsTheAttach) ? $this->Commant : "MESSAGE"),
                    FILED_USER_VIDEO_TIME_OF_LIKE => 0,
                    FILED_USER_VIDEO_TIME_OF_UNLIKE => 0,
                    FILED_USER_VIDEO_PRIVACY => $this->Privace, //0=public 1=me only 2=friends 3=friends of friends 4=all world
                    FILED_USER_VIDEO_INATTACH => (($this->IsTheAttach) ? ON : OFF)
                );
                break;
            case PHOTO:
                $Data = array(
                    FILED_UID => $this->UID,
                    FILED_USER_PHOTO_PHOTO_PATH => $this->FileImagePath,
                    FILED_USER_PHOTO_IS_PROFILE => $this->IsProfile,
                    FILED_USER_PHOTO_COMMENT => ((!$this->IsTheAttach) ? $this->Commant : "MESSAGE"),
                    FILED_USER_PHOTO_TIME_OF_LIKE => 0,
                    FILED_USER_PHOTO_TIME_OF_UNLIKE => 0,
                    FILED_USER_PHOTO_PRIVACY => $this->Privace, //0=public 1=me only 2=friends 3=friends of friends 4=all world
                    FILED_USER_PHOTO_INATTACH => (($this->IsTheAttach) ? ON : OFF)
                );
                break;
        }
        return $Data;
    }

    public function Get_From_URL() {
        $temp = array(
            FILED_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_UID),
            FILED_USER_PHOTO_PHOTO_PATH => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_PHOTO_PATH),
            FILED_USER_PHOTO_IS_PROFILE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_IS_PROFILE),
            FILED_USER_PHOTO_PRIVACY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_PRIVACY), //0=public 1=me only 2=friends 3=friends of friends 4=all world
            FILED_USER_PHOTO_COMMENT => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_COMMENT),
            FILED_USER_PHOTO_TIME_OF_LIKE => 0,
            FILED_USER_PHOTO_TIME_OF_UNLIKE => 0
        );
        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($temp);

        if (!$GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_UID, $Data)) {
            $GLOBALS[CLASS_TOOLS]->System_Log("Not Found User ID", __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::NOTICE);
            return FAIL . KEY_UID;
        }

        if (!$GLOBALS[CLASS_TOOLS]->isKeyExists(FILED_USER_PHOTO_PHOTO_PATH, $Data)) {
            $GLOBALS[CLASS_TOOLS]->System_Log("Not Found URL Photo Path", __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::NOTICE);
            return FAIL . KEY_USER_PHOTO_PHOTO_PATH;
        }


        $where = array(FILED_USER_ID => $Data[FILED_UID]);

        $UserData = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $where);

        if (!is_null($UserData)) {
            $ImagePath = $UserData[FILED_USER_DIR_PATH];

            $ImageURL = pathinfo($Data[FILED_USER_PHOTO_PHOTO_PATH]);

            $ImageExtintion = $ImageURL['extension'];
            $ImageName = $ImageURL['basename'];

            if (is_null($ImageExtintion)) {
                $ImageExtintion = 'jpg';
                $ImageName = $ImageName . '.' . $ImageExtintion;
            }

            if (is_null($ImageName)) {
                $GLOBALS[CLASS_TOOLS]->generateRandomString() . '.' . $ImageExtintion;
            } else {
                foreach ($this->FileAllow as $Key => $Array) {
                    if ($Key == PHOTO) {
                        foreach ($Array as $Ext) {
                            if (strpos($ImageName, $Ext)) {
                                break;
                            }
                        }
                        break;
                    }
                }
            }

            $this->FilePath = $ImagePath . DIRECTORY_SEPARATOR . PHOTO . DIRECTORY_SEPARATOR . $ImageName;
            $this->url_save_image($Data[FILED_USER_PHOTO_PHOTO_PATH], $this->FilePath);
            if (file_exists($this->FilePath)) {

                $Data = $GLOBALS[CLASS_TOOLS]->ChangeValueInArray(FILED_USER_PHOTO_PHOTO_PATH, $this->FilePath, $Data);

                if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_USER_PHOTO, $Data)) {
                    $ImageID = array(
                        KEY_USER_PHOTO_ID => $GLOBALS[CLASS_DATABASE]->lastInsertID(),
                    );
                    return $ImageID;
                } else {
                    $GLOBALS[CLASS_TOOLS]->System_Log("Error in Save image " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
                    return FAIL . URL_ERROR;
                }
            }
        }
    }

    private function url_save_image($inPath, $outPath) { //Download images from remote server
        $in = fopen($inPath, "rb"); //if false
        if ($in != FALSE) {
            $out = fopen($outPath, "wb");
            while ($chunk = fread($in, 8192)) {
                fwrite($out, $chunk, 8192);
            }
            fclose($in);
            fclose($out);
        } else {
            $GLOBALS[CLASS_TOOLS]->System_Log("Filed to Load image in url [ " . $inPath . " ]", __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::NOTICE);
            return FAIL . URL_ERROR;
        }
    }

    public function Get_Multimedia($Type) {
        $UUID = null;
        $Table = null;
        $URLBuffer = array();

        switch ($Type) {
            case AUDIO:
                $Quiry = array(
                    FILED_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_UID),
                    FILED_USER_AUDIO_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_AUDIO_ID),
                    FILED_USER_AUDIO_INATTACH => OFF
                );
                $Table = TABLE_USER_AUDIO;
                break;
            case VIDEO:
                $Quiry = array(
                    FILED_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_UID),
                    FILED_USER_VIDEO_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_VIDEO_ID),
                    FILED_USER_VIDEO_INATTACH => OFF
                );
                $Table = TABLE_USER_VIDEO;
                break;
            case PHOTO:
                $Quiry = array(
                    FILED_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_UID),
                    FILED_USER_PHOTO_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_USER_PHOTO_ID),
                    FILED_USER_PHOTO_INATTACH => OFF
                );
                $Table = TABLE_USER_PHOTO;
                break;
        }

        $Quiry = $GLOBALS[CLASS_TOOLS]->removeNull($Quiry);

        if (is_null($Quiry)) {
            $GLOBALS[CLASS_TOOLS]->System_Log("Quiry Array is Empty", __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
            die(ShowError());
        }

        //Get User UUID
        $UserUUID = array(
            FILED_USER_ID => $Quiry[FILED_UID]
        );

        $RetUser = $GLOBALS[CLASS_DATABASE]->select(TABLE_USER, $UserUUID);

        if (!empty($RetUser) || !is_null($RetUser)) {
            $UUID = $RetUser[FILED_USER_UUID];
            if (empty($UUID)) {
                $GLOBALS[CLASS_TOOLS]->System_Log("I Can't found UUID for this ID : " . $RetUser[FILED_USER_ID], __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
                die(ShowError());
            }
        }

        $RecImage = $GLOBALS[CLASS_DATABASE]->select($Table, $Quiry, '', 1);
        $Path = null;
        if (!is_null($RecImage)) {
            switch ($Type) {
                case AUDIO:
                    $Path = $RecImage[FILED_USER_AUDIO_AUDIO_PATH];
                    break;
                case VIDEO:
                    $Path = $RecImage[FILED_USER_VIDEO_VIDEO_PATH];
                    break;
                case PHOTO:
                    $Path = $RecImage[FILED_USER_PHOTO_PHOTO_PATH];
                    break;
            }

//            $URL = $this->Send_Image_User($Path, $Type, $UUID);
            $URL = $Path;
            if (!empty($URL)) {
                switch ($Type) {
                    case AUDIO:
                        $URLBuffer = array(
                            KEY_UID => $Quiry[FILED_UID],
                            KEY_USER_AUDIO_AUDIO_PATH => $URL,
                            KEY_USER_AUDIO_TIME_OF_LIKE => $RecImage[FILED_USER_AUDIO_TIME_OF_LIKE],
                            KEY_USER_AUDIO_TIME_OF_UNLIKE => $RecImage[FILED_USER_AUDIO_TIME_OF_UNLIKE],
                            KEY_USER_AUDIO_PRIVACY => $RecImage[FILED_USER_AUDIO_PRIVACY],
                            KEY_USER_AUDIO_COMMENT => $RecImage[FILED_USER_AUDIO_COMMENT]
                        );
                        break;
                    case VIDEO:
                        $URLBuffer = array(
                            KEY_UID => $Quiry[FILED_UID],
                            KEY_USER_VIDEO_VIDEO_PATH => $URL,
                            KEY_USER_VIDEO_TIME_OF_LIKE => $RecImage[FILED_USER_VIDEO_TIME_OF_LIKE],
                            KEY_USER_VIDEO_TIME_OF_UNLIKE => $RecImage[FILED_USER_VIDEO_TIME_OF_UNLIKE],
                            KEY_USER_VIDEO_PRIVACY => $RecImage[FILED_USER_VIDEO_PRIVACY],
                            KEY_USER_VIDEO_COMMENT => $RecImage[FILED_USER_VIDEO_COMMENT]
                        );
                        break;
                    case PHOTO:
                        $URLBuffer = array(
                            KEY_UID => $Quiry[FILED_UID],
                            KEY_USER_PHOTO_PHOTO_PATH => $URL,
                            KEY_USER_PHOTO_TIME_OF_LIKE => $RecImage[FILED_USER_PHOTO_TIME_OF_LIKE],
                            KEY_USER_PHOTO_TIME_OF_UNLIKE => $RecImage[FILED_USER_PHOTO_TIME_OF_UNLIKE],
                            KEY_USER_PHOTO_IS_PROFILE => $RecImage[FILED_USER_PHOTO_IS_PROFILE],
                            KEY_USER_PHOTO_PRIVACY => $RecImage[FILED_USER_PHOTO_PRIVACY],
                            KEY_USER_PHOTO_COMMENT => $RecImage[FILED_USER_PHOTO_COMMENT]
                        );
                        break;
                }
                $URLBuffer = $GLOBALS[CLASS_TOOLS]->removeNull($URLBuffer);
                return $URLBuffer;
            } else {
                $GLOBALS[CLASS_TOOLS]->System_Log("ERROR", "Can't Reseved URL Image , Path : " . $Path . " UUID : " . $UUID . ", Image ID : " . $Image[FILED_USER_PHOTO_ID], __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
                return FAIL . URL_ERROR;
            }
        } else {
            $GLOBALS[CLASS_TOOLS]->System_Log("ERROR", "Image Not found : User ID : " . $Image[FILED_UID] . " Image ID : " . $Image[FILED_USER_PHOTO_ID], __CLASS__ . " " . __FUNCTION__, __LINE__, Tools::ERROR);
            return FAIL . URL_ERROR;
        }
    }

    public function Send_Image_User($Path, $type, $UUID = NULL) {
        $URL = '';
        if (!is_null($UUID)) {
            $FileName = pathinfo($Path);
            $ImageName = $FileName['basename'];
            switch ($type) {
                case AUDIO:
                    $DirPath = DIR_IMAGE . DIRECTORY_SEPARATOR . $UUID . DIRECTORY_SEPARATOR . AUDIO;
                    break;
                case VIDEO:
                    $DirPath = DIR_IMAGE . DIRECTORY_SEPARATOR . $UUID . DIRECTORY_SEPARATOR . VIDEO;
                    break;
                case PHOTO:
                    $DirPath = DIR_IMAGE . DIRECTORY_SEPARATOR . $UUID . DIRECTORY_SEPARATOR . PHOTO;
                    break;
            }
            $URL = "http://" . $_SERVER['HTTP_HOST'] . DIRECTORY_SEPARATOR . SystemVariable(FILED_SYSTEM_APPLICATION_NAME) . DIRECTORY_SEPARATOR . $DirPath . DIRECTORY_SEPARATOR . $ImageName;
        } else {
            $FileName = pathinfo($Path);
            $ImageName = $FileName['basename'];
            $Dir = strrchr($FileName['dirname'], DIRECTORY_SEPARATOR);
            $DirPath = DIR_IMAGE . $Dir;
            $URL = "http://" . $_SERVER['HTTP_HOST'] . DIRECTORY_SEPARATOR . SystemVariable(FILED_SYSTEM_APPLICATION_NAME) . DIRECTORY_SEPARATOR . $DirPath . DIRECTORY_SEPARATOR . $ImageName;
        }
        return $URL;
    }

    private function Watermark_Open($oldimage_name, $FilePathImage) {
        list($owidth, $oheight, $ImageType) = getimagesize($oldimage_name);

        $width = $owidth;
        $height = $oheight;

        if ($oheight >= 3000) {
            $MarkImage = IMAGE_WATERMARKER_PATH_1;
        } elseif (($oheight <= 2999) && ($oheight >= 1000)) {
            $MarkImage = IMAGE_WATERMARKER_PATH_2;
        } elseif (($oheight <= 999) && ($oheight >= 400)) {
            $MarkImage = IMAGE_WATERMARKER_PATH_3;
        } else {
            $MarkImage = IMAGE_WATERMARKER_PATH_4;
        }

        $im = imagecreatetruecolor($width, $height);

        switch ($ImageType) {
            case IMAGETYPE_GIF: $img_src = imagecreatefromgif($oldimage_name);
                break;
            case IMAGETYPE_JPEG: $img_src = imagecreatefromjpeg($oldimage_name);
                break;
            case IMAGETYPE_PNG: $img_src = imagecreatefrompng($oldimage_name);
                break;
        }
        imagecopyresampled($im, $img_src, 0, 0, 0, 0, $width, $height, $owidth, $oheight);

        $watermark = imagecreatefrompng($MarkImage);

        list($w_width, $w_height) = getimagesize($MarkImage);

        $pos_x = $width - $w_width;
        $pos_y = $height - $w_height;

        imagecopy($im, $watermark, $pos_x, $pos_y, 0, 0, $w_width, $w_height);

        switch ($ImageType) {
            case IMAGETYPE_GIF: $img_src = imagegif($im, $FilePathImage, 100);
                break;
            case IMAGETYPE_JPEG: $img_src = imagejpeg($im, $FilePathImage, 100);
                break;
            case IMAGETYPE_PNG: $img_src = imagepng($im, $FilePathImage, 100);
                break;
        }
        imagedestroy($im);
        imagedestroy($watermark);

        unlink($oldimage_name);
        return true;
    }

    public function Show_Image_In_Browser($Image) {
        header('Content-Type: image/jpeg');
        imagejpeg($Image, NULL, 100);  // use best image quality (100)
    }

    function __destruct() {
        if (!is_null($this->UID)) {
            unset($this->UID);
        }
        if (!is_null($this->IsProfile)) {
            unset($this->IsProfile);
        }
        if (!is_null($this->Privace)) {
            unset($this->Privace);
        }
        if (!is_null($this->Commant)) {
            unset($this->Commant);
        }
        if (!is_null($this->FileType)) {
            unset($this->FileType);
        }
        if (!is_null($this->FileImagePath)) {
            unset($this->FileImagePath);
        }
        if (!is_null($this->FilePath)) {
            unset($this->FilePath);
        }
        if (!is_null($this->Dir)) {
            unset($this->Dir);
        }
        if (!is_null($this->FileAllow)) {
            unset($this->FileAllow);
        }
        if (!is_null($this->DIR_OPRATION)) {
            unset($this->DIR_OPRATION);
        }

        if (!is_null($this->UserMang)) {
            unset($this->UserMang);
        }
    }

}
