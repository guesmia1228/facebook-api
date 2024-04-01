<?php

use Illuminate\Support\Facades\DB;

function persistStats_platform_Campana_day($infoCredentials, $platformname, $AdAccountObj, $bulkdata, $metadata = ['currency' => null])
{
	// global $dbconn_stats, $dbconn;

	// $campanadata = [];
	// $campanaitem = [
	// 	'v' => 'base', 'id' => 0, 'customer_id' => $AdAccountObj['customer_id'], 'name' => '', 'ad_account' => $AdAccountObj['id'],
	// 	'campana_root' => 0, 'property_id' => 0, 'currency' => $AdAccountObj['currency']
	// ];
	// $i = 1;

	// foreach ($bulkdata as $inputraw) {
	// 	$outputrow = [
	// 		'id_in_platform' => 0, 'campananame' => '', 'plataforma' => $platformname, 'date' => '',
	// 		'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [], 'metrics_conversion' => [],
	// 		'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0, 'clicks' => 0, 'engagements' => 0, 'cpc' => 0, 'cpm' => 0, 'ctr' => 0,
	// 		'video_views' => 0,  'video_starts' => 0, 'video_completes' => 0, 'currency' => $metadata['currency'], 'conversions' => 0, 'objective' => '',
	// 		'device' => '', 'placement' => '', 'platform_position' => ''
	// 	];

	// 	$outputrow = helper_metrics_keytranslator($platformname, 'campana', $inputraw, $outputrow, $metadata);
	// 	$campana_id_en_plataforma = $outputrow['id_in_platform'];
	// 	if (!isset($campanadata[$campana_id_en_plataforma])) {
	// 		$item = get_campaign_by_platformid($platformname, $outputrow['id_in_platform']);
	// 		$campanadata[$outputrow['id_in_platform']] =	 isset($item['customer_id']) ? $item : $campanaitem;
	// 	}

	// 	if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
	// 		continue;
	// 	}

	// 	$query = "INSERT INTO adsconcierge_stats.platform_campana_day (
	// 								user_id, plataforma,  platformid,  ad_account_id, adccountid_pl,currency,
	// 								customer_id, campanaid, idenplatform, campananame,
	// 								dia, unico,  campanaroot, yearweek, yearmonth ,
	// 								impressions,cost,  clicks,reach,cpm,cpc,
	// 								engagements, video_views, conversions,
	// 								metrics_delivery, metrics_costs, metrics_engagement,
	// 								metrics_video, metrics_conversion, metrics_rest,
	// 								device, placement,objective,platform_position,metadata)
	// 							VALUES (?,?,?,?,?,?, ?,?,?,?,?,?,?,YEARWEEK(?),date_format(?,'%Y-%m'),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) AS item
	// 							ON DUPLICATE KEY UPDATE impressions=item.impressions, cost=item.cost, clicks=item.clicks, reach=item.reach, conversions=item.conversions, cpm=item.cpm, cpc=item.cpc";

	// 	$unico = md5($infoCredentials['user_id'] . $outputrow['plataforma'] . $outputrow['id_in_platform'] . $outputrow['date'] . $outputrow['device'] . $outputrow['placement'] . $outputrow['objective'] . $outputrow['platform_position']);

	// 	$stmt2 = $dbconn->prepare($query);
	// 	$stmt2->bind_param("sssssssssssssssisiississsssssssssss", ...[
	// 		$infoCredentials['user_id'], $outputrow['plataforma'], $outputrow['platformid'], $AdAccountObj['id'], $AdAccountObj['account_id'],
	// 		$campanadata[$campana_id_en_plataforma]['currency'], $campanadata[$campana_id_en_plataforma]['customer_id'],
	// 		$campanadata[$campana_id_en_plataforma]['id'], $campana_id_en_plataforma, $campanadata[$campana_id_en_plataforma]['name'],
	// 		$outputrow['date'], $unico,	$campanadata[$campana_id_en_plataforma]['campana_root'], $outputrow['date'], $outputrow['date'],
	// 		$outputrow['impressions'], $outputrow['cost'], $outputrow['clicks'], $outputrow['reach'], $outputrow['cpm'], $outputrow['cpc'],
	// 		$outputrow['engagements'], $outputrow['video_views'],	  $outputrow['conversions'],
	// 		json_encode($outputrow['metrics_delivery']), json_encode($outputrow['metrics_costs']), json_encode($outputrow['metrics_engagement']),
	// 		json_encode($outputrow['metrics_video']), json_encode($outputrow['metrics_conversion']),  json_encode($outputrow['metrics_rest']),
	// 		$outputrow['device'], $outputrow['placement'], $outputrow['objective'], $outputrow['platform_position'], json_encode($inputraw)
	// 	]);

	// 	$stmt2->execute();

	// 	if ($stmt2->error != "") {
	// 		extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt2->errorInfo(), 'infoCredentials' => json_encode($infoCredentials)));
	// 	}
	// }
	$campanadata = [];
	$campanaitem = [
		'v' => 'base', 'id' => 0, 'customer_id' => $AdAccountObj['customer_id'], 'name' => '', 'ad_account' => $AdAccountObj['id'],
		'campana_root' => 0, 'property_id' => 0, 'currency' => $AdAccountObj['currency']
	];

	$i = 1;

	foreach ($bulkdata as $inputraw) {
		$outputrow = [
			'id_in_platform' => 0, 'campananame' => '', 'plataforma' => $platformname, 'date' => '',
			'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [], 'metrics_conversion' => [],
			'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0, 'clicks' => 0, 'engagements' => 0, 'cpc' => 0, 'cpm' => 0, 'ctr' => 0,
			'video_views' => 0,  'video_starts' => 0, 'video_completes' => 0, 'currency' => $metadata['currency'], 'conversions' => 0, 'objective' => '',
			'device' => '', 'placement' => '', 'platform_position' => ''
		];

		$outputrow = helper_metrics_keytranslator($platformname, 'campana', $inputraw, $outputrow, $metadata);

		$campana_id_en_plataforma = $outputrow['id_in_platform'];

		if (!isset($campanadata[$campana_id_en_plataforma])) {
			$item = get_campaign_by_platformid($platformname, $outputrow['id_in_platform']);
			$campanadata[$outputrow['id_in_platform']] = isset($item['customer_id']) ? $item : $campanaitem;
		}

		if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
			continue;
		}

		$query = "INSERT INTO platform_campana_day (
                    user_id, plataforma,  platformid,  ad_account_id, adccountid_pl,currency,
                    customer_id, campanaid, idenplatform, campananame,
                    dia, unico,  campanaroot, yearweek, yearmonth ,
                    impressions,cost,  clicks,reach,cpm,cpc,
                    engagements, video_views, conversions,
                    metrics_delivery, metrics_costs, metrics_engagement,
                    metrics_video, metrics_conversion, metrics_rest,
                    device, placement,objective,platform_position,metadata)
                VALUES (?,?,?,?,?,?, ?,?,?,?,?,?,?,YEARWEEK(?),date_format(?,'%Y-%m'),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) AS item
                ON DUPLICATE KEY UPDATE impressions=item.impressions, cost=item.cost, clicks=item.clicks, reach=item.reach, conversions=item.conversions, cpm=item.cpm, cpc=item.cpc";

		$unico = md5($infoCredentials['user_id'] . $outputrow['plataforma'] . $outputrow['id_in_platform'] . $outputrow['date'] . $outputrow['device'] . $outputrow['placement'] . $outputrow['objective'] . $outputrow['platform_position']);

		DB::connection('mysql_stats')->statement($query, [
			$infoCredentials['user_id'], $outputrow['plataforma'], $outputrow['platformid'], $AdAccountObj['id'], $AdAccountObj['account_id'],
			$campanadata[$campana_id_en_plataforma]['currency'], $campanadata[$campana_id_en_plataforma]['customer_id'],
			$campanadata[$campana_id_en_plataforma]['id'], $campana_id_en_plataforma, $campanadata[$campana_id_en_plataforma]['name'],
			$outputrow['date'], $unico, $campanadata[$campana_id_en_plataforma]['campana_root'], $outputrow['date'], $outputrow['date'],
			$outputrow['impressions'], $outputrow['cost'], $outputrow['clicks'], $outputrow['reach'], $outputrow['cpm'], $outputrow['cpc'],
			$outputrow['engagements'], $outputrow['video_views'], $outputrow['conversions'],
			json_encode($outputrow['metrics_delivery']), json_encode($outputrow['metrics_costs']), json_encode($outputrow['metrics_engagement']),
			json_encode($outputrow['metrics_video']), json_encode($outputrow['metrics_conversion']), json_encode($outputrow['metrics_rest']),
			$outputrow['device'], $outputrow['placement'], $outputrow['objective'], $outputrow['platform_position'], json_encode($inputraw)
		]);

		if (DB::getPdo()->errorCode() != "00000") {
			$errorInfo = DB::getPdo()->errorInfo();
			extLogger([
				'level' => 'Error',
				'category' => 'mysqlError',
				'message' => $errorInfo[2],
				'infoCredentials' => json_encode($infoCredentials)
			]);
		}
	}
}

