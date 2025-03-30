<?php
namespace Domain\Customer;

use DateTime;

class Customer {
  private ?int $id;
  private string $name;
  private string $email;
  private ?string $password; # I chose to determine if it's an admin user simply by the presence of a password!
  private DateTime $birthday;
  private string $cpf;
  private string $rg;
  private string $phone;

  public function __construct(?int $id, string $name, string $email, ?string $password, DateTime $birthday, string $cpf, string $rg, string $phone, array $addresses = []) {
    if ($id)
      $this->id = $id;

    $this->name = $name;
    $this->email = $email;
    if (!$password) $this->password = '';
    else $this->password = $password;
    $this->birthday = $birthday;
    $this->cpf = $cpf;
    $this->rg = $rg;
    $this->phone = $phone;

    $this->validate();
  }

  public function setId(int $id) { $this->id = $id; }
  public function getId(): ?int { return $this->id; }
  public function getName(): string { return $this->name; }
  public function getEmail(): string { return $this->email; }
  public function getPassword(): string { 
    if (!$this->password)
      return '';

    return $this->password; 
  }
  public function getBirthday(): string { return $this->birthday->format('Y-m-d'); }
  public function getCpf(): string { return $this->cpf; }
  public function getRg(): string { return $this->rg; }
  public function getPhone(): string { return $this->phone; }

  private function validate(): void {
    $errors = [];

    if (empty($this->name)) 
      $errors[] = 'Name is required.';

    if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) 
      $errors[] = 'Invalid email format.';

    if (empty($this->cpf) || !preg_match('/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/', $this->cpf)) 
      $errors[] = 'Invalid CPF format. Expected: ###.###.###-##.';

    if (empty($this->rg) || !preg_match('/^\d{1,2}\.\d{3}\.\d{3}-[0-9Xx]$/', $this->rg))
      $errors[] = 'Invalid RG format. Expected: #.###.###-# or ##.###.###-#.';

    if (empty($this->phone))
      $errors[] = 'Phone number is required.';

    if ($this->birthday > new DateTime()) 
      $errors[] = 'Birthday cannot be in the future.';


    if (!empty($errors)) 
      throw new \InvalidArgumentException(implode(' ', $errors));
  }
}
