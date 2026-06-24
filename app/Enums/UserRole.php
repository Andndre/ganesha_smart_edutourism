<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case TicketOfficer = 'ticket_officer';
    case UmkmOwner = 'umkm_owner';
    case Tourist = 'tourist';
}
