<?php
use PHPUnit\Framework\TestCase;
use Domain\Address\Address;

use InvalidArgumentException;

class AddressTest extends TestCase {
  public function testValidAddress() {
    $address = new Address(1, 100, 'Rua Durval Clemente', '1A', '00000-000', 'São Paulo', 'SP');

    $this->assertEquals(1, $address->getId());
    $this->assertEquals(100, $address->getCustomerId());
    $this->assertEquals('Rua Durval Clemente', $address->getStreet());
    $this->assertEquals('1A', $address->getNumber());
    $this->assertEquals('00000-000', $address->getZipcode());
    $this->assertEquals('São Paulo', $address->getCity());
    $this->assertEquals('SP', $address->getState());
  }

  public function testEmptyStreetThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Street is required.');

    new Address(1, 100, '', '1A', '00000-000', 'São Paulo', 'SP');
  }

  public function testEmptyNumberThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Number is required.');

    new Address(1, 100, 'Rua Durval Clemente', '', '00000-000', 'São Paulo', 'SP');
  }

  public function testEmptyZipcodeThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid postal code format. Expected format: #####-###');

    new Address(1, 100, 'Rua Durval Clemente', '1A', '', 'São Paulo', 'SP');
  }

  public function testEmptyCityThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('City is required.');

    new Address(1, 100, 'Rua Durval Clemente', '1A', '00000-000', '', 'SP');
  }

  public function testEmptyStateThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('State is required.');

    new Address(1, 100, 'Rua Durval Clemente', '1A', '00000-000', 'São Paulo', '');
  }

  public function testInvalidZipcodeFormatThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid postal code format. Expected format: #####-###');

    new Address(1, 100, 'Rua Durval Clemente', '1A', '00000000', 'São Paulo', 'SP');
  }

  public function testInvalidZipcodeWithLettersThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid postal code format. Expected format: #####-###');

    new Address(1, 100, 'Rua Durval Clemente', '1A', '12A45-678', 'São Paulo', 'SP');
  }
}
