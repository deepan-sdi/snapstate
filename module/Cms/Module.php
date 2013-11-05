<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Cms;

//	Cache
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

//	Models
use Cms\Model\Users;
//use Cms\Model\Usermo;
use Cms\Model\UsersTable;

use Cms\Model\Group;

use Cms\Model\Carrier;
use Cms\Model\CarrierTable;
use Cms\Model\Language;
use Cms\Model\LanguageTable;
use Cms\Model\Faq;
use Cms\Model\FaqTable;
use Cms\Model\Page;
use Cms\Model\PageTable;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{
   	public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
	    	$e->getApplication()->getServiceManager()->get('viewhelpermanager')->setFactory('cms', function($sm) use ($e) {
	        $viewHelper = new View\Helper\Cms($e->getRouteMatch());
	        return $viewHelper;
	    });
		$eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
	
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	
    public function getAutoloaderConfig()
    {
		return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	public function getServiceConfig()
    {
        return array(
            'factories' => array(
				/*	'Cms\Model\Usermo' =>  function($sm) {
                    $table     = new Usermo();
                    return $table;
                },	*/
				'Cms\Model\UsersTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table     = new UsersTable($dbAdapter);
                    return $table;
                },
				'Cms/Model/Users' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		            $users	= new Users();
		            $users->setDbAdapter($dbAdapter);
		            return $users;
		        },
				'Cms/Model/Group' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		            $users	= new Group();
		            $users->setDbAdapter($dbAdapter);
		            return $users;
		        },
				'Cms/Model/Carrier' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		            $table	= new Carrier();
		            $table->setDbAdapter($dbAdapter);
		            return $table;
		        },
				'Cms/Model/CarrierTable' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					//$cacheAdapter = $sm->get('Zend\Cache\Storage\Filesystem');
		            $table	= new CarrierTable($dbAdapter);
					//$table->setCache($cacheAdapter);
		            return $table;
		        },
				'Cms/Model/Language' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		            $table	= new Language();
		            $table->setDbAdapter($dbAdapter);
		            return $table;
		        },
				'Cms\Model\LanguageTable' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		            $table	= new LanguageTable($dbAdapter);
		            return $table;
		        },
				'Cms\Model\Faq' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		            $table	= new Faq($dbAdapter);
		            return $table;
		        },
				'Cms\Model\FaqTable' => function($sm){
		            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
		            $table	= new FaqTable($dbAdapter);
		            return $table;
		        },
				'Cms\Model\Page' => function($sm){
		            $dbAdapter	= $sm->get('Zend\Db\Adapter\Adapter');
		            $table		= new Page($dbAdapter);
		            return $table;
		        },
				'Cms\Model\PageTable' => function($sm){
		            $dbAdapter	= $sm->get('Zend\Db\Adapter\Adapter');
		            $table		= new PageTable($dbAdapter);
		            return $table;
		        },
				'db-adapter' => function($sm) {
					return $sm->get('db');
				},
				'cache-adapter' => function($sm) {
					return $sm->get('globalcache');
				},
            ),
        );
    }
	
	public function getViewHelperConfig()
	{
	   return array(
	         'factories' => array(
	            'Cms' => function ($sm) {
	               $match = $sm->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch();
	               $viewHelper = new \Cms\View\Helper\Cms($match);
	               return $viewHelper;
	            },
				'Datetime' => function($sm) {
                   $match = $sm->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch();
	               $viewHelper = new \Cms\View\Helper\Datetime($match);
	               return $viewHelper;
                },
				'Requesthelper' => function($sm){
				   $helper = new View\Helper\Requesthelper;
				   $request = $sm->getServiceLocator()->get('Request');
				   $helper->setRequest($request);
				   return $helper;
				},
				'Carriers' => function($sm){
				   $helper = new View\View\Helper\Carriers;
				   return $helper;
				},
				'Carrierlist' => function($sm) {
					$table = $sm->getServiceLocator()->get('Cms/Model/CarrierTable');
                    $helper = new \Cms\View\Helper\Carrierlist($table);
					//$helper->setTableCount($sm->getServiceLocator(), 'Cms\Model\CarrierTable');
                    return $helper;
                }
	         ),
	   );
	}
}
