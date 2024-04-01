<?php

use Illuminate\Support\Facades\DB;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function persistAccount($user_id, $accountItem, $infoCredentials)
{
	return persistAdAccount($user_id, $accountItem, $infoCredentials);
}

function persistAdAccount($user_id, $accountItem, $infoCredentials)
{

	global $dbconn;

	if (isset($accountItem['account_status']) && isset($accountItem['disable_reason'])) {
		$status = $accountItem['account_status'] . '|' . $accountItem['disable_reason'];
	} else {
		$status = 'NO_STATUS_RETRIEVE';
	}

	$metadata = json_encode($accountItem, true);
	$status = unificastatus('campana', $infoCredentials['platform'], $accountItem['status'], ['estado' => $accountItem['status'], 'record' => $accountItem]);

	// $stmt = $dbconn->prepare("INSERT INTO `ads_accounts`
	// 														(`version`,`user_id`, `platform`, `app_id`, `account_id`, `name`, `platform_user_id`, `status`, `currency`, `metadata`, `customer_id`, `auth_id`)
	// 														 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
	// 														 ON DUPLICATE KEY UPDATE `name`=?, `status`=?, `platform_user_id`=?, `metadata`=?");

	// $stmt->bind_param("ssssssssssssssss", ...[
	// 	VERSIONCODIGO,
	// 	$user_id,
	// 	$infoCredentials["platform"],
	// 	$infoCredentials["app_id"],
	// 	$accountItem['account_id'],
	// 	$accountItem['name'],
	// 	$infoCredentials['platform_user_id'],
	// 	$status,
	// 	$accountItem['currency'],
	// 	$metadata,
	// 	$infoCredentials["customer_id_default"],
	// 	$infoCredentials["id"],
	// 	$accountItem['name'],
	// 	$status,
	// 	$infoCredentials['platform_user_id'],
	// 	$metadata
	// ]);

	// try {
	// 	$stmt->execute();
	// 	return true;
	// } catch (Exception $e) {
	// 	print_r($e);
	// }

	try {
		DB::statement("
        INSERT INTO `ads_accounts`
        (`version`,`user_id`, `platform`, `app_id`, `account_id`, `name`, `platform_user_id`, `status`, `currency`, `metadata`, `customer_id`, `auth_id`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE `name`=?, `status`=?, `platform_user_id`=?, `metadata`=?
    ", [
			VERSIONCODIGO,
			$user_id,
			$infoCredentials["platform"],
			$infoCredentials["app_id"],
			$accountItem['account_id'],
			$accountItem['name'],
			$infoCredentials['platform_user_id'],
			$status,
			$accountItem['currency'],
			$metadata,
			$infoCredentials["customer_id_default"],
			$infoCredentials["id"],
			$accountItem['name'],
			$status,
			$infoCredentials['platform_user_id'],
			$metadata
		]);

		return true;
	} catch (Exception $e) {
		print_r($e);
	}
}

function persistPixel($itemData, $infoCredentials, $adaccount_platform_id)
{
	return persistProperties($itemData, $infoCredentials, $adaccount_platform_id, 'PIXEL');
}

