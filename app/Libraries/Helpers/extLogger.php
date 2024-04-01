<?php

function extLogger($record){

  $recordbase = ["version" => "1.1", "_X-OVH-TOKEN" => 'adb07d80-b313-4431-9648-0c1896d06506'] ;
  $recordbase['timestamp'] = time();
  $recordbase['host'] = 'SocialApis:'.PLATFORM_NAME;
  $recordbase['_version'] = '2';
  $recordbase['short_message'] = (isset($record['message'] ))? $record['message']:'-';
  $recordbase['full_message'] = json_encode($record);
  $recordbase['level'] = (isset($record['level'] ))? $record['level']:'DEBUG';
  $recordbase['category'] = (isset($record['category'] ))? $record['category']:'ERROR';

  if (isset($record['functionname'])){
    $recordbase['functionname']= $record['functionname'];
  }

  $parents=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
  $recordbase['function']= $parents[1]['function'];
  $recordbase['functionParent']= isset($parents[2])? $parents[2]['function']:'';

  $curl = curl_init('https://gra1.logs.ovh.com:9200/ldp-logs/message');

  curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic bG9ncy1uYS00MjY5OTpBcXVpbm9lbnRyYXMyMkA=", "Content-Type: application/json"));
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($recordbase));
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  $resp = curl_exec($curl);

  curl_close($curl);
}
