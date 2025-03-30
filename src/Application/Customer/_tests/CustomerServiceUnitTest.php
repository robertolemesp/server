<?php
namespace Application\Tests;

use PHPUnit\Framework\TestCase;

use Domain\Customer\Customer;
use Application\Customer\CustomerService;
use Domain\Customer\CustomerRepositoryInterface;

use Domain\Address\Address;
use Application\Address\AddressService;

class CustomerServiceUnitTest extends TestCase {
  private $customerRepositoryMock;
  private $addressServiceMock;
  private $customerService;

  protected function setUp(): void {
    $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
    $this->addressServiceMock = $this->createMock(AddressService::class);
    $this->customerService = new CustomerService($this->customerRepositoryMock, $this->addressServiceMock);
  }

  public function testCreateCustomer() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securepassword',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '12.345.678-9',
      'phone' => '(12) 3456-7890',
      'addresses' => []
    ];

    $this->customerRepositoryMock->expects($this->once())
      ->method('create')
      ->with($this->callback(function (Customer $customer) {
        return $customer->getName() === 'Roberto Lemes' &&
               $customer->getEmail() === 'roberto@example.com' &&
               $customer->getCpf() === '123.456.789-01' &&
               $customer->getBirthday() === '1995-01-11';
      }))
      ->willReturn(1);

    $this->addressServiceMock->expects($this->never())
      ->method('createMany');

    $createdCustomer = $this->customerService->create($customerData);

    $this->assertIsArray($createdCustomer);
    $this->assertEquals(1, $createdCustomer['id']);
    $this->assertEquals('Roberto Lemes', $createdCustomer['name']);
    $this->assertEquals('roberto@example.com', $createdCustomer['email']);
    $this->assertEquals('1995-01-11', $createdCustomer['birthday']);
    $this->assertEquals('123.456.789-01', $createdCustomer['cpf']);
    $this->assertEmpty($createdCustomer['addresses']); 
  }


  public function testUpdateCustomer() {
    $customerData = [
      'id' => 1,
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securepassword',
      'birthday' => '1980-05-10',
      'cpf' => '123.456.789-01',
      'rg' => '12.345.678-9',
      'phone' => '(12) 3456-7890',
      'addresses' => []
    ];

    $existingCustomer = new Customer(
      $customerData['id'],
      $customerData['name'],
      $customerData['email'],
      $customerData['password'],
      new \DateTime($customerData['birthday']),
      $customerData['cpf'],
      $customerData['rg'],
      $customerData['phone']
    );

    $this->customerRepositoryMock->expects($this->once())
      ->method('findById')
      ->with(1)
      ->willReturn($existingCustomer);

    $this->customerRepositoryMock->expects($this->once())
      ->method('update')
      ->with($this->callback(function (Customer $customer) {
        return $customer->getBirthday() === '1980-05-10';
      }));

    $this->addressServiceMock->expects($this->never())
      ->method('updateMany');

    $this->customerService->update($customerData);
  }

  public function testDeleteCustomer() {
    $this->customerRepositoryMock->expects($this->once())
      ->method('remove')
      ->with(1);

    $this->customerService->remove(1);
  }

  public function testListCustomers() {
    $this->customerRepositoryMock->expects($this->once())
      ->method('list')
      ->willReturn([]);

    $this->customerService->list();
  }

  public function testCreateCustomerWithAddresses() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securepassword',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-11',
      'rg' => '12.345.678-9',
      'phone' => '(12) 3456-7890',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '00000-000',
          'city' => 'São Paulo',
          'state' => 'SP'
        ]
      ]
    ];

    $this->customerRepositoryMock->expects($this->once())
      ->method('create')
      ->with($this->callback(function (Customer $customer) {
        return $customer->getBirthday() === '1995-01-11';
      }))
      ->willReturn(1);

    $this->addressServiceMock->expects($this->once())
      ->method('createMany')
      ->with(
        $this->equalTo(1),
        $this->callback(function ($addresses) {
          return is_array($addresses) &&
            count($addresses) === 1 &&
            $addresses[0]['street'] === 'Rua Durval Clemente' &&
            $addresses[0]['number'] === '1' &&
            $addresses[0]['zipcode'] === '00000-000' &&
            $addresses[0]['city'] === 'São Paulo' &&
            $addresses[0]['state'] === 'SP';
        })
      );

    $this->customerService->create($customerData);
  }

  public function testValidateCredentials() {
    $email = 'roberto@test.com';
    $plainPassword = 'SecurePass123!';
    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

    $customerMock = $this->createMock(Customer::class);

    $customerMock->method('getEmail')->willReturn($email);
    $customerMock->method('getPassword')->willReturn($hashedPassword);

    $this->customerRepositoryMock->expects($this->once())
      ->method('findByEmail')
      ->with($email)
      ->willReturn($customerMock);

    $isValid = $this->customerService->validateCredentials($email, $plainPassword);

    $this->assertTrue($isValid, 'Valid credentials should return true');
  }

  public function testValidateCredentialsWithInvalidPassword() {
    $email = 'roberto@test.com';
    $plainPassword = 'WrongPassword!';
    $hashedPassword = password_hash('SecurePass123!', PASSWORD_BCRYPT);

    $customerMock = $this->createMock(Customer::class);

    $customerMock->method('getEmail')->willReturn($email);
    $customerMock->method('getPassword')->willReturn($hashedPassword);

    $this->customerRepositoryMock->expects($this->once())
      ->method('findByEmail')
      ->with($email)
      ->willReturn($customerMock);

    $isValid = $this->customerService->validateCredentials($email, $plainPassword);

    $this->assertFalse($isValid, 'Invalid password should return false');
  }
}
