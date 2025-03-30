<?php
namespace Application\Customer;

use Domain\Customer\Customer;

interface CustomerServiceInterface {
  public function create(array $creatingCustomer): ?array;
  public function update(array $updatingCustomer): void;
  public function remove(int $id): void;
  public function list(): ?array;
  public function exists(string $email): bool;
  public function validateCredentials(string $email, string $password): ?bool;
}
