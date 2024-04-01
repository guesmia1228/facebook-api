<?php
// helpers
function helper_metrics_keytranslator($platformname, $tipoinput, $inputraw, $outputrow, $metadata = null){
  global $mapeometricas;
  //		print_r(array_keys( $mapeometricas) );
  //		print_r($mapeometricas[$platformname]);
  /*
    var_dump($inputraw);
    //echo PHP_EOL;
    die();
  ***/

  switch ($platformname) {
    case 1:
    case "FACEBOOK":
      switch ($tipoinput) {
        case 'campana':
            $outputrow['id_in_platform'] = $inputraw['campaign_id'];
          break;
        case 'ad':
            $outputrow['id_in_platform'] = $inputraw['ad_id'];
            $outputrow['atomo_id'] = $inputraw['adset_id'];
            $outputrow['adset_id'] = $inputraw['adset_id'];
            $outputrow['campaign_id'] = $inputraw['campaign_id'];
          break;
        case 'lineitem':
        case 'adset':
            $outputrow['id_in_platform'] = $inputraw['adset_id'];
            $outputrow['atomo_id'] = $inputraw['adset_id'];
            $outputrow['campaign_id'] = $inputraw['campaign_id'];
          break;
      }
      $outputrow['date'] = $inputraw['date_start'];
      $outputrow['yearmonth'] = date('Y-m-01', strtotime($inputraw['date_start']));
      $outputrow['platformid'] = 1;
      $outputrow['plataforma'] = 'FACEBOOK';
      switch ($inputraw['publisher_platform']){
        case 'facebook':
            $outputrow['platformid'] = 1;
            $outputrow['plataforma'] = 'FACEBOOK';
          break;
        case 'instagram':
            $outputrow['platformid'] = 4;
            $outputrow['plataforma'] = 'INSTAGRAM';
          break;
      }
      switch ($inputraw['device_platform']){
        case 'mobile_app':
            $outputrow['device']='MOBILE';
          break;
        default:
            $outputrow['device']='ALL';
          break;
      }

      //https://developers.facebook.com/docs/marketing-api/insights/parameters/v9.0
      $outputrow['video_starts'] = isset($inputraw['video_play_actions']) ? $inputraw['video_play_actions']: 0 ;
      //$outputrow['video_views'] = $inputraw['video_play_actions'];
      $outputrow['video_completes'] = isset($inputraw['video_p100_watched_actions']) ? $inputraw['video_p100_watched_actions']: 0 ;
      $outputrow['video_25'] = $inputraw['video_p25_watched_actions'];
      $outputrow['video_50'] = $inputraw['video_p50_watched_actions'];
      $outputrow['video_75'] = $inputraw['video_p75_watched_actions'];
      // $outputrow['placement'] = $inputraw['platform_position'];
      // $outputrow['objective'] = $inputraw['objective'];

    foreach ($inputraw as $clave => $valor) {
      if ($valor == null) {
        continue;
      }

      $clave = strtolower($clave);
      $clavemapeo = isset($mapeometricas[$platformname][$clave]) ? $mapeometricas[$platformname][$clave] : (isset($mapeometricas['default'][$clave]) ? $mapeometricas['default'][$clave] : ['metrics_rest']);

      foreach ($clavemapeo as $claveout) {
        if (is_numeric($outputrow[$claveout])) {
          $outputrow[$claveout] = $outputrow[$claveout] + $valor;
        } elseif (is_string($outputrow[$claveout])) {
          $outputrow[$claveout] = $valor;
        } else {
          $outputrow[$claveout][$clave] = $valor;
        }
      }

    }

    if (isset($inputraw["actions"])) {
      foreach ($inputraw["actions"] as $item) {
        if ($item["action_type"] == "video_view") {
          $outputrow['video_views'] = $item["value"];
        }
      }
    }

    //die();
    break;
    case '4':
    case 'INSTAGRAM':
    $outputrow['plataforma'] = 'INSTAGRAM';
    break;

  }
  return $outputrow;
}