function persistStats_platform_Atomo_day($infoCredentials, $platformname, $AdAccountObj, $bulkdata, $metadata = ['currency' => null])
{
	// global $dbconn_stats, $dbconn;
	// $itemLocalData = [];
	// $localitem = [
	// 	'v' => 'base', 'id' => 0, 'customer_id' => $AdAccountObj['customer_id'], 'name' => '', 'ad_account' => $AdAccountObj['id'],
	// 	'campana_root' => 0, 'property_id' => 0, 'currency' => $AdAccountObj['currency']
	// ];
	// $i = 1;

	// foreach ($bulkdata as $inputraw) {
	// 	$outputrow = [
	// 		'id_in_platform' => 0, 'campananame' => '', 'plataforma' => $platformname, 'date' => '',
	// 		'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [], 'metrics_conversion' => [],
	// 		'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0, 'clicks' => 0, 'engagements' => 0, 'cpc' => 0, 'cpm' => 0, 'ctr' => 0,
	// 		'video_views' => 0, 'video_starts' => 0, 'video_completes' => 0, 'currency' => $metadata['currency'], 'conversions' => 0, 'objective' => '',
	// 		'device' => '', 'placement' => '', 'platform_position' => ''
	// 	];

	// 	$outputrow = helper_metrics_keytranslator($platformname, 'adset', $inputraw, $outputrow, $metadata);
	// 	$item_id_en_plataforma = $outputrow['id_in_platform'];
	// 	if (!isset($itemLocalData[$item_id_en_plataforma])) {
	// 		$item = get_atomo_by_platformid($platformname, $outputrow['id_in_platform']);
	// 		$itemLocalData[$outputrow['id_in_platform']] =	 isset($item['customer_id']) ? $item  : $localitem;
	// 	}

	// 	print_r($inputraw);

	// 	if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
	// 		continue;
	// 	}

	// 	$query = "INSERT INTO adsconcierge_stats.platform_atomo_day (
	// 									user_id, plataforma,  platformid,  ad_account_id, adccountid_pl,currency,
	// 									customer_id, campanaid, idenplatform, adset_name,
	// 									dia, unico,  campanaroot, yearweek, yearmonth ,
	// 									impressions,cost,  clicks,reach,cpm,cpc,
	// 									engagements, video_views, conversions,	video_starts, video_completes,
	// 									metrics_delivery, metrics_costs, metrics_engagement,
	// 									metrics_video, metrics_conversion, metrics_rest,
	// 									device, placement,objective,platform_position,metadata)
	// 								VALUES(?,?,?,?,?,?, ?,?,?,?,?,?,?,YEARWEEK(?),date_format(?,'%Y-%m'),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) AS item
	// 								ON DUPLICATE KEY UPDATE impressions=item.impressions, cost=item.cost, clicks=item.clicks, reach=item.reach, conversions=item.conversions,
	// 																				cpm=item.cpm, cpc=item.cpc";

	// 	$unico = md5($infoCredentials['user_id'] . $outputrow['plataforma'] . $outputrow['id_in_platform'] . $outputrow['date'] . $outputrow['device'] . $outputrow['placement'] . $outputrow['objective'] . $outputrow['platform_position']);

	// 	$stmt2 = $dbconn->prepare($query);
	// 	$stmt2->bind_param("sssssssssssssssisiississsssssssssssss", ...[
	// 		$infoCredentials['user_id'],  $outputrow['plataforma'], $outputrow['platformid'],  $AdAccountObj['id'],  $AdAccountObj['account_id'],
	// 		$itemLocalData[$item_id_en_plataforma]['currency'], $itemLocalData[$item_id_en_plataforma]['customer_id'],
	// 		$itemLocalData[$item_id_en_plataforma]['id'],  $item_id_en_plataforma, $itemLocalData[$item_id_en_plataforma]['name'],
	// 		$outputrow['date'], $unico,	$itemLocalData[$item_id_en_plataforma]['campana_root'], $outputrow['date'], $outputrow['date'],
	// 		$outputrow['impressions'], $outputrow['cost'], $outputrow['clicks'], $outputrow['reach'], $outputrow['cpm'], $outputrow['cpc'],
	// 		$outputrow['engagements'], $outputrow['video_views'], $outputrow['conversions'], $outputrow['video_starts'], $outputrow['video_completes'],
	// 		json_encode($outputrow['metrics_delivery']), json_encode($outputrow['metrics_costs']), json_encode($outputrow['metrics_engagement']),
	// 		json_encode($outputrow['metrics_video']), json_encode($outputrow['metrics_conversion']), json_encode($outputrow['metrics_rest']),
	// 		$outputrow['device'], $outputrow['placement'], $outputrow['objective'], $outputrow['platform_position'], json_encode($inputraw)
	// 	]);

	// 	$stmt2->execute();
	// 	if ($stmt2->error != "") {
	// 		extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt2->errorInfo(), 'infoCredentials' => json_encode($infoCredentials)));
	// 	}
	// }
	$itemLocalData = [];
	$localitem = [
		'v' => 'base', 'id' => 0, 'customer_id' => $AdAccountObj['customer_id'], 'name' => '', 'ad_account' => $AdAccountObj['id'],
		'campana_root' => 0, 'property_id' => 0, 'currency' => $AdAccountObj['currency']
	];

	$i = 1;

	foreach ($bulkdata as $inputraw) {
		$outputrow = [
			'id_in_platform' => 0, 'campananame' => '', 'plataforma' => $platformname, 'date' => '',
			'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [], 'metrics_conversion' => [],
			'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0, 'clicks' => 0, 'engagements' => 0, 'cpc' => 0, 'cpm' => 0, 'ctr' => 0,
			'video_views' => 0, 'video_starts' => 0, 'video_completes' => 0, 'currency' => $metadata['currency'], 'conversions' => 0, 'objective' => '',
			'device' => '', 'placement' => '', 'platform_position' => ''
		];

		$outputrow = helper_metrics_keytranslator($platformname, 'adset', $inputraw, $outputrow, $metadata);

		$item_id_en_plataforma = $outputrow['id_in_platform'];

		if (!isset($itemLocalData[$item_id_en_plataforma])) {
			$item = get_atomo_by_platformid($platformname, $outputrow['id_in_platform']);
			$itemLocalData[$outputrow['id_in_platform']] = isset($item['customer_id']) ? $item : $localitem;
		}

		if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
			continue;
		}

		$query = "INSERT INTO platform_atomo_day (
                    user_id, plataforma,  platformid,  ad_account_id, adccountid_pl,currency,
                    customer_id, campanaid, idenplatform, adset_name,
                    dia, unico,  campanaroot, yearweek, yearmonth ,
                    impressions,cost,  clicks,reach,cpm,cpc,
                    engagements, video_views, conversions,	video_starts, video_completes,
                    metrics_delivery, metrics_costs, metrics_engagement,
                    metrics_video, metrics_conversion, metrics_rest,
                    device, placement,objective,platform_position,metadata)
                VALUES (?,?,?,?,?,?, ?,?,?,?,?,?,?,YEARWEEK(?),date_format(?,'%Y-%m'),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) AS item
                ON DUPLICATE KEY UPDATE impressions=item.impressions, cost=item.cost, clicks=item.clicks, reach=item.reach, conversions=item.conversions,
                cpm=item.cpm, cpc=item.cpc";

		$unico = md5($infoCredentials['user_id'] . $outputrow['plataforma'] . $outputrow['id_in_platform'] . $outputrow['date'] . $outputrow['device'] . $outputrow['placement'] . $outputrow['objective'] . $outputrow['platform_position']);

		DB::connection('mysql_stats')->statement($query, [
			$infoCredentials['user_id'], $outputrow['plataforma'], $outputrow['platformid'], $AdAccountObj['id'], $AdAccountObj['account_id'],
			$itemLocalData[$item_id_en_plataforma]['currency'], $itemLocalData[$item_id_en_plataforma]['customer_id'],
			$itemLocalData[$item_id_en_plataforma]['id'], $item_id_en_plataforma, $itemLocalData[$item_id_en_plataforma]['name'],
			$outputrow['date'], $unico, $itemLocalData[$item_id_en_plataforma]['campana_root'], $outputrow['date'], $outputrow['date'],
			$outputrow['impressions'], $outputrow['cost'], $outputrow['clicks'], $outputrow['reach'], $outputrow['cpm'], $outputrow['cpc'],
			$outputrow['engagements'], $outputrow['video_views'], $outputrow['conversions'], $outputrow['video_starts'], $outputrow['video_completes'],
			json_encode($outputrow['metrics_delivery']), json_encode($outputrow['metrics_costs']), json_encode($outputrow['metrics_engagement']),
			json_encode($outputrow['metrics_video']), json_encode($outputrow['metrics_conversion']), json_encode($outputrow['metrics_rest']),
			$outputrow['device'], $outputrow['placement'], $outputrow['objective'], $outputrow['platform_position'], json_encode($inputraw)
		]);

		if (DB::getPdo()->errorCode() != "00000") {
			$errorInfo = DB::getPdo()->errorInfo();
			extLogger([
				'level' => 'Error',
				'category' => 'mysqlError',
				'message' => $errorInfo[2],
				'infoCredentials' => json_encode($infoCredentials)
			]);
		}
	}
}

