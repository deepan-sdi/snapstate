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

//	Models
//use Front\Model\Users;

//	Cache
use Zend\Cache\Storage\StorageInterface;

class IndexController extends AbstractActionController
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
	/***********************************
	 *	Method: registerUser   	    	
	 *  Purpose: To register an user	
	 **********************************/
	
	public function registerUser($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$userId		= new \MongoId();
		$query		= array('_id'			=> $userId,
							'user_firstname'=> $formData['first_name'],
							'user_lastname' => $formData['last_name'],
							'user_email' 	=> $formData['email_address'],
							'user_group' 	=> USER_GROUP_ID,
							'user_fbuid' 	=> '',
							'user_password' => md5($formData['registration_password']),
							'user_dob' 		=> '',
							'user_gender'	=> '',
							'user_status'	=> '0',
							'date_created'	=> date('m/d/Y H:i:s'),
							'date_modified'	=> date('m/d/Y H:i:s')
							);
		$results	= $collection->insert($query);
		if($results) {
			return $userId;
		} else {
			return 0;
		}
	}
	/*************************************************
	 *	Method: checkEmail                            
	 *  Purpose: To validate the email existence      
	 ************************************************/
	
	public function checkEmail($formData, $opt) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$userSession = new Container('user');
		if($opt == 1) {
			$results	= $collection->find(array('user_email' => trim($formData['email_address'])));
		} else if($opt == 2) {
			$mongoID	= new \MongoID(trim($formData['_id']));
			$document	= array('_id'	=> array('$ne' => $mongoID), 'user_email' => trim($formData['email_address']));
			$results	= $collection->find($document);
		}
		while($results->hasNext())
		{
			$resultArray	= $results->getNext();
		}
		if(isset($resultArray) && is_array($resultArray)) {
			return 1;
		} else {
			return 0;
		}
	}
	/************************************
	 *	Method: selectUser     	         
	 *  Purpose: To select user with auth
	 ***********************************/
	
	public function selectUser($username, $password)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$document	= array('user_email' => $username, 'user_password' => md5($password), 'user_group' => USER_GROUP_ID, 'user_status' => '1');
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/*************************************
	 *	Method: selectUserByEmail     	  
	 *  Purpose: To validate email address
	 ************************************/
	
	public function selectUserByEmail($email)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$document	= array('user_email' => $email, 'user_group' => USER_GROUP_ID, 'user_status' => '1');
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/***********************************
	 *	Method: registerFbUser	    	
	 *  Purpose: To register a FB user	
	 **********************************/
	
	public function registerFBUser($formData) {
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$userId		= new \MongoId();
		$query		= array('_id'			=> $userId,
							'user_firstname'=> $formData['user_firstname'],
							'user_lastname' => $formData['user_lastname'],
							'user_email' 	=> $formData['user_email'],
							'user_group' 	=> USER_GROUP_ID,
							'user_fbuid' 	=> $formData['user_fbuid'],
							'user_password' => '',
							'user_dob' 		=> '',
							'user_gender'	=> '',
							'user_status'	=> '1',
							'date_created'	=> $formData['created_date'],
							'date_modified'	=> $formData['created_date']
							);
		$results	= $collection->insert($query);
		if($results) {
			return $userId;
		} else {
			return 0;
		}
	}
	/*************************************
	 *	Method: selectFBUser     	      
	 *  Purpose: To select user with FBUID
	 ************************************/
	
	public function selectFBUser($fbuid)
	{
		$conn		= $this->connect();
		$collection	= $conn->snapstate->users;
		$document	= array('user_fbuid' => $fbuid, 'user_group' => USER_GROUP_ID, 'user_status' => '1');
		$cursor		= $collection->find($document);
		return $cursor;
	}
	
	public function updateFBDetails($data)
	{
		$conn		= $this->connect();
		$document	= array('$set' => array('user_fbuid' => $data['user_fbuid'], 'user_status' => '1'));
		$query		= array('user_email' => $data['user_email']);
		$collection	= $conn->snapstate->users;
		$collection->update($query, $document);
	}
	/********************************************************************************************
	 *	Action: Index                                                                            
	 *	Page: It acts as a default page.                                                         
	 *******************************************************************************************/
	
	public function indexAction()
	{
		$this->layout('frontend');
    }
	/**************************************
	 *	Action: Dashboard                  
	 *	Page: Displays the dashboard screen
	 *************************************/
	
	public function dashboardAction()
    {
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
		}
		$this->layout('frontend');
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession
		));
    }
	/**************************************
	 *	Action: validate-registration      
	 *	Page: To register a new user	   
	 *************************************/
	
	public function validateRegistrationAction()
    {
		$userSession= new Container('user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['first_name']) && trim($formData['first_name']) != '' && isset($formData['last_name']) && trim($formData['last_name']) != '' && isset($formData['email_address']) && trim($formData['email_address']) != '' && isset($formData['registration_password']) && $formData['registration_password'] != '' && isset($formData['registration_confirm_password']) && $formData['registration_confirm_password'] != '' && $formData['registration_confirm_password'] == $formData['registration_password']) {
				$results	= $this->checkEmail($formData, 1);
				
				if($results == 1) {	// Email Address - already exist condition
					$result	= '-1';
				} else {
					$result	= $this->registerUser($formData);
					//	Registration Mail has to be sent
					
					$link		= '';
					$subject	= 'Snapstate - Confirmation Mail';
					$message	= '<html><head><title>Congratulations</title></head>
										<body>
											<div>
												<p>Hi '.ucwords($formData['first_name']).' '.ucwords($formData['last_name']).', </p> 
												<p>Congratulations! Please find the login information below.</p>
													<p></p>
													<p>Email Address: ' . $formData['email_address'] . '</p>
													<p>Password: '.$formData['registration_password'].'</p>
													<p></p>
													<p>Please click the below link in order to activate your account.</p>
													<p><a href="'.$link.'">'.$link.'</a></p>
													<p></p>
													<p></p>
													<p>Thanks,</p>
													<p>The Snapstate Team.</p>
												</div>
											</body>
										</html>';
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$headers .= 'From: Snapstate.com <admin@snapstate.com>' . "\r\n";
					$to		= 'To: ' . $formData['email_address'] . "\r\n";
					$headersMessage	= $headers . $to;
					//mail('', $subject, $message, $headersMessage);
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: validate-login			   
	 *	Page: Validate the authentication  
	 *************************************/
	
	public function validateLoginAction()
    {
		$userSession= new Container('user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['login_email']) && trim($formData['login_email']) != '' && isset($formData['login_password']) && trim($formData['login_password']) != '') {
				$results	= $this->selectUser($formData['login_email'], $formData['login_password']);
				
				while($results->hasNext()) {
					$resultArray	= $results->getNext();
				}
				
				if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid email address & password
					$userSession->userSession	= $resultArray;
					$result	= '1';
				} else {	//	Authentication failed
					$result	= '-1';
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: validate-forget-password   
	 *	Page: Validate the Forget Password 
	 *************************************/
	
	public function validateForgetPasswordAction()
    {
		$userSession= new Container('user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['forget_password_email']) && trim($formData['forget_password_email']) != '') {
				$results	= $this->selectUserByEmail($formData['forget_password_email']);
				
				while($results->hasNext()) {
					$resultArray	= $results->getNext();
				}
				if(isset($resultArray['_id'])) {
					$password	= mt_rand();
					$document	= array('$set' => array('user_password' => md5($password)));
					$query		= array('user_email' => $resultArray['user_email']);
					$collection	= $conn->snapstate->users;
					$collection->update($query, $document);
					
					$emailaddress	= 'deepan@sdi.la';
					$username	= $resultArray['user_firstname'].' '.$resultArray['user_lastname'];
					$email		= $resultArray['user_email'];
					//	Forget Password Mailer
					$subject	= 'Snapstate - Forget Password';
					$message	= '<html><head><title>Forget Password</title></head>
										<body>
											<div>
												<p>Hi '.ucwords($username).', </p> 
												<p>We have sent your password. Please find the login information below.</p>
													<p></p>
													<p>Email Address: ' . $email . '</p>
													<p>Password: '.$password.'</p>
													<p></p>
													<p></p>
													<p>Thanks,</p>
													<p>The Snapstate Team.</p>
												</div>
											</body>
										</html>';
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$headers .= 'From: Snapstate.com <admin@snapstate.com>' . "\r\n";
					$to		= 'To: ' . $emailaddress . "\r\n";
					$headersMessage	= $headers . $to;
					//mail('', $subject, $message, $headersMessage);
					$result	= '1';
				} else {
					$result	= '-1';
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/**************************************
	 *	Action: validate-fblogin		   
	 *	Page: To login with fb account	   
	 *************************************/
	
	public function validateFbloginAction()
    {
		$userSession= new Container('user');
		$request	= $this->getRequest();
		
		if($request->isPost()) {
			$formData	= $request->getPost();
			
			if(isset($formData['user_firstname']) && trim($formData['user_firstname']) != '' && isset($formData['user_lastname']) && trim($formData['user_lastname']) != '' && isset($formData['user_email']) && trim($formData['user_email']) != '' && isset($formData['user_fbuid']) && $formData['user_fbuid'] != '') {
				$formData['email_address']	= $formData['user_email'];
				$formData['created_date']	= date('m/d/Y H:i:s');
				$results	= $this->checkEmail($formData, 1);
				
				if($results == 1) {	// Already Registered
					$results	= $this->selectFBUser($formData['user_fbuid']);
					while($results->hasNext()) {
						$resultArray	= $results->getNext();
					}
					if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid FBUID
						$userSession->userSession	= $resultArray;
						$result	= '1';
					} else {
						$result	= $this->updateFBDetails($formData);
						$results= $this->selectFBUser($formData['user_fbuid']);
						while($results->hasNext()) {
							$resultArray	= $results->getNext();
						}
						if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid FBUID
							$userSession->userSession	= $resultArray;
							$result	= '1';
						} else {	//	Authentication failed
							$result	= '-2';
						}
					}
				} else {
					$result		= $this->registerFBUser($formData);
					$results	= $this->selectFBUser($formData['user_fbuid']);
					while($results->hasNext()) {
						$resultArray	= $results->getNext();
					}
					if(isset($resultArray) && is_array($resultArray) && count($resultArray) > 0) {	// Valid FBUID
						$userSession->userSession	= $resultArray;
						$result	= '1';
					} else {	//	Authentication failed
						$result	= '-2';
					}
				}
				echo $result;
				
			} else {
				echo "0";	//	improper request
			}
		} else {
			echo "0";	//	improper request
		}
		return $this->getResponse();
    }
	/***************************************************************************************
	 *	Action: Logout                                                                      
	 *	Note:	No view script for this action, it just clear the current user's credentials
	 **************************************************************************************/
	
	public function logoutAction()
	{
		//	Destroy Session Vars
		$userSession = new Container('user');
		$sessionArray	= array();
		foreach($userSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$userSession->offsetUnset($value);
		}
		//	Destroy listing Session Vars
		$listingSession = new Container('listing');
		$sessionArray	= array();
		foreach($listingSession->getIterator() as $key => $value) {
			$sessionArray[]	= $key;
		}
		foreach($sessionArray as $key => $value) {
			$listingSession->offsetUnset($value);
		}
		return $this->redirect()->toRoute('front', array('controller' => 'index', 'action' => 'index'));
	} 
}
