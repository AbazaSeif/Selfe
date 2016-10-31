<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Post_Comment
 *
 * @author abaza
 */
class Comment {

    private function LogERROR($Message, $Function, $Line) {
        $GLOBALS[CLASS_TOOLS]->System_Log($Message, __CLASS__ . "::" . $Function, $Line, Tools::ERROR);
    }

    public function setPostRecomment() {
        $tmp = [
            FILED_POST_COMMENT_RECOMMENT_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_UID),
            FILED_POST_COMMENT_RECOMMENT_CCID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_CCID),
            FILED_POST_COMMENT_RECOMMENT_TEXT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_TEXT)),
            FILED_POST_COMMENT_RECOMMENT_MEDIA_PATH => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_MEDIA_PATH),
            FILED_POST_COMMENT_RECOMMENT_DATE_TIME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_DATE_TIME),
            FILED_POST_COMMENT_RECOMMENT_TIME_OF_LIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_TIME_OF_LIKE),
            FILED_POST_COMMENT_RECOMMENT_TIME_OF_UNLIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_TIME_OF_UNLIKE),
            FILED_POST_COMMENT_RECOMMENT_PRIVACY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_PRIVACY)
        ];

        $Data = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
        if (!$GLOBALS[CLASS_TOOLS]->in_Array($Data, FILED_POST_COMMENT_RECOMMENT_UID, FILED_POST_COMMENT_RECOMMENT_TEXT, FILED_POST_COMMENT_RECOMMENT_CCID)) {
            return FAIL;
        }
        if (!is_null($Data)) {
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_POST_COMMENT_RECOMMENT, $Data)) {
                //Reload Post and Comments and recomments
                return SUCCESS;
            } else {
                $this->LogERROR("Error MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        } else {
            return FAIL;
        }
    }

    public function setPostComment() {
        $tmp = [
            FILED_POST_COMMENT_CID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_CID),
            FILED_POST_COMMENT_UID => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_UID),
            FILED_POST_COMMENT_TEXT => $GLOBALS[CLASS_TOOLS]->forString($GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_TEXT)),
            FILED_POST_COMMENT_DATE_TIME => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_DATE_TIME),
            FILED_POST_COMMENT_MEDIA_PATH => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_MEDIA_PATH),
            FILED_POST_COMMENT_PRIVACY => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_PRIVACY),
            FILED_POST_COMMENT_TIME_OF_LIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_TIME_OF_LIKE),
            FILED_POST_COMMENT_TIME_OF_UNLIKE => $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_TIME_OF_UNLIKE),
            FILED_POST_COMMENT_FOR => POST_POST
        ];

        $Comment = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
        if (!$GLOBALS[CLASS_TOOLS]->in_Array($Comment, FILED_POST_COMMENT_CID, FILED_POST_COMMENT_TEXT, FILED_POST_COMMENT_UID)) {
            return FAIL;
        }

        if (!is_null($Comment)) {
            if ($GLOBALS[CLASS_DATABASE]->insert(TABLE_POST_COMMENT, $Comment)) {
                //TODO: it must to Refresh Post with comment and retern it 
                return SUCCESS;
            } else {
                $this->LogERROR("Error MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __FUNCTION__, __LINE__);
                return FAIL;
            }
        }
    }

    public function getPostsComment($Post_ID) {
        $tmp = [
            FILED_POST_COMMENT_CID => (isset($Post_ID) ? $Post_ID : $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_CID)),
            FILED_POST_COMMENT_FOR => POST_POST
        ];
        $Post = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
        if (!is_null($Post)) {
            $tmpData = $GLOBALS[CLASS_DATABASE]->select(TABLE_POST_COMMENT, $Post, FILED_POST_COMMENT_DATE_TIME);

            if (!is_null($tmpData)) {
                $Data = $GLOBALS[CLASS_TOOLS]->removeNull($tmpData);
                if (!is_null($Data)) {
                    if (is_array($Data[0])) {
                        foreach ($Data as $Postes) {
                            $Recomment = $this->getPostsRecommentComment($Postes[FILED_POST_COMMENT_ID]);
                            if (!is_null($Recomment)) {
                                $Postes[KEY_TABLE_POST_COMMENT_RECOMMENT] = $Recomment;
                            }
                        }
                    } else {
                        $Recomment = $this->getPostsRecommentComment($Data[FILED_POST_COMMENT_ID]);
                        if (!is_null($Recomment)) {
                            $Data[KEY_TABLE_POST_COMMENT_RECOMMENT] = $Recomment;
                        }
                    }

                    return $Data;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getPostsRecommentComment($Post_ID) {
        $tmp = [
            FILED_POST_COMMENT_RECOMMENT_CCID => (isset($Post_ID) ? $Post_ID : $GLOBALS[CLASS_FILTER]->FilterData(KEY_POST_COMMENT_RECOMMENT_CCID))
        ];
        $Post = $GLOBALS[CLASS_TOOLS]->removeNull($tmp);
        if (!is_null($Post)) {
            $tmpData = $GLOBALS[CLASS_DATABASE]->select(TABLE_POST_COMMENT_RECOMMENT, $Post, FILED_POST_COMMENT_RECOMMENT_DATE_TIME);
            $Data = $GLOBALS[CLASS_TOOLS]->removeNull($tmpData);
            return $Data;
        } else {
            return null;
        }
    }

}