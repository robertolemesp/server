<?php
namespace Domain\Customer\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Domain\Customer\Customer;
use Infrastructure\Database\Config\DatabaseConfig;
use Infrastructure\Repository\MySQLCustomerRepository;

class CustomerRepositoryIntegrationTest extends TestCase {
  private $pdo;
  private $customerRepository;

  protected function setUp(): void {
    $dbConfig = DatabaseConfig::getCredentials();
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}";
    $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $this->customerRepository = new MySQLCustomerRepository($this->pdo);
    $this->pdo->exec("DELETE FROM customer");
  }

  public function testCreateCustomer() {
    $customer = new Customer(null, 'Roberto Lemes', 'roberto@test.com', 'AlmostSecurePass123', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $this->customerRepository->create($customer);

    $customerId = $this->pdo->lastInsertId();
    $retrievedCustomer = $this->customerRepository->findById($customerId);

    $this->assertInstanceOf(Customer::class, $retrievedCustomer);
    $this->assertEquals('Roberto Lemes', $retrievedCustomer->getName());
    $this->assertTrue(password_verify('AlmostSecurePass123', $retrievedCustomer->getPassword()));
  }

  public function testEditCustomer() {
    $customer = new Customer(null, 'Roberto Lemes', 'roberto@test.com', 'AlmostSecurePass123', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $this->customerRepository->create($customer);

    $customerId = $this->pdo->lastInsertId();
    $customer = $this->customerRepository->findById($customerId);
    $customer->setName('Roberto Lemes Updated');

    $this->customerRepository->update($customer);
    $updatedCustomer = $this->customerRepository->findById($customerId);

    $this->assertEquals('Roberto Lemes Updated', $updatedCustomer->getName());
  }

  public function testDeleteCustomer() {
    $customer = new Customer(null, 'Roberto Lemes', 'roberto@test.com', 'AlmostSecurePass123', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $this->customerRepository->create($customer);

    $customerId = $this->pdo->lastInsertId();
    $this->customerRepository->remove($customerId);
    $deletedCustomer = $this->customerRepository->findById($customerId);

    $this->assertNull($deletedCustomer);
  }

  public function testListCustomers() {
    $customer1 = new Customer(null, 'Roberto Lemes', 'roberto1@test.com', 'AlmostSecurePass123', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $customer2 = new Customer(null, 'Roberto Lemes Padilha', 'roberto2@test.com', 'AlmostSecurePass123', new \DateTime('1990-05-10'), '12345678902', '1234567891', '1234567891');

    $this->customerRepository->create($customer1);
    $this->customerRepository->create($customer2);

    $customers = $this->customerRepository->list();

    $this->assertGreaterThanOrEqual(2, count($customers));
    $this->assertInstanceOf(Customer::class, $customers[0]);
    $this->assertInstanceOf(Customer::class, $customers[1]);
  }
}
