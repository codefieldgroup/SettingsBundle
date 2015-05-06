<?php

namespace Cf\SettingsBundle\Controller;

use Cf\SettingsBundle\Entity\CfSettings;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Doctrine\DBAL\DBALException as DBALException;
use Doctrine\ORM\NoResultException as NoResultException;

/**
 * IndicationsTemplates controller.
 *
 * @RouteResource("settings")
 */
class ApiSettingsController extends FOSRestController
{
    /**
     * @var array
     */
    public $status;

    /**
     * @var
     */
    public $parameter;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->parameter = [ 'length_row' => '10000', 'count' => -1 ];
    }

    /**
     * Lists all Settings entities.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function cgetAction( Request $request )
    {
        // TODO: JCRC: Implementar la seguridad del sistema
        try{
            $em       = $this->getDoctrine()->getManager();
            $entities = [ ];
            $code     = $request->query->has( 'code' ) ? $request->query->get( 'code' ) : null;
            switch ($code) {
                case 'list' :
                    $limit    = $request->query->has( 'limit' ) ? $request->query->get(
                        'limit'
                    ) : $this->parameter['length_row'];
                    $offset   = $request->query->has( 'offset' ) ? $request->query->get( 'offset' ) : 0;
                    $order_by = $request->query->has( 'order_by' ) ? $request->query->get( 'order_by' ) : null;
                    $search   = $request->query->has( 'search' ) && trim( $request->query->get( 'search' ) ) !== '' ? [
                        'parameter' => $request->query->get( 'search' )
                    ] : [ ];

                    $entities = $em->getRepository( 'cfSettingsBundle:CfSettings' )->searchOr(
                        $search,
                        $this->parameter['count'],
                        $order_by,
                        $limit,
                        $offset
                    );
                    break;
                default:
                    break;
            }
            if ($entities !== null) {
                return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                    $entities,
                    [ ],
                    [ 'parameter' => $this->parameter ]
                );
            }
        }catch ( NoResultException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 1001 ) /*@Error Not Found*/
            );
            //        }catch ( DBALException $e ){
            //            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
            //                null,
            //                $this->get( 'cf.commonbundle.messenger' )->getError( 500 )/*@Error DB Error*/
            //            );
            //        }catch ( \Exception $e ){
            //            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
            //                null,
            //                $this->get( 'cf.commonbundle.messenger' )->getError( 501 )/*@Error Undefined*/,
            //                [ 'parameter' => $this->parameter ]
            //            );
        }
    }

    /**
     * Finds a IndicationsTemplates entity by id.
     *
     * @param Request $request
     * @param         $parameter
     *
     * @return array
     */
    public function getAction( Request $request, $parameter )
    {
        // TODO: JCRC: Implementar la seguridad del sistema
        try{
            $em       = $this->getDoctrine()->getManager();
            $msg      = [ ];
            $entities = [ ];
            // Find by id
            if ($parameter !== null) {
                $code = $request->query->has( 'code' ) ? $request->query->get( 'code' ) : null;
                switch ($code) {
                    case 'show' :
                        $entities                             = $em->getRepository(
                            'cfSettingsBundle:CfSettings'
                        )->findOneByParameter( $parameter );
                        $this->parameter['class_name_entity'] = get_class( $entities );
                        break;
                    default:
                        break;
                }
            } else {
                $msg = $this->get( 'cf.commonbundle.messenger' )->getError( 1004 ); /*@Error ID undefined*/
            }

            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                $entities,
                $msg,
                [ 'parameter' => $this->parameter ]
            );
        }catch ( \PDOException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            );
        }catch ( DBALException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            );
        }catch ( NoResultException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 1001 ) /*@Error Not Found*/
            );
        }catch ( \Exception $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 501 ) /*@Error Undefined*/
            );
        }
    }

    /**
     * Create a new IndicationsTemplates entity.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postAction( Request $request )
    {
        // TODO: JCRC: Implementar la seguridad del sistema
        // Get current user.
        //$this->get('cf.userbundle.listener.user')->getUser();
        try{
            $em = $this->getDoctrine()->getManager();

            $params = $request->request->all(); // Get all params

            $entity = $this->get( 'cf.commonbundle.miscellaneous' )->bindParameters( new CfSettings(), $params );

            $errors = $this->get( 'validator' )->validate( $entity );
            if (count( $errors ) > 0) { //Exist validations errors
                $msg = $this->get( 'cf.commonbundle.messenger' )->parseErrorsByValidator(
                    $errors
                ); //Convert msg errors to string
            } else {
                $em->persist( $entity );
                $em->flush();

                return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                    $entity,
                    $this->get( 'cf.commonbundle.messenger' )->getSuccess( 2001 ) /*@Success Created*/
                );
            }

            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi( null, $msg );
        }catch ( \PDOException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            );
            //        }catch ( DBALException $e ){
            //            //TODO: JCRC: Llevar a los logs en ficheros este error de problema de conexion con la DB.
            //            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
            //                null,
            //                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            //            );
            //        }catch ( \Exception $e ){
            //            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
            //                null,
            //                $this->get( 'cf.commonbundle.messenger' )->getError( 501 ) /*@Error Undefined*/
            //            );
        }
    }

    /**
     * @param Request $request
     * @param $parameter
     *
     * @return array
     */
    public function putAction( Request $request, $parameter )
    {
        //TODO: JCRC: Implementar la seguridad del sistema
        try{
            if ($parameter !== null) {
                $em     = $this->getDoctrine()->getManager();
                $entity = $em->getRepository( 'cfSettingsBundle:CfSettings' )->findOneByParameter( $parameter );

                if ($entity !== null) {
                    $entity = $this->get( 'cf.commonbundle.miscellaneous' )->bindParameters( $entity, $request->request->all() );

                    $errors = $this->get( 'validator' )->validate( $entity );

                    if (count( $errors ) > 0) { //Exist validations errors
                        $msg = $this->get( 'cf.commonbundle.messenger' )->parseErrorsByValidator(
                            $errors
                        ); //Convert msg errors to string
                    } else {
                        $em->persist( $entity );
                        $em->flush();

                        return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                            $entity,
                            $this->get( 'cf.commonbundle.messenger' )->getSuccess( 2002 ) /*@Success Update*/
                        );
                    }
                } else {
                    $msg = $this->get( 'cf.commonbundle.messenger' )->getError( 1001 ); /*@Error element not found*/
                }
            } else {
                $msg = $this->get( 'cf.commonbundle.messenger' )->getError( 1004 ); /*@Error ID not found*/
            }

            //FIXME: VCA: Cuando retorno un error se me cierra el cuadro de dialogo a pesar del error.Ej.Adicionar indicacion vacio.
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi( null, $msg );
        }catch ( \PDOException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            );
        }catch ( DBALException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            );
        }catch ( \Exception $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 501 ) /*@Error Undefined*/
            );
        }
    }

    /**
     * @param Request $request
     * @param $parameter
     *
     * @return array
     */
    public function patchAction( Request $request, $parameter )
    {
        //TODO: JCRC: Implementar la seguridad del sistema
        try{
            if ($parameter !== null) {
                $em     = $this->getDoctrine()->getManager();
                $entity = $em->getRepository( 'cfSettingsBundle:CfSettings' )->findOneByParameter( $parameter );

                if ($entity !== null) {
                    $entity = $this->get( 'cf.commonbundle.miscellaneous' )->bindParameters( $entity, $request->request->all() );
print_r($request->query->all());
                    $errors = $this->get( 'validator' )->validate( $entity );

                    if (count( $errors ) > 0) { //Exist validations errors
                        $msg = $this->get( 'cf.commonbundle.messenger' )->parseErrorsByValidator(
                            $errors
                        ); //Convert msg errors to string
                    } else {
                        $em->persist( $entity );
                        $em->flush();

                        return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                            $entity,
                            $this->get( 'cf.commonbundle.messenger' )->getSuccess( 2002 ) /*@Success Update*/
                        );
                    }
                } else {
                    $msg = $this->get( 'cf.commonbundle.messenger' )->getError( 1001 ); /*@Error element not found*/
                }
            } else {
                $msg = $this->get( 'cf.commonbundle.messenger' )->getError( 1004 ); /*@Error ID not found*/
            }

            //FIXME: VCA: Cuando retorno un error se me cierra el cuadro de dialogo a pesar del error.Ej.Adicionar indicacion vacio.
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi( null, $msg );
        }catch ( \PDOException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            );
        }catch ( DBALException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 ) /*@Error DB Error*/
            );
        }catch ( \Exception $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 501 ) /*@Error Undefined*/
            );
        }
    }

    /**
     * Deletes a IndicationsTemplates entity.
     *
     * @param Request $request
     * @param         $parameter
     *
     * @return mixed
     */
    public function deleteAction( Request $request, $parameter )
    {
        // TODO: JCRC: Implementar la seguridad del sistema
        // Get current user.
        try{
            $msg = null;
            $em  = $this->getDoctrine()->getManager( 'default' ); // ORM access
            if ($parameter !== null) {
                //To Verify if exist ID to delete
                $entity = $em->getRepository( 'cfSettingsBundle:CfSettings' )->findOneByParameter( $parameter );
                if ($entity !== null) {
                    $em->remove( $entity );
                    $em->flush();
                    $msg = $this->get( 'cf.commonbundle.messenger' )->getSuccess( 2003 );/*@Success Remove*/
                }
            } else {
                $msg = $this->get( 'cf.commonbundle.messenger' )->getError( 1004 );/*@Error ID Undefined*/
            }

            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi( null, $msg );
        }catch ( \PDOException $e ){
            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 )
            );
        }catch ( NoResultException $e ){
            $msg = $this->get( 'cf.commonbundle.messenger' )->getError( 1001 );

            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi( null, $msg );
        }catch ( DBALException $e ){

            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 500 )
            );
        }catch ( \Exception $e ){

            return $this->get( 'cf.commonbundle.restapi' )->buildRestApi(
                null,
                $this->get( 'cf.commonbundle.messenger' )->getError( 501 )
            );
        }
    }
}
