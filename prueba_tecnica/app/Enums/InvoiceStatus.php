<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case DUE = 'due';
}
