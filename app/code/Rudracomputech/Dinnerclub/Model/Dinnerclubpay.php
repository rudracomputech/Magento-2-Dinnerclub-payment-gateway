<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rudracomputech\Dinnerclub\Model;



/**
 * Pay In Store payment method model
 */
class Dinnerclubpay extends \Magento\Payment\Model\Method\Cc
{

     protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_stripeApi = false;
    protected $_countryFactory;
    protected $_checkoutSession;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('USD');
    protected $_debugReplacePrivateDataKeys
        = ['number', 'exp_month', 'exp_year', 'cvc'];
	protected $_code = 'dinnerclubpay';
	const METHOD_CODE  = 'dinnerclubpay';	
	
	 public function __construct(\Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		 \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = array()
    ) {
        parent::__construct(
            $context, $registry, $extensionFactory, $customAttributeFactory,
            $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, null,
            null, $data
        );
        $this->_countryFactory = $countryFactory;
		 $this->_checkoutSession = $checkoutSession;
        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
    }
	
	 public function getVerificationRegEx()
    {
        return array_merge(parent::getVerificationRegEx(), array(
            'DN' => '/^[0-9]{3}$/' // Diners Club CCV
       ));
    }
	
	
	public function validate()
	{
		$info = $this->getInfoInstance();
		
		
		$errorMsg = false;
		$ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);
		if (!$info->getCcCid()) {
			$errorMsg = __('Please enter a valid credit card verification number.');
		}
		if (!$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = __('Please enter a valid credit card expiration date.');
        }
		if ($errorMsg) {
            throw new \Magento\Framework\Exception\LocalizedException($errorMsg);
			 return $this;
        }else{
			
			
		
		$this->_checkoutSession->setCcNumber($info->getCcNumber());
		$this->_checkoutSession->setCcExpMonth($info->getCcExpMonth());
		$this->_checkoutSession->setCcExpYear($info->getCcExpYear());
		$this->_checkoutSession->setCcCid($info->getCcCid());
		$this->_checkoutSession->setInstallments($info->getAdditionalInformation('installments'));
		
		return true;
		}
		
		
        
	}
	
	
	
	
	 /**
     * @param string $expYear
     * @param string $expMonth
     * @return bool
     */
    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = new \DateTime();
        if (!$expYear || !$expMonth || (int)$date->format('Y') > $expYear
            || (int)$date->format('Y') == $expYear && (int)$date->format('m') > $expMonth
        ) {
            return false;
        }
        return true;
    }
	
	
	

    /**
     * Assign corresponding data
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $infoInstance = $this->getInfoInstance();

        $infoInstance->setAdditionalInformation('installments',$data['additional_data']['installments']);
       
        return $this;
    }
  
  

}
