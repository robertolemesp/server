<?php
namespace Domain\Customer;

interface CustomerRepositoryInterface {
  public function create(Customer $customer): ?int;
  public function update(Customer $customer): void;
  public function findById(int $id): ?Customer;
  public function findByEmail(string $email): ?Customer;
  public function remove(int $id): void;
  /**
   * @return Customer[]
   */
  public function list(): ?array;
}
