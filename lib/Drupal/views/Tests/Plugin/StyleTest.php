<?php

/**
 * @file
 * Definition of Drupal\views\Tests\Plugin\StyleTest.
 */

namespace Drupal\views\Tests\Plugin;

use stdClass;
use DOMDocument;

/**
 * Tests some general style plugin related functionality.
 */
class StyleTest extends PluginTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Style: General',
      'description' => 'Test general style functionality.',
      'group' => 'Views Plugins',
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->enableViewsTestModule();
  }

  /**
   * Tests the grouping legacy features of styles.
   */
  function testGroupingLegacy() {
    $view = $this->getBasicView();
    // Setup grouping by the job.
    $view->initDisplay();
    $view->initStyle();
    $view->style_plugin->options['grouping'] = 'job';

    // Reduce the amount of items to make the test a bit easier.
    // Set up the pager.
    $view->display['default']->handler->overrideOption('pager', array(
      'type' => 'some',
      'options' => array('items_per_page' => 3),
    ));

    // Add the job field .
    $view->display['default']->handler->overrideOption('fields', array(
      'name' => array(
        'id' => 'name',
        'table' => 'views_test',
        'field' => 'name',
        'relationship' => 'none',
      ),
      'job' => array(
        'id' => 'job',
        'table' => 'views_test',
        'field' => 'job',
        'relationship' => 'none',
      ),
    ));

    // Now run the query and groupby the result.
    $this->executeView($view);

    // This is the old way to call it.
    $sets = $view->style_plugin->render_grouping($view->result, $view->style_plugin->options['grouping']);

    $expected = array();
    // Use Job: as label, so be sure that the label is used for groupby as well.
    $expected['Job: Singer'] = array();
    $expected['Job: Singer'][0] = new stdClass();
    $expected['Job: Singer'][0]->views_test_name = 'John';
    $expected['Job: Singer'][0]->views_test_job = 'Singer';
    $expected['Job: Singer'][0]->views_test_id = '1';
    $expected['Job: Singer'][1] = new stdClass();
    $expected['Job: Singer'][1]->views_test_name = 'George';
    $expected['Job: Singer'][1]->views_test_job = 'Singer';
    $expected['Job: Singer'][1]->views_test_id = '2';
    $expected['Job: Drummer'] = array();
    $expected['Job: Drummer'][2] = new stdClass();
    $expected['Job: Drummer'][2]->views_test_name = 'Ringo';
    $expected['Job: Drummer'][2]->views_test_job = 'Drummer';
    $expected['Job: Drummer'][2]->views_test_id = '3';

    $this->assertEqual($sets, $expected, t('The style plugin should proper group the results with grouping by the rendered output.'));

    $expected = array();
    $expected['Job: Singer'] = array();
    $expected['Job: Singer']['group'] = 'Job: Singer';
    $expected['Job: Singer']['rows'][0] = new stdClass();
    $expected['Job: Singer']['rows'][0]->views_test_name = 'John';
    $expected['Job: Singer']['rows'][0]->views_test_job = 'Singer';
    $expected['Job: Singer']['rows'][0]->views_test_id = '1';
    $expected['Job: Singer']['rows'][1] = new stdClass();
    $expected['Job: Singer']['rows'][1]->views_test_name = 'George';
    $expected['Job: Singer']['rows'][1]->views_test_job = 'Singer';
    $expected['Job: Singer']['rows'][1]->views_test_id = '2';
    $expected['Job: Drummer'] = array();
    $expected['Job: Drummer']['group'] = 'Job: Drummer';
    $expected['Job: Drummer']['rows'][2] = new stdClass();
    $expected['Job: Drummer']['rows'][2]->views_test_name = 'Ringo';
    $expected['Job: Drummer']['rows'][2]->views_test_job = 'Drummer';
    $expected['Job: Drummer']['rows'][2]->views_test_id = '3';

    // The newer api passes the value of the grouping as well.
    $sets_new_rendered = $view->style_plugin->render_grouping($view->result, $view->style_plugin->options['grouping'], TRUE);
    $sets_new_value = $view->style_plugin->render_grouping($view->result, $view->style_plugin->options['grouping'], FALSE);

    $this->assertEqual($sets_new_rendered, $expected, t('The style plugins should proper group the results with grouping by the rendered output.'));

    // Reorder the group structure to group by value.
    $expected['Singer'] = $expected['Job: Singer'];
    $expected['Drummer'] = $expected['Job: Drummer'];
    unset($expected['Job: Singer']);
    unset($expected['Job: Drummer']);

    $this->assertEqual($sets_new_value, $expected, t('The style plugins should proper group the results with grouping by the value.'));
  }

  function testGrouping() {
    $this->_testGrouping(FALSE);
    $this->_testGrouping(TRUE);
  }

  /**
   * Tests the grouping features of styles.
   */
  function _testGrouping($stripped = FALSE) {
    $view = $this->getBasicView();
    // Setup grouping by the job and the age field.
    $view->initDisplay();
    $view->initStyle();
    $view->style_plugin->options['grouping'] = array(
      array('field' => 'job'),
      array('field' => 'age'),
    );

    // Reduce the amount of items to make the test a bit easier.
    // Set up the pager.
    $view->display['default']->handler->overrideOption('pager', array(
      'type' => 'some',
      'options' => array('items_per_page' => 3),
    ));

    // Add the job and age field.
    $view->display['default']->handler->overrideOption('fields', array(
      'name' => array(
        'id' => 'name',
        'table' => 'views_test',
        'field' => 'name',
        'relationship' => 'none',
      ),
      'job' => array(
        'id' => 'job',
        'table' => 'views_test',
        'field' => 'job',
        'relationship' => 'none',
      ),
      'age' => array(
        'id' => 'age',
        'table' => 'views_test',
        'field' => 'age',
        'relationship' => 'none',
      ),
    ));

    // Now run the query and groupby the result.
    $this->executeView($view);

    $expected = array();
    $expected['Job: Singer'] = array();
    $expected['Job: Singer']['group'] = 'Job: Singer';
    $expected['Job: Singer']['rows']['Age: 25'] = array();
    $expected['Job: Singer']['rows']['Age: 25']['group'] = 'Age: 25';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0] = new stdClass();
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_name = 'John';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_job = 'Singer';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_age = '25';
    $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_id = '1';
    $expected['Job: Singer']['rows']['Age: 27'] = array();
    $expected['Job: Singer']['rows']['Age: 27']['group'] = 'Age: 27';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1] = new stdClass();
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_name = 'George';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_job = 'Singer';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_age = '27';
    $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_id = '2';
    $expected['Job: Drummer'] = array();
    $expected['Job: Drummer']['group'] = 'Job: Drummer';
    $expected['Job: Drummer']['rows']['Age: 28'] = array();
    $expected['Job: Drummer']['rows']['Age: 28']['group'] = 'Age: 28';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2] = new stdClass();
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_name = 'Ringo';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_job = 'Drummer';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_age = '28';
    $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_id = '3';


    // Alter the results to support the stripped case.
    if ($stripped) {

      // Add some html to the result and expected value.
      $rand = '<a data="' . $this->randomName() . '" />';
      $view->result[0]->views_test_job .= $rand;
      $expected['Job: Singer']['rows']['Age: 25']['rows'][0]->views_test_job = 'Singer' . $rand;
      $expected['Job: Singer']['group'] = 'Job: Singer';
      $rand = '<a data="' . $this->randomName() . '" />';
      $view->result[1]->views_test_job .= $rand;
      $expected['Job: Singer']['rows']['Age: 27']['rows'][1]->views_test_job = 'Singer' . $rand;
      $rand = '<a data="' . $this->randomName() . '" />';
      $view->result[2]->views_test_job .= $rand;
      $expected['Job: Drummer']['rows']['Age: 28']['rows'][2]->views_test_job = 'Drummer' . $rand;
      $expected['Job: Drummer']['group'] = 'Job: Drummer';

      $view->style_plugin->options['grouping'][0] = array('field' => 'job', 'rendered' => TRUE, 'rendered_strip' => TRUE);
      $view->style_plugin->options['grouping'][1] = array('field' => 'age', 'rendered' => TRUE, 'rendered_strip' => TRUE);
    }


    // The newer api passes the value of the grouping as well.
    $sets_new_rendered = $view->style_plugin->render_grouping($view->result, $view->style_plugin->options['grouping'], TRUE);

    $this->assertEqual($sets_new_rendered, $expected, t('The style plugins should proper group the results with grouping by the rendered output.'));

    // Don't test stripped case, because the actual value is not stripped.
    if (!$stripped) {
      $sets_new_value = $view->style_plugin->render_grouping($view->result, $view->style_plugin->options['grouping'], FALSE);

      // Reorder the group structure to grouping by value.
      $expected['Singer'] = $expected['Job: Singer'];
      $expected['Singer']['rows']['25'] = $expected['Job: Singer']['rows']['Age: 25'];
      $expected['Singer']['rows']['27'] = $expected['Job: Singer']['rows']['Age: 27'];
      $expected['Drummer'] = $expected['Job: Drummer'];
      $expected['Drummer']['rows']['28'] = $expected['Job: Drummer']['rows']['Age: 28'];
      unset($expected['Job: Singer']);
      unset($expected['Singer']['rows']['Age: 25']);
      unset($expected['Singer']['rows']['Age: 27']);
      unset($expected['Job: Drummer']);
      unset($expected['Drummer']['rows']['Age: 28']);

      $this->assertEqual($sets_new_value, $expected, t('The style plugins should proper group the results with grouping by the value.'));
    }
  }


  /**
   * Stores a view output in the elements.
   */
  function storeViewPreview($output) {
    $htmlDom = new DOMDocument();
    @$htmlDom->loadHTML($output);
    if ($htmlDom) {
      // It's much easier to work with simplexml than DOM, luckily enough
      // we can just simply import our DOM tree.
      $this->elements = simplexml_import_dom($htmlDom);
    }
  }

  /**
   * Tests custom css classes.
   */
  function testCustomRowClasses() {
    $view = $this->getBasicView();

    // Setup some random css class.
    $view->initDisplay();
    $view->initStyle();
    $random_name = $this->randomName();
    $view->style_plugin->options['row_class'] = $random_name . " test-token-[name]";

    $rendered_output = $view->preview();
    $this->storeViewPreview($rendered_output);

    $rows = $this->elements->body->div->div->div;
    $count = 0;
    foreach ($rows as $row) {
      $attributes = $row->attributes();
      $class = (string) $attributes['class'][0];
      $this->assertTrue(strpos($class, $random_name) !== FALSE, 'Take sure that a custom css class is added to the output.');

      // Check token replacement.
      $name = $view->field['name']->get_value($view->result[$count]);
      $this->assertTrue(strpos($class, "test-token-$name") !== FALSE, 'Take sure that a token in custom css class is replaced.');

      $count++;
    }
  }

}
