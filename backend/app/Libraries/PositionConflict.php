<?php
namespace App\Libraries;

final class PositionConflict extends \RuntimeException
{
    public array $details;
    public function __construct(array $details)
    {
        parent::__construct('Choose the Upgrade Position to keep.');
        $this->details = $details;
    }
}
