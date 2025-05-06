<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Address;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    #[Test]
    public function it_can_be_created_with_state(): void
    {
        $address = new Address(
            'Main Street',
            'New York',
            '10001',
            'USA',
            'New York'
        );

        $this->assertEquals('Main Street', $address->street);
        $this->assertEquals('New York', $address->city);
        $this->assertEquals('10001', $address->postalCode);
        $this->assertEquals('USA', $address->country);
        $this->assertEquals('New York', $address->state);
    }

    #[Test]
    public function it_can_be_created_without_state(): void
    {
        $address = new Address(
            'Main Street',
            'New York',
            '10001',
            'USA'
        );

        $this->assertEquals('Main Street', $address->street);
        $this->assertEquals('New York', $address->city);
        $this->assertEquals('10001', $address->postalCode);
        $this->assertEquals('USA', $address->country);
        $this->assertNull($address->state);
    }

    #[Test]
    public function it_can_be_created_from_array_with_state(): void
    {
        $address = Address::fromArray([
            'street' => 'Main Street',
            'city' => 'New York',
            'postal_code' => '10001',
            'country' => 'USA',
            'state' => 'New York',
        ]);

        $this->assertEquals('Main Street', $address->street);
        $this->assertEquals('New York', $address->city);
        $this->assertEquals('10001', $address->postalCode);
        $this->assertEquals('USA', $address->country);
        $this->assertEquals('New York', $address->state);
    }

    #[Test]
    public function it_can_be_created_from_array_without_state(): void
    {
        $address = Address::fromArray([
            'street' => 'Main Street',
            'city' => 'New York',
            'postal_code' => '10001',
            'country' => 'USA',
        ]);

        $this->assertEquals('Main Street', $address->street);
        $this->assertEquals('New York', $address->city);
        $this->assertEquals('10001', $address->postalCode);
        $this->assertEquals('USA', $address->country);
        $this->assertNull($address->state);
    }

    #[Test]
    public function it_converts_to_array_with_state(): void
    {
        $address = new Address(
            'Main Street',
            'New York',
            '10001',
            'USA',
            'New York'
        );

        $array = $address->toArray();

        $this->assertEquals([
            'street' => 'Main Street',
            'city' => 'New York',
            'postal_code' => '10001',
            'country' => 'USA',
            'state' => 'New York',
        ], $array);
    }

    #[Test]
    public function it_converts_to_array_without_state(): void
    {
        $address = new Address(
            'Main Street',
            'New York',
            '10001',
            'USA'
        );

        $array = $address->toArray();

        $this->assertEquals([
            'street' => 'Main Street',
            'city' => 'New York',
            'postal_code' => '10001',
            'country' => 'USA',
        ], $array);
    }
}
