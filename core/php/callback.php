<?php
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
if (!jeedom::apiAccess(init('apikey'), 'gCalendar')) {
	echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action',__file__);
	die();
}
$eqLogic = eqLogic::byId(init('eqLogic_id'));
if (!is_object($eqLogic)) {
	echo __('Impossible de trouver l\'équipement correspondant à : ',__file__) . init('eqLogic_id');
	exit();
}

if (!isConnect()) {
	echo __('Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter',__file__) . <a href=' . network::getNetworkAccess() . '/index.php> . __('ici',__file__) . </a> .__('avant et refaire l\'opération de synchronisation',__file__);
	die();
}

$provider = $eqLogic->getProvider();

if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
	unset($_SESSION['oauth2state']);
	exit('Invalid state');
}
try {
	$accessToken = $provider->getAccessToken('authorization_code', [
		'code' => $_GET['code'],
	]);
	$eqLogic->setConfiguration('accessToken', $accessToken->jsonSerialize());
	$eqLogic->setConfiguration('refreshToken', $accessToken->getRefreshToken());
	$eqLogic->save();

	redirect(network::getNetworkAccess('external') . '/index.php?v=d&p=gCalendar&m=gCalendar&id=' . $eqLogic->getId());
} catch (Exception $e) {
	exit(print_r($e));
}