function helper_metrics_campana_day($platformid, $user_id, $customer_id = 0, $datos, $ad_account_id, $ad_account_id_platform, $metadata=['currency' => null]){
  global $dbconn_stats, $dbconn;

  if (!isset($metadata['currency'])) {
    $metadata['currency'] = null;
  }
  if (!is_array($datos)) {
    return null;
  }

  echo ' > Updating ' . count($datos) . ' elements : ';
  $i = 1;

  foreach ($datos as $inputraw) {

    $outputrow = ['id_in_platform' => 0, 'campananame' => '', 'platformid' => $platformid, 'date' => '',
                  'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [],
                  'metrics_conversion' => [], 'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0,
                  'clicks' => 0, 'engagements' => 0, 'video_views' => 0, 'video_starts' => 0,
                  'currency' => $metadata['currency'], 'conversions' => 0, 'objective'=>'', 'device'=>'', 'placement'=>''];
    $outputrow = helper_metrics_keytranslator($platformid, 'campana', $inputraw, $outputrow, $metadata);
    //  print_r( $outputrow);
    //     exit;
    //    echo "Impressions:" . $outputrow['impressions'] . PHP_EOL;
    //   echo "Cost:" . $outputrow['cost'] . PHP_EOL;

    if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
      //continue;
    }

    $sql = "INSERT INTO adsconcierge_stats.platform_campana_day (user_id, platformid, customer_id, ad_account_id, campanaid,
                    idenplatform, campananame, adccountid_pl, dia, unico, currency, metrics_delivery, metrics_costs,
                    metrics_engagement, metrics_video, metrics_conversion, metrics_rest, cost, impressions, reach,
                    clicks, engagements, video_views, conversions, plataforma, campanaroot, yearweek, yearmonth )
              SELECT
                    {$user_id},
                    '{$outputrow['platformid']}',
                    '{$customer_id}',
                    '{$ad_account_id}',
                    cp.id,
                    '{$outputrow['id_in_platform']}',
                    cp.name,
                    '{$ad_account_id_platform}',
                    '{$outputrow['date']}',
                    '" . md5($outputrow['platformid'] . $outputrow['id_in_platform'] . $outputrow['date'] . $user_id) . "',
                    '{$outputrow["metrics_rest"]["account_currency"]}',
                    '" . json_encode($outputrow['metrics_delivery']) . "',
                    '" . json_encode($outputrow['metrics_costs']) . "',
                    '" . json_encode($outputrow['metrics_engagement']) . "',
                    '" . json_encode($outputrow['metrics_video']) . "',
                    '" . json_encode($outputrow['metrics_conversion']) . "',
                    '" . $dbconn_stats->real_escape_string(json_encode($outputrow['metrics_rest'])) . "',
                    '{$outputrow['cost']}', '{$outputrow['impressions']}',
                    '{$outputrow['reach']}',
                    '{$outputrow['clicks']}', '{$outputrow['engagements']}',
                    '" . intval($outputrow['video_views']) . "',
                    '{$outputrow['conversions']}',
                    '{$outputrow['plataforma']}',
                    cp.campana_root,
                    YEARWEEK('{$outputrow['date']}'),
                    date_format('{$outputrow['date']}', '%Y-%m')
              FROM app_thesoci_9c37.campaigns_platform cp
              WHERE
                    cp.id_en_platform = '{$outputrow['id_in_platform']}' AND cp.user_id = {$user_id}
                    ON DUPLICATE KEY UPDATE
                    cost                = '{$outputrow['cost']}',
                    impressions         = '{$outputrow['impressions']}' ,
                    reach               = '{$outputrow['reach']}',
                    clicks              = '{$outputrow['clicks']}',
                    engagements         = '{$outputrow['engagements']}',
                    video_views         = '" . intval($outputrow['video_views']) . "',
                    video_starts        = '" . intval($outputrow['video_starts']) . "',
                    video_completes     = '" . intval($outputrow['video_completes']) . "',
                    video_25            = '" . intval($outputrow['video_25']) . "',
                    video_50            = '" . intval($outputrow['video_50']) . "',
                    video_75            = '" . intval($outputrow['video_75']) . "',
                    conversions         = '{$outputrow['conversions']}',
                    metrics_delivery    = '" . json_encode($outputrow['metrics_delivery']) . "',
                    metrics_costs       = '" . json_encode($outputrow['metrics_costs']) . "',
                    metrics_engagement  = '" . json_encode($outputrow['metrics_engagement']) . "',
                    metrics_video       = '" . json_encode($outputrow['metrics_video']) . "',
                    metrics_conversion  = '" . json_encode($outputrow['metrics_conversion']) . "',
                    metrics_rest        = '" . $dbconn_stats->real_escape_string(json_encode($outputrow['metrics_rest'])) . "'";

    echo $sql.PHP_EOL;
    print_r( $dbconn_stats->query($sql) );
    if (!$dbconn_stats->query($sql)) {
      var_dump( $inputraw);
      echo PHP_EOL; echo PHP_EOL; echo PHP_EOL;            echo PHP_EOL; echo PHP_EOL; echo PHP_EOL;
      echo $dbconn_stats->error;
      echo PHP_EOL; echo PHP_EOL; echo PHP_EOL;            echo PHP_EOL; echo PHP_EOL; echo PHP_EOL;
      var_dump( $outputrow);
      echo PHP_EOL; echo PHP_EOL; echo PHP_EOL;
      echo PHP_EOL . $sql;
      die();
    }
    exit;
  }
}