function persistProperties($itemData, $infoCredentials, $adaccount_platform_id = null, $type = 'PAGE')
{
	global $dbconn;

	$adPixelData['status'] = 0; //todo: fixed in origin code
	$itemData['category'] = isset($itemData['category']) ? $itemData['category']  :  '-';
	$adPixelData['token'] = '-';
	$adPixelData['type'] = $type;

	// $stmt2 = $dbconn->prepare("INSERT INTO `properties_accounts`
	// 															( `version`,`type`, `user_id`, `platform`, `app_id`, `platform_user_id`,
	// 																`id_en_platform`, `token`,`name`,`status`,`category`, `metadata`,
	// 																`adaccount_id`, `auth_id` , `currency` )
	// 															VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
	// 															ON DUPLICATE KEY UPDATE  `name` = ?, `status` = ?, `platform_user_id` = ?, `metadata`= ? ");

	// $stmt2->bind_param("sssssssssssssssssss", ...[
	// 	VERSIONCODIGO,
	// 	$type,
	// 	$infoCredentials["user_id"],
	// 	$infoCredentials["platform"],
	// 	$infoCredentials["app_id"],
	// 	$infoCredentials['platform_user_id'],
	// 	$itemData['id_en_platform'],
	// 	$infoCredentials['access_token'],
	// 	$itemData['name'],
	// 	$adPixelData['status'],
	// 	$itemData['category'],
	// 	json_encode($itemData, true),
	// 	$adaccount_platform_id,
	// 	$infoCredentials["id"],
	// 	isset($itemData["currency"]) ? $itemData["currency"] : NULL,
	// 	$itemData['name'],
	// 	$adPixelData['status'],
	// 	$infoCredentials['platform_user_id'],
	// 	json_encode($itemData, true)
	// ]);

	// $stmt2->execute();

	// if ($stmt2->error != "") {
	// 	printf("Error: %s.\n", $stmt2->error);
	// 	extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt2->error, 'infoCredentials' => json_encode($infoCredentials)));
	// }

	try {
		DB::statement("
        INSERT INTO `properties_accounts`
        (`version`,`type`, `user_id`, `platform`, `app_id`, `platform_user_id`,
        `id_en_platform`, `token`,`name`,`status`,`category`, `metadata`,
        `adaccount_id`, `auth_id` , `currency` )
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE  `name` = ?, `status` = ?, `platform_user_id` = ?, `metadata`= ?
    ", [
			VERSIONCODIGO,
			$type,
			$infoCredentials["user_id"],
			$infoCredentials["platform"],
			$infoCredentials["app_id"],
			$infoCredentials['platform_user_id'],
			$itemData['id_en_platform'],
			$infoCredentials['access_token'],
			$itemData['name'],
			$adPixelData['status'],
			$itemData['category'],
			json_encode($itemData, true),
			$adaccount_platform_id,
			$infoCredentials["id"],
			isset($itemData["currency"]) ? $itemData["currency"] : NULL,
			$itemData['name'],
			$adPixelData['status'],
			$infoCredentials['platform_user_id'],
			json_encode($itemData, true)
		]);

		if (DB::getPdo()->errorCode() != "00000") {
			$errorInfo = DB::getPdo()->errorInfo();
			printf("Error: %s.\n", $errorInfo[2]);
			extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $errorInfo[2], 'infoCredentials' => json_encode($infoCredentials)));
		}
	} catch (Exception $e) {
		print_r($e);
	}

	echo ' Property - ' . $type . '- added -> ' . $itemData['name'] . PHP_EOL;

	// $stmt3 = $dbconn->prepare("INSERT INTO `properties_adsaccount_relations`
	// 															(	`type`,`platform`,`auth_id`, `auth_publicid`,`user_id`,`user_publicid`, `ad_account`, `ad_account_name`,`ad_account_publicid`,
	// 																`property_name`,`property_id`, `property_publicid`)
	// 															(SELECT ?,?,?,?, ?, ? ,account_id, name, public_id, ?,?,
	// 																	(SELECT public_id FROM `properties_accounts`
	// 																		WHERE id_en_platform=? AND auth_id=? LIMIT 1) AS property_public_id
	// 																	FROM `ads_accounts`
	// 																	WHERE account_id=? AND `auth_id`= ? limit 1)
	// 															ON DUPLICATE KEY UPDATE  `property_name` = ?");

	// // ON DUPLICATE KEY UPDATE `ad_account_name` = name, `property_name` = ?");
	// // $adc_publicid= getAdAccount_PublicID($adAccountData['id']);
	// $stmt3->bind_param("sssssssssssss", ...[
	// 	$type,
	// 	$infoCredentials["platform"],
	// 	$infoCredentials["id"],
	// 	$infoCredentials["auth_public_id"],
	// 	$infoCredentials["user_id"],
	// 	$infoCredentials["user_public_id"],
	// 	$itemData['name'],
	// 	$itemData['id_en_platform'],
	// 	$itemData['id_en_platform'],
	// 	$infoCredentials["id"],
	// 	$adaccount_platform_id,
	// 	$infoCredentials["id"],
	// 	$itemData['name']
	// ]);
	// $stmt3->execute();

	// if ($stmt3->error != "") {
	// 	extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt3->error, 'infoCredentials' => json_encode($infoCredentials)));
	// }

	try {
		DB::statement("
        INSERT INTO `properties_adsaccount_relations`
        (`type`,`platform`,`auth_id`, `auth_publicid`,`user_id`,`user_publicid`, `ad_account`, `ad_account_name`,`ad_account_publicid`,
        `property_name`,`property_id`, `property_publicid`)
        SELECT ?,?,?,?, ?, ? ,account_id, name, public_id, ?,?,
        (
            SELECT public_id
            FROM `properties_accounts`
            WHERE id_en_platform=? AND auth_id=?
            LIMIT 1
        ) AS property_public_id
        FROM `ads_accounts`
        WHERE account_id=? AND `auth_id`= ?
        LIMIT 1
        ON DUPLICATE KEY UPDATE  `property_name` = ?
    ", [
			$type,
			$infoCredentials["platform"],
			$infoCredentials["id"],
			$infoCredentials["auth_public_id"],
			$infoCredentials["user_id"],
			$infoCredentials["user_public_id"],
			$itemData['name'],
			$itemData['id_en_platform'],
			$itemData['id_en_platform'],
			$infoCredentials["id"],
			$adaccount_platform_id,
			$infoCredentials["id"],
			$itemData['name']
		]);

		if (DB::getPdo()->errorCode() != "00000") {
			$errorInfo = DB::getPdo()->errorInfo();
			extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $errorInfo[2], 'infoCredentials' => json_encode($infoCredentials)));
		}
	} catch (Exception $e) {
		print_r($e);
	}
}

