<?php declare(strict_types=1);

namespace App;

class Block
{
    public function __construct(public ?int $number = null, public ?Block $next = null) {}
}
