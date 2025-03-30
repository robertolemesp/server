<?php
namespace Infrastructure\Database\Seeder;

use Application\Customer\CustomerService;

use Infrastructure\Logging\LogService;

class TestUserSeeder {
  private $customerService;

  public function __construct(CustomerService $customerService) {
    $this->customerService = $customerService;
  }

  public function seed() {
    $testUserData = [
      'name' => 'Test User',
      'email' => 'test_user@seeding.com',
      'password' => 'AlmostSecurePass123!', 
      'birthday' => '1995-01-11',
      'cpf' => '123.466.789-10',
      'rg' => '12.345.678-9',
      'phone' => '(11) 94815-3588',
      'addresses' => [
        [
          'street' => 'Rua do Seed',
          'number' => '101',
          'zipcode' => '12345-678',
          'city' => 'São Paulo',
          'state' => 'SP'
        ],
        [
          'street' => 'Rua do Seed + 1',
          'number' => '101 + 1',
          'zipcode' => '98765-432',
          'city' => 'São Paulo',
          'state' => 'SP'
        ]
      ]
    ];
    
    $result = $this->customerService->exists($testUserData['email']);
    LogService::info($result ? 'true' : 'false');

    if (!$this->customerService->exists($testUserData['email'])) {
      $this->customerService->create($testUserData);

      LogService::info("Test user created successfully.\n");
    }
  }
}
