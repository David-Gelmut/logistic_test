<?php

namespace App\Enums;

enum NotificationStatusEnum: string
{
case QUEUED = 'queued';
case SENT = 'sent';
case DELIVERED = 'delivered';
case CANCELED = 'canceled';
case FAILED = 'failed';
}
