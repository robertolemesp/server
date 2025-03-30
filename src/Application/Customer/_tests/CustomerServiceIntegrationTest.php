<?php
namespace Application\Tests;

use PHPUnit\Framework\TestCase;

use Infrastructure\DependencyInjection\DependencyInjectionContainer;

use Infrastructure\Database\Connection\DatabaseConnection;
use Infrastructure\Database\Setup\DatabaseSetup;

use Application\Address\AddressService;
use Infrastructure\Repository\Address\MySQLAddressRepository;

class CustomerServiceIntegrationTest extends TestCase {
  private $customerService;
  private $addressService;
  private $dbConnection;
  private $diContainer;

  protected function setUp(): void {
    $this->dbConnection = DatabaseConnection::getConnection();

    $this->diContainer = new DependencyInjectionContainer($this->dbConnection);
  
    $this->customerService = $this->diContainer->getCustomerService();
    $this->addressService = new AddressService(new MySQLAddressRepository($this->dbConnection));
  
    DatabaseSetup::clearTestDatabase();
  }

  protected function tearDown(): void {
    parent::tearDown();
    DatabaseSetup::clearTestDatabase();
  }
  
  public function testCreateCustomerWithAddresses() {
    $creatingCustomer = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '12.345.678-9',
      'phone' => '(11) 91234-5678',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1A',
          'zipcode' => '02040-000',
          'city' => 'São Paulo',
          'state' => 'SP'
        ]
      ]
    ];
  
    $this->customerService->create($creatingCustomer);
  
    $customerData = $this->customerService->list()[0];
    $this->assertNotNull($customerData);
    $this->assertEquals('Roberto Lemes', $customerData['name']);
    $this->assertEquals('roberto@example.com', $customerData['email']);
    $this->assertEquals('1995-01-11', $customerData['birthday']);
    $this->assertEquals('123.456.789-01', $customerData['cpf']);
    $this->assertEquals('12.345.678-9', $customerData['rg']);
    $this->assertEquals('(11) 91234-5678', $customerData['phone']);

    $addresses = $this->addressService->listByCustomerId($customerData['id']);
  
    $this->assertNotEmpty($addresses);
    $this->assertEquals('Rua Durval Clemente', $addresses[0]->getStreet());
    $this->assertEquals('1A', $addresses[0]->getNumber());
    $this->assertEquals('02040-000', $addresses[0]->getZipcode());
    $this->assertEquals('São Paulo', $addresses[0]->getCity());
    $this->assertEquals('SP', $addresses[0]->getState());
  }
  
  public function testListCustomers() {
    $customer1 = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '12.345.678-9',
      'phone' => '(11) 91234-5678',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1A',
          'zipcode' => '02040-000',
          'city' => 'São Paulo',
          'state' => 'SP'
        ]
      ]
    ];

    $customer2 = [
      'name' => 'Mikhael',
      'email' => 'mikhael@example.com',
      'password' => 'AnotherSecurePass123!',
      'birthday' => '1992-05-20',
      'cpf' => '987.654.321-00',
      'rg' => '12.345.678-1',
      'phone' => '(11) 91234-5679',
      'addresses' => [
        [
          'street' => 'Rua Nova',
          'number' => '2B',
          'zipcode' => '12345-678',
          'city' => 'Rio de Janeiro',
          'state' => 'RJ'
        ]
      ]
    ];

    $this->customerService->create($customer1);
    $this->customerService->create($customer2);

    $customers = $this->customerService->list();

    $this->assertCount(2, $customers);
    
    $this->assertEquals('Roberto Lemes', $customers[0]['name']);
    $this->assertEquals('roberto@example.com', $customers[0]['email']);
    $this->assertEquals('1995-01-11', $customers[0]['birthday']);
    $this->assertEquals('123.456.789-01', $customers[0]['cpf']);
    $this->assertEquals('12.345.678-9', $customers[0]['rg']);
    $this->assertEquals('(11) 91234-5678', $customers[0]['phone']);

    $this->assertEquals('Mikhael', $customers[1]['name']);
    $this->assertEquals('mikhael@example.com', $customers[1]['email']);
    $this->assertEquals('1992-05-20', $customers[1]['birthday']);
    $this->assertEquals('987.654.321-00', $customers[1]['cpf']);
    $this->assertEquals('12.345.678-1', $customers[1]['rg']);
    $this->assertEquals('(11) 91234-5679', $customers[1]['phone']);

    $addresses1 = $this->addressService->listByCustomerId($customers[0]['id']);
    $this->assertNotEmpty($addresses1);
    $this->assertEquals('Rua Durval Clemente', $addresses1[0]->getStreet());
    $this->assertEquals('1A', $addresses1[0]->getNumber());
    $this->assertEquals('02040-000', $addresses1[0]->getZipcode());
    $this->assertEquals('São Paulo', $addresses1[0]->getCity());
    $this->assertEquals('SP', $addresses1[0]->getState());

    $addresses2 = $this->addressService->listByCustomerId($customers[1]['id']);
    $this->assertNotEmpty($addresses2);
    $this->assertEquals('Rua Nova', $addresses2[0]->getStreet());
    $this->assertEquals('2B', $addresses2[0]->getNumber());
    $this->assertEquals('12345-678', $addresses2[0]->getZipcode());
    $this->assertEquals('Rio de Janeiro', $addresses2[0]->getCity());
    $this->assertEquals('RJ', $addresses2[0]->getState());
  }
}
