<?php
declare(strict_types=1);
namespace ForgeLabsUk\SyliusUnleashedOrdersPlugin\Enum;
class OrderStateEnum
{
    public const PENDING = 'pending';
    public const WAITING = 'waiting';
    public const IN_PROGRESS = 'in_progress';
    public const FINISHED = 'finished';
    public const ERROR = 'error';
    public const TERMINATION = 'termination';
}
