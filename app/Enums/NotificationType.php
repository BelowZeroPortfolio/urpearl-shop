<?php

namespace App\Enums;

enum NotificationType: string
{
    case LOW_STOCK = 'low_stock';
    case ORDER_CREATED = 'order_created';
    case ORDER_STATUS_CHANGED = 'order_status_changed';
}