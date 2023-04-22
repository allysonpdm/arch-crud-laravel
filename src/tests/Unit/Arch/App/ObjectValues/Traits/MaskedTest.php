<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues\Traits;

use ArchCrudLaravel\App\ObjectValues\Traits\Masked;
use Tests\TestCase;

class MaskedTest extends TestCase
{
    protected string $mask;
    protected mixed $value = 123457890;
    use Masked;

    public function testSetMask()
    {
        $this->setMask('##.##.##-##');
        
        $this->assertEquals('##.##.##-##', $this->mask);
    }

    public function testMaskedValue()
    {
        $this->setMask('(##) ####-####');
        $formattedValue = $this->masked();

        $this->assertEquals('(12) 3456-7890', $formattedValue);
    }
}
