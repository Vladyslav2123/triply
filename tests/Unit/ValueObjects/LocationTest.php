<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\Address;
use App\ValueObjects\Coordinates;
use App\ValueObjects\Location;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    #[Test]
    public function it_converts_to_string_with_state(): void
    {
        $address = new Address(
            'Main Street',
            'New York',
            '10001',
            'USA',
            'New York'
        );

        $coordinates = new Coordinates(40.7128, -74.0060);
        $location = new Location($address, $coordinates);

        $this->assertEquals('Country: USA, City: New York, State: New York', (string) $location);
    }

    #[Test]
    public function it_converts_to_string_without_state(): void
    {
        $address = new Address(
            'Main Street',
            'New York',
            '10001',
            'USA'
        );

        $coordinates = new Coordinates(40.7128, -74.0060);
        $location = new Location($address, $coordinates);

        $this->assertEquals('Country: USA, City: New York', (string) $location);
    }

    #[Test]
    public function it_can_be_created_from_array_with_state(): void
    {
        $location = Location::fromArray([
            'address' => [
                'street' => 'Main Street',
                'city' => 'New York',
                'postal_code' => '10001',
                'country' => 'USA',
                'state' => 'New York',
            ],
            'coordinates' => [
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
        ]);

        $this->assertEquals('Main Street', $location->address->street);
        $this->assertEquals('New York', $location->address->city);
        $this->assertEquals('10001', $location->address->postalCode);
        $this->assertEquals('USA', $location->address->country);
        $this->assertEquals('New York', $location->address->state);
        $this->assertEquals(40.7128, $location->coordinates->latitude);
        $this->assertEquals(-74.0060, $location->coordinates->longitude);
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

        $coordinates = new Coordinates(40.7128, -74.0060);
        $location = new Location($address, $coordinates);

        $array = $location->toArray();

        $this->assertEquals([
            'address' => [
                'street' => 'Main Street',
                'city' => 'New York',
                'postal_code' => '10001',
                'country' => 'USA',
                'state' => 'New York',
            ],
            'coordinates' => [
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
        ], $array);
    }
}
