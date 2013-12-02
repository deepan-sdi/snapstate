<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Front\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//	Session
use Zend\Session\Container;

//	Auth
use Zend\Authentication,
	Zend\Authentication\Result,
	Zend\Authentication\AuthenticationService;

//	Cache
use Zend\Cache\Storage\StorageInterface;

class FriendsController extends AbstractActionController
{
	protected $usersTable;
	
	protected $cache;
	
	public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
    }
	/************************************
	 *	Method: connect     	         
	 *  Purpose: To connect with MongoDB 
	 ***********************************/
	
	public function connect()
	{
		//$conn = new \Mongo(HOST, array("username" => USERNAME, "password" => PASSWORD, "db" => DATABASE));
		$conn = new \Mongo(HOST);
		return $conn;
	}
	/*************************************
	 *	Method: listVideos	     	      
	 *  Purpose: To select the videos	  
	 ************************************/
	
	public function listFriends($page, $limit)
	{
		//	Session for listing
		$listingSession	= new Container('fo_listing');
		$conn		= $this->connect();
		$collection	= $conn->snapstate->media;
		$skip		= ($page - 1) * $limit;
		$sort		= array('date_approved' => 0);
		if(isset($listingSession->keyword) && trim($listingSession->keyword) != '') {
			$keywordArray	= explode(' ', $listingSession->keyword);
			$keywords		= array();
			if(is_array($keywordArray) && count($keywordArray) > 0) {
				foreach($keywordArray as $key => $value) {
					if(trim($value) != '') {
						$keywords[]	= new \MongoRegex("/".trim($value)."/");
					}
				}
			}
			$document	= array('media_status' => '1', 'media_title' => array('$all' => $keywords));
		} else {
			$document	= array('media_status' => '1');
		}
		
		$cursor		= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		return $cursor;
	}
	
	
	
	
	/********************************************************************************************
	 *	Action: friends                                                                           
	 *	Page: It acts as a default page.                                                         
	 *******************************************************************************************/
	
	public function friendsAction()
	{
		$userSession = new Container('fo_user');
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		//	Destroy listing Session Vars
		$listingSession = new Container('fo_listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		
		if ($request->isPost()) {
			$formData	= $request->getPost();
			if(isset($formData['search']) && $formData['search'] != '')
				$listingSession->keyword	= $formData['search'];
			else
				$listingSession->keyword	= '';
			
		}
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'action'		=> $this->params('action'),
			'controller'	=> $this->params('controller'),
		));
    }
	
}
