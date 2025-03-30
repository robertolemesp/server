<?php
namespace Infrastructure\Api\Controller\Customer;

use Infrastructure\Api\Controller\Controller;

use Application\Customer\CustomerService;
use Application\Address\AddressService;

use Infrastructure\Logging\LogService;

class CustomerController extends Controller {
  private $customerService;
  private $addressService;

  public function __construct(CustomerService $customerService, AddressService $addressService) {
    $this->customerService = $customerService;
    $this->addressService = $addressService;
  }

  public function create(): void {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
      $this->jsonResponse($this->customerService->create($data), 201);
    } catch (\InvalidArgumentException $e) {
      $this->jsonResponse(['errors' => [$e->getMessage()]], 400);
    } catch (\Exception $e) {
      LogService::error('Create Customer Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }

  public function update(int $id): void {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) 
      $this->jsonResponse(['errors' => ['Invalid JSON input: ' . json_last_error_msg()]], 400);

    try {
      if (!isset($data['email']))
        $this->jsonResponse(['errors' => ['Missing required field: email']], 400);

      if (!$this->customerService->exists($data['email']))
        $this->jsonResponse(['errors' => ['Customer not found']], 404);

      $data['id'] = $id;
      $this->customerService->update($data);

      $this->jsonResponse(['message' => 'Customer updated successfully'], 200);
    } catch (\InvalidArgumentException $e) {
      $this->jsonResponse(['errors' => [$e->getMessage()]], 400);
    } catch (\Exception $e) {
      LogService::error('Update Customer Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }

  public function remove(int $id): void {
    try {
      $this->customerService->remove($id);
      $this->jsonResponse(['message' => 'Customer deleted successfully'], 200);
    } catch (\Exception $e) {
      LogService::error('Remove Customer Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }

  public function list(): void {
    try {
      $customers = $this->customerService->list();
      $this->jsonResponse($customers, 200);
    } catch (\Exception $e) {
      LogService::error('List Customers Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }

  public function exists(): void {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      if (!isset($data['email']))
        $this->jsonResponse(['errors' => ['Email is required']], 400);

      $exists = $this->customerService->exists($data['email']);
      $this->jsonResponse($exists, 200);
    } catch (\Exception $e) {
      LogService::error('Check Customer Exists Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }

  public function validateCredentials(): void {
    try {
      $data = json_decode(file_get_contents('php://input'), true);

      LogService::info('aaa');
      
      LogService::info($data['email']);
      LogService::info($data['password']);

      if (!isset($data['email'])) 
        $this->jsonResponse(['errors' => ['Email is required']], 400);

      if (!isset($data['password']))
        $this->jsonResponse(['errors' => ['Password is required']], 400);
    
      $isValid = $this->customerService->validateCredentials($data['email'], $data['password']);

      if (!$isValid) 
        $this->jsonResponse(['errors' => ['Invalid credentials']], 401);
       
      $this->jsonResponse(true, 200);
      
    } catch (\Exception $e) {
      LogService::error('Validate Credentials Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }

  public function updateAddress(int $customerId): void {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
      if (empty($data)) {
        $this->addressService->removeAllByCustomerId($customerId);
        $this->jsonResponse(['message' => 'All addresses removed successfully'], 200);
      } 

      $addressUpdateResult = $this->addressService->updateMany($data);

      $this->jsonResponse(
        $addressUpdateResult ? $addressUpdateResult : ['message' => 'Addresses added and/or updated successfully'], 
        200
      );
    } catch (\Exception $e) {
      LogService::error('Update Address Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }

  public function removeAddress(): void {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
      if (empty($data['addressIds']) || !is_array($data['addressIds']))
        $this->jsonResponse(['errors' => ['Invalid or missing addressIds']], 400);

      $this->addressService->removeMany($data['addressIds']);
      $this->jsonResponse(['message' => 'Addresses removed successfully'], 200);
    } catch (\Exception $e) {
      LogService::error('Remove Address Error: ' . $e->getMessage());
      $this->jsonResponse(['errors' => [$e->getMessage()]], 500);
    }
  }
}