function persistCampaign($itemDatabulk, $infoCredentials, $account = [], $platform = null)
{
	global $dbconn;

	$adPixelData['status'] = 0; //todo: fixed in origin code
	$adPixelData['category'] = '-';
	$adPixelData['token'] = '-';
	$adPixelData['type'] = 'PIXEL';

	// $stmt2 =  $dbconn->prepare("INSERT INTO campaigns_platform
	// 	(version,user_id, platform, app_id, auth_id, name, id_en_platform, status, activa,
	// 	currency, metadata, ad_account, campana_root, customer_id, source, start, end)
	// VALUES (?,?,?,?,?,?,?,?,?,?,?,?, ?,?, 'IMPORTED', ?, ? )
	// ON DUPLICATE KEY UPDATE status = ?, metadata = ?, campana_root = ?, customer_id = ?, activa= ? ");

	// $dbconn->begin_transaction();
	// foreach ($itemDatabulk as $itemData) {
	// 	$status = unificastatus('campana', $infoCredentials['platform'], $itemData['status'], ['estado' => $itemData['status'],  'otros' => $itemData['effective_status'], 'record' => $itemData]);
	// 	$isactive = unifyActive('campana', $infoCredentials['platform'], $itemData['status']);

	// 	$stmt2->bind_param("sssssssssssssssssssss", ...[
	// 		VERSIONCODIGO,
	// 		$infoCredentials['user_id'],
	// 		$infoCredentials['platform'],
	// 		$infoCredentials['app_id'],
	// 		$infoCredentials['id'],
	// 		$itemData['name'],
	// 		$itemData['id'],
	// 		$status,
	// 		$isactive,
	// 		isset($account['currency']) ? $account['currency'] : 'USD',
	// 		json_encode($itemData),
	// 		$account['id'],
	// 		//  $infoCredentials['user_id'],
	// 		isset($infoCredentials["campaign_root_default"]) ? $infoCredentials["campaign_root_default"] : '0',
	// 		$infoCredentials["customer_id_default"],
	// 		$itemData['start_time'],
	// 		$itemData['stop_time'],
	// 		$status,
	// 		json_encode($itemData),
	// 		isset($infoCredentials["campaign_root_default"]) ? $infoCredentials["campaign_root_default"] : '0',
	// 		$infoCredentials["customer_id_default"],
	// 		$isactive
	// 	]);

	// 	$stmt2->execute();
	// 	if ($stmt2->error != "") {
	// 		//     printf("Error: %s.\n", $stmt2->error);
	// 		$loggermessage = array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt2->error, 'infoCredentials' => json_encode($infoCredentials));
	// 		extLogger($loggermessage);
	// 	}
	// }
	// $dbconn->commit();

	try {
		DB::beginTransaction();

		foreach ($itemDatabulk as $itemData) {
			$status = unificastatus('campana', $infoCredentials['platform'], $itemData['status'], ['estado' => $itemData['status'], 'otros' => $itemData['effective_status'], 'record' => $itemData]);
			$isactive = unifyActive('campana', $infoCredentials['platform'], $itemData['status']);

			DB::statement("
							INSERT INTO campaigns_platform
							(version,user_id, platform, app_id, auth_id, name, id_en_platform, status, activa,
							currency, metadata, ad_account, campana_root, customer_id, source, start, end)
							VALUES (?,?,?,?,?,?,?,?,?,?,?,?, ?,?, 'IMPORTED', ?, ? )
							ON DUPLICATE KEY UPDATE status = ?, metadata = ?, campana_root = ?, customer_id = ?, activa= ?
					", [
				VERSIONCODIGO,
				$infoCredentials['user_id'],
				$infoCredentials['platform'],
				$infoCredentials['app_id'],
				$infoCredentials['id'],
				$itemData['name'],
				$itemData['id'],
				$status,
				$isactive,
				isset($account['currency']) ? $account['currency'] : 'USD',
				json_encode($itemData),
				$account['id'],
				isset($infoCredentials["campaign_root_default"]) ? $infoCredentials["campaign_root_default"] : '0',
				$infoCredentials["customer_id_default"],
				$itemData['start_time'],
				$itemData['stop_time'],
				$status,
				json_encode($itemData),
				isset($infoCredentials["campaign_root_default"]) ? $infoCredentials["campaign_root_default"] : '0',
				$infoCredentials["customer_id_default"],
				$isactive
			]);

			if (DB::getPdo()->errorCode() != "00000") {
				$errorInfo = DB::getPdo()->errorInfo();
				$loggermessage = array('level' => 'Error', 'category' => 'mysqlError', 'message' => $errorInfo[2], 'infoCredentials' => json_encode($infoCredentials));
				extLogger($loggermessage);
			}
		}

		DB::commit();
		echo 'fin persist' . PHP_EOL;
	} catch (Exception $e) {
		print_r($e);
		DB::rollBack();
	}

	echo 'fin persist' . PHP_EOL;
}

