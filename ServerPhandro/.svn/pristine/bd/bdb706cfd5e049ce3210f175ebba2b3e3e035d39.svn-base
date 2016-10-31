<?php

class Gift_Scan {
    var $isSubDir = false;
    var $i = 1;
    var $index = 1;
    var $FileAllow;

    public function Scan($Dir) {
        $this->FileAllow = FileAllow();
        if ($this->listFolderFiles($Dir)) {
            return true;
        } else {
            return false;
        }
    }

    private function listFolderFiles($dir) {
        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if (!$fileInfo->isDot()) {

                if (in_array($fileInfo->getExtension(), $this->FileAllow[PHOTO])) {
                    $this->Image($fileInfo->getPathname());
                    $this->index++;
                } elseif ($fileInfo->getExtension() == 'php') {
                    continue;
                } else {
                    if (!$this->isSubDir) {
                        $Quiry = " INSERT INTO `Table_Category_Giftes` (`ID`,`_TEXT`, `_FREE`, `_PUBLICE`, `_PRIVATE`) VALUES ( " . $this->i . " ,'" . $fileInfo->getFilename() . "', '1', '1', '0');";
                        if (!$GLOBALS[CLASS_DATABASE]->executeSQL($Quiry)) {
                            $GLOBALS[CLASS_TOOLS]->System_Log("Error MySQL " . $GLOBALS[CLASS_DATABASE]->ReturnError(), __CLASS__ . "::" . __FUNCTION__, __LINE__, Tools::ERROR);
                            return false;
                        }
                    }
                }

                if ($fileInfo->isDir()) {
                    $this->isSubDir = true;
                    $this->listFolderFiles($fileInfo->getPathname());
                }
            }
        }
        $this->i++;
        $this->isSubDir = false;
    }

    private function Image($Path) {
        $IPath = explode(DIRECTORY_SEPARATOR, realpath($Path));
        $filename = basename($Path);
        $SubDir = $IPath[count($IPath) - 2];
        if ($SubDir == DIR_FOR_GIFTES) {
            $SubDir = '';
        }
        $Quirey = "INSERT INTO `Table_Giftes` (`ID` ,`_CATRGORY`, `_SUBDIR`, `_GIFT_PATH`, `_PRICE`, `_LIMITED`,`_SHOW`) VALUES (" . $this->index . " , " . $this->i . " , '{$SubDir}', '{$filename}', '0', 0,0);";
        $GLOBALS[CLASS_DATABASE]->executeSQL($Quirey);
    }

}
