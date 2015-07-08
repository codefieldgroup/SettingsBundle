<?php
// src/cf/SettingsBundle/Listener/CfSettingsListener.php
namespace Cf\SettingsBundle\Listener;

use Cf\SettingsBundle\Entity\CfSettings;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class CfSettingsListener
{
    private $container;

    /**
     * @param $container
     */
    public function __construct( $container )
    {
        $this->container = $container;
    }

    /**
     * @param null $parameter
     * @param int $json_decode
     *
     * @return mixed|null
     */
    public function getSettings( $parameter = null, $json_decode = 0 )
    {
        if ($parameter !== null && is_string( $parameter ) && $parameter !== '') {
            $em     = $this->container->get( 'doctrine.orm.entity_manager' );
            $entity = $em->getRepository( 'cfSettingsBundle:CfSettings' )->findOneByParameter( $parameter );
            $result = $entity !== null ? $entity->getValue() : null;
        } else {
            $em       = $this->container->get( 'doctrine.orm.entity_manager' );
            $entities = $em->getRepository( 'cfSettingsBundle:CfSettings' )->findAll();
            /* @var $entity CfSettings */
            foreach ($entities as $entity) {
                $result [$entity->getParameter()] = $entity->getValue();
            }
        }

        if ($json_decode === 1) {
            $result = json_decode( $result, true );
        }

        return $result;
    }

    /**
     * @param $parameter
     * @param $value
     * @param int $json_encode
     *
     * @return mixed
     */
    public function setSettings( $parameter, $value, $json_encode = 0 )
    {
        $em     = $this->container->get( 'doctrine.orm.entity_manager' );
        $entity = null;
        if ($parameter !== null && is_string( $parameter ) && $parameter !== '') {
            $entity = $em->getRepository( 'cfSettingsBundle:CfSettings' )->findOneByParameter( $parameter );
        } else {
            return null;
        }

        if ($json_encode === 1) {
            $value = json_encode( $value, JSON_FORCE_OBJECT );
        }

        if ($entity !== null) {
            $entity->setValue( $value );
        } else {
            $params = [ 'parameter' => $parameter, 'value' => $value ];
            $entity = $this->container->get( 'cf.commonbundle.miscellaneous' )->bindParameters( new CfSettings(), $params );
        }

        $em->persist( $entity );
        $em->flush();
    }
}