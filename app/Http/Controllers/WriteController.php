<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FacebookAds\Api;
use FacebookAds\Cursor;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Ad;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\AdCreative;
use FacebookAds\Object\Fields\AdCreativeFields;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;
use FacebookAds\Object\AdImage;
use FacebookAds\Object\Fields\AdImageFields;
use FacebookAds\Object\AdSet;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\AdCreativeLinkData;
use FacebookAds\Object\Fields\AdCreativeLinkDataFields;
use FacebookAds\Object\AdCreativeObjectStorySpec;
use FacebookAds\Object\Fields\AdCreativeObjectStorySpecFields;
use FacebookAds\Object\User;
use Illuminate\Support\Facades\DB;
use App\Traits\Common;

class WriteController extends Controller
{
    use Common;

    /** 
     * @OA\Tag(
     * name="Write-Facebook-API",
     * description="API Endpoints of Write Facebook"
     * )
     */
    public function __construct()
    {
        Cursor::setDefaultUseImplicitFetch(false);
    }

    // Entity
    public function entity_update_field($adAccountData, $entityInPlatformId, $data, $entity_publicId, $taskId, $requestParams)
    {
        try {
            $entity = new Adset($entityInPlatformId);
            $result = ($entity->updateSelf([], $data))->exportAllData();

            // persistWrite('entity', $entity_publicId, $taskId, 'entity_update_field', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            return $result;
        } catch (FacebookResponseException $e) {
            echo 'FacebookResponseException returned an error: ' . $e->getMessage();
            // persistWrite('entity', $entity_publicId, $taskId, 'entity_update_field', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getCode() . PHP_EOL;
            // print_r($e->getErrorUserTitle());
            // print_r($e->getErrorUserMessage());
            // persistWrite('entity', $entity_publicId, $taskId, 'entity_update_field', $fields, ['status' => 'Exception', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        }
    }


    public function entity_update_geo($adAccountData, $entityInPlatformId, $data, $entity_publicId, $taskId, $requestParams)
    {
        $entity = new Adset($entityInPlatformId);
        $entity->read(array(
            AdSetFields::TARGETING
        ));
        $targeting = $entity->{AdSetFields::TARGETING};

        $this->entity_update_field($adAccountData, $entityInPlatformId, ['targeting' => array_merge($targeting, $data)], $entity_publicId, $taskId, $requestParams);
    }

    public function entity_update_gender($adAccountData, $entityInPlatformId, $data, $entity_publicId, $taskId, $requestParams)
    {
        $entity = new Adset($entityInPlatformId);
        $entity->read(array(
            AdSetFields::TARGETING
        ));
        $targeting = $entity->{AdSetFields::TARGETING};

        $this->entity_update_field($adAccountData, $entityInPlatformId, ['targeting' => array_merge($targeting, $data)], $entity_publicId, $taskId, $requestParams);
    }

    public function entity_update_language($adAccountData, $entityInPlatformId, $data, $entity_publicId, $taskId, $requestParams)
    {
        $entity = new Adset($entityInPlatformId);
        $entity->read(array(
            AdSetFields::TARGETING
        ));
        $targeting = $entity->{AdSetFields::TARGETING};

        $this->entity_update_field($adAccountData, $entityInPlatformId, ['targeting' => array_merge($targeting, $data)], $entity_publicId, $taskId, $requestParams);
    }

    public function entity_update_audience($adAccountData, $entityInPlatformId, $data, $entity_publicId, $taskId, $requestParams)
    {
        $entity = new Adset($entityInPlatformId);
        $entity->read(array(
            AdSetFields::TARGETING
        ));
        $targeting = $entity->{AdSetFields::TARGETING};

        $this->entity_update_field($adAccountData, $entityInPlatformId, ['targeting' => array_merge($targeting, $data)], $entity_publicId, $taskId, $requestParams);
    }

    public function entity_update_interests($adAccountData, $entityInPlatformId, $data, $entity_publicId, $taskId, $requestParams)
    {
        $entity = new Adset($entityInPlatformId);
        $entity->read(array(
            AdSetFields::TARGETING
        ));
        $targeting = $entity->{AdSetFields::TARGETING};

        $this->entity_update_field($adAccountData, $entityInPlatformId, ['targeting' => array_merge($targeting, $data)], $entity_publicId, $taskId, $requestParams);
    }

    // https://developers.facebook.com/docs/marketing-api/reference/ad-campaign-group#Updating
    public function campaign_update_field($adAccountData, $entityInPlatformId, $fields, $entity_publicId, $taskId, $requestParams)
    {
        debugeo(['donde' => 'ENTRO  campaign_update_field', 'datos' => [$adAccountData, $entityInPlatformId, $fields, $entity_publicId, $taskId, $requestParams]]);
        try {
            $set = new Campaign($entityInPlatformId);
            $result = ($set->updateSelf([], $fields))->exportAllData();
            debugeo(['RESULTADO' => $result]);
            persistWrite('campaign', $entity_publicId, $taskId,  'campaign_update_field', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            return $result;
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            debugeo(['ERRORGRAPH' =>  $e->getMessage()]);
            // persistWrite('campaign', $entity_publicId, $taskId,  'campaign_update_field', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            // persistWrite('campaign', $entity_publicId, $taskId,  'campaign_update_field', $fields, ['status' => 'Exception', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        }
    }

    public function campaign_update_budget($adAccountData, $requestParams)
    {
        $fields = ['budget' => $requestParams['newBudget']];
        $this->campaign_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function campaign_status_to_active($adAccountData, $requestParams)
    {
        $fields = ['status' => 'ACTIVE'];
        $this->campaign_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function campaign_status_to_pause($adAccountData, $requestParams)
    {
        $fields = ['status' => 'PAUSED'];
        $this->campaign_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function campaign_status_to_stop($adAccountData, $requestParams)
    {
        $fields = ['status' => 'PAUSED'];
        $this->campaign_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function campaign_status_to_archive($adAccountData, $requestParams)
    {
        $fields = ['status' => 'ARCHIVED'];
        $this->campaign_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function campaign_delete($adAccountData, $entityInPlatformId, $entity_publicId, $taskId, $requestParams)
    {

        try {
            $set = new Campaign($entityInPlatformId);
            //$result = ($set->deleteSelf())->exportAllData();
            $result = ($set->deleteSelf())->getContent();
            print_r($result);
            var_dump(get_class_methods($result));
            // persistWrite('campaign', $entity_publicId, $taskId,  'campaign_delete', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            return $result;
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            // persistWrite('campaign', $entity_publicId, $taskId,  'campaign_delete', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            // persistWrite('campaign', $entity_publicId, $taskId,  'campaign_delete', $fields, ['status' => 'Exception', 'return' => $e->getMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        }
    }

    // Creativities

    public function creatividad_create($adAccountData,  $entity_platformId, $data, $requestParams)
    {


        foreach ($data as $creatividad) {
            $this->creatividad_create_unit($adAccountData,  $entity_platformId, $creatividad, $requestParams);
        }
    }

    public function ad_create_media($adAccountData, $entityInPlatformId, $data, $requestParams)
    {
        try {
            $adAccount = $adAccountData['account_platform_id'];

            if (
                strpos(mime_content_type($data), 'image') !== false
                || strpos($data, '.jpg') !== false
                || strpos($data, '.png') !== false
            ) {

                $filename = basename($data);
                file_put_contents($filename, file_get_contents($data));

                $image = new AdImage(null, 'act_' . $adAccount);
                $image->{AdImageFields::FILENAME} = $filename;
                $image->create();
                $result = ['id' => $image->{AdImageFields::ID}, 'hash' => $image->{AdImageFields::HASH}];
            }/*
    else{
      $video = new Advideo(null, 'act_'.$adAccount);
      $video->{AdVideoFields::SOURCE} = $data;
      $video->create();
      $result = ['id' => $video->{AdVideoFields::ID}];
    }*/
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            //persistWrite('creative', $entity_publicId, $taskId,  'ad_create_media', $fields,['status'=>'FacebookResponseException', 'return'=> $e->getMessage(), 'error_code'=> $e->getCode(), 'errors' => $e->getMessage()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error create_media: ' . $e->getMessage();
            //persistWrite('creative', $entity_publicId, $taskId,  'ad_create_media', $fields,['status'=>'Exception', 'return'=> $e->getMessage(), 'error_code'=> $e->getCode(), 'errors' => $e->getMessage()], $requestParams);
            exit;
        }
    }

    public function creatividad_create_unit($adAccountData,  $entity_platformId, $data, $requestParams)
    {
        $adAccount = $adAccountData['account_platform_id'];
        if (defined('STDIN')) {
            print_r($adAccount);
            print_r(PHP_EOL);
            print_r($data);
            print_r(PHP_EOL);
        }

        try {
            if (isset($data['dataCloud']) && isset($data['dataCloud']['creative'])) {
                //$data['dataCloud']['creative']['image_url'] = str_replace('/home/pre.adsconcierge.com/adsconcierge-beta/storage', 'https://pre.adsconcierge.com', $data['dataCloud']['creative']['image_url']);

                if (isset($data['dataCloud']['creative']['image_url'])) {
                    $media = $this->ad_create_media($adAccountData, $entity_platformId, $data['dataCloud']['creative']['image_url'], $requestParams);
                    if (defined('STDIN')) {
                        print_r($media);
                        print_r(PHP_EOL);
                    }
                    if (
                        strpos(mime_content_type($data['dataCloud']['creative']['image_url']), 'image') !== false
                        || strpos($data['dataCloud']['creative']['image_url'], '.jpg') !== false
                        || strpos($data['dataCloud']['creative']['image_url'], '.png') !== false
                    ) {
                        $data['dataCloud']['creative']['image_hash'] = $media['hash'];
                    } else {
                        $data['dataCloud']['creative']['video_id'] = $media['id'];
                    }

                    unset($data['dataCloud']['creative']['image_url']);
                }

                $params = $data['dataCloud']['creative'];

                $link_data = new AdCreativeLinkData();
                $link_data->setData(array(
                    AdCreativeLinkDataFields::MESSAGE => $params['body'],
                    AdCreativeLinkDataFields::LINK => $params['link_url'],
                    AdCreativeLinkDataFields::IMAGE_HASH => $params['image_hash'],
                    AdCreativeLinkDataFields::CALL_TO_ACTION => array(
                        'type' => $params['call_to_action_type'],
                        'value' => array(
                            'link' => $params['link_url'],
                        ),
                    ),
                ));

                $object_story_spec = new AdCreativeObjectStorySpec();
                $object_story_spec->setData(array(
                    AdCreativeObjectStorySpecFields::PAGE_ID => $params['page_id'],
                    AdCreativeObjectStorySpecFields::LINK_DATA => $link_data,
                ));

                $creative = new AdCreative(null, 'act_' . $adAccount);

                $creative->setData(array(
                    AdCreativeFields::NAME => $params['name'],
                    AdCreativeFields::TITLE => $params['title'],
                    AdCreativeFields::OBJECT_STORY_SPEC => $object_story_spec,
                ));

                $creative->create();

                $params = array(
                    'name' => 'ad ' . $requestParams['entity_platformId'],
                    'adset_id' => $requestParams['entity_platformId'],
                    'creative' => array('creative_id' => $creative->id),
                    'status' => 'PAUSED',
                );

                if (defined('STDIN')) {
                    print_r($params);
                    print_r(PHP_EOL);
                }

                $result = (new AdAccount('act_' . $adAccount))->createAd([], $params)->exportAllData();

                $fields['id_en_platform'] = $result['id'];
                $fields['campana_platform_id'] = $requestParams['dataFields']['campana_platform_id'];
                $fields['adset_platform_id'] = $requestParams['dataFields']['atomo_platform_id'];

                // persistWrite('creative', $data['creativeId'], $taskId,  'creatividad_create', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            }
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            // persistWrite('creative', $data['creativeId'], $taskId,  'creatividad_create', [], ['status' => 'FacebookResponseException', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getMessage()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
            // echo $e->getErrorUserTitle() . PHP_EOL;
            // echo $e->getErrorUserMessage() . PHP_EOL;
            // echo $e->getCode() . PHP_EOL;
            // persistWrite('creative', $data['creativeId'], $taskId,  'creatividad_create', [], ['status' => 'Exception', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getMessage()], $requestParams);
            exit;
        }
    }

    public function adSet_create($adAccountData, $entity_platformId, $data, $requestParams)
    {
        $adAccount = $adAccountData['account_platform_id'];

        try {
            if (isset($data['dataAdSet'])) {
                $adSet = (new AdAccount('act_' . $adAccount))->createAdSet([], $data['dataAdSet']);

                if (isset($data['creative'])) {
                    foreach ($data['creative'] as $value) {
                        $creatividad = $this->creatividad_create($adAccountData, $entity_platformId, $value, $requestParams);
                        //echo $adSet->id.PHP_EOL;
                        //echo $creatividad['id'].PHP_EOL;

                        $params = array(
                            'name' => 'ad ' . $adSet->id,
                            'adset_id' => $adSet->id,
                            'creative' => array('creative_id' => $creatividad['id']),
                            'status' => 'PAUSED',
                        );

                        json_encode((new AdAccount('act_' . $adAccount))->createAd([], $params)->exportAllData(), JSON_PRETTY_PRINT);
                    }
                }
                return $adSet->exportAllData();
            }

            if (isset($data['atomo'])) {

                foreach ($data['atomo'] as $atomo) {

                    $atomo['dataCloud']['dataAdSet']['campaign_id'] = $requestParams['entity_platformId'];
                    $atomo['dataCloud']['dataAdSet']['targeting']['genders'] = [$atomo['dataCloud']['dataAdSet']['targeting']['genders']];

                    /*$atomo['dataCloud']['dataAdSet']['bid_amount'] = $atomo['dataCloud']['dataAdSet']['bid_amount']*100;
          $atomo['dataCloud']['dataAdSet']['daily_budget'] = $atomo['dataCloud']['dataAdSet']['daily_budget']*100;
          */


                    $adSet = (new AdAccount('act_' . $adAccount))->createAdSet([], $atomo['dataCloud']['dataAdSet']);

                    /*if(isset($atomo['creative_id'])){
            foreach ($atomo['creative_id'] as $creative_id) {
              $params = array(
                'name' => 'ad '.$adSet->id,
                'adset_id' => $adSet->id,
                'creative' => array('creative_id' => $creative_id),
                'status' => 'PAUSED',
              );
  
              json_encode((new AdAccount('act_'.$adAccount))->createAd([], $params)->exportAllData(), JSON_PRETTY_PRINT);
            }
          }*/

                    $result = $adSet->exportAllData();
                    $fields['id_en_platform'] = $result['id'];
                    $fields['campana_platform_id'] = $requestParams['entity_platformId'];

                    $requestCreativity = $requestParams;
                    $requestCreativity['dataFields'] = $atomo['creatividades'];
                    $requestCreativity['dataFields']['campana_platform_id'] = $requestParams['entity_platformId'];
                    $requestCreativity['dataFields']['atomo_platform_id'] = $result['id'];

                    // persistWrite('atomo', $atomo['atomoId'], $taskId,  'adSet_create', $fields, ['status' => 'ok', 'return' => $result], $requestCreativity);
                }
            }
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();

            // persistWrite('atomo', $requestParams['entity_publicId'], $taskId,  'adSet_create', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getMessage()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
            // echo $e->getErrorUserTitle() . PHP_EOL;
            // echo $e->getErrorUserMessage() . PHP_EOL;
            // echo $e->getCode() . PHP_EOL;
            // persistWrite('atomo', $requestParams['entity_publicId'], $taskId,  'adSet_create', $fields, ['status' => 'Exception', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getMessage()], $requestParams);
            exit;
        }
    }

    public function campaign_create($adAccountData, $entity_platformId, $data, $requestParams)
    {
        $adAccount = $adAccountData['account_platform_id'];
        try {

            if (isset($data['campaign'])) {
                $campaign = (new AdAccount('act_' . $adAccount))->createCampaign([], $data['campaign']);

                if (isset($data['adSet'])) {
                    foreach ($data['adSet'] as $value) {
                        $value['dataAdSet']['campaign_id'] = $campaign->{CampaignFields::ID};
                        $adSet_id[] = $this->adSet_create($adAccountData, $entity_platformId, $value, $requestParams);
                    }
                }

                $result = $campaign->exportAllData();

                $fields['id_en_platform'] = $result['id'];

                persistWrite('campaign', $requestParams['entity_publicId'], $requestParams['taskId'], 'campaign_create', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
                return $campaign->exportAllData();
            }
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            // persistWrite('campaign', $requestParams['entity_publicId'], $taskId,  'campaign_create', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
            // echo $e->getErrorUserTitle() . PHP_EOL;
            // echo $e->getErrorUserMessage() . PHP_EOL;
            // echo $e->getCode() . PHP_EOL;
            // persistWrite('campaign', $requestParams['entity_publicId'], $taskId,  'campaign_create', $fields, ['status' => 'Exception', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        }
    }

    // https://developers.facebook.com/docs/marketing-api/reference/ad-campaign#Updating
    public function adset_update_field($adAccountData, $entityInPlatformId, $fields, $entity_publicId, $taskId, $requestParams)
    {

        try {
            $set = new Adset($entityInPlatformId);
            $result = ($set->updateSelf([], $fields))->exportAllData();
            persistWrite('atomo', $entity_publicId, $taskId,  'adset_update_field', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            return $result;
        } catch (FacebookResponseException $e) {
            echo 'FacebookResponseException returned an error: ' . $e->getMessage();
            // persistWrite('atomo', $entity_publicId, $taskId,  'adset_update_field', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getCode();
            //	print_r( $e->getResponse());
            /*	print_r( $e->getErrorUserTitle());
      print_r( $e->getErrorUserMessage());
      print_r( get_class_methods( $e));  */
            // persistWrite('atomo', $entity_publicId, $taskId,  'adset_update_field', $fields, ['status' => 'Exception', 'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        }
    }

    public function adset_update_budget($adAccountData, $requestParams)
    {
        $fields = ['daily_budget' => $requestParams['newBudget']];
        $this->adset_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function adset_status_to_active($adAccountData, $requestParams)
    {
        $fields = ['status' => 'ACTIVE'];
        $this->adset_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function adset_status_to_pause($adAccountData, $requestParams)
    {
        $fields = ['status' => 'PAUSED'];
        $this->adset_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function adset_status_to_stop($adAccountData, $requestParams)
    {
        $fields = ['status' => 'PAUSED'];
        $this->adset_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function adset_status_to_archive($adAccountData, $requestParams)
    {
        $fields = ['status' => 'ARCHIVED'];
        $this->adset_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function adset_delete($adAccountData, $entityInPlatformId, $entity_publicId, $taskId, $requestParams)
    {

        try {
            $set = new Adset($entityInPlatformId);
            $result = ($set->deleteSelf())->getContent();
            print_r($result);
            // persistWrite('atomo', $entity_publicId, $taskId,  'adset_delete', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            return $result;
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            // persistWrite('atomo', $entity_publicId, $taskId,  'adset_delete', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            // persistWrite('atomo', $entity_publicId, $taskId,  'adset_delete', $fields, ['status' => 'Exception', 'return' => $e->getMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        }
    }

    // https://developers.facebook.com/docs/marketing-api/reference/adgroup#Updating
    public function ad_update_field($adAccountData, $entityInPlatformId, $fields, $entity_publicId, $taskId, $requestParams)
    {
        try {
            echo "aqui";
            echo $entityInPlatformId;
            $fields = ['name' => 'prueba 3'];
            print_r($fields);

            $set = new AdCreative('23849735284790502');
            $set->read(array(
                AdCreativeFields::NAME,
            ));
            // Output Ad name.
            echo $set->name;
            $result = ($set->updateSelf([], $fields))->exportAllData();
            die();

            // persistWrite('creative', $entity_publicId, $taskId,  'ad_update_fields', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            return $result;
        } catch (FacebookResponseException $e) {
            // echo 'FacebookResponseException returned an error: ' . $e->getMessage();
            // persistWrite('creative', $entity_publicId, $taskId,  'ad_update_fields', $fields, [
            //     'status' => 'FacebookResponseException',
            //     'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()
            // ], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getCode();
            // print_r($e->getResponse());
            // persistWrite('creative', $entity_publicId, $taskId,  'ad_update_fields', $fields, [
            //     'status' => 'Exception',
            //     'return' => $e->getErrorUserTitle() . ' -- ' . $e->getErrorUserMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()
            // ], $requestParams);
            exit;
        }
    }

    public function ad_update_budget($adAccountData, $requestParams)
    {
        $fields = ['daily_budget' => $requestParams['newBudget']];
        $this->ad_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function ad_status_to_active($adAccountData, $requestParams)
    {
        $fields = ['status' => 'ACTIVE'];
        $this->ad_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function ad_status_to_pause($adAccountData, $requestParams)
    {
        $fields = ['status' => 'PAUSED'];
        $this->ad_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function ad_status_to_stop($adAccountData, $requestParams)
    {
        $fields = ['status' => 'PAUSED'];
        $this->ad_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function ad_status_to_archive($adAccountData, $requestParams)
    {
        $fields = ['status' => 'ARCHIVED'];
        $this->ad_update_field($adAccountData,   $requestParams['entity_platformId'], $fields, $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    public function ad_delete($adAccountData, $entityInPlatformId, $entity_publicId, $taskId, $requestParams)
    {
        try {
            $set = new Ad($entityInPlatformId);
            $result = ($set->deleteSelf())->getContent();
            // persistWrite('creative', $entity_publicId, $taskId,  'ad_delete', $fields, ['status' => 'ok', 'return' => $result], $requestParams);
            return $result;
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            // persistWrite('creative', $entity_publicId, $taskId,  'ad_delete', $fields, ['status' => 'FacebookResponseException', 'return' => $e->getMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        } catch (\Exception $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            // persistWrite('creative', $entity_publicId, $taskId,  'ad_delete', $fields, ['status' => 'Exception', 'return' => $e->getMessage(), 'error_code' => $e->getCode(), 'errors' => $e->getErrors()], $requestParams);
            exit;
        }
    }

    /**
     * @OA\Post(
     * path="/apicreate",
     * summary="Post functions",
     * description="Actions for retrieve_all ,get_account_adsaccounts ,get_account_properties ,get_properties ,get_account_pixels ,get_pixels ,get_account_entities ,get_account_campaigns ,get_campaigns ,get_adsets ,get_account_adsets ,get_account_ads ,get_creativities ,get_stats_campaigns ,get_account_stats_campaigns ,get_stats_adSet ,get_account_stats_adsets ,get_stats_ad ,get_account_stats_ads ,retrieve_stats_all ,retrieve_all_accounts_stats ,entity_update_geo ,entity_update_language ,entity_update_interests ,entity_update_gender ,entity_update_audience ,campaign_update_fields ,campaign_update_budget ,campaign_status_to_active ,campaign_status_to_pause ,campaign_status_to_stop ,campaign_status_to_archive ,campaign_delete ,campaign_create ,atomo_update_fields ,atomo_update_budget ,atomo_status_to_active ,atomo_status_to_pause ,atomo_status_to_stop ,atomo_status_to_archive ,atomo_delete ,atomo_create ,ad_update_fields ,ad_update_budget ,ad_status_to_active ,ad_status_to_pause ,ad_status_to_stop ,ad_status_to_archive ,ad_delete ,ad_create ,ad_create_media",
     * operationId="post",
     * tags={"Write-Facebook-API"},
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
            if (!isset($request['action'])) {
                echo 'No action defined in request';
                die();
            }
            if (!isset($request['auth_id']) && !isset($request['auth_publicId'])) {
                echo 'No AUTH ID defined in request';
                die();
            }
        }

        // conformamos el request
        $requestParams = array(
            'action' => isset($request['action']) ? $request['action'] : false,
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
            case "entity_update_geo":
                $retorno = $this->entity_update_geo($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
            case "entity_update_language":
                $retorno = $this->entity_update_language($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
            case "entity_update_interests":
                $retorno = $this->entity_update_interests($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
            case "entity_update_gender":
                $retorno = $this->entity_update_gender($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
            case "entity_update_audience":
                $retorno = $this->entity_update_audience($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
                //fin wrappers de updates
                //campaign writes
                //https://developers.facebook.com/docs/marketing-api/reference/ad-campaign-group#fields
            case "campaign_update_fields":
                //status enum {ACTIVE, PAUSED, DELETED, ARCHIVED}
                //	bid_strategy enum {LOWEST_COST_WITHOUT_CAP, LOWEST_COST_WITH_BID_CAP, COST_CAP}
                // objective enum{APP_INSTALLS, BRAND_AWARENESS, CONVERSIONS, EVENT_RESPONSES, LEAD_GENERATION, LINK_CLICKS, LOCAL_AWARENESS, MESSAGES, OFFER_CLAIMS, PAGE_LIKES, POST_ENGAGEMENT, PRODUCT_CATALOG_SALES, REACH, STORE_VISITS, VIDEO_VIEWS}
                // special_ad_categories array<enum {NONE, EMPLOYMENT, HOUSING, CREDIT, ISSUES_ELECTIONS_POLITICS}>
                $retorno =  $this->campaign_update_field($adAccountData,   $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
            case "campaign_update_budget":
                $retorno =     $this->campaign_update_budget($adAccountData, $requestParams);
                break;
            case "campaign_status_to_active":
                $retorno =    $this->campaign_status_to_active($adAccountData, $requestParams);
                break;
            case "campaign_status_to_pause":
                $retorno =   $this->campaign_status_to_pause($adAccountData, $requestParams);
                break;
            case "campaign_status_to_stop":
                $retorno =    $this->campaign_status_to_stop($adAccountData, $requestParams);
                break;
            case "campaign_status_to_archive":
                $retorno =    $this->campaign_status_to_archive($adAccountData, $requestParams);
                break;
            case "campaign_delete":
                $retorno =     $this->campaign_delete($adAccountData, $requestParams['entity_platformId'],  $requestParams['entity_publicId'], $requestParams['taskId'],  $requestParams);
                break;
            case "campaign_create":
                $retorno =     $this->campaign_create($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
                break;
                //fin campaign write
                //adSet writes
            case "atomo_update_fields":
                $retorno =    $this->adset_update_field($adAccountData,   $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
            case "atomo_update_budget":
                $retorno =    $this->adset_update_budget($adAccountData, $requestParams);
                break;
            case "atomo_status_to_active":
                $this->adset_status_to_active($adAccountData, $requestParams);
                break;
            case "atomo_status_to_pause":
                $this->adset_status_to_pause($adAccountData, $requestParams);
                break;
            case "atomo_status_to_stop":
                $this->adset_status_to_stop($adAccountData, $requestParams);
                break;
            case "atomo_status_to_archive":
                $this->adset_status_to_archive($adAccountData, $requestParams);
                break;
            case "atomo_delete":
                $this->adset_delete($adAccountData, $requestParams['entity_platformId'], $requestParams['entity_publicId'],  $requestParams['taskId'], $requestParams);
                break;
            case "atomo_create":
                $this->adSet_create($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
                break;
                //fin adSet write
                //ad writes
            case "ad_update_fields":
                $retorno = $this->ad_update_field($adAccountData,   $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
                break;
            case "ad_update_budget":
                $retorno = $this->ad_update_budget($adAccountData, $requestParams);
                break;
            case "ad_status_to_active":
                $retorno = $this->ad_status_to_active($adAccountData, $requestParams);
                break;
            case "ad_status_to_pause":
                $this->ad_status_to_pause($adAccountData, $requestParams);
                break;
            case "ad_status_to_stop":
                $this->ad_status_to_stop($adAccountData, $requestParams);
                break;
            case "ad_status_to_archive":
                $this->ad_status_to_archive($adAccountData, $requestParams);
                break;
            case "ad_delete":
                $this->ad_delete($adAccountData, $requestParams['entity_platformId'], $requestParams['entity_publicId'],  $requestParams['taskId'], $requestParams);
                break;
            case "ad_create":
                $this->creatividad_create($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
                break;
                // case "ad_update_media":
                //     // TODO
                //     $data = ['field_name' => 'value'];
                //     $this->ad_update_media($adAccountData, $requestParams['entity_platformId'], $data, $requestParams);
                //     break;
            case "ad_create_media":
                $this->ad_create_media($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
                break;
                //fin ad write

            default:
                echo "NO ACTION FOUND";
        }
    }

    /**
     * @OA\Post(
     * path="/apicreate/entity_update_geo",
     * summary="Entity Update Geo",
     * description="Entity Update Geo",
     * operationId="entity_update_geo",
     * tags={"Write-Facebook-API"},
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

    public function entityUpdateGeoHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->entity_update_geo($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/entity_update_language",
     * summary="Entity Update Language",
     * description="Entity Update Language",
     * operationId="entity_update_language",
     * tags={"Write-Facebook-API"},
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

    public function entityUpdateLanguageHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->entity_update_language($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/entity_update_interests",
     * summary="Entity Update Interests",
     * description="Entity Update Interests",
     * operationId="entity_update_interests",
     * tags={"Write-Facebook-API"},
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

    public function entityUpdateInterestsHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->entity_update_interests($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/entity_update_gender",
     * summary="Entity Update Gener",
     * description="Entity Update Gener",
     * operationId="entity_update_gender",
     * tags={"Write-Facebook-API"},
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

    public function entityUpdateGenderHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->entity_update_gender($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/entity_update_audience",
     * summary="Entity Update Audience",
     * description="Entity Update Audience",
     * operationId="entity_update_audience",
     * tags={"Write-Facebook-API"},
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

    public function entityUpdateAudienceHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->entity_update_audience($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_update_fields",
     * summary="Campaign Update Fields",
     * description="Campaign Update Fields",
     * operationId="campaign_update_fields",
     * tags={"Write-Facebook-API"},
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

    public function campaignUpdateFieldsHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =  $this->campaign_update_field($adAccountData,   $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_update_budget",
     * summary="Campaign Update Budget",
     * description="Campaign Update Budget",
     * operationId="campaign_update_budget",
     * tags={"Write-Facebook-API"},
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

    public function campaignUpdateBudgetHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =     $this->campaign_update_budget($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_status_to_active",
     * summary="Campaign Status To Active",
     * description="Campaign Status To Active",
     * operationId="campaign_status_to_active",
     * tags={"Write-Facebook-API"},
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

    public function campaignStatusToActiveHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =    $this->campaign_status_to_active($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_status_to_pause",
     * summary="Campaign Status To Pause",
     * description="Campaign Status To Pause",
     * operationId="campaign_status_to_pause",
     * tags={"Write-Facebook-API"},
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

    public function campaignStatusToPauseHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =   $this->campaign_status_to_pause($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_status_to_stop",
     * summary="Campaign Status To Stop",
     * description="Campaign Status To Stop",
     * operationId="campaign_status_to_stop",
     * tags={"Write-Facebook-API"},
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

    public function campaignStatusToStopHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =    $this->campaign_status_to_stop($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_status_to_archive",
     * summary="Campaign Status To Archive",
     * description="Campaign Status To Archive",
     * operationId="campaign_status_to_archive",
     * tags={"Write-Facebook-API"},
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

    public function campaignStatusToArchiveHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =    $this->campaign_status_to_archive($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_delete",
     * summary="Campaign Delete",
     * description="Campaign Delete",
     * operationId="campaign_delete",
     * tags={"Write-Facebook-API"},
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

    public function campaignDeleteHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =     $this->campaign_delete($adAccountData, $requestParams['entity_platformId'],  $requestParams['entity_publicId'], $requestParams['taskId'],  $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/campaign_create",
     * summary="Campaign Create",
     * description="Campaign Create",
     * operationId="campaign_create",
     * tags={"Write-Facebook-API"},
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

    public function campaignCreateHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =     $this->campaign_create($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_update_fields",
     * summary="Atomo Update Fields",
     * description="Atomo Update Fields",
     * operationId="atomo_update_fields",
     * tags={"Write-Facebook-API"},
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

    public function atomoUpdateFieldsHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =    $this->adset_update_field($adAccountData,   $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_update_budget",
     * summary="Atomo Update Budget",
     * description="Atomo Update Budget",
     * operationId="atomo_update_budget",
     * tags={"Write-Facebook-API"},
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

    public function atomoUpdateBudgetHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno =    $this->adset_update_budget($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_status_to_active",
     * summary="Atomo Status To Active",
     * description="Atomo Status To Active",
     * operationId="atomo_status_to_active",
     * tags={"Write-Facebook-API"},
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

    public function atomoStatusToActiveHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->adset_status_to_active($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_status_to_pause",
     * summary="Atomo Status To Pause",
     * description="Atomo Status To Pause",
     * operationId="atomo_status_to_pause",
     * tags={"Write-Facebook-API"},
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

    public function atomoStatusToPauseHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->adset_status_to_pause($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_status_to_stop",
     * summary="Atomo Status To Stop",
     * description="Atomo Status To Stop",
     * operationId="atomo_status_to_stop",
     * tags={"Write-Facebook-API"},
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

    public function atomoStatusToStopHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->adset_status_to_stop($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_status_to_archive",
     * summary="Atomo Status To Archive",
     * description="Atomo Status To Archive",
     * operationId="atomo_status_to_archive",
     * tags={"Write-Facebook-API"},
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

    public function atomoStatusToArchiveHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->adset_status_to_archive($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_delete",
     * summary="Atomo Delete",
     * description="Atomo Delete",
     * operationId="atomo_delete",
     * tags={"Write-Facebook-API"},
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

    public function atomoDeleteHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->adset_delete($adAccountData, $requestParams['entity_platformId'], $requestParams['entity_publicId'],  $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/atomo_create",
     * summary="Atomo Create",
     * description="Atomo Create",
     * operationId="atomo_create",
     * tags={"Write-Facebook-API"},
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

    public function atomoCreateHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->adSet_create($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_update_fields",
     * summary="Ad Update Fields",
     * description="Ad Update Fields",
     * operationId="ad_update_fields",
     * tags={"Write-Facebook-API"},
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

    public function adUpdateFieldsHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->ad_update_field($adAccountData,   $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams['entity_publicId'], $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_update_budget",
     * summary="Ad Update Budget",
     * description="Ad Update Budget",
     * operationId="ad_update_budget",
     * tags={"Write-Facebook-API"},
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

    public function adUpdateBudgetHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->ad_update_budget($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_status_to_active",
     * summary="Ad Status To Active",
     * description="Ad Status To Active",
     * operationId="ad_status_to_active",
     * tags={"Write-Facebook-API"},
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

    public function adStatusToActiveHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $retorno = $this->ad_status_to_active($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_status_to_pause",
     * summary="Ad Status To Pause",
     * description="Ad Status To Pause",
     * operationId="ad_status_to_pause",
     * tags={"Write-Facebook-API"},
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

    public function adStatusToPauseHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->ad_status_to_pause($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_status_to_stop",
     * summary="Ad Status To Stop",
     * description="Ad Status To Stop",
     * operationId="ad_status_to_stop",
     * tags={"Write-Facebook-API"},
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

    public function adStatusToStopHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->ad_status_to_stop($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_status_to_archive",
     * summary="Ad Status To Archive",
     * description="Ad Status To Archive",
     * operationId="ad_status_to_archive",
     * tags={"Write-Facebook-API"},
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

    public function adStatusToArchiveHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->ad_status_to_archive($adAccountData, $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_delete",
     * summary="Ad Delete",
     * description="Ad Delete",
     * operationId="ad_delete",
     * tags={"Write-Facebook-API"},
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

    public function adDeleteHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->ad_delete($adAccountData, $requestParams['entity_platformId'], $requestParams['entity_publicId'],  $requestParams['taskId'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_create",
     * summary="Ad Create",
     * description="Ad Create",
     * operationId="ad_create",
     * tags={"Write-Facebook-API"},
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

    public function adCreateHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->creatividad_create($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
    }

    /**
     * @OA\Post(
     * path="/apicreate/ad_create_media",
     * summary="Ad Create Media",
     * description="Ad Create Media",
     * operationId="ad_create_media",
     * tags={"Write-Facebook-API"},
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

    public function adCreateMediaHandler(Request $request)
    {
        [$requestParams, $infoCredentials, $adAccountData] = $this->initialfunc($request);
        $this->ad_create_media($adAccountData, $requestParams['entity_platformId'], $requestParams['dataFields'], $requestParams);
    }

    public function test()
    {
        return response('success');
    }
}