function helper_metrics_ads_day($platformid, $user_id, $customer_id = 0, $addAcountID, $datos){
  global $dbconn_stats, $db;

  if (!isset($metadata['currency'])) {
    $metadata['currency'] = null;
  }

  foreach ($datos as $inputraw) {
    //   print_r($inputraw);
    //die();
    $outputrow = ['id_in_platform' => 0, 'campananame' => '', 'platformid' => $platformid, 'date' => '',
                  'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [],
                  'metrics_conversion' => [], 'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0,
                  'clicks' => 0, 'engagements' => 0, 'video_views' => 0, 'video_starts' => 0,
                  'currency' => $metadata['currency'], 'conversions' => 0];

    $outputrow = helper_metrics_keytranslator($platformid, 'ad', $inputraw, $outputrow, $metadata);

    if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
      continue;
    }
    echo "helper_metrics_ads_day *** " . $outputrow['impressions'] . PHP_EOL . PHP_EOL;

    $sql = "INSERT INTO adsconcierge_stats.platform_ads_day ( user_id, platformid, customer_id, ad_account_id, campanaid,
                        lineitemid, idenplatform, adccountid_pl, dia, unico,
                        ad_name, metrics_delivery, metrics_costs, metrics_engagement, metrics_video,
                        metrics_conversion, metrics_rest, cost, impressions, reach,
                        clicks, engagements, video_views, conversions, plataforma,
                        campanaid_enplatform, lineitem_enplatform,
                        campanaroot, yearweek, yearmonth )
                  SELECT
                        {$user_id}, {$outputrow['platformid']}, c.customer_id, aa.id, cp.id,
                        cpo.id, '{$outputrow['id_in_platform']}', aa.account_id, '{$outputrow['date']}',
                        '" . md5($outputrow['platformid'] . $outputrow['id_in_platform'] . $outputrow['date'] . $user_id) . "',
                        c.title,'" . json_encode($outputrow['metrics_delivery']) . "', '" . json_encode($outputrow['metrics_costs']) . "',
                        '" . json_encode($outputrow['metrics_engagement']) . "', '" . json_encode($outputrow['metrics_video']) . "',
                        '" . json_encode($outputrow['metrics_conversion']) . "',
                        '" . $dbconn_stats->real_escape_string(json_encode($outputrow['metrics_rest'])) . "', '{$outputrow['cost']}',
                        '{$outputrow['impressions']}', '{$outputrow['reach']}',
                        '{$outputrow['clicks']}', '{$outputrow['engagements']}', '{$outputrow['video_views']}',
                        '{$outputrow['conversions']}', '{$platformid}',
                        cp.id_en_platform, cpo.id_en_platform,
                        cp.campana_root,  YEARWEEK('{$outputrow['date']}'),  date_format('{$outputrow['date']}', '%Y-%m')
                  FROM app_thesoci_9c37.creatividades c
                  JOIN app_thesoci_9c37.campaigns_platform_atomo cpo on cpo.id = c.atomo_id
                  JOIN app_thesoci_9c37.campaigns_platform cp ON cp.id = cpo.campana_platform_id
                  JOIN app_thesoci_9c37.ads_accounts aa ON aa.id = cpo.ad_account
                  WHERE c.id_en_platform = '{$outputrow['id_in_platform']}' and c.user_id = {$user_id}
                  ON DUPLICATE KEY UPDATE
                        cost                = '{$outputrow['cost']}',
                        impressions         = '{$outputrow['impressions']}' ,
                        reach               = '{$outputrow['reach']}',
                        clicks              = '{$outputrow['clicks']}',
                        engagements         = '{$outputrow['engagements']}',
                        video_views         = '{$outputrow['video_views']}',
                        conversions         = '{$outputrow['conversions']}',
                        metrics_delivery    = '" . json_encode($outputrow['metrics_delivery']) . "',
                        metrics_costs       = '" . json_encode($outputrow['metrics_costs']) . "',
                        metrics_engagement  = '" . json_encode($outputrow['metrics_engagement']) . "',
                        metrics_video       = '" . json_encode($outputrow['metrics_video']) . "',
                        metrics_conversion  = '" . json_encode($outputrow['metrics_conversion']) . "',
                        metrics_rest        = '" . $dbconn_stats->real_escape_string(json_encode($outputrow['metrics_rest'])) . "'";

    echo $sql.PHP_EOL;

    if (!$dbconn_stats->query($sql)) {
      var_dump( $inputraw);
      echo $dbconn_stats->error;
      //var_dump( $outputrow);
      echo PHP_EOL . PHP_EOL . $sql;
      //die();
    }
  }
  echo ' DB AdsxDia data updated > ';
  //$sql = "UPDATE adsconcierge_stats.background_job SET status = 'finished' WHERE id = {$job_id}";
  //$db->query($sql);
}

