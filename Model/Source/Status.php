<?php
/**
 * Status Source Model
 *
 * @category  Ashokkumar
 * @package   Ashokkumar_LoginAsCustomer
 */
declare(strict_types=1);

namespace Ashokkumar\LoginAsCustomer\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Ashokkumar\LoginAsCustomer\Model\AuditLog;

/**
 * Class Status
 *
 * Provides status options for filters and grid
 */
class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => AuditLog::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => AuditLog::STATUS_SUCCESS, 'label' => __('Success')],
            ['value' => AuditLog::STATUS_EXPIRED, 'label' => __('Expired')],
            ['value' => AuditLog::STATUS_FAILED, 'label' => __('Failed')]
        ];
    }
}
