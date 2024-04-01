<?php
if (!defined('ENTORNO')) {
  if (defined('STDIN')) {
    define('ENTORNO', 'CLI');
  } else {
    define('ENTORNO', 'CLD');
  }
}
if (!defined('BLOCK_SIZE_CAMPAIGNS')) {
  define('BLOCK_SIZE_CAMPAIGNS', 25);
}
if (!defined('BLOCK_SIZE_ATOMOS')) {
  define('BLOCK_SIZE_ATOMOS', 50);
}
if (!defined('BLOCK_SIZE_ADS')) {
  define('BLOCK_SIZE_ADS', 100);
}
define('PLATFORM_NAME', 'FACEBOOK');
define('PLATFORM_ID', 1);
define('VERSIONCODIGO', 'FB_3.3.' .    (getenv('K_REVISION', true)   ?:  '18')); //max 10 char
define('VERSIONGTASKS', '_FB_3_3_' .    (getenv('K_REVISION', true)   ?:  '18')); //max 10 char
define('VERBOSE', getenv('VERBOSE', true)   ?: FALSE);
define('FUNCTION_API_NAME', 'function-facebook-api'); //https://facebook-api-controller-dot-adsconcierge.uc.r.appspot.com]
define('CLOUD_COLANAME', 'facebook-sync');
define('CLOUD_ENABLED', TRUE);

if (!defined('BLOCK_SIZE_CAMPAIGNS')) {
  define('BLOCK_SIZE_CAMPAIGNS', 25);
}
if (!defined('BLOCK_SIZE_ATOMOS')) {
  define('BLOCK_SIZE_ATOMOS', 50);
}
if (!defined('BLOCK_SIZE_ADS')) {
  define('BLOCK_SIZE_ADS', 100);
}

