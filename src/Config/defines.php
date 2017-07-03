<?php
const FDEF_VERSION = '0.0.0';
const FDEF_CLI     = PHP_SAPI === 'cli';

const FDEF_DEFAULT_CHARSET  = 'utf-8';
const FDEF_DEFAULT_TIMEZONE = 'UTC';

const FDEF_DATA_PCONNECT        = false;
const FDEF_DATA_TRANSACTION     = false;
const FDEF_DATA_WRITEONCE       = false;
const FDEF_DATA_NEWIDPREFIX     = '@insertid';
const FDEF_DATA_CACHEUSE        = true;
const FDEF_DATA_CACHETTL        = 3600;
const FDEF_DATA_CACHEDEPENDENCY = false; // TODO: not yet

const FDEF_PACKET_REQ_KEY     = 'DATA';
const FDEF_PACKET_RES_ERRCODE = 'errorCode';
const FDEF_PACKET_RES_ERRTEXT = 'errorMessage';

const FDEF_NS_SEPARATOR = '\\';
const FDEF_NS_MODULE    = 'Module';
const FDEF_NS_PLUGIN    = 'Plugin';

const FDEF_TYPE_NULL      = 0;
const FDEF_TYPE_BOOL      = 1;
const FDEF_TYPE_INTEGER   = 2;
const FDEF_TYPE_FLOAT     = 3;
const FDEF_TYPE_DOUBLE    = 4;
const FDEF_TYPE_CHAR      = 5;
const FDEF_TYPE_STRING    = 6;
const FDEF_TYPE_DATE      = 7;

const FDEF_EMPTY_DATE     = '0000-00-00';
const FDEF_EMPTY_TIME     = '00:00:00';
const FDEF_EMPTY_DATETIME = FDEF_EMPTY_DATE . ' ' . FDEF_EMPTY_TIME;

const FDEF_ERROR_NONE   =   0;
const FDEF_ERROR_SYSTEM = 100;
const FDEF_ERROR_DB     = 200;
const FDEF_ERROR_CACHE  = 300;
const FDEF_ERROR_APPL   = 400;

define('FDEF_PATH_SRC'  , dirname(__DIR__));
define('FDEF_PATH_LOG'  , dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'log');
define('FDEF_PATH_TMP'  , sys_get_temp_dir() . DIRECTORY_SEPARATOR . FDEF_VERSION);
define('FDEF_THREAD_ID' , uniqid(null, true));
define('FDEF_PREFIX_LOG', '[' . FDEF_VERSION. '][' . FDEF_THREAD_ID . '] ');