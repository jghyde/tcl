<?php
namespace Drupal\radioactivity\Tests;

use Drupal\Tests\UnitTestCase;
use Drupal\radioactivity\Incident;
use Drupal\radioactivity\DefaultIncidentStorage;
use Drupal\radioactivity\RadioactivityProcessor;
use Drupal\radioactivity\StorageFactory;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\State\State;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Unit tests for Radioactivity.
 *
 * @group radioactivity
 */
class RadioactivityUnitTest extends UnitTestCase {


  private $storage;
  private $incident;
  private $dummyIncident;
  private $processor;

  /**
   * Perform any initial set up tasks that run before every test method.
   */
  public function setUp() {
    parent::setUp();

    $prophecy = $this->prophesize(DefaultIncidentStorage::CLASS);

    $this->dummyIncident = new Incident('test_field', 'test_type', 1, 10);

    $prophecy->addIncident($this->dummyIncident)->willReturn(NULL);
    $prophecy->getIncidents()->willReturn([$this->dummyIncident]);

    $this->storage = $prophecy->reveal();
    $this->incident = $this->prophesize(Incident::CLASS);

    // Prepare the mock processor.
    $type_manager = $this->prophesize(EntityTypeManager::CLASS);
    $state = $this->prophesize(State::CLASS);
    $query_factory = $this->prophesize(QueryFactory::CLASS);
    $logger = $this->prophesize(LoggerChannelFactory::CLASS);
    $factory = $this->prophesize(StorageFactory::CLASS);

    // TODO: add prophesized methods for above.
    $this->processor = new RadioactivityProcessor(
        $type_manager->reveal(),
        $state->reveal(),
        $query_factory->reveal(),
        $logger->reveal(),
        $factory->reveal()
    );

  }

  /**
   * Test storage.
   */
  public function testStorage() {
    // TODO: This one doesn't actually test anything, yet.
    $this->storage->addIncident($this->dummyIncident);
    $incidents = $this->storage->getIncidents();
    $this->assertTrue(is_array($incidents));
    $this->assertTrue(
      serialize($incidents[0]) == serialize($this->dummyIncident)
    );
  }

  /**
   * Test processor.
   */
  public function testProcessor() {
    // TODO: This one will fail for now.
    $this->assertTrue($this->processor->processEmits() == 0);
  }

}
