
<?php

$Dirs = array(
    '/System/system_check.php',
    '/conf/Configration.php',
    '/Comment/Comment.php',
    '/Security/Anti_Attack.php',
    '/System/geoip/geoip.inc',
    '/System/geoip/geolocation.php',
    '/System/geoip/localTime.php',
    '/Zone/Zone.php',
    '/System/Colorise.php',
    '/System/Gift_Scan.php',
    '/Security/Filtering.php',
    '/Database/SQLClass.php',
    '/News/Post.php',
    '/UserManger/Creating_New.php',
    '/UserManger/UserMangment.php',
    '/UserManger/Class_Login.php',
    '/Friend/Friend_Class.php',
    '/Message/Message_Class.php',
    '/System/Core.php',
    '/System/Tools.php',
    '/System/UUID.php',
    '/System/ErrorPage.php',
    '/System/Maltimedia_module.php',
    '/System/Filesystem.php',
    '/Search/Search_Class.php',
    '/Sync/Syncronization.php',
    '/Group/Class_Group.php',
    '/SMS/SMS.php',
    '/qrcode/qrlib.php',
    '/online_radio/onlineradio.php',
    '/System/whois.php'
);

foreach ($Dirs as $include) {
    $FileIncude = MAIN_DIR . $include;
    if (is_file($FileIncude)) {
        include_once $FileIncude;
    }
}