function helper_metrics_lineitem_day($platformid, $user_id, $customer_id = 0, $ad_account_id, $datos){
  global $dbconn_stats, $dbconn;
  if (!isset($metadata['currency'])){
    $metadata['currency'] = null;
  }

  foreach ($datos as $inputraw) {
    $outputrow = ['id_in_platform' => 0, 'campananame' => '', 'platformid' => $platformid, 'date' => '',
                  'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [],
                  'metrics_conversion' => [], 'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0,
                  'clicks' => 0, 'engagements' => 0, 'video_views' => 0, 'video_starts' => 0,
                  'currency' => $metadata['currency'], 'conversions' => 0];

    $outputrow = helper_metrics_keytranslator($platformid, 'lineitem', $inputraw, $outputrow, $metadata);

    echo "helper_metrics_lineitem_day ************" . PHP_EOL;

    if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
      continue;
    }

    $sql = "INSERT INTO adsconcierge_stats.platform_atomo_day (
                    user_id, platformid, customer_id, ad_account_id, campanaid,
                    atomoid, idenplatform, adccountid_pl, dia, unico,
                    metrics_delivery, metrics_costs, metrics_engagement, metrics_video,
                    metrics_conversion, metrics_rest, cost, impressions, reach,
                    clicks, engagements, video_views, conversions, plataforma,
                    campanaroot, yearweek, yearmonth)
                SELECT {$user_id}, {$outputrow['platformid']}, cpo.customer_id, aa.id, cpo.campana_platform_id,
                    cpo.id, '{$outputrow['id_in_platform']}',  aa.account_id, '{$outputrow['date']}', '" . md5($outputrow['platformid'] . $outputrow['id_in_platform'] . $outputrow['date'] . $user_id) . "',
                    '" . json_encode($outputrow['metrics_delivery']) . "', '" . json_encode($outputrow['metrics_costs']) . "', '" . json_encode($outputrow['metrics_engagement']) . "', '" . json_encode($outputrow['metrics_video']) . "',
                    '" . json_encode($outputrow['metrics_conversion']) . "', '" . $dbconn_stats->real_escape_string(json_encode($outputrow['metrics_rest'])) . "', '{$outputrow['cost']}', '{$outputrow['impressions']}', '{$outputrow['reach']}',
                    '{$outputrow['clicks']}',
                    '{$outputrow['engagements']}',
                    '{$outputrow['video_views']}',
                    '{$outputrow['conversions']}',
                    '{$platformid}',
                    cp.campana_root,  YEARWEEK('{$outputrow['date']}'),  date_format('{$outputrow['date']}', '%Y-%m')
                FROM app_thesoci_9c37.campaigns_platform_atomo cpo
                JOIN app_thesoci_9c37.campaigns_platform cp ON cp.id = cpo.campana_platform_id
                JOIN app_thesoci_9c37.ads_accounts aa ON aa.id = cpo.ad_account
                WHERE cpo.id_en_platform = '{$outputrow['id_in_platform']}' and cpo.user_id = {$user_id}
                ON DUPLICATE KEY UPDATE
                    cost                = '{$outputrow['cost']}',
                    impressions         = '{$outputrow['impressions']}' ,
                    reach               = '{$outputrow['reach']}',
                    clicks              = '{$outputrow['clicks']}',
                    engagements         = '{$outputrow['engagements']}',
                    video_views         = '{$outputrow['video_views']}',
                    conversions         = '{$outputrow['conversions']}',
                    metrics_delivery    = '" . json_encode($outputrow['metrics_delivery']) . "',
                    metrics_costs       = '" . json_encode($outputrow['metrics_costs']) . "',
                    metrics_engagement  = '" . json_encode($outputrow['metrics_engagement']) . "',
                    metrics_video       = '" . json_encode($outputrow['metrics_video']) . "',
                    metrics_conversion  = '" . json_encode($outputrow['metrics_conversion']) . "',
                    metrics_rest        = '" . $dbconn_stats->real_escape_string(json_encode($outputrow['metrics_rest'])) . "'";

    //var_dump($outputrow);die();
    if (!$dbconn_stats->query($sql)){
      echo 'ERROR INSERT ' . PHP_EOL;
      print_r();
    }
  }
  echo ' DB AtomoxDia data updated > ';
}

function global_metrics() {

}
