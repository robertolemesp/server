<?php
namespace Application\Address;

use Domain\Address\Address;
use Domain\Address\AddressRepositoryInterface;

class AddressService implements AddressServiceInterface {
  public function __construct(private AddressRepositoryInterface $addressRepository) {}

  public function createMany(int $customerId, array $addresses): ?array {
    if (empty($addresses)) 
      return null;

    return $this->addressRepository->createMany($customerId, $this->mapAddresses($customerId, $addresses));
  }

  public function listByCustomerId(int $customerId): array {
    return $this->addressRepository->findByCustomerId($customerId) ?? [];
  }

  public function updateMany(array $addresses): ? array {
    if (empty($addresses)) 
      return null;
    
    $customerId = $addresses[0]['customerId'];
    
    $updatingAddresses = [];
    $newAddresses = [];
    
    foreach ($addresses as $address) {
      if (empty($address['id'])) {
        $newAddresses[] = $address;
        continue;
      }  

      $updatingAddresses[] = $address;
    }

    $mappedUpdatingAddresses = $this->mapAddresses($customerId, $updatingAddresses);
    
    
    if (!empty($updatingAddresses)) 
      $this->addressRepository->updateMany($mappedUpdatingAddresses);

    if (!empty($newAddresses)) {
      $newAddresses = $this->createMany($customerId, $newAddresses);
      
      return array_map(
        fn(Address $address) => $this->mapAddressToArray($address),
        array_merge(
          $mappedUpdatingAddresses ?? [], 
          $newAddresses ?? []
        ) ?? []
      );
    }

    return null;
  }

  public function removeAllByCustomerId(int $customerId): void {
    $addresses = $this->addressRepository->findByCustomerId($customerId);

    $this->addressRepository->removeMany(
      array_map(
        fn(Address $address) => $address->getId(), 
        $addresses
      )
    );
  }

  public function remove(int $id): void {
    if (!$this->addressRepository->findById($id)) 
      throw new \InvalidArgumentException("Address not found.");

    $this->addressRepository->remove($id);
  }

  public function removeMany(array $addressIds): void {
    if (empty($addressIds)) 
      return;

    $existingAddresses = array_filter(
      array_map(fn($id) => $this->addressRepository->findById($id), $addressIds)
    );

    if (count($existingAddresses) !== count($addressIds)) 
      throw new \InvalidArgumentException("One or more addresses not found.");
    
    $this->addressRepository->removeMany($addressIds);
  }

  public function mapAddressToArray(Address $address): array {
    if (!$address instanceof Address) 
      throw new \InvalidArgumentException("Expected Address instance in mapAddressToArray()");

    return [
      'id' => $address->getId(),
      'customerId' => $address->getCustomerId(),
      'street' => $address->getStreet(),
      'number' => $address->getNumber(),
      'city' => $address->getCity(),
      'zipcode' => $address->getZipcode(),
      'state' => $address->getState()
    ];
  }

  public function mapAddresses(int $customerId, array $addresses): array {
    return array_map(fn($address) => new Address(
      $address['id'] ?? null,
      $customerId,
      $address['street'],
      $address['number'],
      $address['zipcode'],
      $address['city'],
      $address['state']
    ), $addresses);
  }
}
