<?php

/** @var \Laravel\Lumen\Routing\Router $router */


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/', 'ReadController@handler');
    $router->post('/retrieve_all', 'ReadController@retrieveAllHandler');
    $router->post('/get_account_adsaccounts', 'ReadController@getAccountAdsAccountsHandler');
    $router->post('/get_account_properties', 'ReadController@getPropertiesHandler');
    $router->post('/get_properties', 'ReadController@getPropertiesHandler');
    $router->post('/get_account_pixels', 'ReadController@getPixelsHandler');
    $router->post('/get_pixels', 'ReadController@getPixelsHandler');
    $router->post('/get_account_entities', 'ReadController@getAccountEntitiesHandler');
    $router->post('/get_account_campaigns', 'ReadController@getCampaignsHandler');
    $router->post('/get_campaigns', 'ReadController@getCampaignsHandler');
    $router->post('/get_adsets', 'ReadController@getAdsetsHandler');
    $router->post('/get_account_adsets', 'ReadController@getAdsetsHandler');
    $router->post('/get_account_ads', 'ReadController@getAccountAdsHandler');
    $router->post('/get_creativities', 'ReadController@getCreativitiesHandler');
    $router->post('/get_stats_campaigns', 'ReadController@getStatsCampaignsHandler');
    $router->post('/get_account_stats_campaigns', 'ReadController@getStatsCampaignsHandler');
    $router->post('/get_stats_adSet', 'ReadController@getStatsAdSetHandler');
    $router->post('/get_account_stats_adsets', 'ReadController@getStatsAdSetHandler');
    $router->post('/get_stats_ad', 'ReadController@getStatsAdHandler');
    $router->post('/get_account_stats_ads', 'ReadController@getStatsAdHandler');
    $router->post('/retrieve_stats_all', 'ReadController@retrieveStatsAllHandler');
    $router->post('/retrieve_all_accounts_stats', 'ReadController@retrieveStatsAllHandler');
});

$router->group(['prefix' => 'apicreate'], function () use ($router) {
    $router->post('/', 'WriteController@handler');
    $router->post('/entity_update_geo', 'WriteController@entityUpdateGeoHandler');
    $router->post('/entity_update_language', 'WriteController@entityUpdateLanguageHandler');
    $router->post('/entity_update_interests', 'WriteController@entityUpdateInterestsHandler');
    $router->post('/entity_update_gender', 'WriteController@entityUpdateGenderHandler');
    $router->post('/entity_update_audience', 'WriteController@entityUpdateAudienceHandler');
    $router->post('/campaign_update_fields', 'WriteController@campaignUpdateFieldsHandler');
    $router->post('/campaign_update_budget', 'WriteController@campaignUpdateBudgetHandler');
    $router->post('/campaign_status_to_active', 'WriteController@campaignStatusToActiveHandler');
    $router->post('/campaign_status_to_pause', 'WriteController@campaignStatusToPauseHandler');
    $router->post('/campaign_status_to_stop', 'WriteController@campaignStatusToStopHandler');
    $router->post('/campaign_status_to_archive', 'WriteController@campaignStatusToArchiveHandler');
    $router->post('/campaign_delete', 'WriteController@campaignDeleteHandler');
    $router->post('/campaign_create', 'WriteController@campaignCreateHandler');
    $router->post('/atomo_update_fields', 'WriteController@atomoUpdateFieldsHandler');
    $router->post('/atomo_update_budget', 'WriteController@atomoUpdateBudgetHandler');
    $router->post('/atomo_status_to_active', 'WriteController@atomoStatusToActiveHandler');
    $router->post('/atomo_status_to_pause', 'WriteController@atomoStatusToPauseHandler');
    $router->post('/atomo_status_to_stop', 'WriteController@atomoStatusToStopHandler');
    $router->post('/atomo_status_to_archive', 'WriteController@atomoStatusToArchiveHandler');
    $router->post('/atomo_delete', 'WriteController@atomoDeleteHandler');
    $router->post('/atomo_create', 'WriteController@atomoCreateHandler');
    $router->post('/ad_update_fields', 'WriteController@adUpdateFieldsHandler');
    $router->post('/ad_update_budget', 'WriteController@adUpdateBudgetHandler');
    $router->post('/ad_status_to_active', 'WriteController@adStatusToActiveHandler');
    $router->post('/ad_status_to_pause', 'WriteController@adStatusToPauseHandler');
    $router->post('/ad_status_to_stop', 'WriteController@adStatusToStopHandler');
    $router->post('/ad_status_to_archive', 'WriteController@adStatusToArchiveHandler');
    $router->post('/ad_delete', 'WriteController@adDeleteHandler');
    $router->post('/ad_create', 'WriteController@adCreateHandler');
    $router->post('/ad_create_media', 'WriteController@adCreateMediaHandler');
});

$router->get('/key', function () {
    return \Illuminate\Support\Str::random(32);
});
