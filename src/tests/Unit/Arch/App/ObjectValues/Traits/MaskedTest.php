<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues\Traits;

use ArchCrudLaravel\App\ObjectValues\Traits\Masked;
use Tests\TestCase;

class MaskedTest extends TestCase
{
    protected string $mask;
    protected mixed $value;
    use Masked;

    public function testSetMask()
    {
        $this->setMask('##.##.##-##');
        
        $this->assertEquals('##.##.##-##', $this->mask);
    }

    /**
     * @dataProvider maskedValueProvider
     */
    public function testMaskedValue($value, $mask, $expected)
    {
        $this->value = $value;
        $this->setMask($mask);
        $formattedValue = $this->masked();

        $this->assertEquals($expected, $formattedValue);
    }

    public function maskedValueProvider()
    {
    return [
        [1234567890, '(##) ####-####', '(12) 3456-7890'],
        [52998224725, '###.###.###-##', '529.982.247-25'],
        ['99999999999', '###.###.###-##', '999.999.999-99'],
        ['32631433000131', '##.###.###/####-##', '32.631.433/0001-31'],
        [75113190, '#####-###', '75113-190'],
    ];
}
}
