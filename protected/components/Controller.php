<?php
/**
 * Override default Controller methods.
 *
 * MagnusBilling <info@magnusbilling.com>
 * 11/05/2017
 */
class Controller extends BaseController
{
    public $nofilterPerAdminGroup = array(
        'offer',
        'module',
        'methodpay',
        'groupUserGroup',
        'groupUser',
        'campaignRestrictPhone',
        'plan',
        'prefix',
        'provider',
        'rate',
        'refillprovider',
        'servers',
        'services',
        'smtps',
        'templateMail',
        'trunk',
        'trunkReport',
        'userType',
        'groupuser',
        'configuration',
    );

    //Allowed controllers to no admin users use updateall
    public $controllerAllowUpdateAll = array(
        'rateCallshop',
    );

    public function authorizedNoSession()
    {
        $allow = array(
            'site',
            'authentication',
            'asteriskDialplan',
            'asteriskFiles',
            'signup',
            'call0800Web',
            'ata',
            'buyCredit',
            'callApp',
            'clicToCall',
            'gerencianet',
            'joomla',
            'mBillingSoftphone',
            'moip',
            'pagSeguro',
            'paypal',
            'placetoPay',
            'transferToMobile',
            'pagHiper',
            'mercadoPago',
        );
        return in_array($this->controllerName, $allow);
    }

}
