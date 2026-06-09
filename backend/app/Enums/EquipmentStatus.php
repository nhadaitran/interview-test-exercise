<?php

namespace App\Enums;

enum EquipmentStatus: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case MAINTENANCE = 'maintenance';
}
