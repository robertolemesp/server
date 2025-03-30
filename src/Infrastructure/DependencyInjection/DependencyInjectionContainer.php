<?php
namespace Infrastructure\DependencyInjection;

use PDO;

use Application\Customer\CustomerService;
use Infrastructure\Repository\Customer\MySQLCustomerRepository;

use Application\Address\AddressService;
use Infrastructure\Repository\Address\MySQLAddressRepository;

use Infrastructure\Api\Router\Router;
use Infrastructure\Api\Controller\Customer\CustomerController;

class DependencyInjectionContainer {
  private PDO $pdo;
  private AddressService $addressService;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
    $this->addressService = new AddressService(new MySQLAddressRepository($this->pdo));
  }

  public function getCustomerService(): CustomerService {
    return new CustomerService(new MySQLCustomerRepository($this->pdo), $this->getAddressService());
  }

  public function getAddressService(): AddressService {
    return $this->addressService;
  }

  public function getCustomerController(): CustomerController {
    return new CustomerController($this->getCustomerService(), $this->getAddressService());
  }

  public function getRouter(): Router {
    return new Router($this->getCustomerController());
  }
}
