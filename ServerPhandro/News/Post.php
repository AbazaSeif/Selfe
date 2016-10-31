<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of News
 *
 * @author abaza
 */
class Post extends Comment {

    var $UserMang = null;
    var $Frinds = null;

    public function init() {
        if (is_null($this->UserMang)) {
            $this->UserMang = new UserMangment();
        }
        if (is_null($this->Frinds)) {
            $this->Frinds = new Friend_Class();
        }
    }

    function __destruct() {
        if (!is_null($this->UserMang)) {
            unset($this->UserMang);
        }
        if (!is_null($this->Frinds)) {
            unset($this->Frinds);
        }
    }

    private function LogERROR($Message, $Function, $Line) {
        $GLOBALS[CLASS_TOOLS]->System_Log($Message, __CLASS__ . "::" . $Function, $Line, Tools::ERROR);
    }

    public function Set_Post() {
        $tmp_PostData = [
            FILED_POST_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_UID),
            FILED_POST_TEXT => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_TEXT),
            FILED_POST_MEDIA_PATH => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_MEDIA_PATH),
            FILED_POST_MOOD => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_MOOD),
            FILED_POST_PLACE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_PLACE),
            FILED_POST_PRIVACY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_PRIVACY),
            FILED_POST_DATE_TIME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_DATE_TIME),
            FILED_POST_TIME_OF_LIKE => 0,
            FILED_POST_TIME_OF_UNLIKE => 0
        ];

        $PostData = $GLOBALS[CLASS_TOOLS]->removeNull($tmp_PostData);

        if (!is_null($PostData)) {
            if ($GLOBALS[CLASS_TOOLS]->in_Array($PostData, FILED_POST_UID, FILED_POST_TEXT, FILED_POST_PRIVACY)) {
                if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_POST, $PostData)) {
                    return $this->Get_Posts_User($PostData[FILED_POST_UID]);
                } else {
                    $this->LogERROR("Error MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                    return FAIL;
                }
            } else {
                $this->LogERROR("Error in function in_Array", __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("No Data in Post", __FUNCTION__, __LINE__);
            die(ShowError());
        }
    }

    public function Get_Posts_User($User_ID) {
        $PostBuffer = array();
        $tmp = [
            FILED_POST_UID => (isset($User_ID) ? $User_ID : $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_UID))
        ];
        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
        if (!is_null($Data)) {
            $tmpBuffer = $GLOBALS[CLASS_DATABASE]->select(TABLE_POST, $Data, FILED_POST_TIME);
            if (!is_null($tmpBuffer)) {
                $Buffer = $GLOBALS[CLASS_TOOLS]->removeNull($tmpBuffer);
                //$Buffer it is Post and it is Clean now
                //Now i want to get the Comment and Recooment for eatch post and
                //put all in one array
                if (!is_null($Buffer)) {
                    if (is_array($Buffer[0])) {
                        foreach ($Buffer as $Posts) {
                            $Comment = $this->getPostsComment($Posts[FILED_POST_ID]);
                            if (!is_null($Comment)) {
                                $Posts[KEY_TABLE_POST_COMMENT] = $Comment;
                            }
                            array_push($PostBuffer, $Posts);
                        }
                    } else {
                        $Comment = $this->getPostsComment($Buffer[FILED_POST_ID]);
                        if (!is_null($Comment)) {
                            $Buffer[KEY_TABLE_POST_COMMENT] = $Comment;
                        }
                        array_push($PostBuffer, $Buffer);
                    }

                    $MyShearPost = $this->GetShear($tmp[FILED_POST_UID]);
                    if (!is_null($MyShearPost)) {
                        $PostBuffer[KEY_TABLE_SHAER] = $MyShearPost;
                    }

                    return $PostBuffer;
                } else {
                    $this->LogERROR("Error MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                    return FAIL;
                }
            } else {
                $this->LogERROR("Error in function in_Array", __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("No Data in Post", __FUNCTION__, __LINE__);
            die(ShowError());
        }
    }

    public function Del_Post() {
        $Post = [
            FILED_POST_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_UID),
            FILED_POST_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_ID)
        ];

        if ($GLOBALS[CLASS_DATABASE]->delete(TABLE_POST, $Post, 1)) {
            return $this->Get_Posts_User($Post[FILED_POST_UID]);
        } else {
            $this->LogERROR("Error MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
            return FAIL;
        }
    }

    public function Edit_Post() {
        $Where = [
            FILED_POST_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_ID),
            FILED_POST_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_UID)
        ];
        $tmp_PostData = [
            FILED_POST_TEXT => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_TEXT),
            FILED_POST_MEDIA_PATH => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_MEDIA_PATH),
            FILED_POST_MOOD => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_MOOD),
            FILED_POST_PLACE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_PLACE),
            FILED_POST_PRIVACY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_PRIVACY),
            FILED_POST_DATE_TIME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_DATE_TIME),
            FILED_POST_TIME_OF_LIKE => 0,
            FILED_POST_TIME_OF_UNLIKE => 0
        ];

        $PostData = $GLOBALS[CLASS_TOOLS]->removeNull($tmp_PostData);

        if (!is_null($PostData)) {
            if ($GLOBALS[CLASS_TOOLS]->in_Array($PostData, FILED_POST_TEXT, FILED_POST_PRIVACY)) {
                if ($GLOBALS[CLASS_DATABASE]->update(TABLE_POST, $PostData, $Where)) {
                    return $this->Get_Posts_User($Where[FILED_POST_UID]);
                }
            } else {
                $this->LogERROR("Error in function in_Array", __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            $this->LogERROR("No Data in Post", __FUNCTION__, __LINE__);
            die(ShowError());
        }
    }

    public function Shear() {
        $this->init();
        $tmpData = [
            FILED_SHAER_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SHAER_UID),
            FILED_SHAER_POST_ID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SHAER_POST_ID),
            FILED_SHAER_TEXT => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SHAER_TEXT),
            FILED_SHAER_DATE_TIME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SHAER_DATE_TIME),
            FILED_SHAER_TIME_OF_LIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SHAER_TIME_OF_LIKE),
            FILED_SHAER_TIME_OF_UNLIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SHAER_TIME_OF_UNLIKE),
            FILED_SHAER_PRIVACY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_SHAER_PRIVACY)
        ];

        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($tmpData);
        if ($GLOBALS[CLASS_DATABASE]->isExist(TABLE_POST, [FILED_POST_ID => $Data[FILED_SHAER_POST_ID]])) {
            $Post = $GLOBALS[CLASS_DATABASE]->select(TABLE_POST, [FILED_POST_ID => $Data[FILED_SHAER_POST_ID]]);
            $post_user_id = $Post[FILED_POST_UID];
            $user_want_share = $Data[FILED_SHAER_UID];
            if ($this->Frinds->isFriends($post_user_id, $user_want_share)) {
                if (!is_null($Data)) {
                    if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_SHAER, $Data)) {
                        return $this->Get_Posts_User($Data[FILED_SHAER_UID]);
                    } else {
                        $this->LogERROR("Error MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                        return FAIL;
                    }
                }
            } else {
                return NOT_FRINDS;
            }
        } else {
            return FAIL;
        }
    }

    private function GetShear($UserID) {
        $this->init();
        $ShearPost = array();
        $Data = $GLOBALS[CLASS_DATABASE]->select(TABLE_SHAER, [FILED_SHAER_UID => $UserID], FILED_SHAER_DATE_TIME);
        if (!is_null($Data)) {
            if (is_array($Data[0])) {
                foreach ($Data as $post) {
                    $ID = $post[FILED_SHAER_POST_ID];
                    $DataRet = $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_POST, [FILED_POST_ID => $ID]));

                    if (!is_null($DataRet)) {
                        $DataRet[FILED_USER_NAME] = $this->UserMang->WhoIs($DataRet[FILED_POST_UID], FILED_USER_NAME, KEY_TABLE_USER);
                        $tmp1 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_ID, $DataRet);
                        $tmp2 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_UID, $tmp1);
                        $tmp3 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_PRIVACY, $tmp2);
                        $tmp4 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_MOOD, $tmp3);
                        $tmp5 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_PLACE, $tmp4);
                        $tmp5['SHAER' . FILED_SHAER_TEXT] = $post[FILED_SHAER_TEXT];
                        $tmp5['SHAER' . FILED_SHAER_DATE_TIME] = $post[FILED_SHAER_DATE_TIME];
                        $tmpFinal = $GLOBALS[CLASS_TOOLS]->Key_Sort_Down_to_Up($tmp5);
                        array_push($ShearPost, $tmpFinal);
                    }
                }
            } else {
                $ID = $Data[FILED_SHAER_POST_ID];
                $DataRet = $GLOBALS[CLASS_TOOLS]->removeNull($GLOBALS[CLASS_DATABASE]->select(TABLE_POST, [FILED_POST_ID => $ID]));

                if (!is_null($DataRet)) {
                    $DataRet[FILED_USER_NAME] = $this->UserMang->WhoIs($DataRet[FILED_POST_UID], FILED_USER_NAME, KEY_TABLE_USER);
                    $tmp1 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_ID, $DataRet);
                    $tmp2 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_UID, $tmp1);
                    $tmp3 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_PRIVACY, $tmp2);
                    $tmp4 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_MOOD, $tmp3);
                    $tmp5 = $GLOBALS[CLASS_TOOLS]->RemoveKeyInArray(FILED_POST_PLACE, $tmp4);
                    $tmp5['SHAER' . FILED_SHAER_TEXT] = $Data[FILED_SHAER_TEXT];
                    $tmp5['SHAER' . FILED_SHAER_DATE_TIME] = $Data[FILED_SHAER_DATE_TIME];
                    $tmpFinal = $GLOBALS[CLASS_TOOLS]->Key_Sort_Down_to_Up($tmp5);
                    array_push($ShearPost, $tmpFinal);
                }
            }
//            return $GLOBALS[CLASS_TOOLS]->removeDuplicat($ShearPost);
            return $ShearPost;
        } else {
            return null;
        }
    }

}