<?php
/**
 * Audit Log Resource Model
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AuditLog Resource Model
 */
class AuditLog extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ashokkumar_login_as_customer_log', 'entity_id');
    }
}
