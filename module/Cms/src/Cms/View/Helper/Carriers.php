<?php	
namespace Cms\View\Helper;
use Zend\View\Helper\AbstractHelper;

//	Session
use Zend\Session\Container;

class Carriers extends AbstractHelper
{
	protected $result;
	
	public function __invoke($val)
    {
		$userSession = new Container('user');
		$this->result	= array();
		if($userSession->offsetExists('carriersList')) {
			$this->result	= $userSession->carriersList;
		}
        return $this->result;
    }
	
}
 ?>