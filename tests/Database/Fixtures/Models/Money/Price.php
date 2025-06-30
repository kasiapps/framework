<?php

namespace Kasi\Tests\Database\Fixtures\Models\Money;

use Kasi\Database\Eloquent\Factories\HasFactory;
use Kasi\Database\Eloquent\Model;
use Kasi\Tests\Database\Fixtures\Factories\Money\PriceFactory;

class Price extends Model
{
    /** @use HasFactory<PriceFactory> */
    use HasFactory;

    protected $table = 'prices';

    protected static string $factory = PriceFactory::class;
}
