<?php
namespace Application\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use Application\Address\AddressService;
use Domain\Address\Address;
use Domain\Address\AddressRepositoryInterface;

class AddressServiceUnitTest extends TestCase {
  private AddressRepositoryInterface|MockObject $addressRepositoryMock;
  private AddressService $addressService;

  protected function setUp(): void {
    $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
    $this->addressService = new AddressService($this->addressRepositoryMock);
  }

  public function testCreateManyAddresses(): void {
    $customerId = 1;

    $addresses = [[
      'street' => 'Rua Durval Clemente',
      'number' => '1A',
      'zipcode' => '02040-000',
      'city' => 'São Paulo',
      'state' => 'SP'
    ]];

    $this->addressRepositoryMock->expects($this->once())
      ->method('createMany')
      ->with(
        $customerId,
        $this->callback(fn($arg) =>
          $arg[0] instanceof Address &&
          $arg[0]->getStreet() === 'Rua Durval Clemente'
        )
      );

    $this->addressService->createMany($customerId, $addresses);
  }

  public function testUpdateManyHandlesBothNewAndExistingAddresses(): void {
    $rawAddresses = [
      [
        'id' => 1,
        'customerId' => 1,
        'street' => 'Rua 1',
        'number' => '101',
        'zipcode' => '11111-111',
        'city' => 'SP',
        'state' => 'SP'
      ],
      [
        'customerId' => 1,
        'street' => 'Rua 2',
        'number' => '102',
        'zipcode' => '22222-222',
        'city' => 'RJ',
        'state' => 'RJ'
      ]
    ];

    $this->addressRepositoryMock->expects($this->once())
      ->method('updateMany')
      ->with($this->callback(fn($addresses) =>
        count($addresses) === 1 && $addresses[0]->getId() === 1
      ));

    $this->addressRepositoryMock->expects($this->once())
      ->method('createMany')
      ->with(
        1,
        $this->callback(
          fn($addresses) => count($addresses) === 1 && $addresses[0]->getStreet() === 'Rua 2'
        )
      )
      ->willReturn([
        new Address(2, 1, 'Rua 2', '102', '22222-222', 'RJ', 'RJ')
      ]);

    $result = $this->addressService->updateMany($rawAddresses);

    $this->assertIsArray($result);
    $this->assertCount(2, $result);
    $this->assertEquals(1, $result[0]['id']);
    $this->assertEquals(2, $result[1]['id']);
  }

  public function testRemoveAddress(): void {
    $address = new Address(1, 1, 'Rua Durval Clemente', '1', '02040-000', 'São Paulo', 'SP');

    $this->addressRepositoryMock->expects($this->once())
      ->method('findById')
      ->with(1)
      ->willReturn($address);

    $this->addressRepositoryMock->expects($this->once())
      ->method('remove')
      ->with(1);

    $this->addressService->remove(1);
  }

  public function testRemoveManyThrowsExceptionWhenMissingAddress(): void {
    $this->addressRepositoryMock->expects($this->exactly(2))
      ->method('findById')
      ->willReturnOnConsecutiveCalls(
        new Address(1, 1, 'Rua A', '1', '11111-111', 'City A', 'ST'),
        null
      );

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('One or more addresses not found.');

    $this->addressService->removeMany([1, 2]);
  }

  public function testListAddressesByCustomerId(): void {
    $this->addressRepositoryMock->expects($this->once())
      ->method('findByCustomerId')
      ->with(1)
      ->willReturn([
        new Address(1, 1, 'Rua Durval Clemente', '1', '02040-000', 'São Paulo', 'SP')
      ]);

    $addresses = $this->addressService->listByCustomerId(1);

    $this->assertCount(1, $addresses);
    $this->assertEquals('Rua Durval Clemente', $addresses[0]->getStreet());
  }
}
