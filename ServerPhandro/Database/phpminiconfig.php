<?php
 $ACCESS_PWD=''; #!!!IMPORTANT!!! this is script access password, SET IT if you want to protect you DB from public access
 #DEFAULT db connection settings
 # --- WARNING! --- if you set defaults - it's recommended to set $ACCESS_PWD to protect your db!
 $DBDEF=array(
 'user'=>'root',#required
 'pwd'=>'', #required
 'db'=>'super_wow',  #optional, default DB
 'host'=>'localhost',#optional
 'port'=>'3306',#optional
 'chset'=>'utf8',#optional, default charset
 );