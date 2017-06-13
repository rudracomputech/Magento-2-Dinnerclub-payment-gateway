<?php
namespace Rudracomputech\Dinnerclub\Controller\Payment;




class Verify extends \Magento\Framework\App\Action\Action {
	
  
  
	
    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context
    
    )
    {
		

    	
        parent::__construct($context);
    }
	
	
 
	
	public function execute() {
		echo 'OK';
		//return;
		
		
	
	}

	
}
