<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case PENDING = 'pending';

    case RECEIVED = 'received';

    case CANCELLED = 'cancelled';
}