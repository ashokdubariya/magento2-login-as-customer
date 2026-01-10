<?php
/**
 * Audit Log Collection
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Model\ResourceModel\AuditLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ashokkumar\LoginAsCustomer\Model\AuditLog;
use Ashokkumar\LoginAsCustomer\Model\ResourceModel\AuditLog as AuditLogResource;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AuditLog::class, AuditLogResource::class);
    }
}