function persistStats_platform_Ads_day($infoCredentials, $platformname, $AdAccountObj, $bulkdata, $metadata = ['currency' => null])
{
	// global $dbconn_stats, $dbconn;
	// $itemLocalData = [];
	// $localitem = [
	// 	'v' => 'base', 'id' => 0, 'customer_id' => $AdAccountObj['customer_id'], 'name' => '', 'ad_account' => $AdAccountObj['id'],
	// 	'campana_root' => 0, 'property_id' => 0, 'currency' => $AdAccountObj['currency']
	// ];
	// $i = 1;

	// $query = "INSERT INTO adsconcierge_stats.platform_ads_day (
	// 							user_id, plataforma,  platformid,  ad_account_id, adccountid_pl,currency,
	// 							customer_id, campanaid, idenplatform, ad_name, campana_id_enplatform, atomo_id_enplatform,
	// 							dia, unico,  campanaroot, yearweek, yearmonth ,
	// 							impressions,cost,  clicks,reach,cpm,cpc,
	// 							engagements, video_views, conversions,	video_starts, video_completes,
	// 							metrics_delivery, metrics_costs, metrics_engagement,
	// 							metrics_video, metrics_conversion, metrics_rest,
	// 							device, placement,objective,platform_position,metadata,cost_eur)
	// 						VALUES (?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,YEARWEEK(?),date_format(?,'%Y-%m'),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) AS item
	// 						ON DUPLICATE KEY UPDATE impressions = item.impressions, cost=item.cost, clicks=item.clicks, reach=item.reach, conversions=item.conversions,
	// 																		cpm=item.cpm, cpc=item.cpc, cost_eur=item.cost_eur  ";

	// foreach ($bulkdata as $inputraw) {
	// 	$outputrow = [
	// 		'id_in_platform' => 0, 'ad_id' => '', 'ad_name' => '', 'campananame' => '', 'campaign_id' => '', 'adset_id' => '', 'plataforma' => $platformname, 'date' => '',
	// 		'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [], 'metrics_conversion' => [],
	// 		'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0, 'clicks' => 0, 'engagements' => 0, 'cpc' => 0, 'cpm' => 0, 'ctr' => 0,
	// 		'video_views' => 0, 'video_starts' => 0, 'video_completes' => 0, 'currency' => $AdAccountObj['currency'], 'conversions' => 0,
	// 		'device' => '', 'placement' => '', 'platform_position' => '', 'objective' => 'ukn', 'cost_eur' => null
	// 	];

	// 	$outputrow = array_merge_recursive($outputrow, $localitem);

	// 	$outputrow = helper_metrics_keytranslator($platformname, 'ad', $inputraw, $outputrow, $metadata);

	// 	$item_id_en_plataforma = $outputrow['id_in_platform'];
	// 	if (!isset($itemLocalData[$item_id_en_plataforma])) {
	// 		$item = get_ad_by_platformid($platformname, $outputrow['id_in_platform']);
	// 		$itemLocalData[$outputrow['id_in_platform']] =	 isset($item['customer_id']) ? $item  : $localitem;
	// 	}

	// 	if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
	// 		//desactivado para tener coherencia en las graficas
	// 		//   continue;
	// 	}

	// 	if (isset($AdAccountObj['currencyEurRate'])) {
	// 		$outputrow['cost_eur'] = $outputrow['cost'] / $AdAccountObj['currencyEurRate'];
	// 	}

	// 	$unico = md5($infoCredentials['user_id'] . $outputrow['plataforma'] . $outputrow['id_in_platform'] . $outputrow['date'] . $outputrow['device'] . $outputrow['placement'] . $outputrow['objective'] . $outputrow['platform_position']);

	// 	$itemname =  $itemLocalData[$item_id_en_plataforma]['name'] != '' ? $itemLocalData[$item_id_en_plataforma]['name'] : $outputrow['ad_name'];
	// 	$stmt2 = $dbconn->prepare($query);
	// 	$stmt2->bind_param("sssssssssssssssssisiississssssssssssssss", ...[
	// 		$infoCredentials['user_id'],  $outputrow['plataforma'],   $outputrow['platformid'],  $AdAccountObj['id'],  $AdAccountObj['account_id'], $AdAccountObj['currency'],
	// 		$itemLocalData[$item_id_en_plataforma]['customer_id'], $itemLocalData[$item_id_en_plataforma]['id'], $item_id_en_plataforma, $itemname, $outputrow['campaign_id'], $outputrow['adset_id'],
	// 		$outputrow['date'], $unico,	$itemLocalData[$item_id_en_plataforma]['campana_root'], $outputrow['date'], $outputrow['date'],
	// 		$outputrow['impressions'],	round($outputrow['cost'], 4),	  $outputrow['clicks'], $outputrow['reach'], $outputrow['cpm'], $outputrow['cpc'],
	// 		$outputrow['engagements'],		  $outputrow['video_views'],	  $outputrow['conversions'],	  $outputrow['video_starts'],	  $outputrow['video_completes'],
	// 		json_encode($outputrow['metrics_delivery']),		 json_encode($outputrow['metrics_costs']),	   json_encode($outputrow['metrics_engagement']),
	// 		json_encode($outputrow['metrics_video']),		 json_encode($outputrow['metrics_conversion']),	   json_encode($outputrow['metrics_rest']),
	// 		$outputrow['device'], $outputrow['placement'], $outputrow['objective'], 'UP' . $outputrow['platform_position'], json_encode($inputraw),
	// 		round($outputrow['cost_eur'], 4)
	// 	]);

	// 	$stmt2->execute();

	// 	if ($stmt2->error != "") {
	// 		extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt2->errorInfo(), 'infoCredentials' => json_encode($infoCredentials)));
	// 	}
	// }
	$itemLocalData = [];
	$localitem = [
		'v' => 'base', 'id' => 0, 'customer_id' => $AdAccountObj['customer_id'], 'name' => '', 'ad_account' => $AdAccountObj['id'],
		'campana_root' => 0, 'property_id' => 0, 'currency' => $AdAccountObj['currency']
	];

	$i = 1;

	$query = "INSERT INTO platform_ads_day (
        user_id, plataforma,  platformid,  ad_account_id, adccountid_pl,currency,
        customer_id, campanaid, idenplatform, ad_name, campana_id_enplatform, atomo_id_enplatform,
        dia, unico,  campanaroot, yearweek, yearmonth ,
        impressions,cost,  clicks,reach,cpm,cpc,
        engagements, video_views, conversions,	video_starts, video_completes,
        metrics_delivery, metrics_costs, metrics_engagement,
        metrics_video, metrics_conversion, metrics_rest,
        device, placement,objective,platform_position,metadata,cost_eur)
        VALUES (?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,YEARWEEK(?),date_format(?,'%Y-%m'),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) AS item
        ON DUPLICATE KEY UPDATE impressions = item.impressions, cost=item.cost, clicks=item.clicks, reach=item.reach, conversions=item.conversions,
        cpm=item.cpm, cpc=item.cpc, cost_eur=item.cost_eur  ";

	foreach ($bulkdata as $inputraw) {
		$outputrow = [
			'id_in_platform' => 0, 'ad_id' => '', 'ad_name' => '', 'campananame' => '', 'campaign_id' => '', 'adset_id' => '', 'plataforma' => $platformname, 'date' => '',
			'metrics_delivery' => [], 'metrics_costs' => [], 'metrics_engagement' => [], 'metrics_video' => [], 'metrics_conversion' => [],
			'metrics_rest' => [], 'cost' => 0, 'impressions' => 0, 'reach' => 0, 'clicks' => 0, 'engagements' => 0, 'cpc' => 0, 'cpm' => 0, 'ctr' => 0,
			'video_views' => 0, 'video_starts' => 0, 'video_completes' => 0, 'currency' => $AdAccountObj['currency'], 'conversions' => 0,
			'device' => '', 'placement' => '', 'platform_position' => '', 'objective' => 'ukn', 'cost_eur' => null
		];

		$outputrow = array_merge_recursive($outputrow, $localitem);
		$outputrow = helper_metrics_keytranslator($platformname, 'ad', $inputraw, $outputrow, $metadata);

		$item_id_en_plataforma = $outputrow['id_in_platform'];

		if (!isset($itemLocalData[$item_id_en_plataforma])) {
			$item = get_ad_by_platformid($platformname, $outputrow['id_in_platform']);
			$itemLocalData[$outputrow['id_in_platform']] = isset($item['customer_id']) ? $item : $localitem;
		}

		if ($outputrow['impressions'] == 0 && $outputrow['video_views'] == 0 && $outputrow['reach'] == 0 && $outputrow['clicks'] == 0 && $outputrow['engagements'] == 0) {
			//desactivado para tener coherencia en las graficas
			//   continue;
		}

		if (isset($AdAccountObj['currencyEurRate'])) {
			$outputrow['cost_eur'] = $outputrow['cost'] / $AdAccountObj['currencyEurRate'];
		}

		$unico = md5($infoCredentials['user_id'] . $outputrow['plataforma'] . $outputrow['id_in_platform'] . $outputrow['date'] . $outputrow['device'] . $outputrow['placement'] . $outputrow['objective'] . $outputrow['platform_position']);

		$itemname =  $itemLocalData[$item_id_en_plataforma]['name'] != '' ? $itemLocalData[$item_id_en_plataforma]['name'] : $outputrow['ad_name'];

		DB::connection('mysql_stats')->statement($query, [
			$infoCredentials['user_id'], $outputrow['plataforma'], $outputrow['platformid'], $AdAccountObj['id'], $AdAccountObj['account_id'], $AdAccountObj['currency'],
			$itemLocalData[$item_id_en_plataforma]['customer_id'], $itemLocalData[$item_id_en_plataforma]['id'], $item_id_en_plataforma, $itemname, $outputrow['campaign_id'], $outputrow['adset_id'],
			$outputrow['date'], $unico, $itemLocalData[$item_id_en_plataforma]['campana_root'], $outputrow['date'], $outputrow['date'],
			$outputrow['impressions'], round($outputrow['cost'], 4), $outputrow['clicks'], $outputrow['reach'], $outputrow['cpm'], $outputrow['cpc'],
			$outputrow['engagements'], $outputrow['video_views'], $outputrow['conversions'], $outputrow['video_starts'], $outputrow['video_completes'],
			json_encode($outputrow['metrics_delivery']), json_encode($outputrow['metrics_costs']), json_encode($outputrow['metrics_engagement']),
			json_encode($outputrow['metrics_video']), json_encode($outputrow['metrics_conversion']), json_encode($outputrow['metrics_rest']),
			$outputrow['device'], $outputrow['placement'], $outputrow['objective'], 'UP' . $outputrow['platform_position'], json_encode($inputraw),
			round($outputrow['cost_eur'], 4)
		]);

		if (DB::getPdo()->errorCode() != "00000") {
			$errorInfo = DB::getPdo()->errorInfo();
			extLogger([
				'level' => 'Error',
				'category' => 'mysqlError',
				'message' => $errorInfo[2],
				'infoCredentials' => json_encode($infoCredentials)
			]);
		}
	}
}
