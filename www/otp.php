<?php

/**
 * This page asks the user to authenticate using a Yubikey.
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>.
 * @package SimpleSAMLphp\Module\yubikey
 */

if (!array_key_exists('StateId', $_REQUEST)) {
    throw new SimpleSAML_Error_BadRequest('Missing AuthState parameter.');
}
$authStateId = $_REQUEST['StateId'];
$state = SimpleSAML_Auth_State::loadState($authStateId, 'yubikey:otp:init');

$error = false;
if (array_key_exists('otp', $_POST)) { // we were given an OTP
    try {
        if (SimpleSAML\Module\yubikey\Auth\Process\OTP::authenticate($state, $_POST['otp'])) {
            SimpleSAML_Auth_State::saveState($state, 'yubikey:otp:init');
            SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
        } else {
            $error = '{yubikey:errors:invalid_yubikey}';
        }
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}

$cfg = SimpleSAML_Configuration::getInstance();
$tpl = new SimpleSAML_XHTML_Template($cfg, 'yubikey:otp.php');
$trans = $tpl->getTranslator();
$tpl->data['params'] = array('StateId' => $authStateId);
$tpl->data['error'] = ($error) ? $trans->t($error) : false;
$tpl->show();
