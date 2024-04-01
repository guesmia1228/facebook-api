<?php

use Google\Cloud\Scheduler\V1\AppEngineHttpTarget;
use Google\Cloud\Scheduler\V1\CloudSchedulerClient;
use Google\Cloud\Scheduler\V1\Job;
use Google\Cloud\Scheduler\V1\Job\State;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Protobuf\Timestamp;
use Illuminate\Support\Facades\DB;


$array_methods = [
  'retrieve_all',
  'get_campaigns',
  'get_properties',
  'get_pixels',
  'get_adsets',
  'get_creativities',
  'get_stats_campaigns',
  'get_stats_adSet',
  'get_stats_ad',
  'retrieve_stats_all',
  'get_stats_ad',
];

//index cloud functions
use Psr\Http\Message\ServerRequestInterface;

function executeSql($query)
{
  // global $dbconn;
  // $res = $dbconn->query($query);

  // return $res->fetch_assoc();

  $results = DB::select($query);

  if (!empty($results)) {
    return (array) $results[0];
  }

  return null;
}

function executeSqlAll($query)
{
  // global $dbconn;
  // $res = $dbconn->query($query);

  // return $res->fetch_all(MYSQLI_ASSOC);
  $results = DB::select($query);

  return json_decode(json_encode($results), true);
}

function getUserCredentialsByCampaignPublicId($auth_public_id)
{
  // global $dbconn;

  $query = "SELECT  AUP.id, AUP.auths_user_id, AUP.user_id, AUP.platform, AUP.app_id, AUP.app_secret, AUP.platform_user_id as platform_user_id,
                    AUP.retorno as retorno, AUP.public_id as auth_public_id, AUP.access_token, AUP.refresh_token, U.name as user_name,
                    U.email as user_email, U.customer_id_default, U.campaign_root_default, U.public_id as user_public_id
              FROM auths_user_platform AUP
              JOIN users U ON (AUP.user_id = U.id)
              JOIN campaigns_platform CP ON (CP.auth_id = AUP.id)
              WHERE CP.public_id = '" . $auth_public_id . "';";

  // $result = $dbconn->query($query);
  // $infoCredentials = $result->fetch_assoc();
  $infoCredentials = executeSql($query);

  if (isset($infoCredentials['user_id'])) {
    $user_id = $infoCredentials['user_id'];
  } else {
    echo 'No AUTH ID found';
    $loggermessage = array(
      'level'           =>  'Error',
      'source'          =>  'index',
      'message'         =>  'No AUTH ID found',
      'auth_public_id'  => $auth_public_id,
      'infoCredentials' => json_encode($infoCredentials)
    );

    extLogger($loggermessage);
    die();
  }
  //customizacion para twitter:
  switch ($infoCredentials['platform']) {
    case 'TWITTER':
      $appsecret = json_decode($infoCredentials['app_secret'], true);
      $infoCredentials['app_secret'] = $appsecret['consumer_secret'];
      break;
  }

  return $infoCredentials;
}

function getUserCredentialsByPublicId($auth_public_id)
{
  return getUserCredentials($auth_public_id);
}

function getUserCredentials($auth_public_id)
{
  // global $dbconn;

  $query = "SELECT  AUP.id, AUP.auths_user_id, AUP.user_id, AUP.platform, AUP.app_id, AUP.app_secret, AUP.platform_user_id as platform_user_id,
                    AUP.retorno as retorno, AUP.public_id as auth_public_id, AUP.access_token, AUP.refresh_token, U.name as user_name,
                    U.email as user_email, U.customer_id_default, U.campaign_root_default, U.public_id as user_public_id
              FROM auths_user_platform AUP
              JOIN users U ON (AUP.user_id = U.id)
              WHERE AUP.public_id = '" . $auth_public_id . "';";

  // $result = $dbconn->query($query);
  // $infoCredentials = $result->fetch_assoc();
  $infoCredentials = executeSql($query);

  if (isset($infoCredentials['user_id'])) {
    $user_id = $infoCredentials['user_id'];
  } else {
    echo 'No AUTH ID found';
    $loggermessage = array(
      'level'           =>  'Error',
      'source'          =>  'index',
      'message'         =>  'No AUTH ID found',
      'auth_public_id'  => $auth_public_id,
      'infoCredentials' => json_encode($infoCredentials)
    );

    extLogger($loggermessage);
    die();
  }
  //customizacion para twitter:
  switch ($infoCredentials['platform']) {
    case 'TWITTER':
      $appsecret = json_decode($infoCredentials['app_secret'], true);
      $infoCredentials['app_secret'] = $appsecret['consumer_secret'];
      break;
  }

  return $infoCredentials;
}

