<?php
/**
 * Payment CC Types Source Model
 *

 */

namespace Rudracomputech\Dinnerclub\Model\Source;

class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return array('VI','DN', 'MC', 'AE', 'DI', 'JCB', 'OT');
    }
	

}
