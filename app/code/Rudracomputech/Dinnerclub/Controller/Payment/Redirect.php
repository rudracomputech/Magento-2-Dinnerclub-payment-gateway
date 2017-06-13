<?php
namespace Rudracomputech\Dinnerclub\Controller\Payment;


use Magento\Sales\Model\Order;

class Redirect extends \Magento\Framework\App\Action\Action {
	
  
  
	protected $_checkoutSession;
	
	 /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
	
	
	/**
     * Order object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
	
	 /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
	private $redirectFactory;
	protected $messageManager;
    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
		

    	 $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
		  $this->redirectFactory = $redirectFactory;
		  $this->messageManager = $messageManager;
        $this->_logger = $logger;
        parent::__construct($context);
    }
	
	
   protected function _getOrder($incrementId= null)
    {
        if (!$this->_order) {
            $incrementId = $incrementId ? $incrementId : $this->_getCheckout()->getLastRealOrderId();
            $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        }
        return $this->_order;
    }
	
	/**
     * Get frontend checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Execute view action
     * 
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		
	$error_array = array(1=>'Transaction failed!',
						 2=>'Transaction timeout!',
						 3=>'Authorization server unreachable!',
						 4=>'Authorization request invalid!',
						 10=>'Incorrect PAN length!',
						 11=>'Incorrect card valid date length!',
						 12=>'Incorrect CVV2 length!',
						 13=>'Incorrect currency_code length!',
						 14=>'Incorrect amount length!',
						 15=>'MySQL connect failed!',
						 16=>'Too many installments!',
						 20=>'This transaction does not exist!'
						);
						
					
            
			if (!$this->_getOrder()->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('No order for processing found'));
            }
			
			
			$orderId = $this->_getOrder()->getIncrementId();
			$order = $this->_getOrder();
			
		#$payment = $info->getPayment();
       # $order = $payment->getOrder();
		#$payment = $order->getPayment();
		
      
		$ccNumbers = $this->_getCheckout()->getCcNumber();
		 // remove credit card number delimiters such as "-" and space
                $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumbers);
		
		$CcExpMonth = sprintf("%02d", $this->_getCheckout()->getCcExpMonth());
		
		$CcExpYear = substr($this->_getCheckout()->getCcExpYear(),-2);
		$Cvv2 = $this->_getCheckout()->getCcCid();
		
		$configHelper = $this->_objectManager->get('Rudracomputech\Dinnerclub\Helper\Data');
		
        
		
		
		
		$gatewayurl = $configHelper->getConfig('payment/dinnerclubpay/gatewayurl');
		/*Data to post using query in array */  
		$post_data["acceptor_id"]= $configHelper->getConfig('payment/dinnerclubpay/gatewayacceptorid');
		$post_data["currency_code"]= 978; // according to document it set to be 978 always
		$post_data["req_type"]= 'auth';
		$post_data["language"]= 'si';
		$post_data["email"]=  $order->getCustomerEmail();
		
		/* card details */
		$post_data["product_desc"]=  'Nakup v spletni trgovini BFC Shop ';
		$post_data["transaction_id"]=  $orderId;
		$post_data["amount"]= number_format($order->getGrandTotal(), 2, '.', ''); // number_format($order->getGrandTotal() ,2);
		$post_data["installments"]= $this->_getCheckout()->getInstallments();
		$post_data["valid"]=  $CcExpMonth.$CcExpYear ;
		$post_data["pan"]=  $ccNumber;
		$post_data["cvv2"]=  $Cvv2;
		$post_data["send"]= 'Send';
		
		
		
		$str = http_build_query($post_data);
		$ch = curl_init(); 
		// set the post-to url (do not include the ?query+string here!) 
		curl_setopt ($ch, CURLOPT_URL, $gatewayurl);
		// Header control 
		//Tell it to make a POST, not a GET 
		curl_setopt ($ch, CURLOPT_POST, 1);
		// Put the query string here starting without "?"
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $str);
		// This allows the output to be set into a variable
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		// execute the curl session and return the output to a variable $response        
		$response = curl_exec($ch);
		
		$xml = simplexml_load_string($response);
		// Close the curl session 
		curl_close ($ch); 
		
		
	
		
		
		if(isset($xml->error_code) ){
			$responseErrorText = $xml->error_code->__toString();
		}else{
			$this->_getCheckout()->unsQuoteId();

			$this->messageManager->addError( __("Payment gateway is not configured correctly.Please try to make payment again."));

			#$order->setStatus($order::STATUS_FRAUD);

			return $this->redirectFactory->create()->setPath('checkout/onepage/failure');
		}
		
		
		
		if($responseErrorText == 0 ) {
			
			
				            // Payment was successful, so update the order's state, send order email and move to the success page
					
					$order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);

					#$order->sendNewOrderEmail();
					
					#$order->setEmailSent(true);
					
					$order->save();

					$this->messageManager->addSuccess( __($xml->error_text->__toString()) );
					
					return $this->redirectFactory->create()->setPath('checkout/onepage/success');
					
				}
			else {
			#echo "under else";
			
				// There is a problem in the response we get
				//$this->cancelAction($responseErrorText);
				
				    $this->_getCheckout()->unsQuoteId();
					
					$this->messageManager->addError( __('Error  "'.$error_array[$responseErrorText].'" '.$xml->error_text->__toString()) );
					
					#$order->setStatus($order::STATUS_FRAUD);
				
				return $this->redirectFactory->create()->setPath('checkout/onepage/failure');
				
			}
		
		
       
    }
	
	
	
}
