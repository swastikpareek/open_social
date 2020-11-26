<?php

namespace Drupal\Tests\social_user\Kernel;

use Drupal\Tests\social_graphql\Kernel\SocialGraphQLTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the users endpoint added to the Open Social schema by this module.
 *
 * This uses the GraphQLTestBase which extends KernelTestBase since this class
 * is interested in testing the implementation of the GraphQL schema that's a
 * part of this module. We're not interested in the HTTP functionality since
 * that is covered by the graphql module itself. Thus BrowserTestBase is not
 * needed.
 *
 * @group social_graphql
 */
class GraphQLUsersEndpointTest extends SocialGraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    "social_user",
    // User creation in social_user requires a service in role_delegation.
    // TODO: Possibly untangle this?
    "role_delegation",
  ];

  /**
   * An array of test users that serves as test data.
   *
   * @var \Drupal\user\Entity\User[]
   */
  private $users = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Load the existing non-anonymous users as they're part of the dataset that
    // we want to verify test output against.
    $this->users = array_values(
      array_filter(
        User::loadMultiple(),
        static function (User $u) {
          return !$u->isAnonymous();
        }
      )
    );
    // Create a set of 10 test users that we can query. The data of the users
    // shouldn't matter.
    for ($i = 0; $i < 10; ++$i) {
      $this->users[] = $this->createUser();
    }
  }

  /**
   * Test the filter for the users query.
   */
  public function testUsersQueryFilter(): void {
    $this->assertEndpointSupportsPagination('users', $this->users);
  }

  /**
   * Ensure that the fields for the user endpoint properly resolve.
   *
   * This test does not test the validity of the resolved data but merely that
   * the API contract is adhered to.
   */
  public function testUserFieldsPresence() : void {
    // Test as the admin users, this allows us to test all the fields that are
    // available in an all-access scenario.
    $this->setCurrentUser(User::load(1));
    $test_user = $this->createUser();
    $query = "
      query {
        user(uuid: \"{$test_user->uuid()}\") {
          uuid
          display_name
          mail
          created_at
          updated_at
          status
          roles
        }
      }
    ";
    $expected_data = [
      'data' => [
        'user' => [
          'uuid' => $test_user->uuid(),
          'display_name' => $test_user->getDisplayName(),
          'mail' => $test_user->getEmail(),
          'created_at' => $test_user->getCreatedTime(),
          'updated_at' => $test_user->getChangedTime(),
          'status' => 'ACTIVE',
          'roles' => ['authenticated'],
        ],
      ],
    ];

    $this->assertQuery($query, $expected_data, 'user fields are present');
  }

}