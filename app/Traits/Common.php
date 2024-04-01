<?php

namespace App\Traits;

use Illuminate\Http\Request;
use FacebookAds\Api;

trait Common
{
  public function loginApi($infoCredentials, $appId, $appSecret)
  {
    $api = Api::init($appId, $appSecret, $infoCredentials['access_token'], false);
    $api->setDefaultGraphVersion('14.0');
    return Api::instance();
  }

  public function initialfunc(Request $request)
  {
    if ($request) {
      if (!isset($request['auth_id']) && !isset($request['auth_publicId'])) {
        echo 'No AUTH ID defined in request';
        die();
      }
    }

    // conformamos el request
    $requestParams = array(
      'auth_id' => isset($request['auth_id']) ? $request['auth_id'] : (isset($request['auth_publicId']) ? $request['auth_publicId'] : NULL),
      'auth_publicId' => isset($request['auth_publicId']) ? $request['auth_publicId'] : (isset($request['auth_id']) ? $request['auth_id'] : NULL),
      'ad_account_platformId' => isset($request['ad_account_platformId']) ? $request['ad_account_platformId'] : (isset($request['ad_account_id']) ? $request['ad_account_id'] : NULL),
      'ad_account_publicId' => isset($request['ad_account_publicId']) ? $request['ad_account_publicId'] : false,
      'work_entity_name' => isset($request['work_entity_name']) ? $request['work_entity_name'] : false,
      'entity_platformId' => isset($request['entity_platformId']) ? $request['entity_platformId'] : false,
      'entity_publicId' => isset($request['entity_publicId']) ? $request['entity_publicId'] : false,
      'periodEnum' => isset($request['periodEnum']) ? $request['periodEnum'] : false,
      'period' => isset($request['period']) ? $request['period'] : false,
      'platform_object_id' => isset($request['ad_account_id']) ? $request['ad_account_id'] : null,
      'start_date' => isset($request['start_date']) ? $request['start_date'] : date('Y-m-d', strtotime(' - 7 day')),
      'end_date' => isset($request['end_date']) ? $request['end_date'] : date('Y-m-d', strtotime('today')),
      'callchild' => isset($request['callchild']) ? $request['callchild'] : [],
      'dataFields' => isset($request['dataFields']) ? $request['dataFields'] : [],
      'taskId' => isset($request['taskId']) ? $request['taskId'] : false,
      'newBudget' => isset($request['newBudget']) ? $request['newBudget'] : false,
      'newBid' => isset($request['newBid']) ? $request['newBid'] : false,
    );

    // Rest of the code...

    $loggermessage = array('level' => 'info', 'source' => 'index', 'step' => 'inicio index', 'payload' => serialize($requestParams));
    extLogger($loggermessage);

    $infoCredentials = getUserCredentialsByPublicId($requestParams['auth_publicId']);
    $user_id = $infoCredentials['user_id'];
    $requestParams['user_id'] = $user_id;

    $timeini = microtime(TRUE);
    $this->loginApi($infoCredentials, $infoCredentials['app_id'], $infoCredentials['app_secret']);
    // validamos token
    // try {
    //     // check_token_isalive($infoCredentials);
    // } catch (\Exception $e) {
    //     $loggermessage = array('level' => 'Exception', 'source' => 'GeneralException',  'shortmessage' =>  'token no validado',  'message' =>    $e->getMessage(),  'user_id' => $infoCredentials['user_id'], 'auth_id' => $infoCredentials['id'], 'payload' => serialize($requestParams),  'error' => serialize($e));
    //     extLogger($loggermessage);
    //     echo 'Exeption as ' . $e->getMessage() . PHP_EOL;
    //     exit;
    // }

    if (isset($requestParams['ad_account_platformId'])  &&  $requestParams['ad_account_platformId'] != false) {
      $adAccountData =     getAdAccount_DataByPlatformId($requestParams['ad_account_platformId']);
    }
    if (!isset($adAccountData) &&  $requestParams['ad_account_publicId']) {
      echo 'ad_account_publicId' . PHP_EOL;
      $adAccountData =     getAdAccount_ByPublicID($requestParams['ad_account_publicId']);
    }

    // echo 'ejecuto ' . $requestParams['action'] . PHP_EOL;
    // $loggermessage = array('level' => 'info', 'source' => 'index', 'step' => 'ejecuto ' . $requestParams['action'], 'user_id' => $infoCredentials['user_id']);
    // extLogger($loggermessage);

    return [$requestParams, $infoCredentials, $adAccountData];
  }
}