function persistAdset($itemDatabulk, $infoCredentials, $account = [], $platform = null)
{
	// global $dbconn;
	// $query = " INSERT INTO campaigns_platform_atomo (version, user_id,platform, app_id, auth_id, ad_account, id_en_platform, campana_platform_id, adaccount_platform_id,
	// 											property_platform_id, name, status, budget, budget_total, platform_properties_id, metadata, source, gender, geo_include, platform_placements,
	// 											start, end, optimizacion, age, device, bid_type,chargeby, currency,objetivo, puja_valor, product_type, customer_id, campana_root_id, campanaplatform_id)
	// 							VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'IMPORTED',?,?,?,?,?,?,?,?,?,?,?,?,?,?,
	// 							(SELECT customer_id FROM campaigns_platform WHERE id_en_platform =? AND user_id= ? AND platform = ? limit 1),
	// 							(SELECT campana_root FROM campaigns_platform WHERE id_en_platform =? AND user_id= ? AND platform = ? limit 1),
	// 							(SELECT id FROM campaigns_platform WHERE id_en_platform =? AND user_id= ? AND platform = ? limit 1)   )
	// 							ON DUPLICATE KEY UPDATE  status= ?,  metadata= ?, budget= ?, budget_total= ?, start= ?, end= ? ";

	// $stmt2 = $dbconn->prepare($query);
	// //campana_root_id,customer_id
	// $base_itemData = ['bid_unit' => null, 'bid_type' => null, 'product_type' => null, 'targeting' => ['publisher_platforms' => [], 'genders' => [], 'device' => [], 'device_platforms' => [], 'geo_locations' => []], 'bid_strategy' => null, 'chargeby' => null, 'property_platform_id' => null, 'currency' => $account['currency'], 'objective' => null];
	// $property_page_id = 0;
	// foreach ($itemDatabulk as $itemData) {
	// 	$itemData = array_replace_recursive($base_itemData,  $itemData);
	// 	if (!isset($itemData['targeting_age'])) {
	// 		$itemData['targeting_age'] = ['age_min' => 18, 'age_max' => 65];
	// 	}
	// 	//if (!isset($itemData['targeting']['genders'])) {		$itemData['targeting']['genders']=[]; }
	// 	switch ($platform) {
	// 		case 'FACEBOOK':
	// 			if (isset($itemData['targeting']['age_min'])) {
	// 				$itemData['targeting_age']['age_min'] = $itemData['targeting']['age_min'];
	// 			}
	// 			if (isset($itemData['targeting']['age_max'])) {
	// 				$itemData['targeting_age']['age_max'] = $itemData['targeting']['age_max'];
	// 			}
	// 			if (isset($itemData['billing_event'])) {
	// 				$itemData['chargeby']  = $itemData['billing_event'];
	// 			}
	// 			$property_page_id = isset($itemData['promoted_object']['page_id']) ?  $itemData['promoted_object']['page_id'] : null;
	// 			break;
	// 	}

	// 	/*		$adPixelData['status'] = 0; //todo: fixed in origin code
	// 	$adPixelData['category'] = '-';
	// 	$adPixelData['token'] = '-';
	// 	$adPixelData['type'] = 'PIXEL'; */

	// 	/*	SET @customer_id :=  ? ;	SET @campana_root_id := ? ;
	// 	SELECT @customer_id:=`customer_id`, @campana_root_id:=`campana_root`, @campanaplatform_id:=`id`  from campaigns_platform where id_en_platform =? and user_id= ? and platform = ? limit 1;
	// 	*/
	// 	$status = unificastatus('adset', $infoCredentials['platform'], $itemData['status'], ['estado' => $itemData['status'],  'otros' => $itemData['effective_status'], 'record' => $itemData]);
	// 	$device = unificadevice('adset', $infoCredentials['platform'], $itemData['targeting']['device']);

	// 	//print_r($account );

	// 	$stmt2->bind_param("sssssssssssssssssssssssssssssssssssssssssssss", ...[
	// 		VERSIONCODIGO,
	// 		$infoCredentials['user_id'], $infoCredentials['platform'], $infoCredentials['app_id'], $infoCredentials['id'], $account['id'],
	// 		$itemData['id'], $itemData['campaign_id'], $account['account_platform_id'], $property_page_id, $itemData['name'], $status,
	// 		$itemData['daily_budget'], $itemData['lifetime_budget'], json_encode($itemData['promoted_object']), json_encode($itemData),
	// 		json_encode($itemData['targeting']['genders']), json_encode($itemData['targeting']['geo_locations']), json_encode($itemData['targeting']['publisher_platforms']),
	// 		$itemData['start_time'], $itemData['end_time'], $itemData['optimization_goal'], json_encode($itemData['targeting_age']),
	// 		json_encode($device), $itemData['bid_strategy'], $itemData['chargeby'], $itemData['currency'], $itemData['objective'],
	// 		$itemData['puja_valor'], $itemData['product_type'], $itemData['campaign_id'], $infoCredentials['user_id'], $infoCredentials['platform'],
	// 		$itemData['campaign_id'], $infoCredentials['user_id'], $infoCredentials['platform'], $itemData['campaign_id'],	$infoCredentials['user_id'],
	// 		$infoCredentials['platform'], $status, json_encode($itemData), $itemData['daily_budget'], $itemData['lifetime_budget'], $itemData['start_time'],
	// 		$itemData['end_time']
	// 	]);

	// 	$stmt2->execute();

	// 	if ($stmt2->error != "") {
	// 		extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt2->error, 'infoCredentials' => json_encode($infoCredentials)));
	// 	}
	// }
	$query = "INSERT INTO campaigns_platform_atomo 
		(version, user_id, platform, app_id, auth_id, ad_account, id_en_platform, campana_platform_id, adaccount_platform_id,
		property_platform_id, name, status, budget, budget_total, platform_properties_id, metadata, source, gender, geo_include, platform_placements,
		start, end, optimizacion, age, device, bid_type, chargeby, currency, objetivo, puja_valor, product_type, customer_id, campana_root_id, campanaplatform_id)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'IMPORTED',?,?,?,?,?,?,?,?,?,?,?,?,?,?,
		(SELECT customer_id FROM campaigns_platform WHERE id_en_platform = ? AND user_id = ? AND platform = ? LIMIT 1),
		(SELECT campana_root FROM campaigns_platform WHERE id_en_platform = ? AND user_id = ? AND platform = ? LIMIT 1),
		(SELECT id FROM campaigns_platform WHERE id_en_platform = ? AND user_id = ? AND platform = ? LIMIT 1))
		ON DUPLICATE KEY UPDATE status = ?, metadata = ?, budget = ?, budget_total = ?, start = ?, end = ?";

	foreach ($itemDatabulk as $itemData) {
		$base_itemData = ['bid_unit' => null, 'bid_type' => null, 'product_type' => null, 'targeting' => ['publisher_platforms' => [], 'genders' => [], 'device' => [], 'device_platforms' => [], 'geo_locations' => []], 'bid_strategy' => null, 'chargeby' => null, 'property_platform_id' => null, 'currency' => $account['currency'], 'objective' => null];
		$itemData = array_replace_recursive($base_itemData, $itemData);

		if (!isset($itemData['targeting_age'])) {
			$itemData['targeting_age'] = ['age_min' => 18, 'age_max' => 65];
		}

		switch ($platform) {
			case 'FACEBOOK':
				if (isset($itemData['targeting']['age_min'])) {
					$itemData['targeting_age']['age_min'] = $itemData['targeting']['age_min'];
				}
				if (isset($itemData['targeting']['age_max'])) {
					$itemData['targeting_age']['age_max'] = $itemData['targeting']['age_max'];
				}
				if (isset($itemData['billing_event'])) {
					$itemData['chargeby'] = $itemData['billing_event'];
				}
				$property_page_id = isset($itemData['promoted_object']['page_id']) ? $itemData['promoted_object']['page_id'] : null;
				break;
		}

		$status = unificastatus('adset', $infoCredentials['platform'], $itemData['status'], ['estado' => $itemData['status'], 'otros' => $itemData['effective_status'], 'record' => $itemData]);
		$device = unificadevice('adset', $infoCredentials['platform'], $itemData['targeting']['device']);

		DB::statement($query, [
			VERSIONCODIGO,
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$infoCredentials['app_id'],
			$infoCredentials['id'],
			$account['id'],
			$itemData['id'],
			$itemData['campaign_id'],
			$account['account_platform_id'],
			$property_page_id,
			$itemData['name'],
			$status,
			$itemData['daily_budget'],
			$itemData['lifetime_budget'],
			json_encode($itemData['promoted_object']),
			json_encode($itemData),
			json_encode($itemData['targeting']['genders']),
			json_encode($itemData['targeting']['geo_locations']),
			json_encode($itemData['targeting']['publisher_platforms']),
			$itemData['start_time'],
			$itemData['end_time'],
			$itemData['optimization_goal'],
			json_encode($itemData['targeting_age']),
			json_encode($device),
			$itemData['bid_strategy'],
			$itemData['chargeby'],
			$itemData['currency'],
			$itemData['objective'],
			$itemData['puja_valor'],
			$itemData['product_type'],
			$itemData['campaign_id'],
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$itemData['campaign_id'],
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$itemData['campaign_id'],
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$status,
			json_encode($itemData),
			$itemData['daily_budget'],
			$itemData['lifetime_budget'],
			$itemData['start_time'],
			$itemData['end_time']
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

function persistAd($itemDatabulk, $infoCredentials, $account = [], $platform = null)
{
	// global $dbconn;

	// $query = "INSERT INTO creatividades (version, user_id,platform, app_id, auth_id, ad_account, id_en_platform, adset_platform_id, campana_platform_id,
	// 							adaccount_platform_id,property_platform_id,	name,content,url, status,platform_status,	previewurl,	metadata,source,
	// 							customer_id, campana_root, campanaplatform_id, atomo_id)
	// 						VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'IMPORTED',
	// 						(SELECT customer_id  FROM campaigns_platform_atomo WHERE id_en_platform =? AND user_id= ? AND platform = ? limit 1),
	// 						(SELECT campana_root_id  FROM campaigns_platform_atomo WHERE id_en_platform =? AND user_id= ? AND platform = ? limit 1),
	// 						(SELECT campanaplatform_id  FROM campaigns_platform_atomo WHERE id_en_platform =? AND user_id= ? AND platform = ? limit 1)  ,
	// 						(SELECT id FROM campaigns_platform_atomo WHERE id_en_platform =? AND user_id= ? AND platform = ? limit 1))
	// 						ON DUPLICATE KEY UPDATE platform_status=?, metadata=? ";

	// $stmt2 = $dbconn->prepare($query);
	// $base_itemData = [
	// 	'id' => null, 'name' => null, 'content' => null, 'description' => null, 'url' => null, 'type_platform' => null, 'banner' => null,
	// 	'media_type' => null, 'metadata' => json_encode([]), 'adset_id' => null, 'preview_shareable_link' => null
	// ];

	// echo "antes del foreach";
	// print_r($itemDatabulk);
	// foreach ($itemDatabulk as $itemData) {
	// 	print_r($itemData);
	// 	$property_page_id = 0;
	// 	$itemData = array_replace_recursive($base_itemData,  $itemData);
	// 	if (!isset($itemData['targeting_age'])) {
	// 		$itemData['targeting_age'] = ['age_min' => 18, 'age_max' => 65];
	// 	}

	// 	switch ($platform) {
	// 		case 'FACEBOOK':
	// 			$property_page_id = isset($itemData['promoted_object']['page_id']) ?  $itemData['promoted_object']['page_id'] : null;
	// 			break;
	// 	}

	// 	$status = unificastatus('ad', $infoCredentials['platform'], $itemData['status'], ['estado' => $itemData['status'],  'otros' => $itemData['effective_status'], 'record' => $itemData]);
	// 	//	echo substr_count($query,'?'  ).PHP_EOL;
	// 	//	echo strlen("ssssssssssssssssssssssssssss" ).PHP_EOL;
	// 	//$campana_id= isset(  $itemData['campaign_id']  )?   $itemData['campaign_id']: "Select campana_platform_id  from campaigns_platform_atomo where id_en_platform ='{$itemData['adset_id']}' and user_id='{$infoCredentials['user_id']}' and platform ='{$infoCredentials['platform']}' limit 1  ";
	// 	//	echo $campana_id;

	// 	//$itemData['name']=substr($itemData['name'],0, (                ) )

	// 	if (!isJson(json_encode($itemData))) {
	// 		// to-do logear en log ovh
	// 		//	 error_reporting(E_ALL  );
	// 		// echo 'abc'.PHP_EOL;
	// 		// 	print_r( $itemData );
	// 		//	 	print_r(json_encode($itemData,JSON_UNESCAPED_UNICODE));
	// 		// 	print_r(json_encode($itemData,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
	// 	}

	// 	$stmt2->bind_param("ssssssssssssssssssssssssssssssss", ...[
	// 		VERSIONCODIGO,
	// 		$infoCredentials['user_id'],  $infoCredentials['platform'], $infoCredentials['app_id'], $infoCredentials['id'], $account['id'],
	// 		$itemData['id'], $itemData['adset_id'], $itemData['campaign_id'], $account['account_platform_id'], $property_page_id,
	// 		$itemData['name'], $itemData['content'], $itemData['url'], $status, $status, $itemData['preview_shareable_link'], json_encode($itemData),
	// 		$itemData['adset_id'], $infoCredentials['user_id'], $infoCredentials['platform'],
	// 		$itemData['adset_id'], $infoCredentials['user_id'], $infoCredentials['platform'],
	// 		$itemData['adset_id'], $infoCredentials['user_id'], $infoCredentials['platform'],
	// 		$itemData['adset_id'], $infoCredentials['user_id'], $infoCredentials['platform'],
	// 		$status, json_encode($itemData)
	// 	]);

	// 	$stmt2->execute();
	// 	if ($stmt2->error != "") {
	// 		print_r($stmt2->error);
	// 		extLogger(array('level' => 'Error', 'category' => 'mysqlError', 'message' => $stmt2->error, 'infoCredentials' => json_encode($infoCredentials)));
	// 	}
	// }
	$query = "INSERT INTO creatividades 
		(version, user_id, platform, app_id, auth_id, ad_account, id_en_platform, adset_platform_id, campana_platform_id,
		adaccount_platform_id, property_platform_id, name, content, url, status, platform_status, previewurl, metadata, source,
		customer_id, campana_root, campanaplatform_id, atomo_id)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'IMPORTED',
		(SELECT customer_id FROM campaigns_platform_atomo WHERE id_en_platform = ? AND user_id = ? AND platform = ? LIMIT 1),
		(SELECT campana_root_id FROM campaigns_platform_atomo WHERE id_en_platform = ? AND user_id = ? AND platform = ? LIMIT 1),
		(SELECT campanaplatform_id FROM campaigns_platform_atomo WHERE id_en_platform = ? AND user_id = ? AND platform = ? LIMIT 1),
		(SELECT id FROM campaigns_platform_atomo WHERE id_en_platform = ? AND user_id = ? AND platform = ? LIMIT 1))
		ON DUPLICATE KEY UPDATE platform_status = ?, metadata = ?";

	$base_itemData = [
		'id' => null, 'name' => null, 'content' => null, 'description' => null, 'url' => null, 'type_platform' => null, 'banner' => null,
		'media_type' => null, 'metadata' => json_encode([]), 'adset_id' => null, 'preview_shareable_link' => null
	];

	foreach ($itemDatabulk as $itemData) {
		$property_page_id = 0;
		$itemData = array_replace_recursive($base_itemData, $itemData);

		if (!isset($itemData['targeting_age'])) {
			$itemData['targeting_age'] = ['age_min' => 18, 'age_max' => 65];
		}

		switch ($platform) {
			case 'FACEBOOK':
				$property_page_id = isset($itemData['promoted_object']['page_id']) ? $itemData['promoted_object']['page_id'] : null;
				break;
		}

		$status = unificastatus('ad', $infoCredentials['platform'], $itemData['status'], ['estado' => $itemData['status'], 'otros' => $itemData['effective_status'], 'record' => $itemData]);

		DB::statement($query, [
			VERSIONCODIGO,
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$infoCredentials['app_id'],
			$infoCredentials['id'],
			$account['id'],
			$itemData['id'],
			$itemData['adset_id'],
			$itemData['campaign_id'],
			$account['account_platform_id'],
			$property_page_id,
			$itemData['name'],
			$itemData['content'],
			$itemData['url'],
			$status,
			$status,
			$itemData['preview_shareable_link'],
			json_encode($itemData),
			'IMPORTED',
			$itemData['adset_id'],
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$itemData['adset_id'],
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$itemData['adset_id'],
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$itemData['adset_id'],
			$infoCredentials['user_id'],
			$infoCredentials['platform'],
			$status,
			json_encode($itemData)
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

function persistWrite($entityType, $entity_publicId, $taskId, $actionName, $fields, $result, $requestParams = null)
{
	global $dbconn;
	persistTaskUpdate($taskId, $result);
	persistEntityChange($entityType, $entity_publicId, $actionName, $fields, $taskId, $result, $requestParams);
	notification($entityType, $entity_publicId, $actionName, $fields, $taskId, $result, $requestParams);
	return true;
}

function persistTaskUpdate($taskId, $result)
{
	// global $dbconn;

	// $sql = "SELECT google_task
	//       FROM   `tasks`
	//       WHERE  id = '$taskId'";

	// $resultado = $dbconn->query($sql);
	// $fila = $resultado->fetch_assoc();

	// $tasks = $dbconn->prepare("UPDATE `tasks`
	// 		SET status = ?
	// 		WHERE id = ?");

	// $tasks->bind_param("ss", $result['status'], $taskId);

	// $gcloud_tasks = $dbconn->prepare("UPDATE `gcloud_tasks`
	// 		SET status = ?
	// 		WHERE task_id = ?");

	// $gcloud_tasks->bind_param("ss", $result['status'], $fila['google_task']);

	// try {
	// 	$tasks->execute();
	// 	$gcloud_tasks->execute();
	// 	return true;
	// } catch (Exception $e) {
	// 	print_r($e);
	// 	extLogger(['level' => 'Error', 'category' => 'Exception', 'message' => $e]);
	// }
	$taskSql = "SELECT google_task
		FROM `tasks`
		WHERE id = ?";
	$taskResult = DB::select($taskSql, [$taskId]);
	$taskRow = $taskResult[0];

	$tasksUpdateSql = "UPDATE `tasks`
					 SET status = ?
					 WHERE id = ?";
	$gcloudTasksUpdateSql = "UPDATE `gcloud_tasks`
								 SET status = ?
								 WHERE task_id = ?";

	try {
		DB::beginTransaction();

		DB::update($tasksUpdateSql, [$result['status'], $taskId]);
		DB::update($gcloudTasksUpdateSql, [$result['status'], $taskRow->google_task]);

		DB::commit();
		return true;
	} catch (Exception $e) {
		DB::rollBack();
		print_r($e);
		extLogger(['level' => 'Error', 'category' => 'Exception', 'message' => $e]);
	}
}

function persistEntityChange($entityType, $entity_publicId, $actionName, $fields, $taskId, $result, $requestParams = null)
{
	// global $dbconn;

	// $platform = strtolower(PLATFORM_NAME);

	// switch ($entityType) {
	// 	case 'campaign':
	// 		$tableName =	'campaigns_platform';

	// 		if ($actionName == 'campaign_create' && $result['status'] == 'ok') {
	// 			$tarea = [
	// 				"function" => "function-" . $platform . "-api",
	// 				"cola_trabajo" => $requestParams['cola_trabajo'],
	// 				"type" => "atomo_create",
	// 				"action" => "atomo_create",
	// 				"ad_account_platformId" => $requestParams['ad_account_platformId'],
	// 				"auth_id" => $requestParams['auth_id'],
	// 				"entity_platformId" => $fields['id_en_platform'],
	// 				"entity_publicId" => null,
	// 				"dataFields" => $requestParams['dataFields'],
	// 				"user_id" => $requestParams['user_id'],
	// 				"Execution_interval" => 0,
	// 				"taskId" => $requestParams['taskId']
	// 			];

	// 			echo json_encode($tarea);
	// 			echo PHP_EOL;
	// 			$infoCredentials['auth_public_id'] = $requestParams['auth_id'];
	// 			$infoCredentials['user_id'] = $requestParams['user_id'];

	// 			execGoogleTask($tarea, $infoCredentials);
	// 		}
	// 		break;
	// 	case 'atomo':
	// 		$tableName =	'campaigns_platform_atomo';

	// 		if ($actionName == 'adSet_create' && $result['status'] == 'ok') {
	// 			$tarea = [
	// 				"function" => "function-" . $platform . "-api",
	// 				"cola_trabajo" => $requestParams['cola_trabajo'],
	// 				"type" => "ad_create",
	// 				"action" => "ad_create",
	// 				"ad_account_platformId" => $requestParams['ad_account_platformId'],
	// 				"auth_id" => $requestParams['auth_id'],
	// 				"entity_platformId" => $fields['id_en_platform'],
	// 				"entity_publicId" => null,
	// 				"dataFields" => $requestParams['dataFields'],
	// 				"user_id" => $requestParams['user_id'],
	// 				"Execution_interval" => 0,
	// 				"taskId" => $requestParams['taskId']
	// 			];

	// 			echo json_encode($tarea);
	// 			echo PHP_EOL;
	// 			$infoCredentials['auth_public_id'] = $requestParams['auth_id'];
	// 			$infoCredentials['user_id'] = $requestParams['user_id'];

	// 			execGoogleTask($tarea, $infoCredentials);
	// 		}


	// 		break;
	// 	case 'creative':
	// 		$tableName =	'creatividades';
	// 		$resultado = $dbconn->query("SELECT running_tasks
	// 		        FROM   $tableName
	// 		        WHERE  id = '$entity_publicId'");
	// 		$fila = $resultado->fetch_assoc();
	// 		break;
	// }

	// if (isset($fila)) {
	// 	$sql = "SELECT running_tasks
	//         FROM   $tableName
	//         WHERE  public_id = '$entity_publicId'";

	// 	$resultado = $dbconn->query($sql);
	// 	$fila = $resultado->fetch_assoc();
	// }
	// $running_tasks = json_decode($fila['running_tasks']);
	// foreach ($running_tasks as $value) {
	// 	if ($value->id == $taskId) {
	// 		$value->status = $result['status'];
	// 	}
	// }

	// $columns = 'running_tasks=?, ';
	// $values[] = json_encode($running_tasks);
	// $bind = 's';
	// foreach ($fields as $key => $value) {
	// 	$key = ($key == 'daily_budget') ? 'budget' : $key;
	// 	$columns .= $key . '=?, ';
	// 	$values[] = $value;
	// 	$bind .= 's';
	// }

	// $where = '';
	// $values[] = $entity_publicId;
	// $bind .= 's';

	// if (
	// 	$tableName == 'campaigns_platform_atomo' ||
	// 	$tableName == 'creatividades'
	// ) {
	// 	$where = " WHERE id = ?";
	// } else {
	// 	$where = " WHERE public_id = ?";
	// }


	// if (defined('STDIN')) {
	// 	print_r($tableName);
	// 	print_r(PHP_EOL);
	// 	print_r($columns);
	// 	print_r(PHP_EOL);
	// 	print_r($values);
	// 	print_r(PHP_EOL);
	// }

	// try {

	// 	if ($where != '') {
	// 		$stmt = $dbconn->prepare("UPDATE " . $tableName . "
	// 				SET " . trim($columns, ', ') . $where);

	// 		$stmt->bind_param($bind, ...$values);
	// 		$stmt->execute();
	// 	}
	// 	extLogger(['level' => 'info', 'category' => 'persist Entity', 'short_message' => 'result', 'entityType' => $entityType, 'functionname' => $actionName, 'result' => json_encode($result)]);

	// 	return true;
	// } catch (Exception $e) {
	// 	print_r($e);
	// 	extLogger(['level' => 'Error', 'category' => 'Exception', 'message' => $e, 'infoCredentials' => json_encode($infoCredentials)]);
	// }
	$platform = strtolower(PLATFORM_NAME);

	switch ($entityType) {
		case 'campaign':
			$tableName = 'campaigns_platform';

			if ($actionName == 'campaign_create' && $result['status'] == 'ok') {
				$tarea = [
					"function" => "function-" . $platform . "-api",
					"cola_trabajo" => $requestParams['cola_trabajo'],
					"type" => "atomo_create",
					"action" => "atomo_create",
					"ad_account_platformId" => $requestParams['ad_account_platformId'],
					"auth_id" => $requestParams['auth_id'],
					"entity_platformId" => $fields['id_en_platform'],
					"entity_publicId" => null,
					"dataFields" => $requestParams['dataFields'],
					"user_id" => $requestParams['user_id'],
					"Execution_interval" => 0,
					"taskId" => $requestParams['taskId']
				];

				echo json_encode($tarea);
				echo PHP_EOL;

				$infoCredentials['auth_public_id'] = $requestParams['auth_id'];
				$infoCredentials['user_id'] = $requestParams['user_id'];

				execGoogleTask($tarea, $infoCredentials);
			}
			break;

		case 'atomo':
			$tableName = 'campaigns_platform_atomo';

			if ($actionName == 'adSet_create' && $result['status'] == 'ok') {
				$tarea = [
					"function" => "function-" . $platform . "-api",
					"cola_trabajo" => $requestParams['cola_trabajo'],
					"type" => "ad_create",
					"action" => "ad_create",
					"ad_account_platformId" => $requestParams['ad_account_platformId'],
					"auth_id" => $requestParams['auth_id'],
					"entity_platformId" => $fields['id_en_platform'],
					"entity_publicId" => null,
					"dataFields" => $requestParams['dataFields'],
					"user_id" => $requestParams['user_id'],
					"Execution_interval" => 0,
					"taskId" => $requestParams['taskId']
				];

				echo json_encode($tarea);
				echo PHP_EOL;

				$infoCredentials['auth_public_id'] = $requestParams['auth_id'];
				$infoCredentials['user_id'] = $requestParams['user_id'];

				execGoogleTask($tarea, $infoCredentials);
			}
			break;

		case 'creative':
			$tableName = 'creatividades';

			$resultado = DB::select("SELECT running_tasks
                                    FROM $tableName
                                    WHERE id = ?", [$entity_publicId]);

			$fila = $resultado[0];
			break;
	}

	if (isset($fila)) {
		$sql = "SELECT running_tasks
                FROM $tableName
                WHERE public_id = ?";

		$resultado = DB::select($sql, [$entity_publicId]);

		$fila = $resultado[0];
	}

	$running_tasks = json_decode($fila['running_tasks']);

	foreach ($running_tasks as $value) {
		if ($value->id == $taskId) {
			$value->status = $result['status'];
		}
	}

	$columns = 'running_tasks=?, ';
	$values[] = json_encode($running_tasks);
	$bind = 's';

	foreach ($fields as $key => $value) {
		$key = ($key == 'daily_budget') ? 'budget' : $key;
		$columns .= $key . '=?, ';
		$values[] = $value;
		$bind .= 's';
	}

	$where = '';
	$values[] = $entity_publicId;
	$bind .= 's';

	if (
		$tableName == 'campaigns_platform_atomo' ||
		$tableName == 'creatividades'
	) {
		$where = " WHERE id = ?";
	} else {
		$where = " WHERE public_id = ?";
	}

	if (defined('STDIN')) {
		print_r($tableName);
		print_r(PHP_EOL);
		print_r($columns);
		print_r(PHP_EOL);
		print_r($values);
		print_r(PHP_EOL);
	}

	try {
		if ($where != '') {
			$stmt = DB::statement("UPDATE $tableName
                                   SET " . trim($columns, ', ') . $where, $values);
		}

		extLogger([
			'level' => 'info',
			'category' => 'persist Entity',
			'short_message' => 'result',
			'entityType' => $entityType,
			'functionname' => $actionName,
			'result' => json_encode($result)
		]);

		return true;
	} catch (Exception $e) {
		print_r($e);
		extLogger([
			'level' => 'Error',
			'category' => 'Exception',
			'message' => $e,
			'infoCredentials' => json_encode($infoCredentials)
		]);
	}
}

function notification($entityType, $entity_publicId, $actionName, $fields, $taskId, $result, $requestParams)
{
	// global $API_ENDPOINT_BASE;
	// global $ENDPOINT_BASE;

	$API_ENDPOINT_BASE =  getenv('API_ENDPOINT_BASE', true)   ?:  'https://pre.adsconcierge.com/api';
	$ENDPOINT_BASE =  getenv('ENDPOINT_BASE', true)   ?:  'https://pre.adsconcierge.com';

	$data = [
		"userid" => $requestParams['user_id'],
		"entityType" => $entityType,
		"entity_publicId" => $entity_publicId,
		"actionName" => $actionName,
		"fields" => $fields,
		"taskId" => $taskId,
		"result" => $result,
		"requestParams" =>  $requestParams
	];

	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => $API_ENDPOINT_BASE . "/createNotify",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		echo "cURL Error #:" . $err;
		echo json_encode($data);
	} else {
		echo $response;
	}
}
