<?php

namespace Application\Tests;

use PHPUnit\Framework\TestCase;

use Domain\Address\Address;

use Infrastructure\DependencyInjection\DependencyInjectionContainer;

use Infrastructure\Database\Connection\DatabaseConnection;
use Infrastructure\Database\Setup\DatabaseSetup;

class AddressServiceIntegrationTest extends TestCase {
  private $addressService;
  private $customerService;
  private $container;

  protected function setUp(): void {
    $pdo = DatabaseConnection::getConnection();
    $this->container = new DependencyInjectionContainer($pdo);
    $this->addressService = $this->container->getAddressService();
    $this->customerService = $this->container->getCustomerService();

    DatabaseSetup::clearTestDatabase();
  }

  protected function tearDown(): void {
    DatabaseSetup::clearTestDatabase();
  }

  private function createTestCustomer(): int {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '12.345.678-9',
      'phone' => '(12) 3456-7890'
    ];

    $this->customerService->create($customerData);
    $customers = $this->customerService->list();

    $this->assertNotEmpty($customers);
    return $customers[0]['id'];
  }

  public function testCreateAddress() {
    $customerId = $this->createTestCustomer();

    $addresses = [[
      'customerId' => $customerId,
      'street' => 'Rua Durval Clemente',
      'number' => '1A',
      'zipcode' => '12345-678',
      'city' => 'São Paulo',
      'state' => 'SP'
    ]];

    $this->addressService->createMany($customerId, $addresses);
    $retrieved = $this->addressService->listByCustomerId($customerId);

    $this->assertNotEmpty($retrieved);
    $this->assertEquals('Rua Durval Clemente', $retrieved[0]->getStreet());
  }

  public function testUpdateAddress(): void {
    $customerId = $this->createTestCustomer();
  
    $addresses = [[
      'street' => 'Rua Durval Clemente',
      'number' => '1',
      'zipcode' => '12345-678',
      'city' => 'São Paulo',
      'state' => 'SP'
    ]];
  
    $this->addressService->createMany($customerId, $addresses);
  
    $retrieved = $this->addressService->listByCustomerId($customerId);
    $this->assertNotEmpty($retrieved);
  
    $updated = [[
      'id' => $retrieved[0]->getId(),
      'customerId' => $customerId,
      'street' => 'Rua Atualizada',
      'number' => '2B',
      'zipcode' => '02041-000',
      'city' => 'Rio de Janeiro',
      'state' => 'RJ'
    ]];
  
    $this->addressService->updateMany($updated);
  
    $afterUpdate = $this->addressService->listByCustomerId($customerId);
    $this->assertEquals('Rua Atualizada', $afterUpdate[0]->getStreet());
    $this->assertEquals('RJ', $afterUpdate[0]->getState());
  }
  

  public function testRemoveAddress(): void {
    $customerId = $this->createTestCustomer();
  
    $addresses = [[
      'street' => 'Rua X',
      'number' => '10',
      'zipcode' => '99999-999',
      'city' => 'City',
      'state' => 'ST'
    ]];
  
    $this->addressService->createMany($customerId, $addresses);
  
    $retrieved = $this->addressService->listByCustomerId($customerId);
    $this->assertNotEmpty($retrieved);
  
    $ids = array_map(fn($addr) => $addr->getId(), $retrieved);
    $this->addressService->removeMany($ids);
  
    $afterRemoval = $this->addressService->listByCustomerId($customerId);
    $this->assertEmpty($afterRemoval);
  }
  

  public function testAddressValidationFailsIfStreetMissing() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Street is required.');

    new Address(null, 1, '', '1', '12345-678', 'São Paulo', 'SP');
  }

  public function testInvalidZipcodeFormat() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid postal code format. Expected format: #####-###');

    new Address(null, 1, 'Rua', '1', '1234', 'São Paulo', 'SP');
  }

  public function testCityIsRequired() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('City is required.');

    new Address(null, 1, 'Rua Teste', '1', '12345-678', '', 'SP');
  }

  public function testStateIsRequired() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('State is required.');

    new Address(null, 1, 'Rua Teste', '1', '12345-678', 'São Paulo', '');
  }

  public function testNumberIsRequired() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Number is required.');

    new Address(null, 1, 'Rua Teste', '', '12345-678', 'São Paulo', 'SP');
  }
}
