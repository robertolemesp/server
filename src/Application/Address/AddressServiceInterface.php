<?php
namespace Application\Address;

use Domain\Address\Address;

interface AddressServiceInterface {
  public function createMany(int $customerId, array $addresses): ?array;
  public function listByCustomerId(int $customerId): array;
  public function updateMany(array $addresses): ?array;
  public function removeAllByCustomerId(int $customerId): void;
  public function removeMany(array $addressIds): void;
  public function remove(int $id): void;

  public function mapAddressToArray(Address $address): array;

  /**
   * Maps an array of address data to Address entities, linked to a given customer.
   *
   * @param int $customerId
   * @param array $addresses Each address can be an array or already an Address object.
   * @return Address[]
   */
  public function mapAddresses(int $customerId, array $addresses): array;
}
