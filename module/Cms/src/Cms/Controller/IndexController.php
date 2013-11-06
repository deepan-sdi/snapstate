<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Cms\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//	Session
use Zend\Session\Container;

//	Auth
use Zend\Authentication,
	Zend\Authentication\Result,
	Zend\Authentication\AuthenticationService;

//	Forms
use Cms\Form\LoginForm;
use Cms\Form\ForgetPasswordForm;
use Cms\Form\SiteSettingsForm;

//	Models
use Cms\Model\Users;

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
	/************************************
	 *	Method: selectUser     	         
	 *  Purpose: To select user with auth
	 ***********************************/
	
	public function selectUser($conn, $username, $password)
	{
		$collection	= $conn->snapstate->users;
		$document	= array('user_email' => $username, 'user_password' => $password, 'group_id' => '1');
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/*************************************
	 *	Method: selectUserByEmail     	  
	 *  Purpose: To validate email address
	 ************************************/
	
	public function selectUserByEmail($conn, $email)
	{
		$collection	= $conn->snapstate->users;
		$document	= array('user_email' => $email);
		$cursor		= $collection->find($document);
		return $cursor;
	}
	/********************************************************************************************
	 *	Action: Index                                                                            
	 *	Page: It acts as a default page. Authentication process will be triggered from this page.
	 *******************************************************************************************/
	
	public function indexAction()
	{
		$userSession = new Container('user');
		if (isset($userSession->userSession['_id']) && trim($userSession->userSession['_id']) != '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'dashboard'));
		}
	    
		$loginForm	= new LoginForm();
	    $loginForm->get('submit')->setValue('Login');
		$forgetPasswordForm = new ForgetPasswordForm();
	 	$request	= $this->getRequest();
		$message	= '';
		
		if ($request->isPost()) {
            $usersModel	= new Users();
            $loginForm->setInputFilter($usersModel->getInputFilter());
            $loginForm->setData($request->getPost());
			
            if ($loginForm->isValid()) {
				$formData	= $loginForm->getData();
				$conn		= $this->connect();
				$results	= $this->selectUser($conn, trim($formData['Email']), trim(md5($formData['Password'])));
				
				while($results->hasNext())
				{
					$resultArray	= $results->getNext();
				}
				
				if(isset($resultArray['_id'])) {
					if($formData["Remember"] == 1) {
						setcookie('cookie_user_email', trim($formData['Email']), time()+60*60*24*30, '/');
						setcookie('cookie_user_password', trim($formData['Password']), time()+60*60*24*30, '/');
					} else {
						setcookie('cookie_user_email', '', time()+60*60*24*30, '/');
						setcookie('cookie_user_password', '', time()+60*60*24*30, '/');
					}
					//	Session for Carrier accessibility
					$userSession = new Container('user');
					$userSession->userSession	= $resultArray;
					return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'dashboard'));
				} else {
					$message	= "The email address or password you entered is incorrect.";
				}
				
            }
        }
		/*	Cookie for Remember me option	*/
		$cookie = $this->getRequest()->getCookie();
		if($cookie) {
			if ($cookie->offsetExists('cookie_user_email') && $cookie->offsetExists('cookie_user_password')) {
				$loginForm->get('Email')->setValue($cookie->offsetGet('cookie_user_email'));
				$loginForm->get('Password')->setValue($cookie->offsetGet('cookie_user_password'));
				$loginForm->get('Remember')->setAttribute('checked', true);
			}
		}
		return new ViewModel(array(
            'page' => 1,	// page	=> 1	//	No header, No Leftnav
			'loginForm' => $loginForm,
			'forgetPasswordForm' => $forgetPasswordForm,
			'message'	=> $message,
        ));
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
		return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
	} 
	/****************************
	 *	Action: Forget Password  
	 *	Type:	Ajax call        
	 ***************************/
	
	public function forgetPasswordAction() {
		$request = $this->getRequest();
		if($request->isPost()) {
			$usersModel	= new Users();
			$forgetPasswordForm = new ForgetPasswordForm();
            $forgetPasswordForm->setInputFilter($usersModel->getInputFilterForgetPassword());
            $forgetPasswordForm->setData($request->getPost());
			$conn			= $this->connect();
			
            if ($forgetPasswordForm->isValid()) {
				$formData	= $request->getPost();
				$results	= $this->selectUserByEmail($conn, trim($formData['email']));
				while($results->hasNext())
				{
					$resultArray	= $results->getNext();
				}
				if(isset($resultArray['_id'])) {
					$password	= mt_rand();
					$document	= array('$set' => array('password' => md5($password)));
					$query		= array('user_email' => $resultArray['user_email']);
					$collection	= $conn->snapstate->users;
					$collection->update($query, $document);
					/*	echo $password;
					die();	*/
					/*	$subject	= 'Snapstate Admin Panel - Forget Password';
						$message	= '<html><head><title>Forget Password</title></head>
											<body>
												<div>
													<p>Hi '.ucwords($username).', </p> 
													<p>We have sent your password. Please find the login information below.</p>
													<p></p>
													<p>Email Address: ' . $formData['email'] . '</p>
													<p>Password: '.$password.'</p>
													<p></p>
													<p></p>
													<p>Thanks,</p>
													<p>Snapstate.com Team.</p>
												</div>
											</body>
										</html>';
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
						$headers .= 'From: Sbapstate.com <admin@snapstate.com>' . "\r\n";
						$to = 'To: ' . $emailaddress . "\r\n";
						$headersMessage	= $headers . $to;
						mail('', $subject, $message, $headersMessage);	*/
					echo '1';
				} else {
					echo '0';
				}
			} else {
				echo '0';
			}
		}
		return $this->getResponse();
	}
	/**************************************
	 *	Action: Dashboard                  
	 *	Page: Displays the dashboard screen
	 *************************************/
	
	public function dashboardAction()
    {
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		return new ViewModel(array(
			'userObject'	=> $userSession->userSession
		));
    }
	/**************************************
	 *	Action: site-settings              
	 *	Page: Displays the Site settings   
	 *************************************/
	
	public function siteSettingsAction()
    {
		$userSession = new Container('user');
		if (!isset($userSession->userSession['_id']) || trim($userSession->userSession['_id']) == '') {
			return $this->redirect()->toRoute('cms', array('controller' => 'index', 'action' => 'index'));
		}
		$siteSettingsForm	= new SiteSettingsForm();
		$siteDetails		= array();
		$message	= '';
		return new ViewModel(array(
			'userObject'		=> $userSession->userSession,
			'siteSettingsForm'	=> $siteSettingsForm,
			'message'			=> $message,
			'siteDetails'		=> $siteDetails,
		));
    }
}
