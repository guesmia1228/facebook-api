<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// function persistWrite($entityType, $entity_publicId, $taskId, $actionName, $fields, $result)
// {
// 	global $dbconn;
// 	persistTaskUpdate($taskId, $result);
// 	persistEntityChange($entityType, $entity_publicId,  $actionName, $fields, $result);
// 	return true;
// }

// function persistTaskUpdate($taskId, $result)
// {
// 	global $dbconn;
// }

// function persistEntityChange($entityType, $entity_publicId, $actionName, $fields, $result)
// {
// 	global $dbconn;

// 	switch ($entityType) {
// 		case 'campaign':
// 			$tableName =	'campaigns_platform';
// 			break;
// 		case 'atomo':
// 			$tableName =	'campaigns_platform_atomo';
// 			break;
// 		case 'creative':
// 			$tableName =	'creatividades';
// 			break;
// 	}

// 	/*
// 		$stmt = $dbconn->prepare("INSERT INTO `app_thesoci_9c37`.`ads_accounts` (`version`,`user_id`, `platform`, `app_id`, `account_id`, `name`, `platform_user_id`, `status`, `currency`, `metadata`, `customer_id`, `auth_id`) VALUES
// 		(?, ?,?,?,?,?,?,?,?,?,?,?) 	ON DUPLICATE KEY UPDATE `name`= ?,  `status`= ?, `platform_user_id`=?,  `metadata`= ? ");
// 		$stmt->bind_param("ssssssssssssssss",...[VERSIONCODIGO ,$user_id,
// 		$infoCredentials["platform"],
// 		$infoCredentials["app_id"],
// 		$accountItem['account_id'],
// 		$accountItem['name'],
// 		$infoCredentials['platform_user_id'],
// 		$status,
// 		$accountItem['currency'],
// 		$metadata,
// 		$infoCredentials["customer_id_default"],
// 		$infoCredentials["id"],
// 		$accountItem['name'],
// 		$status,
// 		$infoCredentials['platform_user_id'],
// 		$metadata]);
// 		try {
// 			$stmt->execute();
// 			return true;
// 		} catch (Exception $e) {
// 		print_r($e);
// 		}
// 	*/
// }
