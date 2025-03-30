<?php
namespace Application\Customer;

use Domain\Customer\Customer;
use Domain\Customer\CustomerRepositoryInterface;
use Domain\Address\Address;
use Application\Address\AddressService;
use Exception;

class CustomerService implements CustomerServiceInterface {
  public function __construct(
    private CustomerRepositoryInterface $customerRepository,
    private AddressService $addressService
  ) {}

  public function create($creatingCustomer): ?array {
    try {
      $password = isset($creatingCustomer['password']) && is_string($creatingCustomer['password']) && !empty($creatingCustomer['password'])
        ? password_hash($creatingCustomer['password'], PASSWORD_BCRYPT)
        : null;

      $customer = new Customer(
        null,
        $creatingCustomer['name'],
        $creatingCustomer['email'],
        $password,
        new \DateTime($creatingCustomer['birthday']),
        $creatingCustomer['cpf'],
        $creatingCustomer['rg'],
        $creatingCustomer['phone']
      );

      $customerId = $this->customerRepository->create($customer);

      if (!$customerId) 
        throw new \RuntimeException("Failed to create customer");

      $customer->setId($customerId);

      $addresses = [];
      if (isset($creatingCustomer['addresses']) && !empty($creatingCustomer['addresses'])) 
        $addresses = $this->addressService->createMany($customer->getId(), $creatingCustomer['addresses']);

      return [
        'id' => $customer->getId(),
        'name' => $customer->getName(),
        'email' => $customer->getEmail(),
        'birthday' => $customer->getBirthday(),
        'cpf' => $customer->getCpf(),
        'rg' => $customer->getRg(),
        'phone' => $customer->getPhone(),
        'addresses' => array_map(
          fn(Address $address) => $this->addressService->mapAddressToArray($address),
          $addresses ?? []
        )
      ];
    } catch (Exception $e) {
      throw new Exception("Error creating customer: " . $e->getMessage(), 0, $e);
    }
  }

  public function update(array $data): void {
    try {
      $foundCustomer = $this->customerRepository->findById($data['id']);

      if (!$foundCustomer) 
        throw new \InvalidArgumentException("Customer not found.");

      $customer = new Customer(
        $foundCustomer->getId(),
        $data['name'],
        $foundCustomer->getEmail(),
        $data['password'] ?? null,
        new \DateTime($data['birthday']),
        $data['cpf'],
        $data['rg'],
        $data['phone']
      );

      $this->customerRepository->update($customer);
      
    } catch (Exception $e) {
      throw new Exception("Error updating customer: " . $e->getMessage(), 0, $e);
    }
  }

  public function remove(int $id): void {
    try {
      $this->addressService->removeAllByCustomerId($id);
      $this->customerRepository->remove($id);
    } catch (Exception $e) {
      throw new Exception("Error removing customer: " . $e->getMessage(), 0, $e);
    }
  }

  public function list(): array {
    try {
      return array_map(
        fn($customer) => $this->mapCustomerWithAddresses($customer), 
        $this->customerRepository->list()
      );
    } catch (Exception $e) {
      throw new Exception("Error listing customers: " . $e->getMessage(), 0, $e);
    }
  }

  public function exists(string $email): bool {
    try {
      return $this->customerRepository->findByEmail($email) !== null;
    } catch (Exception $e) {
      throw new Exception("Error checking customer existence: " . $e->getMessage(), 0, $e);
    }
  }

  public function validateCredentials(string $email, string $password): ?bool {
    try {
      $customer = $this->customerRepository->findByEmail($email);

      if (!$customer) 
        return false;

      return password_verify($password, $customer->getPassword());
    } catch (Exception $e) {
      throw new Exception("Error validating credentials: " . $e->getMessage(), 0, $e);
    }
  }

  private function mapCustomerWithAddresses(Customer $customer): array {
    try {
      $addresses = array_map(
        fn($address) => $this->addressService->mapAddressToArray($address), 
        $this->addressService->listByCustomerId($customer->getId()) ?? []
      );

      return [
        'id' => $customer->getId(),
        'name' => $customer->getName(),
        'email' => $customer->getEmail(),
        'password' => $customer->getPassword(),
        'birthday' => $customer->getBirthday(),
        'cpf' => $customer->getCpf(),
        'rg' => $customer->getRg(),
        'phone' => $customer->getPhone(),
        'addresses' => $addresses,
      ];
    } catch (Exception $e) {
      throw new Exception("Error mapping customer with addresses: " . $e->getMessage(), 0, $e);
    }
  }
}
