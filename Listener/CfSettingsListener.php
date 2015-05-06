<?php
// src/cf/SettingsBundle/Listener/CfSettingsListener.php
namespace Cf\SettingsBundle\Listener;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class CfSettingsListener
{
    /**
     * @return array
     */
    public function getSettings()
    {
        return [ ];//[['value' => '0', 'name' => 'Superdesactivado', 'selected' => false, 'class' => 'label-danger arrowed arrowed-left'], ['value' => '1', 'name' => 'Extraacivado', 'selected' => true, 'class' => 'label-info arrowed arrowed-left',],];
    }

    /**
     * @param $parameter
     * @param $value
     *
     * @return array
     */
    public function update_settings( $parameter, $value )
    {
        return [ $parameter, $value ]; // Update DB and return true or false.
    }
}