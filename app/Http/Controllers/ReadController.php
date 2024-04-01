<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FacebookAds\Api;
use FacebookAds\Cursor;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdFields;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\User;
use Illuminate\Support\Facades\DB;
use App\Traits\Common;

class ReadController extends Controller
{
    use Common;

    /** 
     * @OA\Tag(
     * name="Read-Facebook-API",
     * description="API Endpoints of Read Facebook"
     * )
     */
    public function __construct()
    {
        Cursor::setDefaultUseImplicitFetch(false);
    }

    public function get_adsAccounts($requestParams, $infoCredentials)
    {

        // global $dbconn_stats, $dbconn;
        /*
      print_r($infoCredentials);
      $fields = array('account_status','name','id','age','amount_spent','attribution_spec','account_id',
      'balance','business_name','business_city','business_country_code','currency','owner','partner',
      'user_tos_accepted','spend_cap','tos_accepted','offsite_pixels_tos_accepted','user_tasks','disable_reason',
      'has_migrated_permissions','is_prepay_account','media_agency','can_create_brand_lift_study','is_direct_deals_enabled',
      'is_in_middle_of_local_entity_migration');
    */

        $fields = array(
            'account_status', 'name', 'id', 'age', 'amount_spent', 'attribution_spec', 'account_id',
            'balance', 'business_name', 'business_city', 'business_country_code', 'currency', 'partner',
            'user_tos_accepted', 'spend_cap', 'tos_accepted', 'offsite_pixels_tos_accepted', 'user_tasks',
            'disable_reason', 'has_migrated_permissions', 'media_agency', 'can_create_brand_lift_study',
            'is_direct_deals_enabled', 'is_in_middle_of_local_entity_migration'
        );
        $params = array('summary' => true, 'limit' => 80);

        echo 'Search - Ads Accounts ' . PHP_EOL;

        try {

            $adAccounts = (new User($infoCredentials['platform_user_id']))->getAdAccounts($fields, $params);
            $adAccounts->setDefaultUseImplicitFetch(true);
            $arr = array();

            foreach ($adAccounts as $adAccount) {
                $accountItem = $adAccount->getData();
                //			print_r($accountItem );
                echo 'get_adsAccounts ' . $accountItem['account_id'] . ' --- ' . $accountItem['name'] . ',' . PHP_EOL;
                // array_push($arr, array("id"=>$accountItem['account_id'], "currency"=>$accountItem['currency']));
                //array_push($arr, array("id"=>$accountItem['account_id'], "currency"=>$accountItem['currency']));
                $arr[] = $accountItem['account_id'];
                persistAdAccount($infoCredentials['user_id'], $accountItem, $infoCredentials);
                //break;
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }

        return $arr;
    }

    // Ads

    public function get_account_ads($adAccountData, $infoCredentials)
    {

        try {
            // global $data, $dbconn_stats, $dbconn, $api;
            echo ' - Search get_account_ads -  ' . PHP_EOL;
            $adAccount = $adAccountData['account_platform_id'];
            $itemFields = AdFields::getInstance();
            $fields = $itemFields->getvalues();

            $ad_account = new AdAccount('act_' . $adAccount);
            //AdFields::getInstance()->getvalues()
            //https://developers.facebook.com/docs/marketing-api/reference/adgroup#read-adaccount
            $items = ((new AdAccount('act_' . $adAccount))->getAds(array(
                AdFields::NAME, AdFields::ID, AdFields::ACCOUNT_ID, AdFields::ADSET_ID, AdFields::CAMPAIGN_ID,
                AdFields::EFFECTIVE_STATUS, AdFields::SOURCE_AD, AdFields::CREATIVE, AdFields::CONFIGURED_STATUS,
                AdFields::ISSUES_INFO, AdFields::BID_AMOUNT, AdFields::BID_INFO, AdFields::BID_TYPE
            ), array('summary' => false, 'date_preset' => 'last_30d')));
            $items->setDefaultUseImplicitFetch(true);
            $adAccount_data = getAdAccount_DataByPlatformId($adAccount);

            //  echo ' . AdAccount : ' . $adAccount . ', ';
            //  echo ' - ads : ' . count($items) . PHP_EOL;
            echo ' - pre loop get_account_ads -  ' . PHP_EOL;
            $itemDatabulk = array();
            $i = 0;

            foreach ($items as $citemmm) {
                $citem = $citemmm->getData();
                $itemDatabulk[] = $citem;
                $i++;
                if ($i > BLOCK_SIZE_ADS) {
                    echo ' - pre loop get_account_ads -  ' . PHP_EOL;
                    persistAd($itemDatabulk, $infoCredentials,  $adAccount_data, $infoCredentials['platform']);
                    $itemDatabulk = array();
                    $i = 0;
                }
                //     echo '      -> ads : ' . $citem['name'] . '  act_' . $adAccount . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo 'Exception returned an error: ' . $e->getMessage()  . '--' . $e->getCode();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage() . '--' . $e->getCode();
            exit;
        }

        echo ' - pre loop final get_account_ads -  ' . PHP_EOL;
        persistAd($itemDatabulk, $infoCredentials,  $adAccount_data, $infoCredentials['platform']);
        // Get campaigns, después del loop de inserción en tabla de las campañas, lanzas una sola task get adsets de la cuenta, en lugar de champaña
        // $event = array( 'auth_id' => $infoCredentials['id'], 'type' => 'get_account_adsets', 'function' => 'function-facebook-api',  'subject_id' => $adAccount);
        // execGoogleTask($event);
        //$event = array( 'auth_id' => $infoCredentials['id'], 'type' => 'get_stats_campaigns', 'function' => 'function-facebook-api',  'subject_id' => $adAccount);
        //execGoogleTask($event);

        if (isset($requestParams['dataFields']['firstCharge']) && $requestParams['dataFields']['firstCharge'] == true) {
            execGoogleTask(['delaySeconds' => false, 'random' => rand(), 'action' => 'firstCharge', 'function' => 'function-adsconcierge-api'], $infoCredentials);
        }
    }

    // https://developers.facebook.com/docs/marketing-api/reference/adgroup#Creating

    public function get_publish_post($requestParams, $infoCredentials)
    {
        global $api;

        try {

            $fields = array('id', 'name', 'category', 'tasks');
            $params = array('summary' => true);
            $adAccount = $requestParams['ad_account_platformId'];

            $pageid = $requestParams['dataFields']['pageid'];

            $fb = new \Facebook\Facebook(
                [
                    'app_id' => $infoCredentials['app_id'],
                    'app_secret' => $infoCredentials['app_secret'],
                    'default_graph_version' => 'v14.0',
                ]
            );
            $fb->setDefaultAccessToken($infoCredentials['access_token']);

            $response = $fb->get($pageid . '?fields=access_token');
            $response = $response->getDecodedBody();

            $action = '/published_posts?limit=100&fields=permalink_url,created_time,id,message,attachments{description,media,title,type,url},shares,reactions';

            $published_posts = $fb->get(
                $pageid . $action,
                $response['access_token']
            );

            $published_posts = $published_posts->getDecodedBody();
            $itemData = [];

            do {

                foreach ($published_posts['data'] as $value) {
                    $flag = true;

                    foreach ($requestParams['dataFields']["filters"] as $filtro => $value_filtro) {
                        switch ($filtro) {
                            case 'link':
                                if (strpos(strtolower((string)$value['permalink_url']), strtolower($value_filtro)) === false) {
                                    $flag = false;
                                }
                                break;
                            case 'tags':
                                $flagtag = true;

                                foreach ($value_filtro as $tag) {
                                    if (strpos(strtolower((string)$value['message']), strtolower($tag)) !== false) {
                                        $flagtag = false;
                                        break;
                                    }
                                }

                                if ($flagtag) {
                                    $flag = false;
                                }
                                break;
                            case 'shares':
                                if (!isset($value['shares']) || $value['shares']['count'] < $value_filtro) {
                                    $flag = false;
                                }
                                break;
                        }
                    }

                    if ($flag) {
                        $img = (array)$value->enclosure;

                        $itemData[] = [
                            'adset_id' => $requestParams['dataFields']['atomo_id'],
                            'name' => $value['id'],
                            'url' => $value['permalink_url'],
                            'title' => $value['id'],
                            'content' => isset($value['message']) ? $value['message'] : '',
                            'banner' => (string)$value['attachments']['data'][0]['url'],
                        ];
                    }
                }
                $next = $published_posts['paging']['next'];
                $published_posts = json_decode(file_get_contents($next), true);
            } while (count($published_posts['data']) > 0);


            $infoCredentials = getUserCredentialsByCampaignPublicId($requestParams['dataFields']['campaign_public_id']);
            if (count($itemData) > 0) {
                persistAd($itemData, $infoCredentials, $account = [], $platform = null);
            }
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    // Api por cli
    public function creatividad_update($fb, $data, $infoCredentials)
    {
        echo '** PLATFORM ' . $infoCredentials['platform'] . PHP_EOL;
        print_r($data);

        switch ($infoCredentials['platform']) {
            case 'FACEBOOK':
                $this->fb_update_creatividad_fields($fb, $infoCredentials, $data['parent_platform_id'], $data['fields']);
                break;
        }
    }

    public function creatividad_delete($fb, $data, $infoCredentials)
    {
    }

    public function get_creativities($ad_account, $infoCredentials)
    {
        // get_creativities($data['ad_account_id'], $infoCredentials);

        // global  $dbconn_stats, $dbconn;

        $AdFields = AdFields::getInstance();
        $fields = $AdFields->getvalues();

        try {
            $account = new AdAccount('act_' . $ad_account);
            $ads = $account->getAds($fields);

            // $stmtadset = $dbconn->prepare("INSERT INTO creatividades
            //           ( id_en_platform, user_id, customer_id, name,  platform, campana_root, campana_platform_id,
            //             atomo_id, platform_status, metadata, source )
            //         VALUES  (?,?,?,?,?,?,
            //                 (SELECT id
            //                   FROM campaigns_platform
            //                   WHERE campaigns_platform.id_en_platform = ? AND campaigns_platform.user_id = ? LIMIT 1),
            //                 (SELECT id
            //                   FROM campaigns_platform_atomo
            //                   WHERE id_en_platform = ? AND user_id = ? LIMIT 1),?,?, 'IMPORTED')
            //         ON DUPLICATE KEY UPDATE platform_status=?, metadata=?");

            $stmtadset = DB::connection()->getPdo()->prepare("INSERT INTO creatividades
                        ( id_en_platform, user_id, customer_id, name,  platform, campana_root, campana_platform_id,
                            atomo_id, platform_status, metadata, source )
                        VALUES  (?,?,?,?,?,?,
                                (SELECT id
                                FROM campaigns_platform
                                WHERE campaigns_platform.id_en_platform = ? AND campaigns_platform.user_id = ? LIMIT 1),
                                (SELECT id
                                FROM campaigns_platform_atomo
                                WHERE id_en_platform = ? AND user_id = ? LIMIT 1),?,?, 'IMPORTED')
                        ON DUPLICATE KEY UPDATE platform_status=?, metadata=?");

            echo "Creatividades -> " . count($ads) . PHP_EOL;

            foreach ($ads as $ad) {

                $ad = $ad->getData();
                echo ' Creatividades ' . $ad['name'] . ' id: ' . $ad['id'] . PHP_EOL;

                // $stmtadset->bind_param("sisssssisissss", ...[
                //     $ad['id'],
                //     $infoCredentials['user_id'],
                //     $infoCredentials["customer_id_default"],
                //     $ad['name'],
                //     $infoCredentials['platform'],
                //     $infoCredentials["campaign_root_default"],
                //     $ad['campaign']['id'],
                //     $infoCredentials['user_id'],
                //     $ad['adset_id'],
                //     $infoCredentials['user_id'],
                //     $ad['status'],
                //     json_encode($ad),
                //     $ad['status'],
                //     json_encode($ad)
                // ]);

                $stmtadset->bindParam(1, $ad['id']);
                $stmtadset->bindParam(2, $infoCredentials['user_id']);
                $stmtadset->bindParam(3, $infoCredentials["customer_id_default"]);
                $stmtadset->bindParam(4, $ad['name']);
                $stmtadset->bindParam(5, $infoCredentials['platform']);
                $stmtadset->bindParam(6, $infoCredentials["campaign_root_default"]);
                $stmtadset->bindParam(7, $ad['campaign']['id']);
                $stmtadset->bindParam(8, $infoCredentials['user_id']);
                $stmtadset->bindParam(9, $ad['adset_id']);
                $stmtadset->bindParam(10, $infoCredentials['user_id']);
                $stmtadset->bindParam(11, $ad['status']);
                $stmtadset->bindParam(12, json_encode($ad));
                $stmtadset->bindParam(13, $ad['status']);
                $stmtadset->bindParam(14, json_encode($ad));

                $stmtadset->execute();

                // if ($stmtadset->error != "") {
                //     printf("not inserted - Error: %s.\n", $stmtadset->error);
                // }

                if ($stmtadset->errorInfo()[2] != "") {
                    printf("not inserted - Error: %s.\n", $stmtadset->errorInfo()[2]);
                }
                echo PHP_EOL;
            }
        } catch (\Exception $e) {
            print_r($e);
        }
    }

    public function fb_update_creatividad_fields($fb, $infoCredentials, $id_en_platform, $fields)
    {

        try {
            // endpoint definition
            $endpoint =  "/" . $id_en_platform;
            echo '** Graph version ' . $fb->getDefaultGraphVersion() . PHP_EOL;
            $response = $fb->post($endpoint, $fields, $infoCredentials['access_token'], null, 'v10.0');
            var_dump($response);
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $graphNode = $response->getGraphNode();
        print_r($graphNode);
        // return $graphNode;
        /* handle the result */
    }

    // Atomos

    // https://developers.facebook.com/docs/marketing-api/reference/ad-campaign
    public function get_account_adsets($adAccountData, $infoCredentials)
    {
        // print_r( $adAccountData);
        //  global $data,  $api;
        $adAccount = $adAccountData['account_platform_id'];
        echo ' - Search get_account_adsets -  ' . PHP_EOL;

        $AdSetFields = AdSetFields::getInstance();
        $fields = $AdSetFields->getvalues();
        /*
      Array $fields
      (
      [0] => account_id
      [1] => adlabels
      [2] => adset_schedule
      [3] => asset_feed_id
      [4] => attribution_spec
      [5] => bid_adjustments
      [6] => bid_amount
      [7] => bid_constraints
      [8] => bid_info
      [9] => bid_strategy
      [10] => billing_event
      [11] => budget_remaining
      [12] => campaign
      [13] => campaign_id
      [14] => configured_status
      [15] => created_time
      [16] => creative_sequence
      [17] => daily_budget
      [18] => daily_min_spend_target
      [19] => daily_spend_cap
      [20] => destination_type
      [21] => effective_status
      [22] => end_time
      [23] => frequency_control_specs
      [24] => full_funnel_exploration_mode
      [25] => id
      [26] => instagram_actor_id
      [27] => is_dynamic_creative
      [28] => issues_info
      [29] => learning_stage_info
      [30] => lifetime_budget
      [31] => lifetime_imps
      [32] => lifetime_min_spend_target
      [33] => lifetime_spend_cap
      [34] => multi_optimization_goal_weight
      [35] => name
      [36] => optimization_goal
      [37] => optimization_sub_event
      [38] => pacing_type
      [39] => promoted_object
      [40] => recommendations
      [41] => recurring_budget_semantics
      [42] => review_feedback
      [43] => rf_prediction_id
      [44] => source_adset
      [45] => source_adset_id
      [46] => start_time
      [47] => status
      [48] => targeting
      [49] => time_based_ad_rotation_id_blocks
      [50] => time_based_ad_rotation_intervals
      [51] => updated_time
      [52] => use_new_app_click
      [53] => campaign_spec
      [54] => daily_imps
      [55] => date_format
      [56] => execution_options
      [57] => line_number
      [58] => rb_prediction_id
      [59] => time_start
      [60] => time_stop
      [61] => topline_id
      [62] => tune_for_category
      [63] => upstream_events
      )
    **/

        unset($fields[24]);
        unset($fields[54]);
        unset($fields[57]);
        unset($fields[61]);

        $ad_account = new AdAccount('act_' . $adAccount);
        //CampaignFields::getInstance()->getvalues()
        $items = ((new AdAccount('act_' . $adAccount))->getAdSets($fields,  array('summary' => false, 'date_preset' => 'last_30d')));
        $items->setDefaultUseImplicitFetch(true);

        $adAccount_data = getAdAccount_DataByPlatformId($adAccount);
        /// echo ' . AdAccount : ' . $adAccount . ', ';
        echo ' -get  adsets : ' . count($items) . PHP_EOL;

        $itemDatabulk = array();
        $i = 0;

        foreach ($items as $citemmm) {
            $citem = $citemmm->getData();
            $itemDatabulk[] = $citem;
            //       echo ' - adsets : ' .$citem['id'] .' --- '. $citem['name']. PHP_EOL;
            $i++;
            if ($i > BLOCK_SIZE_ATOMOS) {
                echo ' - pre loop get_account_adsets -  ' . PHP_EOL;
                persistAdset($itemDatabulk, $infoCredentials,  $adAccount_data,   $infoCredentials['platform']);
                $itemDatabulk = array();
                $i = 0;
            }
        }

        echo ' -prepersits  adsets : ' . $i . PHP_EOL;
        persistAdset($itemDatabulk, $infoCredentials,  $adAccount_data,   $infoCredentials['platform']);
        // Get campaigns, después del loop de inserción en tabla de las campañas, lanzas una sola task get adsets de la cuenta, en lugar de champaña
        // $event = array( 'auth_id' => $infoCredentials['id'], 'type' => 'get_account_adsets', 'function' => 'function-facebook-api',  'subject_id' => $adAccount);
        // execGoogleTask($event);

        //$event = array( 'auth_id' => $infoCredentials['id'], 'type' => 'get_stats_campaigns', 'function' => 'function-facebook-api',  'subject_id' => $adAccount);
        //execGoogleTask($event);

    }

    public function campaignplatform_atomo_create($fb, $data, $infoCredentials)
    {
    }

    // Campagins

    public function get_account_campaigns($adAccountData, $infoCredentials)
    {
        // get_campaigns($data['ad_account_id'], $infoCredentials);
        // print_r( $adAccountData);
        // global $data,  $api;
        $adAccount = $adAccountData['account_platform_id'];

        echo ' - Search Campaigns -  ' . PHP_EOL;

        $items = ((new AdAccount('act_' . $adAccount))->getCampaigns(CampaignFields::getInstance()->getvalues(),  array('summary' => false, 'date_preset' => 'last_30d')));
        $items->setDefaultUseImplicitFetch(true);

        $adAccount_data = getAdAccount_DataByPlatformId($adAccount);

        // echo ' . AdAccount : ' . $adAccount . ', ';
        echo ' - get Campaigns : ' . count($items) . PHP_EOL;

        $itemDatabulk = array();
        $i = 0;
        foreach ($items as $citemmm) {
            $citem = $citemmm->getData();
            $itemDatabulk[] = $citem;
            $i++;
            //  echo '      -> Campaign: ' . $citem['name'] . '  act_' . $adAccount . PHP_EOL;

            if ($i > BLOCK_SIZE_CAMPAIGNS) {
                echo ' - pre loop get_Campaigns -  ' . PHP_EOL;
                persistCampaign($itemDatabulk, $infoCredentials,  $adAccount_data,   $infoCredentials['platform']);
                $itemDatabulk = array();
                $i = 0;
            }
        }
        echo 'pre persist ' . $i . PHP_EOL;
        persistCampaign($itemDatabulk, $infoCredentials,  $adAccount_data,   $infoCredentials['platform']);
        // Get campaigns, después del loop de inserción en tabla de las campañas, lanzas una sola task get adsets de la cuenta, en lugar de champaña
        //  $event = array( 'auth_id' => $infoCredentials['id'], 'type' => 'get_account_adsets', 'function' => 'function-facebook-api',  'subject_id' => $adAccount);
        // execGoogleTask($event);
        return true;
        //$event = array( 'auth_id' => $infoCredentials['id'], 'type' => 'get_stats_campaigns', 'function' => 'function-facebook-api',  'subject_id' => $adAccount);
        //execGoogleTask($event);

    }

    // Custom

    public function retrieve_all($requestParams, $infoCredentials)
    {
        echo 'Retrieve all entities for ' . $infoCredentials['user_name'] . ' ' . $infoCredentials['user_email'] . PHP_EOL;

        $accountsArray = $this->get_adsAccounts($requestParams, $infoCredentials);
        $random = rand();
        $numItems = count($accountsArray);
        $i = 0;
        foreach ($accountsArray as $adAccount) {
            // print_r( $adAccount);
            //	get_account_pixels($adAccount['id'], $infoCredentials);
            //	get_account_campaigns($adAccount['id'], $infoCredentials);
            //		get_account_adsets($adAccount['id'], $infoCredentials);
            //		get_account_ads($adAccount['id'], $infoCredentials);
            /***
      // buscamos properties
      $event = array(  'auth_id' => $infoCredentials['id'], 'type' => 'get_properties','action' => 'get_account_properties', 'function' => 'function-facebook-api', 'subject_id' =>  $adAccount['id'], 'callchild'=> [ array('type' => 'get_campaigns')     ] );
      execGoogleTask($event, $data);
      $event = array(  'auth_id' => $infoCredentials['id'], 'type' => 'get_properties','action' => 'get_account_pixels', 'function' => 'function-facebook-api', 'subject_id' =>  $adAccount['id'], 'callchild'=> [ ] );
  
      execGoogleTask($event, $data);
             ***/
            //delay de 10 minutos para que de tiempo a traerse las properties, los tiempos son arbitrarios y estimados en cuanto puede llevar ejecutarse la funcion
            //no se hace encadenado por si falla la tarea no entrar en un bucle infinito
            //echo 'get campa'.PHP_EOL;  'entity_platformId' => $adAccount

            //$event = array('delaySeconds'=>false, 'random'=>$random, 'action' => 'get_account_campaigns','entity_platformId' => $adAccount,'ad_account_platformId' => $adAccount, 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name'=> 'ad_account'  );
            $event = array('delaySeconds' => false, 'random' => $random, 'action' => 'get_account_entities', 'entity_platformId' => $adAccount, 'ad_account_platformId' => $adAccount, 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
            if (++$i === $numItems) {
                $event['dataFields']['firstevent'] = true;
            }
            execGoogleTask($event, $infoCredentials, $requestParams);
            /*
        $eventos[]= $event;
        //echo 'get ADSET '.PHP_EOL;
        //delay de 30 minutos para que de tiempo a traerse las campaigns
        //     $event = array('delaySeconds'=>1800,  'auth_id' => $infoCredentials['id'], 'type' => 'get_account_adsets',  'action' => 'get_account_adsets', 'function' => FUNCTION_API_NAME, 'entity_name'=> 'ad_account'  ,   'entity_platformId' =>  $adAccount );
        $event = array('delaySeconds'=>1800, 'random'=>$random, 'action' => 'get_account_adsets','entity_platformId' => $adAccount,'ad_account_platformId' => $adAccount, 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name'=> 'ad_account'  );
        $eventos[]= $event;
        execGoogleTask($event,$infoCredentials,$requestParams);
        // echo 'get ADS '.PHP_EOL;
        //delay de 90 minutos para que de tiempo a traerse las get_account_adsets
        // $event = array('delaySeconds'=>5400,  'auth_id' => $infoCredentials['id'], 'type' => 'get_account_ads',  'action' => 'get_account_ads' , 'function' => FUNCTION_API_NAME,  'entity_name'=> 'ad_account'  ,  'entity_platformId' => $adAccount );
        $event = array('delaySeconds'=>1800, 'random'=>$random, 'action' => 'get_account_ads','entity_platformId' => $adAccount,'ad_account_platformId' => $adAccount, 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name'=> 'ad_account'  );
        $eventos[]= $event;
        execGoogleTask($event,$infoCredentials,$requestParams);
      **/
            //to-do		execGoogleTask(array('multiple'=>true, 'tareas'=> $eventos) ,$infoCredentials,$requestParams);

            //aqui va una tarea a las 3 horas para ejecutar los update de id's internos  (campana_root, customer_id, campanaplatform_id etc) en las tablas por si no dio tiempo a que exisitieran cuando se importaron
        }
    }

    public function retrieve_all_adsAccounts_stats($requestParams, $period = 'last_3d', $infoCredentials = [])
    {

        $adAccounts = getAllAdsAccounts_by_authPublicId($infoCredentials);
        $delay = 0;
        //    get_stats_campaigns($accountsArray, $data, $infoCredentials);
        $random = rand();
        foreach ($adAccounts as $adAccountData) {
            //solo llamamos a stats por ads, y de ahí consolidamos en adsets y campanas, asi ahorramos api calls
            //   $event = array('delaySeconds'=> $delay+=30 ,  'auth_id' => $infoCredentials['id'], 'type' => 'get_account_stats_ads',  'action' => 'get_account_stats_ads' , 'function' => FUNCTION_API_NAME,  'entity_name'=> 'ad_account'  ,  'public_id' => $adAccountData['account_platform_id'] ,  'ad_account_publicId' => $adAccountData['account_platform_id']);
            $event = array('action' => 'get_account_stats_ads', 'ad_account_publicId' => $adAccountData['public_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'periodEnum' => $period,  'period' => $period, 'entity_publicId' => $adAccountData['public_id'],  'entity_platformId' =>  $adAccountData['account_platform_id'],  'type' => 'get_account_stats', 'function' => FUNCTION_API_NAME, 'work_entity_name' => 'ad_account');
            execGoogleTask($event, $infoCredentials, $requestParams);
            //exit;
        }
    }


    public function refresh_token($infoCredentials)
    {
    }

    //chequear token vive
    //https://developers.facebook.com/docs/facebook-login/access-tokens/session-info-access-token
    public function check_token_isalive($infoCredentials)
    {
        /*
      experimental
      $me = new AdUser('me');
      print_r($me);
    **/
        try {
            $fb = new \Facebook\Facebook(['app_id' => $infoCredentials['app_id'], 'app_secret' => $infoCredentials['app_secret'], 'default_graph_version' => 'v2.10',]);
            $response = $fb->get('/' . $infoCredentials["platform_user_id"] . '/permissions', $infoCredentials["access_token"]);
            print_r($response);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {

            // $loggermessage = array('level' => 'Exception', 'source' => 'FacebookResponseException', 'message' => 'EXPIRADO' .  $e->getMessage(),  'user_id' => $infoCredentials['user_id'], 'auth_id' => $infoCredentials['id'], 'payload' => serialize($requestParams),  'error' => serialize($e));
            // extLogger($loggermessage);

            echo 'Graph returned an error: ' . $e->getMessage() . PHP_EOL;
            echo 'EXPIRADO' . PHP_EOL;
            // $query = "UPDATE auths_user_platform set auths_user_platform.activa = 'EXPIRATE' WHERE auths_user_platform.public_id = '" . $dataRequest['auth_id'] . "'";
            // $dbconn->query($query);


            $query = "UPDATE auths_user_platform SET activa = 'EXPIRATE' WHERE public_id = :public_id";
            // DB::statement(DB::raw($query), ['public_id' => $dataRequest['auth_id']]);

            echo 'EVENT MAIL NOTIFY USER TOKEN IS EXPIRATED' . PHP_EOL;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            // $loggermessage = array('level' => 'Exception', 'source' => 'FacebookResponseException', 'message' =>   $e->getMessage(),  'user_id' => $infoCredentials['user_id'], 'auth_id' => $infoCredentials['id'], 'payload' => serialize($requestParams),  'error' => serialize($e));
            // extLogger($loggermessage);
        }

        if (isset($response)) {
            //   $me = $response->getGraphUser();
            //   echo 'Logged in as ' . $me->getName() . PHP_EOL;
            return true;
        } else {
            return false;
        }
    }

    // Get Properties

    /// TODO TO-DO
    // CAMBIAR LA QUERY DE INSERT, QUITANDO EL SELECT GLOBAL Y METIENDO LOS INSERT EN LAS COLUMNAS, REPLICAR DE TWITTER

    public function get_account_properties($AdAccountObj, $infoCredentials)
    {
        //get_properties($data['ad_account_id'], $infoCredentials);
        // global  $dbconn;

        $fields = array('id', 'name', 'category', 'tasks');
        $params = array('summary' => true);
        $account = $AdAccountObj['account_platform_id'];
        echo 'Search Properties ' . PHP_EOL;

        //$adAccount = new AdAccount('act_' . $account );
        //$pages=$adAccount->getPromotePages($fields,  $params);
        //print_r( $infoCredentials );
        //print_r( $AdAccountObj );
        //$id='10211561598937409';

        $pages = (new User($AdAccountObj['platform_user_id']))->getAccounts($fields,  $params);
        $pages->setDefaultUseImplicitFetch(true);
        //print_r($pages->getData() );
        echo ' Properties from Addcount ' . ' act_' . $account . PHP_EOL;
        //print_r($pages['data']);
        try {
            // foreach ($pages as $p) {
            //     //	print_r($p);
            //     $page = $p->getData();
            //     //	print_r($page);
            //     $page_id = $page['id'];
            //     $pagename = $page['name'];
            //     $category = $page['category'];
            //     $token = $infoCredentials['access_token'];

            //     $status = 0;
            //     $metadata = json_encode($page, true);
            //     $stmt2 = $dbconn->prepare("INSERT INTO `app_thesoci_9c37`.`properties_accounts` (`user_id`, `platform`, `app_id`,
            //                               `platform_user_id`, `id_en_platform`, `token`,`name`,`status`,`category`,
            //                               `metadata`, `adaccount_id`, `auth_id`)
            //                           VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
            //                           ON DUPLICATE KEY UPDATE `name`=?, `status`=?, `platform_user_id`=?, `metadata`=?");
            //     $stmt2->bind_param("ssssssssssssssss", ...[
            //         $infoCredentials["user_id"], $infoCredentials["platform"],
            //         $infoCredentials["app_id"], $infoCredentials['platform_user_id'], $page_id, $token,
            //         $pagename, $status, $category, $metadata, $account, $infoCredentials["id"],
            //         $pagename, $status, $infoCredentials['platform_user_id'], $metadata
            //     ]);
            //     $stmt2->execute();

            //     echo '   Property added -> ' . $page['name'] . PHP_EOL;

            //     $query = "SELECT id as pageid, public_id as property_public_id
            //         FROM `app_thesoci_9c37`.`properties_accounts`
            //         WHERE  auth_id = '{$infoCredentials["id"]}'
            //               AND platform = '{$infoCredentials["platform"]}'
            //               AND platform_user_id= '{$infoCredentials['platform_user_id']}'
            //               AND id_en_platform = '{$page_id}'";

            //     $res_pa = $dbconn->query($query);
            //     $id_pa = $res_pa->fetch_assoc();

            //     $stmt3 = $dbconn->prepare("INSERT INTO `app_thesoci_9c37`.`properties_adsaccount_relations`
            //                             ( `type`,`auth_id`, `ad_account`, `ad_account_name`,`property_name`,
            //                               `property_id`, `user_id`, `platform`,`auth_publicid`, `user_publicid`,
            //                               `ad_account_publicid`, `property_publicid` )
            //                           VALUES (
            //                             'PAGE',?,?,
            //                             (SELECT name
            //                               FROM app_thesoci_9c37.ads_accounts
            //                               WHERE ads_accounts.auth_id = ? AND account_id = ?),
            //                             ?,?,?,?,?,?,
            //                             (SELECT public_id
            //                               FROM app_thesoci_9c37.ads_accounts
            //                               WHERE account_id = ? AND ads_accounts.auth_id = ?),
            //                             ?)
            //                           ON DUPLICATE KEY UPDATE
            //                             `ad_account_name` = (SELECT name
            //                                                   FROM app_thesoci_9c37.ads_accounts
            //                                                   WHERE ads_accounts.auth_id = ? AND account_id = ?),
            //                             `property_name` = ?");

            //     $adc_publicid = getAdAccount_DataByPlatformId($account);

            //     //echo "SELECT name FROM app_thesoci_9c37.ads_accounts where ads_accounts.auth_id = ".$infoCredentials["id"]." and account_id = ".$account.PHP_EOL;

            //     $stmt3->bind_param("ssssssssssssssss", ...[
            //         $infoCredentials["id"], $account, $infoCredentials["id"],
            //         $account, $pagename, $id_pa["pageid"], $infoCredentials["user_id"], $infoCredentials["platform"],
            //         $infoCredentials["auths_user_id"], getUserPublicId($infoCredentials["user_id"]), $account,
            //         $infoCredentials["id"], $id_pa["property_public_id"], $account, $infoCredentials["id"], $pagename
            //     ]);

            //     $stmt3->execute();
            //     if ($stmt3->error != "") {
            //         extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt3->error, 'infoCredentials' => json_encode($infoCredentials)));
            //     }
            // }
            foreach ($pages as $p) {
                $page = $p->getData();
                $page_id = $page['id'];
                $pagename = $page['name'];
                $category = $page['category'];
                $token = $infoCredentials['access_token'];
                $status = 0;
                $metadata = json_encode($page, true);

                $stmt2 = DB::connection()->getPdo()->prepare("INSERT INTO `properties_accounts` (`user_id`, `platform`, `app_id`,
                                      `platform_user_id`, `id_en_platform`, `token`,`name`,`status`,`category`,
                                      `metadata`, `adaccount_id`, `auth_id`)
                                  VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                                  ON DUPLICATE KEY UPDATE `name`=?, `status`=?, `platform_user_id`=?, `metadata`=?");
                $stmt2->bindParam(1, $infoCredentials["user_id"]);
                $stmt2->bindParam(2, $infoCredentials["platform"]);
                $stmt2->bindParam(3, $infoCredentials["app_id"]);
                $stmt2->bindParam(4, $infoCredentials['platform_user_id']);
                $stmt2->bindParam(5, $page_id);
                $stmt2->bindParam(6, $token);
                $stmt2->bindParam(7, $pagename);
                $stmt2->bindParam(8, $status);
                $stmt2->bindParam(9, $category);
                $stmt2->bindParam(10, $metadata);
                $stmt2->bindParam(11, $account);
                $stmt2->bindParam(12, $infoCredentials["id"]);
                $stmt2->bindParam(13, $pagename);
                $stmt2->bindParam(14, $status);
                $stmt2->bindParam(15, $infoCredentials['platform_user_id']);
                $stmt2->bindParam(16, $metadata);

                $stmt2->execute();

                echo '   Property added -> ' . $page['name'] . PHP_EOL;

                $query = "SELECT id as pageid, public_id as property_public_id
                        FROM `properties_accounts`
                        WHERE  auth_id = '{$infoCredentials["id"]}'
                                AND platform = '{$infoCredentials["platform"]}'
                                AND platform_user_id= '{$infoCredentials['platform_user_id']}'
                                AND id_en_platform = '{$page_id}'";
                $id_pa = DB::selectOne($query);

                $stmt3 = DB::connection()->getPdo()->prepare("INSERT INTO `properties_adsaccount_relations`
                                    ( `type`,`auth_id`, `ad_account`, `ad_account_name`,`property_name`,
                                      `property_id`, `user_id`, `platform`,`auth_publicid`, `user_publicid`,
                                      `ad_account_publicid`, `property_publicid` )
                                  VALUES (
                                    'PAGE',?,?,
                                    (SELECT name
                                      FROM ads_accounts
                                      WHERE ads_accounts.auth_id = ? AND account_id = ?),
                                    ?,?,?,?,?,?,
                                    (SELECT public_id
                                      FROM ads_accounts
                                      WHERE account_id = ? AND ads_accounts.auth_id = ?),
                                    ?)
                                  ON DUPLICATE KEY UPDATE
                                    `ad_account_name` = (SELECT name
                                                          FROM ads_accounts
                                                          WHERE ads_accounts.auth_id = ? AND account_id = ?),
                                    `property_name` = ?");

                $adc_publicid = getAdAccount_DataByPlatformId($account);

                $stmt3->bindParam(1, $infoCredentials["id"]);
                $stmt3->bindParam(2, $account);
                $stmt3->bindParam(3, $infoCredentials["id"]);
                $stmt3->bindParam(4, $account);
                $stmt3->bindParam(5, $pagename);
                $stmt3->bindParam(6, $id_pa->pageid);
                $stmt3->bindParam(7, $infoCredentials["user_id"]);
                $stmt3->bindParam(8, $infoCredentials["platform"]);
                $stmt3->bindParam(9, $infoCredentials["auths_user_id"]);
                $stmt3->bindParam(10, getUserPublicId($infoCredentials["user_id"]));
                $stmt3->bindParam(11, $account);
                $stmt3->bindParam(12, $infoCredentials["id"]);
                $stmt3->bindParam(13, $id_pa->property_public_id);
                $stmt3->bindParam(14, $account);
                $stmt3->bindParam(15, $infoCredentials["id"]);
                $stmt3->bindParam(16, $pagename);

                $stmt3->execute();

                if ($stmt3->errorInfo()[2] != "") {
                    extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt3->errorInfo()[2], 'infoCredentials' => json_encode($infoCredentials)));
                }
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            extLogger(array('level' => 'Exception', 'category' => 'apiError',  'message' => $e->getMessage(), 'infoCredentials' => json_encode($infoCredentials)));
        }
    }

    public function get_account_pixels($ad_account, $infoCredentials)
    {
        // global  $dbconn_stats, $dbconn;

        echo 'SEARCH Pixels ' . PHP_EOL;

        $fields = array(
            'id', 'name', 'is_unavailable', 'code', 'owner_ad_account',
            'account_id', 'first_party_cookie_status', 'automatic_matching_fields'
        );

        $adAccount = new AdAccount('act_' . $ad_account);
        //$adAccountData = $adAccount->getData();
        $adPixels = $adAccount->getAdsPixels($fields, array('summary' => true, 'limit' => 80));

        foreach ($adPixels as $adPixel) {
            $pixel = $adPixel->getData();
            //var_dump($pixel);
            //die();

            $itemData = array_intersect_key($adPixel->getData(), array_flip($fields));
            //$adPixelData = array_merge($adPixelData, $this->userPlatformData);
            $itemData['ad_account_platformId'] = $pixel['owner_ad_account']['account_id'];
            $itemData['id_en_platform'] = $adPixel->getData()['id'];
            $itemData['id'] = $adPixel->getData()['id'];
            $itemData['name'] = $adPixel->getData()['name'];
            /*  $adPixelData['status'] = 0; //todo: fixed in origin code
        $adPixelData['category'] = '-';
        $adPixelData['token'] = '-';
        $adPixelData['type'] = 'PIXEL';
      **/
            //$this->persistPropertiesAccounts->execute($adPixelData);
            //print_r ( $itemData );

            persistPixel($itemData, $infoCredentials, $pixel['owner_ad_account']['account_id']);

            // buscamos y persistimos la relacion de las ads con la properties_accounts
            // die();
        }
    }

    // Get Stats

    function get_stats_global($parentobject, $infoCredentials, $params,  $parentevent = '')
    {

        $fields = ['account_id', 'impressions', 'clicks', 'campaign_id', 'campaign_name', 'account_currency', 'buying_type', 'date_start', 'objective', 'reach', 'spend', 'inline_post_engagement', 'cost_per_action_type', 'cpc', 'cpm', 'website_ctr'];
        switch ($parentevent) {
            case 'get_stats_campaigns':
            case 'get_account_stats_campaigns':
                $fields = ['account_id', 'impressions', 'clicks', 'campaign_id', 'campaign_name', 'account_currency', 'buying_type', 'date_start', 'objective', 'reach', 'spend', 'inline_post_engagement', 'cost_per_action_type', 'cpc', 'cpm', 'website_ctr'];
                break;
            case 'get_stats_ad':
                //$fields = ['ad_id, adset_id, impressions', 'campaign_id', 'account_id', 'account_currency', 'buying_type', 'campaign_name', 'clicks', 'conversions', 'date_start', 'date_stop', 'objective', 'reach', 'spend', 'inline_post_engagement', 'actions', 'wish_bid', 'ad_bid_type', 'social_spend', 'video_thruplay_watched_actions', 'video_play_actions', 'ad_bid_value'];
                $fields[] = 'ad_id';
                $fields[] = 'ad_name';
                $fields[] = 'adset_id';
                $fields[] = 'campaign_id';
                break;
            case 'get_stats_adSet':
                $fields[] = 'adset_name';
                $fields[] = 'adset_id';
                // ['impressions', 'campaign_id', 'account_id', 'account_currency', 'clicks', 'conversions', 'date_start', 'date_stop', 'objective', 'reach', 'spend',  'ad_bid_type', 'video_play_actions', 'ad_bid_value', 'adset_name', 'adset_id'];
                break;
        }

        $api = Api::init($infoCredentials['app_id'], $infoCredentials['app_secret'], $infoCredentials['access_token']);
        try {
            $datos = ($parentobject)->getInsights($fields, $params);
            $datos->setDefaultUseImplicitFetch(true);
            $retorno = [];

            foreach ($datos as $citem) {
                $retorno[] = $citem->getData();
            }
            return $retorno;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $loggermessage = array('level' => 'Exception', 'category' => 'FbapiError', 'message' => $e->getMessage(), 'params' => json_encode($params),  'infoCredentials' => json_encode($infoCredentials));
            extLogger($loggermessage);
        }
    }

    // addaccount/campaign stats
    //https://developers.facebook.com/docs/marketing-api/insights/parameters/v11.0
    // date_preset enum{today, yesterday, this_month, last_month, this_quarter, maximum, last_3d, last_7d, last_14d, last_28d, last_30d, last_90d, last_week_mon_sun, last_week_sun_sat, last_quarter, last_year, this_week_mon_today, this_week_sun_today, this_year}
    function get_account_stats_campaigns($AdAccountObj, $data, $period, $infoCredentials)
    {
        if (!$period) $period = 'last_3d';

        $parentobject =  new AdAccount("act_" .  $AdAccountObj['account_platform_id']);
        //$AdAccountObj = getAdAccount_DataByPlatformId($adAccount);
        $period = unifyPeriod('campaign', $infoCredentials['platform'], $period);
        $params = [
            'level' => 'campaign',
            'date_preset' => $period,
            'time_increment' => '1',
            'breakdowns' => 'publisher_platform,platform_position,device_platform'
        ];

        $message = 'Stats campaigns ad_account: ' . $data['ad_account_id'] . ' from ' . $data['start_date'] . ' to ' . $data['end_date'] . PHP_EOL;
        // $error_message = ['add_acount' => $adAccount];
        $bulkdata = $this->get_stats_global($parentobject, $infoCredentials, $params,  'get_stats_campaigns');

        echo PHP_EOL . PHP_EOL . ' Qty items - FB API' . count($bulkdata) . PHP_EOL;
        persistStats_platform_Campana_day($infoCredentials, $infoCredentials['platform'], $AdAccountObj, $bulkdata);
    }

    function get_account_stats_adSets($AdAccountObj, $data, $period, $infoCredentials)
    {
        if (!$period) $period = 'last_3d';

        $parentobject =  new AdAccount("act_" . $AdAccountObj['account_platform_id']);
        //   $AdAccountObj = getAdAccount_DataByPlatformId($adAccount);

        $period = unifyPeriod('adset', $infoCredentials['platform'], $period);

        $params = [
            'level' => 'adset',
            'date_preset' => $period,
            'time_increment' => '1',
            'breakdowns' => 'publisher_platform,platform_position,device_platform'
        ];

        $message = 'Stats adset ad_account: ' . $data['ad_account_id'] . ' from ' . $data['start_date'] . ' to ' . $data['end_date'] . PHP_EOL;
        // $error_message = ['add_acount' => $adAccount];
        $bulkdata = $this->get_stats_global($parentobject, $infoCredentials, $params,  'get_stats_adSet');

        echo PHP_EOL . PHP_EOL . 'get_account_stats_adSet  Qty items - FB API' . count($bulkdata) . PHP_EOL;
        persistStats_platform_Atomo_day($infoCredentials, $infoCredentials['platform'], $AdAccountObj, $bulkdata);
    }

    function get_account_stats_ads($AdAccountObj, $data, $period, $infoCredentials)
    {
        if (!$period) $period = 'last_3d';

        //  print_r($AdAccountObj );

        $parentobject =  new AdAccount("act_" . $AdAccountObj['account_platform_id']);
        // $AdAccountObj = getAdAccount_DataByPlatformId($adAccount);
        $period = unifyPeriod('ad', $infoCredentials['platform'], $period);

        $params = [
            'level' => 'ad',
            'date_preset' => $period,
            'time_increment' => '1',
            'breakdowns' => 'publisher_platform,platform_position,device_platform'
        ];
        //  'time_range' => array('since' => substr($data['start_date'], 0, 10), 'until' => substr($data['end_date'], 0, 10)),
        $message = 'Stats adset ad_account: ' . $data['ad_account_id'] . ' from ' . $data['start_date'] . ' to ' . $data['end_date'] . PHP_EOL;
        // $error_message = ['add_acount' => $adAccount];
        //    print_r($params );
        // exit;
        $bulkdata = $this->get_stats_global($parentobject, $infoCredentials, $params,  'get_stats_ad');

        echo PHP_EOL . PHP_EOL . 'get_account_stats_ads  Qty items - FB API' . count($bulkdata) . PHP_EOL;
        //print_r($bulkdata[0] );
        persistStats_platform_Ads_day($infoCredentials, $infoCredentials['platform'], $AdAccountObj, $bulkdata);
    }

    /**
     * @OA\Post(
     * path="/api",
     * summary="Post functions",
     * description="Actions for retrieve_all ,get_account_adsaccounts ,get_account_properties ,get_properties ,get_account_pixels ,get_pixels ,get_account_entities ,get_account_campaigns ,get_campaigns ,get_adsets ,get_account_adsets ,get_account_ads ,get_creativities ,get_stats_campaigns ,get_account_stats_campaigns ,get_stats_adSet ,get_account_stats_adsets ,get_stats_ad ,get_account_stats_ads ,retrieve_stats_all ,retrieve_all_accounts_stats ,entity_update_geo ,entity_update_language ,entity_update_interests ,entity_update_gender ,entity_update_audience ,campaign_update_fields ,campaign_update_budget ,campaign_status_to_active ,campaign_status_to_pause ,campaign_status_to_stop ,campaign_status_to_archive ,campaign_delete ,campaign_create ,atomo_update_fields ,atomo_update_budget ,atomo_status_to_active ,atomo_status_to_pause ,atomo_status_to_stop ,atomo_status_to_archive ,atomo_delete ,atomo_create ,ad_update_fields ,ad_update_budget ,ad_status_to_active ,ad_status_to_pause ,ad_status_to_stop ,ad_status_to_archive ,ad_delete ,ad_create ,ad_create_media",
     * operationId="post",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="action", type="string", enum = {"retrieve_all" ,"get_account_adsaccounts" ,"get_account_properties" ,"get_properties" ,"get_account_pixels" ,"get_pixels" ,"get_account_entities" ,"get_account_campaigns" ,"get_campaigns" ,"get_adsets" ,"get_account_adsets" ,"get_account_ads" ,"get_creativities" ,"get_stats_campaigns" ,"get_account_stats_campaigns" ,"get_stats_adSet" ,"get_account_stats_adsets" ,"get_stats_ad" ,"get_account_stats_ads" ,"retrieve_stats_all" ,"retrieve_all_accounts_stats" ,"entity_update_geo" ,"entity_update_language" ,"entity_update_interests" ,"entity_update_gender" ,"entity_update_audience" ,"campaign_update_fields" ,"campaign_update_budget" ,"campaign_status_to_active" ,"campaign_status_to_pause" ,"campaign_status_to_stop" ,"campaign_status_to_archive" ,"campaign_delete" ,"campaign_create" ,"atomo_update_fields" ,"atomo_update_budget" ,"atomo_status_to_active" ,"atomo_status_to_pause" ,"atomo_status_to_stop" ,"atomo_status_to_archive" ,"atomo_delete" ,"atomo_create" ,"ad_update_fields", "ad_update_budget" ,"ad_status_to_active" ,"ad_status_to_pause" ,"ad_status_to_stop" ,"ad_status_to_archive" ,"ad_delete" ,"ad_create" ,"ad_create_media"}),
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function handler(Request $request)
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

        echo 'ejecuto ' . $requestParams['action'] . PHP_EOL;
        $loggermessage = array('level' => 'info', 'source' => 'index', 'step' => 'ejecuto ' . $requestParams['action'], 'user_id' => $infoCredentials['user_id']);
        extLogger($loggermessage);


        switch ($requestParams['action']) {
            case "retrieve_all":
                $this->retrieve_all($requestParams, $infoCredentials);
                break;
            case "get_account_adsaccounts":
                $this->get_adsAccounts($requestParams, $infoCredentials);
                break;
            case "get_account_properties":
            case "get_properties":
                $this->get_account_properties($adAccountData, $infoCredentials);
                break;
            case "get_account_pixels":
            case "get_pixels":
                echo 'getpisexl';
                $this->get_account_pixels($adAccountData, $infoCredentials);
                break;

                // Have to make function
            case "get_account_entities":
                $random = ''; //rand();
                $event = array('random' => $random,  'action' => 'get_account_properties',  'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
                execGoogleTask($event, $infoCredentials, $requestParams);
                $random = ''; //rand();
                $event = array('random' => $random,  'action' => 'get_account_campaigns',  'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
                execGoogleTask($event, $infoCredentials, $requestParams);
                $event = array('delaySeconds' => 1000,   'random' => $random, 'action' => 'get_account_adsets', 'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
                execGoogleTask($event, $infoCredentials, $requestParams);
                //creo task get_account_ads
                $event = array('delaySeconds' => 2400, 'random' => $random,  'action' => 'get_account_ads', 'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
                execGoogleTask($event, $infoCredentials, $requestParams);
                //get_account_campaigns($adAccountData, $infoCredentials);
                //get_account_adsets($adAccountData, $infoCredentials);
                //get_account_ads($adAccountData, $infoCredentials);
                break;
            case "get_account_campaigns":
            case "get_campaigns":
                // get_account_campaigns($requestParams['ad_account_id'], $infoCredentials);
                $this->get_account_campaigns($adAccountData, $infoCredentials);
                break;
            case "get_adsets":
            case "get_account_adsets":
                $this->get_account_adsets($adAccountData, $infoCredentials);
                break;
            case "get_account_ads":
                echo 'get ads';
                $this->get_account_ads($adAccountData, $infoCredentials);
                break;
                //to-do
            case "get_creativities":
                $this->get_creativities($adAccountData, $infoCredentials);
                break;
                // STATS
            case "get_stats_campaigns":
            case "get_account_stats_campaigns":
                $this->get_account_stats_campaigns($adAccountData, $requestParams, 'last_30d', $infoCredentials);
                break;
            case "get_stats_adSet":
            case "get_account_stats_adsets":
                $this->get_account_stats_adSets($adAccountData, $requestParams, 'last_7d', $infoCredentials);
                break;
            case "get_stats_ad":
            case "get_account_stats_ads":
                echo 'sss' . PHP_EOL;
                $this->get_account_stats_ads($adAccountData, $requestParams, $requestParams['period'], $infoCredentials);
                break;
            case "retrieve_stats_all":
            case "retrieve_all_accounts_stats":
                $this->retrieve_all_adsAccounts_stats($requestParams, $requestParams['period'], $infoCredentials, null);
                break;
            default:
                echo "NO ACTION FOUND";
        }
    }

    /**
     * @OA\Post(
     * path="/api/retrieve_all",
     * summary="Retrieve All",
     * description="Retrieve All",
     * operationId="retrieve_all",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function retrieveAllHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->retrieve_all($requestParams, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/retrieve_stats_all",
     * summary="Retrieve Stats All",
     * description="Retrieve Stats All",
     * operationId="retrieve_stats_all",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/retrieve_all_accounts_stats",
     * summary="Retrieve All Accounts Stats",
     * description="Retrieve All Accounts Stats",
     * operationId="retrieve_all_accounts_stats",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function retrieveStatsAllHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->retrieve_all_adsAccounts_stats($requestParams, $requestParams['period'], $infoCredentials, null);
    }

    /**
     * @OA\Post(
     * path="/api/get_account_adsaccounts",
     * summary="Get Account Ads Accounts",
     * description="Get Account Ads Accounts",
     * operationId="get_account_adsaccounts",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */
    public function getAccountAdsAccountsHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->get_adsAccounts($requestParams, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_account_properties",
     * summary="Get Account Properties",
     * description="Get Account Properties",
     * operationId="get_account_properties",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/get_properties",
     * summary="Get Properties",
     * description="Get Properties",
     * operationId="get_properties",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */
    public function getPropertiesHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->get_account_properties($adAccountData, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_account_pixels",
     * summary="Get Account Pixels",
     * description="Get Account Pixels",
     * operationId="get_account_pixels",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/get_pixels",
     * summary="Get Pixels",
     * description="Get Pixels",
     * operationId="get_pixels",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getPixelsHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        echo 'getpisexl';
        $this->get_account_pixels($adAccountData, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_account_entities",
     * summary="Get Account Entities",
     * description="Get Account Entities",
     * operationId="get_account_entities",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getAccountEntitiesHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);

        $random = ''; //rand();
        $event = array('random' => $random,  'action' => 'get_account_properties',  'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
        execGoogleTask($event, $infoCredentials, $requestParams);
        $random = ''; //rand();
        $event = array('random' => $random,  'action' => 'get_account_campaigns',  'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
        execGoogleTask($event, $infoCredentials, $requestParams);
        $event = array('delaySeconds' => 1000,   'random' => $random, 'action' => 'get_account_adsets', 'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
        execGoogleTask($event, $infoCredentials, $requestParams);
        //creo task get_account_ads
        $event = array('delaySeconds' => 2400, 'random' => $random,  'action' => 'get_account_ads', 'entity_platformId' => $adAccountData['account_platform_id'], 'ad_account_platformId' => $adAccountData['account_platform_id'], 'type' => 'get_entity_data', 'function' => FUNCTION_API_NAME, 'entity_name' => 'ad_account');
        execGoogleTask($event, $infoCredentials, $requestParams);
        //get_account_campaigns($adAccountData, $infoCredentials);
        //get_account_adsets($adAccountData, $infoCredentials);
        //get_account_ads($adAccountData, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_account_campaigns",
     * summary="Get Account Campaigns",
     * description="Get Account Campaigns",
     * operationId="get_account_campaigns",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/get_campaigns",
     * summary="Get Campaigns",
     * description="Get Campaigns",
     * operationId="get_campaigns",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getCampaignsHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);

        // get_account_campaigns($requestParams['ad_account_id'], $infoCredentials);
        $this->get_account_campaigns($adAccountData, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_adsets",
     * summary="Get AdSets",
     * description="Get AdSets",
     * operationId="get_adsets",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/get_account_adsets",
     * summary="Get Account AdSets",
     * description="Get Account AdSets",
     * operationId="get_account_adsets",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getAdsetsHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);
        $this->get_account_adsets($adAccountData, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_account_ads",
     * summary="Get Account Ads",
     * description="Get Account Ads",
     * operationId="get_account_ads",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getAccountAdsHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);
        $this->get_account_ads($adAccountData, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_creativities",
     * summary="Get Creativities",
     * description="Get Creativities",
     * operationId="get_creativities",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getCreativitiesHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);
        $this->get_creativities($adAccountData, $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_stats_campaigns",
     * summary="Get Stats Campaigns",
     * description="Get Stats Campaigns",
     * operationId="get_stats_campaigns",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/get_account_stats_campaigns",
     * summary="Get Account Stats Campaigns",
     * description="Get Account Stats Campaigns",
     * operationId="get_account_stats_campaigns",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getStatsCampaignsHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);
        $this->get_account_stats_campaigns($adAccountData, $requestParams, 'last_30d', $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_stats_adSet",
     * summary="Get Stats AdSet",
     * description="Get Stats AdSet",
     * operationId="get_stats_adSet",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/get_account_stats_adsets",
     * summary="Get Stats Stats AdSets",
     * description="Get Stats Stats AdSets",
     * operationId="get_account_stats_adsets",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getStatsAdSetHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);
        $this->get_account_stats_adSets($adAccountData, $requestParams, 'last_7d', $infoCredentials);
    }

    /**
     * @OA\Post(
     * path="/api/get_stats_ad",
     * summary="Get Stats Ad",
     * description="Get Stats Ad",
     * operationId="get_stats_ad",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    /**
     * @OA\Post(
     * path="/api/get_account_stats_ads",
     * summary="Get Account Stats Ads",
     * description="Get Account Stats Ads",
     * operationId="get_account_stats_ads",
     * tags={"Read-Facebook-API"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       required={"action","auth_id"},
     *       @OA\Property(property="auth_id", type="string"),
     *       @OA\Property(property="auth_publicId", type="string"),
     *       @OA\Property(property="ad_account_platformId", type="string"),
     *       @OA\Property(property="ad_account_publicId", type="string"),
     *       @OA\Property(property="work_entity_name", type="string"),
     *       @OA\Property(property="entity_platformId", type="string"),
     *       @OA\Property(property="entity_publicId", type="string"),
     *       @OA\Property(property="periodEnum", type="string"),
     *       @OA\Property(property="period", type="string"),
     *       @OA\Property(property="platform_object_id", type="string"),
     *       @OA\Property(property="start_date", type="string"),
     *       @OA\Property(property="end_date", type="string"),
     *       @OA\Property(property="callchild", type="string"),
     *       @OA\Property(property="dataFields", type="string"),
     *       @OA\Property(property="taskId", type="string"),
     *       @OA\Property(property="newBudget", type="string"),
     *       @OA\Property(property="newBid", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     * ),
     * @OA\Response(
     *    response="default",
     *    description="unexpected error",
     *    @OA\Schema(ref="#/components/schemas/Error")
     * )
     * )
     */

    public function getStatsAdHandler(Request $request)
    {
        [$infoCredentials, $requestParams, $adAccountData] = $this->initialfunc($request);

        echo 'sss' . PHP_EOL;
        $this->get_account_stats_ads($adAccountData, $requestParams, $requestParams['period'], $infoCredentials);
    }

    public function test()
    {
        return response('success');
    }
}
