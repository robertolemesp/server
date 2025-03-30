<?php
namespace Domain\Address;

class Address {
  private ?int $id;
  private ?int $customerId;
  private string $street;
  private string $number;
  private string $zipcode;
  private string $city;
  private string $state;

  public function __construct(?int $id, ?int $customerId, string $street, string $number, string $zipcode, string $city, string $state) {
    if ($id)
      $this->id = $id;
    
    if ($customerId)
      $this->customerId = $customerId;

    $this->street = $street;
    $this->number = $number;
    $this->zipcode = $zipcode;
    $this->city = $city;
    $this->state = $state;

    $this->validate();
  }

  public function getId(): ?int { return $this->id; }
  public function getCustomerId(): ?int { return $this->customerId; }
  public function getStreet(): string { return $this->street; }
  public function getNumber(): string { return $this->number; }
  public function getZipcode(): string { return $this->zipcode; }
  public function getCity(): string { return $this->city; }
  public function getState(): string { return $this->state; }

  private function validate() {
    $errors = [];

    if (empty($this->street)) {
      $errors[] = 'Street is required.';
    } elseif (strlen($this->street) < 3) {
      $errors[] = 'Street must be at least 3 characters.';
    } elseif (strlen($this->street) > 256) {
      $errors[] = 'Street must be no more than 256 characters.';
    }

    if (empty($this->number))
      $errors[] = 'Number is required.';

    if (empty($this->zipcode) || !preg_match('/^[0-9]{5}\-[0-9]{3}$/', $this->zipcode))
      $errors[] = 'Invalid postal code format. Expected format: #####-###';

    if (empty($this->city))
      $errors[] = 'City is required.';
    
    if (empty($this->state))
      $errors[] = 'State is required.';
    
    if (!empty($errors)) 
      throw new \InvalidArgumentException(implode(' ', $errors));
  }
}
