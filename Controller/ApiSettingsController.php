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
        $this->parameter = ['length_row' => '10000', 'count' => -1, 'class_name_entity' => new CfSettings()];
    }

    /**
     * Lists all Settings entities.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function cgetAction(Request $request)
    {
        // TODO: JCRC: Implementar la seguridad del sistema
        try {
            $em = $this->getDoctrine()->getManager();
            $entities = [];
            $code = $request->query->has('code') ? $request->query->get('code') : null;
            switch ($code) {
                case 'list' :
                    $entities = $this->get('cf.settingsbundle')->getSettings();
                    break;
                default:
                    break;
            }
            if ($entities !== null) {
                return $this->get('cf.commonbundle.restapi')->buildRestApi(
                    $entities,
                    [],
                    ['parameter' => $this->parameter]
                );
            }
        } catch (NoResultException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(1001) /*@Error Not Found*/
            );
        } catch (DBALException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500)/*@Error DB Error*/
            );
        } catch (\Exception $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(501)/*@Error Undefined*/,
                ['parameter' => $this->parameter]
            );
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
    public function getAction(Request $request, $parameter)
    {
        // TODO: JCRC: Implementar la seguridad del sistema
        try {
            $em = $this->getDoctrine()->getManager();
            $msg = [];
            $entities = [];
            // Find by id
            if ($parameter !== null) {
                $code = $request->query->has('code') ? $request->query->get('code') : null;
                switch ($code) {
                    case 'show' :
                        $entities = $this->get('cf.settingsbundle')->getSettings($parameter);
                        break;
                    default:
                        break;
                }
            } else {
                $msg = $this->get('cf.commonbundle.messenger')->getError(1004); /*@Error ID undefined*/
            }

            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                $entities,
                $msg,
                ['parameter' => $this->parameter]
            );
        } catch (\PDOException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch (DBALException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch (NoResultException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(1001) /*@Error Not Found*/
            );
        } catch (\Exception $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(501) /*@Error Undefined*/
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
    public function postAction(Request $request)
    {
        // TODO: JCRC: Implementar la seguridad del sistema
        // Get current user.
        //$this->get('cf.userbundle.listener.user')->getUser();
        try {
            $em = $this->getDoctrine()->getManager();

            $params = $request->request->all(); // Get all params

            $entity = $this->get('cf.commonbundle.miscellaneous')->bindParameters(new CfSettings(), $params);

            $errors = $this->get('validator')->validate($entity);
            if (count($errors) > 0) { //Exist validations errors
                $msg = $this->get('cf.commonbundle.messenger')->parseErrorsByValidator(
                    $errors
                ); //Convert msg errors to string
            } else {
                $em->persist($entity);
                $em->flush();

                return $this->get('cf.commonbundle.restapi')->buildRestApi(
                    $entity,
                    $this->get('cf.commonbundle.messenger')->getSuccess(2001) /*@Success Created*/
                );
            }

            return $this->get('cf.commonbundle.restapi')->buildRestApi(null, $msg);
        } catch (\PDOException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch (DBALException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch (\Exception $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(501) /*@Error Undefined*/
            );
        }
    }

    /**
     * @param Request $request
     * @param $parameter
     *
     * @return array
     */
    public function putAction(Request $request, $parameter)
    {
        //TODO: JCRC: Implementar la seguridad del sistema
        try {
            $parameter = $request->request->get('parameter');
            if ($parameter !== null) {
                $entity = $this->get('cf.settingsbundle')->setSettings($parameter, $request->request->get('value'));

                return $this->get('cf.commonbundle.restapi')->buildRestApi(
                    $entity,
                    $this->get('cf.commonbundle.messenger')->getSuccess(2002) /*@Success Update*/
                );
            }

        } catch (\PDOException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch
        (DBALException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch (\Exception $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(501) /*@Error Undefined*/
            );
        }
    }

    /**
     * @param Request $request
     * @param $parameter
     *
     * @return array
     */
    public function patchAction(Request $request, $parameter)
    {
        //TODO: JCRC: Implementar la seguridad del sistema
        try {
            if ($parameter !== null) {
                $em = $this->getDoctrine()->getManager();
                $entity = $em->getRepository('cfSettingsBundle:CfSettings')->findOneByParameter($parameter);

                if ($entity !== null) {
                    $entity = $this->get('cf.commonbundle.miscellaneous')->bindParameters(
                        $entity,
                        $request->request->all()
                    );
                    $errors = $this->get('validator')->validate($entity);

                    if (count($errors) > 0) { //Exist validations errors
                        $msg = $this->get('cf.commonbundle.messenger')->parseErrorsByValidator(
                            $errors
                        ); //Convert msg errors to string
                    } else {
                        $em->persist($entity);
                        $em->flush();

                        return $this->get('cf.commonbundle.restapi')->buildRestApi(
                            $entity,
                            $this->get('cf.commonbundle.messenger')->getSuccess(2002) /*@Success Update*/
                        );
                    }
                } else {
                    $msg = $this->get('cf.commonbundle.messenger')->getError(1001); /*@Error element not found*/
                }
            } else {
                $msg = $this->get('cf.commonbundle.messenger')->getError(1004); /*@Error ID not found*/
            }

            return $this->get('cf.commonbundle.restapi')->buildRestApi(null, $msg);
        } catch (\PDOException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch (DBALException $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(500) /*@Error DB Error*/
            );
        } catch (\Exception $e) {
            return $this->get('cf.commonbundle.restapi')->buildRestApi(
                null,
                $this->get('cf.commonbundle.messenger')->getError(501) /*@Error Undefined*/
            );
        }
    }

}
