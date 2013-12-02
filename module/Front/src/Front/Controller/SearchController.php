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

class SearchController extends AbstractActionController
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
	
	public function listVideos($page, $limit)
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
	/*************************************************
	 *	Method: getVideoDetails                       
	 *  Purpose: To fetch the video details		      
	 ************************************************/
	
	public function getVideoDetails($id) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->media;
		$userSession	= new Container('fo_user');
		$results		= $collection->find(array('_id' => new \MongoID(trim($id))));
		$resultArray	= array();
		while($results->hasNext())	{
			$resultArray	= $results->getNext();
		}
		return $resultArray;
	}
	/*************************************************
	 *	Method: getMediaTags                       	  
	 *  Purpose: To fetch the video tags		      
	 ************************************************/
	
	public function getMediaTags($id) {
		$conn			= $this->connect();
		$collection		= $conn->snapstate->media_tags;
		$results		= $collection->find(array('media_id' => trim($id)));
		$resultArray	= array();
		while($results->hasNext())	{
			$tempArray		= $results->getNext();
			//$resultArray[]	= new \MongoID($tempArray['tag_id']);
			$resultArray[]	= $tempArray['tag_id'];
		}
		return $resultArray;
	}
	/*************************************************
	 *	Method: getRecommendedVideos              	  
	 *  Purpose: To fetch the recommended videos	  
	 ************************************************/
	
	public function getRecommendedVideos($page = 0, $limit = 0) {
		$conn			= $this->connect();
		
		//	Recommended Videos
		$videoSession	= new Container('fo_videos_recommended');
		$keywordQuery	= array();
		$categoryQuery	= array();
		$tagQuery		= array();
		$skip			= ($page - 1) * $limit;
		
		//	Title - Keywords
		if(isset($videoSession->videoSession['title']) && trim($videoSession->videoSession['title']) != '') {
			$keywordArray	= explode(' ', $videoSession->videoSession['title']);
			$keywords		= array();
			if(is_array($keywordArray) && count($keywordArray) > 0) {
				foreach($keywordArray as $key => $value) {
					if(trim($value) != '') {
						$keywords[]	= new \MongoRegex("/".trim($value)."/");
					}
				}
			}
			$keywordQuery	= array('$in' => $keywords);
		}
		
		//	Category
		$categoryQuery	= (isset($videoSession->videoSession['category']) && trim($videoSession->videoSession['category']) != '') ? $videoSession->videoSession['category'] : array();
		
		//	Tags
		$tagsMediaArray	= array();
		if(isset($videoSession->videoSession['tags']) && is_array($videoSession->videoSession['tags']) && count($videoSession->videoSession['tags']) > 0) {
			$tagQuery		= array('$in' => $videoSession->videoSession['tags']);
			$collection		= $conn->snapstate->media_tags;
			$doc			= array('tag_id' => $tagQuery);
			$results		= $collection->find($doc);
			$tagsMediaArray	= array();
			while($results->hasNext())	{
				$tempArray			= $results->getNext();
				if($videoSession->videoSession['id'] != $tempArray['media_id']) {
					$tagsMediaArray[]	= new \MongoId($tempArray['media_id']);
				}
			}
		}
		$mongoID	= new \MongoID(trim($videoSession->videoSession['id']));
		$document	= array('$or' => array(
							array('media_title' => $keywordQuery, 'media_status' => '1', 'media_approved' => '1', '_id'	=> array('$ne' => $mongoID)),
							array('media_category' => $categoryQuery, 'media_status' => '1', 'media_approved' => '1', '_id'	=> array('$ne' => $mongoID)),
							array('_id' => array('$in' => $tagsMediaArray), 'media_status' => '1', 'media_approved' => '1')
						));
		$collection		= $conn->snapstate->media;
		$sort			= array('date_approved' => 1);
		
		if($limit > 0)
			$results	= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		else
			$results	= $collection->find($document)->sort($sort);
		return $results;
	}
	/*************************************************
	 *	Method: doVote		                       	  
	 *  Purpose: To up & down vote the video		  
	 ************************************************/
	
	public function doVote($type, $videoId) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$collection	= $conn->snapstate->media_ratings;
		$vote		= ($type == 1) ? 'like' : 'dislike';
		$query		= array('media_id'	=> (string)base64_decode($videoId),
							'user_id'	=> (string)$userSession->userSession['_id'],
							'date_voted'=> date('m/d/Y H:i:s'),
							'rating' 	=> $vote
							);
		$results	= $collection->insert($query);
	}
	/*************************************************
	 *	Method: trackVideoViews                    	  
	 *  Purpose: To track video views                 
	 ************************************************/
	
	public function trackVideoViews($videoId) {
		$conn		= $this->connect();
		$userSession= new Container('fo_user');
		$userID		= (isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') ? (string)$userSession->userSession['_id'] : '0';
		$collection	= $conn->snapstate->media_views;
		$query		= array('media_id'		=> (string)$videoId,
							'user_id'		=> $userID,
							'date_viewed'	=> date('m/d/Y H:i:s')
							);
		$results	= $collection->insert($query);
	}
	/*************************************************
	 *	Method: getWatchedVideos              	  	  
	 *  Purpose: To fetch the watched videos	  	  
	 ************************************************/
	
	public function getWatchedVideos($page = 0, $limit = 0) {
		$conn			= $this->connect();
		$userSession	= new Container('fo_user');
		//	Watched Videos
		$videoWatchedSession	= new Container('fo_videos_watched');
		$skip			= ($page - 1) * $limit;
		$collection		= $conn->snapstate->media_views;
		$sort			= array('date_viewed' => 1);
		$document		= array('user_id' => (string)$userSession->userSession['_id']);
		
		/*	if($limit > 0)
			$results	= $collection->find($document)->skip($skip)->limit($limit)->sort($sort);
		else
			$results	= $collection->find($document)->sort($sort);
		return $results;	*/
		
		
		// use all fields
		$keys = array("media_id" => 1);;
		// set intial values
		$initial = array("items" => array());
		// JavaScript function to perform
		$reduce = "function (obj, prev) { prev.items.push(obj.date_viewed); }";
		$condition = array('condition' => $document);
		$results	= $collection->group($keys, $initial, $reduce, $condition);
		return $results;	
		
		/*	$results	= $collection->aggregate(array(
						    array('$match' => $document),
						    array('$group' => array('media_id' => '$key', 'count' => array('$sum' => 1))),
						    array('$sort' => $sort),
						    array('$skip' => $skip),
						    array('$limit' => $limit)
						));
		return $results;	*/
		
	}
	
	
	
	/********************************************************************************************
	 *	Action: search                                                                           
	 *	Page: It acts as a default page.                                                         
	 *******************************************************************************************/
	
	public function searchAction()
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
	/********************************************************************************************
	 *	Action: view-video                                                                       
	 *	Page: video detail page			                                                         
	 *******************************************************************************************/
	
	public function viewVideoAction()
	{
		$userSession = new Container('fo_user');
		$pageSession = new Container('fo_page');
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		$request	= $this->getRequest();
		$id			= $this->params()->fromRoute('id', 0);
		$originalID	= base64_decode($id);
		$videoArray	= $this->getVideoDetails($originalID);
		
		//	Track Video Views
		if(!isset($pageSession->pageSession['last_video']) || (isset($pageSession->pageSession['last_video']) && $pageSession->pageSession['last_video'] != $originalID)) {
			$this->trackVideoViews($originalID);
			$pageSession->pageSession	= array('last_video' => $originalID);
		}
		//	Media Tags
		$mediaTags	= $this->getMediaTags((string)$videoArray['_id']);
		
		//	Recommended Videos
		$videoSession 		= new Container('fo_videos_recommended');
		$recommendedArray	= array('title'		=> $videoArray['media_title'],
									'category'	=> $videoArray['media_category'],
									'tags'		=> $mediaTags,
									'id'		=> new \MongoId($videoArray['_id']));
		$videoSession->videoSession	= $recommendedArray;
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'videoArray'	=> $videoArray,
			'action'		=> $this->params('action'),
			'controller'	=> $this->params('controller'),
		));
    }
	/********************************************************************************************
	 *	Action: recommended-videos                                                               
	 *	Page: display recommended videos via AJAX                                                
	 *******************************************************************************************/
	
	public function recommendedVideosAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		$message	= '';
		$resultArray= array();
		$recommendedVideos	= $this->getRecommendedVideos();
		while($recommendedVideos->hasNext())	{
			$resultArray[]	= $recommendedVideos->getNext();
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'totalRecords'	=> $recommendedVideos->count(),
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
	}
	/********************************************************************************************
	 *	Action: view-recommended                                                                 
	 *	Page: It loads the videos via AJAX call                                                  
	 *******************************************************************************************/
	
	public function viewRecommendedAction()
	{
		$userSession = new Container('fo_user');
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'controller'	=> $this->params('controller'),
			'action'		=> $this->params('action'),
		));
    }
	/*******************************
	 *	Action: list-videos         
	 *  Module: To list the videos  
	 *	Note:	AJAX call with view 
	 ******************************/
	
	public function listRecommendedAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$perPage	= $matches->getParam('perPage', '3');
		
		//	Session for listing
		$listingSession = new Container('fo_listing');
		if($page == '0') {
			$listingSession->page	= 1;
			$page	= 1;
		} else if($listingSession->offsetExists('page')) {
			$page	= $listingSession->page+1;
			$listingSession->page	= $page;
		} else {
			$page	= 1;
		}
		$message		= '';
		$perpage		= PERPAGE;
		$recordsArray	= $this->getRecommendedVideos($page, $perPage);
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		
		while($recordsArray->hasNext())
		{
			$resultArray[]	= $recordsArray->getNext();
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'totalRecords'	=> $totalRecords,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/***************************************************************************************
	 *	Action: vote-video                                                                  
	 *	Page: AJAX page for up & down voting												
	 **************************************************************************************/
	
	public function voteVideoAction()
	{
		$userSession	= new Container('fo_user');
		$request		= $this->getRequest();
		
		if(isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') {
			if($request->isPost()) {
				$formData	= $request->getPost();
				if(!isset($userSession->mediaSession['rating'][base64_decode($formData['videoId'])]) && isset($formData['type']) && ($formData['type'] == '1' || $formData['type'] == '2')) {
					$this->doVote($formData['type'], $formData['videoId']);
					if(isset($userSession->mediaSession['rating'])) {
						$tempArray['rating']										= $userSession->mediaSession['rating'];
						$tempArray['rating'][base64_decode($formData['videoId'])]	= base64_decode($formData['videoId']);
						$userSession->mediaSession									= $tempArray;
					} else {
						$tempArray['rating'][base64_decode($formData['videoId'])]	= base64_decode($formData['videoId']);
						$userSession->mediaSession									= $tempArray;
					}
					echo trim($formData['type']);
				} else if(isset($userSession->mediaSession['rating'][base64_decode($formData['videoId'])])) {
					echo "-2";	// Voted
				} else {
					echo "-1";	//	improper data
				}
				
			} else {
				echo "-1";	//	improper data
			}
		} else {
			echo "0";	//	user session is in-active
		}
		return $this->getResponse();
	}
	/********************************************************************************************
	 *	Action: view-watched	                                                                 
	 *	Page: It loads the watched videos via AJAX call                                          
	 *******************************************************************************************/
	
	public function viewWatchedAction()
	{
		$userSession	= new Container('fo_user');
		if(!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
		}
		$this->layout('frontend');
		$request 		= $this->getRequest();
		$message		= '';
		$errorMessage	= '';
		
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession,
			'message'		=> $message,
			'errorMessage'	=> $errorMessage,
			'controller'	=> $this->params('controller'),
			'action'		=> $this->params('action'),
		));
    }
	/***************************************
	 *	Action: list-watched    	    	
	 *  Module: To list the watched videos  
	 *	Note:	AJAX call with view 		
	 **************************************/
	
	public function listWatchedAction()
    {
		$result = new ViewModel();
	    $result->setTerminal(true);
		
		$matches	= $this->getEvent()->getRouteMatch();
		$page		= $matches->getParam('id', 1);
		$perPage	= $matches->getParam('perPage', '2');
		
		//	Session for listing
		$listingSession = new Container('fo_listing');
		if($page == '0') {
			$listingSession->page	= 1;
			$page	= 1;
		} else if($listingSession->offsetExists('page')) {
			$page	= $listingSession->page+1;
			$listingSession->page	= $page;
		} else {
			$page	= 1;
		}
		$message		= '';
		$perpage		= 2;
		$recordsArray	= $this->getWatchedVideos($page, $perPage);
		echo '<pre>===>'; print_r($recordsArray); echo '</pre>';
		echo '<pre>===>'; print_r($recordsArray['retval']); echo '</pre>';
		$totalRecords	= 0;
		$resultArray	= 0;
		$totalRecords	= $recordsArray->count();
		$resultArray	= array();
		
		while($recordsArray->hasNext())
		{
			$resultArray[]	= $recordsArray->getNext();
		}
		$result->setVariables(array('records'		=> $resultArray,
									'message'		=> $message,
									'page'			=> $page,
									'perPage'		=> $perPage,
									'totalRecords'	=> $totalRecords,
									'action'		=> $this->params('action'),
									'controller'	=> $this->params('controller')));
		return $result;
    }
	/**************************************
	 *	Action: testAction	   	       	   
	 *	Page: Blank page with Session	   
	 *************************************/
	
	public function testAction()
    {
		echo "123";
		$userSession	= new Container('fo_user');
		echo '<pre>===>'; print_r($userSession->userSession); echo '</pre>';
		return $this->getResponse();
	}
}
