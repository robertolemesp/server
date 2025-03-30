<?php
namespace Domain\Address\Tests;

use PDO;
use PHPUnit\Framework\TestCase;

use Domain\Address\Address;
use Infrastructure\Repository\Address\MySQLAddressRepository;

class AddressRepositoryIntegrationTest extends TestCase {
  private $pdo;
  private $addressRepository;

  protected function setUp(): void {
    global $pdo;
    
    $this->pdo = $pdo;
    
    $this->pdo->exec("DELETE FROM customer_address");

    $this->addressRepository = new MySQLAddressRepository($this->pdo);
  }

  public function testCreateManyAddresses() {
    $customerId = 1;

    $addresses = [
      new Address(null, $customerId, 'Rua Durval Clemente', '10', '00000-000', 'São Paulo', 'SP'),
      new Address(null, $customerId, 'Rua Teste', '20', '11111-111', 'Rio de Janeiro', 'RJ')
    ];

    $createdAddresses = $this->addressRepository->createMany($customerId, $addresses);

    $this->assertCount(2, $createdAddresses);
    $this->assertEquals('Rua Durval Clemente', $createdAddresses[0]->getStreet());
    $this->assertEquals('10', $createdAddresses[0]->getNumber());
    $this->assertEquals('00000-000', $createdAddresses[0]->getZipcode());
    $this->assertEquals('São Paulo', $createdAddresses[0]->getCity());
    $this->assertEquals('SP', $createdAddresses[0]->getState());
    
    $this->assertEquals('Rua Teste', $createdAddresses[1]->getStreet());
    $this->assertEquals('20', $createdAddresses[1]->getNumber());
    $this->assertEquals('11111-111', $createdAddresses[1]->getZipcode());
    $this->assertEquals('Rio de Janeiro', $createdAddresses[1]->getCity());
    $this->assertEquals('RJ', $createdAddresses[1]->getState());
  }

  public function testFindById() {
    $address = new Address(null, 1, 'Rua Nova', '30', '22222-222', 'Belo Horizonte', 'MG');
    $createdAddresses = $this->addressRepository->createMany(1, [$address]);

    $foundAddress = $this->addressRepository->findById($createdAddresses[0]->getId());

    $this->assertNotNull($foundAddress);
    $this->assertEquals('Rua Nova', $foundAddress->getStreet());
    $this->assertEquals('30', $foundAddress->getNumber());
    $this->assertEquals('22222-222', $foundAddress->getZipcode());
    $this->assertEquals('Belo Horizonte', $foundAddress->getCity());
    $this->assertEquals('MG', $foundAddress->getState());
  }

  public function testFindAddressesByCustomerId() {
    $customerId = 2;

    $addresses = [
      new Address(null, $customerId, 'Rua A', '100', '33333-333', 'Curitiba', 'PR'),
      new Address(null, $customerId, 'Rua B', '200', '44444-444', 'Porto Alegre', 'RS')
    ];

    $this->addressRepository->createMany($customerId, $addresses);
    $retrievedAddresses = $this->addressRepository->findByCustomerId($customerId);

    $this->assertCount(2, $retrievedAddresses);
    $this->assertEquals('Rua A', $retrievedAddresses[0]->getStreet());
    $this->assertEquals('100', $retrievedAddresses[0]->getNumber());
    $this->assertEquals('Curitiba', $retrievedAddresses[0]->getCity());
    $this->assertEquals('PR', $retrievedAddresses[0]->getState());

    $this->assertEquals('Rua B', $retrievedAddresses[1]->getStreet());
    $this->assertEquals('200', $retrievedAddresses[1]->getNumber());
    $this->assertEquals('Porto Alegre', $retrievedAddresses[1]->getCity());
    $this->assertEquals('RS', $retrievedAddresses[1]->getState());
  }

  public function testUpdateManyAddresses() {
    $customerId = 3;

    $addresses = [
      new Address(null, $customerId, 'Rua Original', '50', '55555-555', 'Recife', 'PE')
    ];

    $createdAddresses = $this->addressRepository->createMany($customerId, $addresses);

    $updatedAddress = new Address(
      $createdAddresses[0]->getId(),
      $customerId,
      'Rua Alterada',
      '60',
      '11111-111',
      'Rio de Janeiro',
      'RJ'
    );

    $this->addressRepository->updateMany([$updatedAddress]);

    $retrievedAddress = $this->addressRepository->findById($updatedAddress->getId());

    $this->assertEquals('Rua Alterada', $retrievedAddress->getStreet());
    $this->assertEquals('60', $retrievedAddress->getNumber());
    $this->assertEquals('11111-111', $retrievedAddress->getZipcode());
    $this->assertEquals('Rio de Janeiro', $retrievedAddress->getCity());
    $this->assertEquals('RJ', $retrievedAddress->getState());
  }

  public function testRemoveAddress() {
    $customerId = 4;
    
    $addresses = [
      new Address(null, $customerId, 'Rua Removida', '70', '77777-777', 'Natal', 'RN')
    ];

    $createdAddresses = $this->addressRepository->createMany($customerId, $addresses);
    $this->assertCount(1, $createdAddresses);

    $this->addressRepository->remove($createdAddresses[0]->getId());

    $foundAddress = $this->addressRepository->findById($createdAddresses[0]->getId());
    $this->assertNull($foundAddress);
  }

  public function testRemoveManyAddresses() {
    $customerId = 5;

    $addresses = [
      new Address(null, $customerId, 'Rua X', '80', '88888-888', 'Fortaleza', 'CE'),
      new Address(null, $customerId, 'Rua Y', '90', '99999-999', 'Manaus', 'AM')
    ];

    $createdAddresses = $this->addressRepository->createMany($customerId, $addresses);
    
    $idsToRemove = array_map(fn($address) => $address->getId(), $createdAddresses);

    $this->addressRepository->removeMany($idsToRemove);

    $retrievedAddresses = $this->addressRepository->findByCustomerId($customerId);
    $this->assertCount(0, $retrievedAddresses);
  }

  public function testDeleteAllByCustomerId() {
    $customerId = 6;

    $addresses = [
      new Address(null, $customerId, 'Rua Alpha', '101', '10101-101', 'João Pessoa', 'PB'),
      new Address(null, $customerId, 'Rua Beta', '202', '20202-202', 'Aracaju', 'SE')
    ];

    $this->addressRepository->createMany($customerId, $addresses);

    $retrievedAddresses = $this->addressRepository->findByCustomerId($customerId);
    $this->assertCount(2, $retrievedAddresses);

    $this->addressRepository->removeAllByCustomerId($customerId);

    $retrievedAddressesAfterDeletion = $this->addressRepository->findByCustomerId($customerId);
    $this->assertCount(0, $retrievedAddressesAfterDeletion);
  }
}
