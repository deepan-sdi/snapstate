<?php	
namespace Cms\View\Helper;
use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;  
use Zend\ServiceManager\ServiceLocatorInterface;

//	Models
use Cms\Model\Carrier;

//	Cache
//use Zend\Cache\Storage\StorageInterface;

class Carrierlist extends AbstractHelper implements ServiceLocatorAwareInterface 
{
    //protected $cache;
	
	protected $result;
	protected $table;
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)  
    {  
        $this->serviceLocator = $serviceLocator;  
        return $this;  
    }
	
	public function getServiceLocator()  
    {  
        return $this->serviceLocator;  
    }
	
	/*	public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
    }	*/
	
	public function __construct(Carrier $table)
	{
	    $this->table = $table;
	}
	
	public function __invoke($val)
    {
		$this->result = $this->table->fetchCarriers();
        return $this->result;
    }
	
	public function setTableCount($sm, $myCountTable)
    {
       /*	 $this->result = $this->getCarrierTable()->fetchCarriers();
        return $this->result;	*/
		
    }
	
	public function getCarrierTable()
    {
        if (!isset($this->carrierTable)) {
            $sm = $this->getServiceLocator();
            $this->carrierTable = $sm->get('Cms\Model\CarrierTable');
        }
        return $this->carrierTable;
    }
}
 ?>