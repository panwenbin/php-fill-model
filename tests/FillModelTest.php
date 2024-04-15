<?php
use Panwenbin\FillModel\FillModel;
use PHPUnit\Framework\TestCase;

class User {
    public $id;
    public $name;
    public $email;
    private $password;
}

class Address {
    public $user_id;
    public $city;
}

final class FillModelTest extends TestCase
{
    public $testUserArray = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'user@domain.com', 'password' => '123456'],
        ['id' => 2, 'name' => 'Jane Doe', 'email' => 'user2@domain.com', 'password' => '234567'],
        ['id' => 3, 'name' => 'William Smith', 'email' => 'user3@domain.com', 'password' => '345678'],
    ];

    public $testAddressArray = [
        ['user_id' => 1, 'city' => 'New York'],
        ['user_id' => 2, 'city' => 'Los Angeles'],
        ['user_id' => 2, 'city' => 'Las Vegas'],
    ];

    private function assertPropertiesEquals(object $object, array $data)
    {
      $reflection = new ReflectionClass($object);
      foreach ($data as $key => $value) {
          $property = $reflection->getProperty($key);
          $property->setAccessible(true);
          $this->assertEquals($value, $property->getValue($object));
      }
    }

    public function testFillOne()
    {
        $user = FillModel::fillOne(User::class, $this->testUserArray[0]);
        $this->assertInstanceOf(User::class, $user);
        $this->assertPropertiesEquals($user, $this->testUserArray[0]);
    }

    public function testFillMany()
    {
        $users = FillModel::fillMany(User::class, $this->testUserArray);
        $this->assertIsArray($users);
        $this->assertCount(count($this->testUserArray), $users);

        reset($users);
        foreach($this->testUserArray as $testUser) {
            $user = current($users);
            $this->assertInstanceOf(User::class, $user);
            $this->assertPropertiesEquals($user, $testUser);
            next($users);
        }
    }

    public function testFillManyHasOne()
    {
        $users = FillModel::fillMany(User::class, $this->testUserArray);
        FillModel::fillManyHasOne($users, Address::class, $this->testAddressArray, 'address', 'user_id', 'id');
        $this->assertIsArray($users);
        $this->assertCount(count($this->testUserArray), $users);
        $this->assertPropertiesEquals($users[0]->address, $this->testAddressArray[0]);
        $this->assertPropertiesEquals($users[1]->address, $this->testAddressArray[2]);
    }

    public function testfillManyHasMany()
    {
        $users = FillModel::fillMany(User::class, $this->testUserArray);
        FillModel::fillManyHasMany($users, Address::class, $this->testAddressArray, 'addresses', 'user_id', 'id');
        $this->assertIsArray($users[0]->addresses);
        $this->assertCount(1, $users[0]->addresses);
        $this->assertIsArray($users[1]->addresses);
        $this->assertCount(2, $users[1]->addresses);
        $this->assertIsArray($users[2]->addresses);
        $this->assertCount(0, $users[2]->addresses);
        $this->assertPropertiesEquals($users[0]->addresses[0], $this->testAddressArray[0]);
        $this->assertPropertiesEquals($users[1]->addresses[0], $this->testAddressArray[1]);
        $this->assertPropertiesEquals($users[1]->addresses[1], $this->testAddressArray[2]);
    }
}