function getAllAdsAccounts_by_authPublicId($infoCredentials = [])
{

  $rows = executeSqlAll("SELECT id, name, platform, user_id,app_id,platform_user_id,account_id,account_id as account_platform_id,customer_id, auth_id,
                                currency, status, activa, laststatus, public_id, tier
                            FROM ads_accounts
                            WHERE platform='" . PLATFORM_NAME . "' and user_id ='{$infoCredentials['user_id']}';");
  $retorno = [];
  foreach ($rows as $item) {
    if (isset($item['account_platform_id'])) {
      $retorno[$item['account_platform_id']] = $item;
    }
  }

  if (!empty($retorno)) {
    return $retorno;
  }

  return null;
}

function getAdAccount_ByPublicID($ad_account_publicId, $infoCredentials = [])
{
  $accountdata = executeSql("SELECT id, name, platform, user_id,app_id,platform_user_id,account_id,account_id as account_platform_id,customer_id, auth_id,
                                    currency, status, activa, laststatus, public_id, tier
                                FROM ads_accounts
                                WHERE public_id = '" . $ad_account_publicId . "';");
  if (!empty($accountdata)) {
    if ($accountdata['currency']) {
      $accountdata['currencyEurRate'] = getExchangeRateLast($accountdata['currency']);
    }
    return $accountdata;
  }

  return null;
}

function getAdAccount_Data($platform_id, $infoCredentials = [])
{
  return getAdAccount_DataByPlatformId($platform_id, $infoCredentials);
}

function getAdAccount_DataByPlatformId($platform_id, $infoCredentials = [])
{
  /* retorna este array
  Array
  (
  [id] => 3
  [name] => Diariomotor Medios Digitales SL EURO
  [platform] => FACEBOOK
  [user_id] => 6
  [app_id] => 2230398120518804
  [platform_user_id] => 10211561598937409
  [account_id] => 36894230
  [customer_id] => 32
  [auth_id] => 215
  [currency] => EUR
  [isdefault] => 0
  [metadata] => {"account_id":"36894230","account_status":1,"ad_account_promotable_objects":null,"age":3916.3829282407,"agency_client_declaration":null,"amount_spent":"4855433","attribution_spec":null,"balance":"1937","business":null,"business_city":"Madrid","business_country_code":"ES","business_name":"Diariomotor Medios Digitales SL","business_state":null,"business_street":null,"business_street2":null,"business_zip":null,"capabilities":null,"created_time":null,"currency":"EUR","disable_reason":0,"end_advertiser":null,"end_advertiser_name":null,"extended_credit_invoice_group":null,"failed_delivery_checks":null,"fb_entity":null,"funding_source":null,"funding_source_details":null,"has_migrated_permissions":true,"has_page_authorized_adaccount":null,"id":"act_36894230","io_number":null,"is_attribution_spec_system_default":null,"is_direct_deals_enabled":false,"is_in_3ds_authorization_enabled_market":null,"is_in_middle_of_local_entity_migration":null,"is_notifications_enabled":null,"is_personal":null,"is_prepay_account":null,"is_tax_id_required":null,"line_numbers":null,"media_agency":null,"min_campaign_group_spend_cap":null,"min_daily_budget":null,"name":"Diariomotor Medios Digitales SL EURO","offsite_pixels_tos_accepted":true,"owner":null,"partner":null,"rf_spec":null,"show_checkout_experience":null,"spend_cap":"0","tax_id":null,"tax_id_status":null,"tax_id_type":null,"timezone_id":null,"timezone_name":null,"timezone_offset_hours_utc":null,"tos_accepted":{"web_custom_audience_tos":1},"user_tasks":null,"user_tos_accepted":null,"can_create_brand_lift_study":true}
  [status] => 1|0
  [activa] => Y
  [ncampanas] => 0
  [laststatus] =>
  [creationdate] => 2021-09-02 21:31:40
  [lastupdate] => 2021-09-02 21:31:40
  [public_id] => 2974f8c9-0c35-11ec-8d81-ac1f6b17ff4a
  [tier] => base
  [customers] =>
  )
  **/
  $accountdata = executeSql("SELECT id, name, platform, user_id,app_id,platform_user_id,account_id,account_id as account_platform_id,customer_id,		auth_id, currency, status, activa, laststatus, public_id, tier		FROM ads_accounts where account_id = '" . $platform_id . "';");
  if (!empty($accountdata)) {
    if ($accountdata['currency']) {
      $accountdata['currencyEurRate'] = getExchangeRateLast($accountdata['currency']);
    }
    return $accountdata;
  }
  return null;
}

function getAtomoPublicIdById($atomID)
{
  $row = executeSql("SELECT public_id FROM campaigns_platform_atomo where id = '" . $atomID . "';");
  if (!empty($row)) {
    return $row['public_id'];
  }
  return null;
}

function getCreatividadPublicIdById($creatividadID)
{
  $row = executeSql("SELECT public_id FROM creatividades where id = '" . $creatividadID . "';");
  if (!empty($row)) {
    return $row['public_id'];
  }
  return null;
}

function getUserPublicId($id)
{
  $row = executeSql("SELECT public_id FROM users where id = '" . $id . "';");
  if (!empty($row)) {
    return $row['public_id'];
  }
  return null;
}

function getAuthFrom_authId($id)
{
  return executeSql("SELECT * FROM auths_user_platform where id = '" . $id . "';");
}

function execGoogleTask($tareas, $infoCredentials, $requestReceived = null)
{
  global $DEBUG;

  if (defined('CLOUD_ENABLED')) {
    try {
      $projectId = 'adsconcierge';
      $locationId = 'us-central1';
      $colaTrabajo = 'default';
      if (isset($tareas['multiple'])) {
        $tarea = $tareas['tareas'][0];
      } else {
        $tarea = $tareas;
      }

      $interval = isset($tarea['Execution_interval']) ? substr($tarea['Execution_interval'], 0, 2) : '12H';
      $queueId = defined('CLOUD_COLANAME') ? CLOUD_COLANAME : (isset($tarea['cola_trabajo']) ? $tarea['cola_trabajo'] : 'default');
      $childs = (isset($tarea['callchild']) ? $tarea['callchild'] : (isset($requesreceived['callchild']) ? $requesreceived['callchild'] : []));
      $tarea['type'] = isset($tarea['type']) ?  $tarea['type'] : 'get';
      //https://us-central1-adsconcierge.cloudfunctions.net/function-facebook-api
      $url_cloudfunctions = 'https://' . $locationId . '-' . $projectId . '.cloudfunctions.net/' . $tarea['function'];

      $url_appengine = "https://adsconcierge.uc.r.appspot.com/{$tarea['function']}/{$tarea['type']}/{$tarea['action']}/" . VERSIONCODIGO;
      $url = $url_appengine;

      $auth_id = $infoCredentials['auth_public_id'];
      $user_id = $infoCredentials['user_id'];

      $payload = array(
        "auth_id" => $infoCredentials['auth_public_id'],
        "auth_publicId" => $infoCredentials['auth_public_id'],
        "action" => $tarea['action'],
        "ad_account_publicId" => isset($tarea['ad_account_publicId']) ?  $tarea['ad_account_publicId'] : '',
        "ad_account_platformId" => isset($tarea['ad_account_platformId']) ?  $tarea['ad_account_platformId'] : '',
        "entity_publicId" => isset($tarea['entity_publicId']) ?  $tarea['entity_publicId'] : '',
        "entity_platformId" => isset($tarea['entity_platformId']) ?  $tarea['entity_platformId'] : '',
        "work_entity_name" => isset($tarea['work_entity_name']) ?  $tarea['work_entity_name'] : '',
        "periodEnum" => isset($tarea['periodEnum']) ?  $tarea['periodEnum'] : '',
        "period" => isset($tarea['period']) ?  $tarea['period'] : '',
        "dataFields" => isset($tarea['dataFields']) ?  $tarea['dataFields'] : '',
        "taskId" => isset($tarea['taskId']) ?  $tarea['taskId'] : '',
        "type" => $tarea['type'],
        "callchild" => $childs,
        "random" => isset($tarea['random']) ?  $tarea['random'] : date("dG"),
        "versioncodigo" => VERSIONCODIGO
      );

      // echo 'fn:execGoogleTask '. json_encode($payload).PHP_EOL;

      // Instantiate the client and queue name.
      $client = new CloudTasksClient(['credentials' => __DIR__ . '/configGoogleCloud.json', 'projectId' => $projectId]);
      $queueName = $client->queueName($projectId, $locationId, $queueId);

      // Create an Http Request Object.
      $httpRequest = new HttpRequest();
      $httpRequest->setUrl($url);
      $httpRequest->setHttpMethod(HttpMethod::POST);

      if (isset($payload)) {
        $payload['urirequest'] = $url;
        $httpRequest->setBody(json_encode($payload));
      }
      $task = new Task();
      $task->setHttpRequest($httpRequest);
      if (isset($tarea['delaySeconds'])) {
        $future_timestamp = new Timestamp();
        $future_timestamp->setSeconds(time() +  $tarea['delaySeconds']);
        $future_timestamp->setNanos(0);
        $task->setScheduleTime($future_timestamp);
      }
      $md5payload = md5(serialize($payload));
      //	 	echo 'taxname '. "projects/{$projectId}/locations/{$locationId}/queues/{$queueId}/tasks/{$user_id}_{$auth_id}_{$payload['action']}_{$payload['ad_account_publicId']}{$payload['ad_account_platformId']}_{$md5payload}".VERSIONGTASKS  .PHP_EOL  ;

      $task->setName("projects/{$projectId}/locations/{$locationId}/queues/{$queueId}/tasks/{$user_id}_{$auth_id}_{$payload['action']}_{$payload['ad_account_publicId']}{$payload['ad_account_platformId']}_{$md5payload}" . VERSIONGTASKS  . ENTORNO);

      $response = $client->createTask($queueName, $task);
      echo 'fn:execGoogleTask Tarea creada .. ' . $response->getName() . PHP_EOL;
      debugeo('Queue -> ' . $queueName);
      debugeo('URL -> ' . $url);
      debugeo(json_encode($payload));
      // print_r($response);
      //$this->persistGCloudTask_db($user_id, $auth_id, '', $interval, $payload['action'], json_encode($payload), str_replace("projects/adsconcierge/locations/us-central1/queues/ads-concierge-sync/tasks/", "", $response->getName()));
    } catch (Exception $e) {
      echo 'execGoogleTask Exception ' .  print_r($e->getMessage(), true);
    }
  } else {
    echo 'Task will be created on DEBUG == false' . PHP_EOL;
    echo json_encode($tarea) . PHP_EOL;
  }
}

function get_campaign_by_platformid($platformname, $item_id_en_platform)
{
  $row = executeSql("SELECT id,name, customer_id, ad_account, campana_root, property_id, currency
                        FROM campaigns_platform
                        WHERE platform='" . $platformname . "' and  id_en_platform = '" . $item_id_en_platform . "' limit 1");
  return $row;
}

function get_atomo_by_platformid($platformname, $item_id_en_platform)
{
  $row = executeSql("SELECT id,name, customer_id, ad_account, campana_root_id, property_id, currency, campanaplatform_id
                        FROM campaigns_platform_atomo
                        WHERE platform='" . $platformname . "' and  id_en_platform = '" . $item_id_en_platform . "' limit 1");
  return $row;
}

function get_ad_by_platformid($platformname, $item_id_en_platform)
{
  $row = executeSql("SELECT id,name, customer_id, ad_account, campana_root, property_id, currency, campanaplatform_id,atomo_id
                        FROM creatividades
                        WHERE platform='" . $platformname . "' and  id_en_platform = '" . $item_id_en_platform . "' limit 1");
  return $row;
}

function get_adaccount_from_campaign_platform($campaign_id_en_platform)
{
  $row = executeSql("SELECT *
                        FROM ads_accounts
                        WHERE id in (SELECT ad_account
                                        FROM campaigns_platform
                                        WHERE id_en_platform = '" . $campaign_id_en_platform . "');");
  if (!empty($row)) {
    return $row['account_id'];
  }
  return null;
}

function isJson($string)
{
  json_decode($string);
  return json_last_error() === JSON_ERROR_NONE;
}

function getExchangeRateLast($currencyOUT = null, $currencyIN = 'EUR')
{
  $where = '';
  if ($currencyOUT != null) {
    $where .= "and  a.conversionkey='{$currencyIN}{$currencyOUT}' ";
  } else {
    $where .= "and  a.conversionkey like '{$currencyIN}%' ";
  }

  $row = executeSql("SELECT a.conversionkey, a.day, a.rate
                        FROM currencyconversion a
                        WHERE 1=1 {$where}
                        AND a.day = (SELECT MAX(b.day)
                                        FROM currencyconversion b
                                        WHERE b.conversionkey=a.conversionkey
                                        GROUP BY b.conversionkey ) ");

  if (isset($row['rate'])) {
    return $row['rate'];
  } else {
    return false;
  }
}

function debugeo($valor)
{
  if (defined('VERBOSE') && VERBOSE || isset($_REQUEST['verbose'])) {
    print_r($valor);
  }
}

function unificastatus($entidad, $plataforma, $campoestado, $record)
{
  $active = [];
  $pause = [];
  $stop = [];

  switch ($plataforma) {
    case '1';
    case 'FACEBOOK':
      switch ($entidad) {
        case 'campana':
          if (is_array($record['otros']) && count($record['otros'])) {
            return $campoestado . '-' . json_encode($record['otros']);
          }
          break;
        default:
          return $campoestado;
          break;
      }
      break;
    case '3':
    case 'TWITTER':
      switch ($entidad) {
        case 'campana':
          if (is_array($record['otros']) &&  count($record['otros'])) {
            return $campoestado . '-' . json_encode($record['otros']);
          }
          break;
        default:
          return $campoestado;
          break;
      }
  }
  return $campoestado;
}


function unifyActive($entidad, $plataforma, $campoestado)
{
  $isactive = ['active'];

  switch ($plataforma) {
    case '1';
    case 'FACEBOOK':
      return (in_array(strtolower($campoestado), $isactive) == 1) ? 'Y' : 'N';
      break;
    case '3':
    case 'TWITTER':
      return (in_array(strtolower($campoestado), $isactive) == 1) ? 'Y' : 'N';
      break;
  }
}

function unifyDevices($entidad, $plataforma, $device)
{
  $mobile = ['android devices', 'ios devices', 'mobile_app', 'mobile'];
  $desktop = ['desktop and laptop computers', 'desktop'];


  if (in_array(strtolower($device), $mobile) == 1) {
    return 'MOBILE';
  } else if (in_array(strtolower($device), $desktop) == 1) {
    return 'DESKTOP';
  } else {
    return 'ALL';
  }
}

function unifyPeriod($entidad, $plataforma, $period)
{
  $facebook = ['today', 'yesterday', 'this_week_sun_today', 'this_week_mon_today', 'last_week_sun_sat', 'last_week_mon_sun', 'this_month', 'last_month', 'this_quarter', 'last_3d', 'last_7d', 'last_14d', 'last_28d', 'last_30d', 'last_90d', 'this_year', 'last_year', 'lifetime'];

  switch ($plataforma) {
    case '1';
    case 'FACEBOOK':
      return (in_array(strtolower($period), $facebook) == 1) ? $period : 'today';
      break;
    case '3':
    case 'TWITTER':
      //TODO validar que fechas necesita
      return (in_array(strtolower($period), $facebook) == 1) ? $period : 'today';
      break;
  }
}

function get_user_id()
{
  // global $dbconn;
  // $userid = NULL;

  // if ($resultado = $dbconn->query("SELECT id FROM users WHERE hashuser ='" . $_COOKIE["id_user"] . "'")) {
  //   $userid = $resultado->fetch_object()->id;
  //   $resultado->close();
  // }
  $userid = DB::selectOne(DB::raw("SELECT id FROM users WHERE hashuser = :hashuser"), ['hashuser' => $_COOKIE["id_user"]]);


  return $userid;
}

function dateRanges($startTime, $endTime, $intervalmodel = 'P1D')
{
  if (is_string($startTime)) {
    $startTime = new DateTime($startTime);
  }
  if (is_string($endTime)) {
    $endTime = new DateTime($endTime);
  }
  // $interval = new DateInterval('P7D');
  $interval = new DateInterval($intervalmodel);
  $dateRange = new DatePeriod($startTime, $interval, $endTime);
  $previous = null;
  $dates = array();

  foreach ($dateRange as $dt) {
    $current = $dt;
    if (!empty($previous)) {
      $show = $current;
      $dates[] = array($previous, $show);
    }
    $previous = $current;
  }

  if (isset($dates[count($dates) - 1])) {
    $dates[] = array($dates[count($dates) - 1][1], $endTime);
  } else {
    $dates[] = array($startTime, $endTime);
  }

  return $dates;
}
