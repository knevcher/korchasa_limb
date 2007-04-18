<?php
/**
 * Limb Web Application Framework
 *
 * @link http://limb-project.com
 *
 * @copyright  Copyright &copy; 2004-2007 BIT
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 * @version    $Id: lmbMaterializedPathTreeTest.class.php 5677 2007-04-18 14:02:43Z alex433 $
 * @package    tree
 */
lmb_require('limb/dbal/src/lmbSimpleDb.class.php');
lmb_require('limb/tree/src/tree/lmbMaterializedPathTree.class.php');

class MaterializedPathTreeTestVersion extends lmbMaterializedPathTree
{
  function __construct()
  {
    parent :: __construct('test_materialized_path_tree');
  }
}

class lmbMaterializedPathTreeTest extends UnitTestCase
{
  var $db = null;
  var $driver = null;

  function _createTreeImp()
  {
    return new MaterializedPathTreeTestVersion();
  }

  function setUp()
  {
    $toolkit = lmbToolkit :: instance();
    $this->db = new lmbSimpleDb($toolkit->getDefaultDbConnection());

    $this->imp = $this->_createTreeImp();

    $this->_cleanUp();
  }

  function tearDown()
  {
    $this->_cleanUp();
  }

  function _cleanUp()
  {
    $this->db->delete('test_materialized_path_tree');
  }

  function testGetNodeFailed()
  {
    $this->assertNull($this->imp->getNode(10000));
  }

  function testGetNode()
  {
    $node = array(
      'identifier' => 'test',
      'id' => 10,
      'path' => '/10/',
      'priority' => 11,
      'root_id' => 10,
      'level' => 2,
      'parent_id' => 1000,
      'children' => 0
    );

    $this->db->insert('test_materialized_path_tree', $node);

    $this->assertEqual($node, $this->imp->getNode(10)->export());
  }

  function testGetNodeByNode()
  {
    $node = array(
      'identifier' => 'test',
      'id' => 10,
      'path' => '/10/',
      'priority' => 11,
      'root_id' => 10,
      'level' => 2,
      'parent_id' => 1000,
      'children' => 0
     );

    $this->db->insert('test_materialized_path_tree', $node);
    $node1 = $this->imp->getNode(10);
    $node2 = $this->imp->getNode($node1);

    $this->assertEqual($node1, $node2);
  }

  function testGetParentFailed()
  {
    $this->assertNull($this->imp->getParent(1000));
  }

  function testGetParent()
  {
    $root_node = array(
      'identifier' => 'root',
      'id' => 1,
      'path' => '/1/',
      'priority' => 11,
      'root_id' => 1,
      'level' => 1,
      'parent_id' => 0,
      'children' => 1
    );

    $this->db->insert('test_materialized_path_tree', $root_node);

    $node = array(
      'identifier' => 'test',
      'id' => 10,
      'path' => '/1/10/',
      'root_id' => 1,
      'level' => 2,
      'parent_id' => 1,
      'children' => 0
    );

    $this->db->insert('test_materialized_path_tree', $node);

    $this->assertEqual($root_node, $this->imp->getParent(10)->export());
    $this->assertNull($this->imp->getParent(1));
  }

  function testCreateRootNodeAutoGenerateId()
  {
    $node = array(
      'identifier' => 'test',
      'path' => '/0/',
      'root_id' => 0,
      'level' => 23,
      'parent_id' => 1000,
      'children' => 1000
    );

    $node_id = $this->imp->createNode($node);

    $this->assertNotIdentical($node_id, false);

    $rs = $this->db->select('test_materialized_path_tree')->sort(array('id' => 'ASC'));
    $this->assertEqual($rs->count(), 1);

    $rs->rewind();
    $row = $rs->current()->export();

    $this->assertEqual($row['id'], $node_id, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test', '%s, invalid parameter: identifier');
    $this->assertEqual($row['level'], 1, '%s, invalid parameter: level');
    $this->assertEqual($row['parent_id'], 0, '%s, invalid parameter: parent_id');
    $this->assertEqual($row['root_id'], $node_id, '%s, invalid parameter: root_id');
    $this->assertEqual($row['path'], '/' . $node_id . '/', '%s, invalid parameter: path');
    $this->assertEqual($row['children'], 0, '%s, invalid parameter: children');
  }

  function testCreateRootNodeUseNodeId()
  {
    $node = array(
      'identifier' => 'test',
      'id' => $use_this_node_id = 10,
      'path' => '/0/',
      'root_id' => 0,
      'level' => 23,
      'parent_id' => 1000,
      'children' => 1000
    );

    $node_id = $this->imp->createNode($node);

    $this->assertEqual($node_id, $use_this_node_id);

    $rs = $this->db->select('test_materialized_path_tree')->sort(array('id' => 'ASC'));
    $this->assertEqual($rs->count(), 1);

    $rs->rewind();
    $row = $rs->current()->export();

    $this->assertEqual($row['id'], $node_id, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test', '%s, invalid parameter: identifier');
    $this->assertEqual($row['level'], 1, '%s, invalid parameter: level');
    $this->assertEqual($row['parent_id'], 0, '%s, invalid parameter: parent_id');
    $this->assertEqual($row['root_id'], $node_id, '%s, invalid parameter: root_id');
    $this->assertEqual($row['path'], '/' . $node_id . '/', '%s, invalid parameter: path');
    $this->assertEqual($row['children'], 0, '%s, invalid parameter: children');
  }

  function testCreateRootNodeUseNodeIdFailed()
  {
    $node = array(
      'identifier' => 'test',
      'id' => $use_this_node_id = 10,
      'path' => '/0/',
      'root_id' => 0,
      'level' => 23,
      'parent_id' => 1000,
      'children' => 1000
    );

    $node_id = $this->imp->createNode($node);
    $this->assertFalse($this->imp->createNode($node));
  }

  function testCreateRootNodeDumb()
  {
    $node = array(
      'identifier' => 'test',
      'id' => 1000000000,
      'path' => '/0/',
      'root_id' => 0,
      'level' => 23,
      'parent_id' => 1000,
      'children' => 10000
    );


    $node_id = $this->imp->createNode($node);

    $this->assertEqual($node_id, 1000000000);

    $rs = $this->db->select('test_materialized_path_tree')->sort(array('id' => 'ASC'));
    $rs->rewind();
    $row = $rs->current()->export();

    $this->assertEqual($row['id'], 1000000000, '%s, invalid parameter: id');
  }

  function testCreateSubNodeFailed()
  {
    $this->assertFalse($this->imp->createNode(array(),100000));
  }

  function testCreateSubNodeFailedTheresNodeWithSuchId()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));

    $sub_node = array(
      'identifier' => 'test',
      'id' => $parent_node_id,
    );

    $this->assertFalse($this->imp->createNode($sub_node, $parent_node_id));
  }

  function testCreateSubNodeAutoGenerateId()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));

    $parent_node = $this->imp->getNode($parent_node_id);

    $sub_node = array(
      'identifier' => 'test',
      'path' => '/0/',
      'root_id' => 0,
      'level' => 23,
      'parent_id' => 1000,
      'children' => 1000
    );

    $sub_node_id = $this->imp->createNode($sub_node, $parent_node_id);

    $this->assertNotIdentical($sub_node_id, false);

    $rs = $this->db->select('test_materialized_path_tree')->sort(array('id' => 'ASC'));
    $this->assertEqual($rs->count(), 2);

    $rs->rewind();
    $row = $rs->current()->export();

    $this->assertEqual($row['children'], 1, '%s, invalid parameter in parent: children');

    $rs->next();
    $row = $rs->current()->export();

    $this->assertEqual($row['id'], $sub_node_id, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test', '%s, invalid parameter: identifier');
    $this->assertEqual($row['level'], 2, '%s, invalid parameter: level');
    $this->assertEqual($row['parent_id'], $parent_node_id, '%s, invalid parameter: parent_id');
    $this->assertEqual($row['root_id'], $parent_node['root_id'], '%s, invalid parameter: root_id');
    $this->assertEqual($row['path'], '/' . $parent_node_id . '/'. $sub_node_id . '/', '%s, invalid parameter: path');
    $this->assertEqual($row['children'], 0, '%s, invalid parameter: children');
  }

  function testCreateSubNodeUseId()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));

    $parent_node = $this->imp->getNode($parent_node_id);

    $sub_node = array(
      'identifier' => 'test',
      'id' => $use_this_node_id = 1000,
      'path' => '/0/',
      'root_id' => 0,
      'level' => 23,
      'parent_id' => 1000,
      'children' => 1000
    );

    $sub_node_id = $this->imp->createNode($sub_node, $parent_node_id);

    $this->assertEqual($sub_node_id, $use_this_node_id);

    $rs = $this->db->select('test_materialized_path_tree')->sort(array('id' => 'ASC'));
    $this->assertEqual($rs->count(), 2);

    $rs->rewind();
    $row = $rs->current()->export();

    $this->assertEqual($row['children'], 1, '%s, invalid parameter in parent: children');

    $rs->next();
    $row = $rs->current()->export();

    $this->assertEqual($row['id'], $sub_node_id, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test', '%s, invalid parameter: identifier');
    $this->assertEqual($row['level'], 2, '%s, invalid parameter: level');
    $this->assertEqual($row['parent_id'], $parent_node_id, '%s, invalid parameter: parent_id');
    $this->assertEqual($row['root_id'], $parent_node['root_id'], '%s, invalid parameter: root_id');
    $this->assertEqual($row['path'], '/' . $parent_node_id . '/'. $sub_node_id . '/', '%s, invalid parameter: path');
    $this->assertEqual($row['children'], 0, '%s, invalid parameter: children');
  }

  function testDeleteNodeFailed()
  {
    $this->imp->deleteNode(100000);
  }

  function testDeleteNode()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id1 = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);
    $sub_node_id2 = $this->imp->createNode(array('identifier' => 'test2'), $parent_node_id);
    $this->imp->createNode(array('identifier' => 'test0'), $sub_node_id1);

    $this->imp->deleteNode($sub_node_id1);

    $rs = $this->db->select('test_materialized_path_tree')->sort(array('id' => 'ASC'));
    $this->assertEqual($rs->count(), 2);

    $rs->rewind();
    $row = $rs->current()->export();

    $this->assertEqual($row['children'], 1, 'invalid parent parameter: children');

    $rs->next();
    $row = $rs->current()->export();

    $this->assertEqual($row['id'], $sub_node_id2, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test2', '%s, invalid parameter: identifier');
    $this->assertEqual($row['level'], 2, '%s, invalid parameter: level');
    $this->assertEqual($row['parent_id'], $parent_node_id, '%s, invalid parameter: parent_id');
    $this->assertEqual($row['children'], 0, '%s, invalid parameter: children');
  }

  function testIsNode()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);

    $this->assertTrue($this->imp->isNode($sub_node_id));
    $this->assertTrue($this->imp->isNode($parent_node_id));
    $this->assertFalse($this->imp->isNode(1000));
  }

  function testGetParentsFailed()
  {
    $this->assertFalse($this->imp->getParents(10000));
  }

  function testGetParents()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $this->imp->createNode(array('identifier' => 'test0'), $parent_node_id);

    $sub_node_id1 = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);
    $sub_node_id2 = $this->imp->createNode(array('identifier' => 'test2'), $sub_node_id1);

    $rs = $this->imp->getParents($sub_node_id2);
    $this->assertEqual($rs->count(), 2);

    $nodes = $rs->getArray();

    $this->_checkProperNesting($nodes);
    $this->_checkResultNodesArray($nodes);

    $row = reset($nodes);

    $this->assertEqual($row['id'], $parent_node_id, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'root', '%s, invalid parameter: identifier');

    $row = end($nodes);

    $this->assertEqual($row['id'], $sub_node_id1, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test1', '%s, invalid parameter: identifier');

    $rs = $this->imp->getParents($sub_node_id1);
    $nodes = $rs->getArray('id');

    $this->assertEqual($rs->count(), 1);

    $this->_checkProperNesting($nodes);
    $this->_checkResultNodesArray($nodes);
  }

  function testGetChildrenFailed()
  {
    $this->assertNull($this->imp->getChildren(10000));
  }

  function testGetChildren()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id1 = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);
    $sub_node_id2 = $this->imp->createNode(array('identifier' => 'test2'), $parent_node_id);
    $this->imp->createNode(array('identifier' => 'test0'), $sub_node_id1);

    $rs = $this->imp->getChildren($parent_node_id)->sort(array('id' => 'ASC'));
    $nodes = $rs->getArray('id');

    $this->assertEqual(sizeof($nodes), 2);
    $this->_checkResultNodesArray($nodes);

    $row = reset($nodes);

    $this->assertEqual($row['id'], $sub_node_id1, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test1', '%s, invalid parameter: identifier');

    $row = end($nodes);

    $this->assertEqual($row['id'], $sub_node_id2, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test2', '%s, invalid parameter: identifier');
  }

  function testCountChildrenFailed()
  {
    $this->assertFalse($this->imp->countChildren(10000));
  }

  function testCountChildren()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id1 = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);
    $sub_node_id2 = $this->imp->createNode(array('identifier' => 'test2'), $parent_node_id);
    $this->imp->createNode(array('identifier' => 'test0'),$sub_node_id1);

    $this->assertEqual(2, $this->imp->countChildren($parent_node_id));
  }

  function testGetSiblingsFailed()
  {
    $this->assertNull($this->imp->getSiblings(10000));
  }

  function testGetSiblings()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id1 = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);
    $sub_node_id2 = $this->imp->createNode(array('identifier' => 'test2'), $parent_node_id);
    $this->imp->createNode(array('identifier' => 'test0'), $sub_node_id1);

    $rs = $this->imp->getSiblings($sub_node_id2)->sort(array('id' => 'ASC'));
    $nodes = $rs->getArray('id');

    $this->assertEqual(sizeof($nodes), 2);

    $row = reset($nodes);

    $this->assertEqual($row['id'], $sub_node_id1, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test1', '%s, invalid parameter: identifier');

    $row = end($nodes);

    $this->assertEqual($row['id'], $sub_node_id2, '%s, invalid parameter: id');
    $this->assertEqual($row['identifier'], 'test2', '%s, invalid parameter: identifier');
  }

  function testUpdateNodeFailed()
  {
    $this->assertFalse($this->imp->updateNode(10000, array()));
  }

  function testUpdateNode()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $node_id = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);

    $node = array(
      'identifier' => 'test',
      'id' => 12,
      'path' => '/0/',
      'root_id' => 0,
      'level' => 23,
      'parent_id' => 1000,
      'children' => 1000
    );

    $this->assertTrue($this->imp->updateNode($node_id, $node));

    $updated_node = $this->imp->getNode($node_id);

    $this->assertEqual($updated_node['identifier'], 'test', '%s, invalid parameter: identifier');
    $this->assertNotEqual($updated_node->export(), $node, 'invalid update');

    $root_node = $this->imp->getNode($parent_node_id);
    $this->assertEqual($root_node['identifier'], 'root');
  }

  function testMoveTreeSinceOnlyJunkFieldsFailed()
  {
    $parent_node_id = $this->imp->createNode(array('identifier' => 'root'));
    $node_id = $this->imp->createNode(array('identifier' => 'test1'), $parent_node_id);

    $node = array(
      'no_such_field' => 'test');

    $this->assertFalse($this->imp->updateNode($node_id, $node));
  }

  function testMoveTreeFailed()
  {
    $this->assertFalse($this->imp->moveNode(1, 1));

    $this->assertFalse($this->imp->moveNode(1, 2));

    $node_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id = $this->imp->createNode(array('identifier' => 'test'), $node_id);

    $this->assertFalse($this->imp->moveNode($node_id, $node_id-1));

    $this->assertFalse($this->imp->moveNode($node_id, $sub_node_id));
  }

  function testMoveTree()
  {
    $root_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id_1 = $this->imp->createNode(array('identifier' => 'test'), $root_id);
    $sub_node_id_1_1 = $this->imp->createNode(array('identifier' => 'test'), $sub_node_id_1);
    $sub_node_id_1_1_1 = $this->imp->createNode(array('identifier' => 'test'), $sub_node_id_1_1);
    $sub_node_id_1_1_2 = $this->imp->createNode(array('identifier' => 'test'), $sub_node_id_1_1);

    $root_id_2 = $this->imp->createNode( array('identifier' => 'test'));
    $sub_node_id_2 = $this->imp->createNode(array('identifier' => 'test'), $root_id_2);

    $root_node = $this->imp->getNode($root_id);
    $this->assertEqual($root_node['children'], 1, 'invalid parent parameter: children');

    $this->assertTrue($this->imp->moveNode($sub_node_id_1, $sub_node_id_2));

    $root_node = $this->imp->getNode($root_id);
    $this->assertEqual($root_node['children'], 0, 'invalid parent parameter: children');

    $sub_node_2 = $this->imp->getNode($sub_node_id_2);
    $this->assertEqual($sub_node_2['children'], 1, 'invalid parent parameter: children');

    $current_path = '/' . $root_id_2 . '/' . $sub_node_id_2 . '/';

    $sub_node_1 = $this->imp->getNode($sub_node_id_1);

    $current_path .= $sub_node_id_1 . '/';
    $this->assertEqual($sub_node_1['level'], 3, '%s, invalid parameter: level');
    $this->assertEqual($sub_node_1['parent_id'], $sub_node_id_2, '%s, invalid parameter: parent_id');
    $this->assertEqual($sub_node_1['path'], $current_path, '%s, invalid parameter: path');
    $this->assertEqual($sub_node_1['root_id'], $root_id_2, '%s, invalid parameter: root_id');

    $current_path .= $sub_node_id_1_1 . '/';
    $sub_node_1_1 = $this->imp->getNode($sub_node_id_1_1);

    $this->assertEqual($sub_node_1_1['level'], 4, '%s, invalid parameter: level');
    $this->assertEqual($sub_node_1_1['parent_id'], $sub_node_id_1, '%s, invalid parameter: parent_id');
    $this->assertEqual($sub_node_1_1['path'], $current_path , '%s, invalid parameter: path');
    $this->assertEqual($sub_node_1_1['root_id'], $root_id_2, '%s, invalid parameter: root_id');

    $sub_node_1_1_1 = $this->imp->getNode($sub_node_id_1_1_1);

    $this->assertEqual($sub_node_1_1_1['level'], 5, '%s, invalid parameter: level');
    $this->assertEqual($sub_node_1_1_1['parent_id'], $sub_node_id_1_1, '%s, invalid parameter: parent_id');
    $this->assertEqual($sub_node_1_1_1['path'], $current_path . $sub_node_id_1_1_1 . '/', '%s, invalid parameter: path');
    $this->assertEqual($sub_node_1_1_1['root_id'], $root_id_2, '%s, invalid parameter: root_id');

    $sub_node_1_1_2 = $this->imp->getNode($sub_node_id_1_1_2);

    $this->assertEqual($sub_node_1_1_2['level'], 5, '%s, invalid parameter: level');
    $this->assertEqual($sub_node_1_1_2['parent_id'], $sub_node_id_1_1, '%s, invalid parameter: parent_id');
    $this->assertEqual($sub_node_1_1_2['path'], $current_path . $sub_node_id_1_1_2 . '/', '%s, invalid parameter: path');
    $this->assertEqual($sub_node_1_1_2['root_id'], $root_id_2, '%s, invalid parameter: root_id');
  }

  function testUpdateWithNewParentIdCallMoveTreeAsWell()
  {
    $root_id1 = $this->imp->createNode(array('identifier' => 'root1'));
    $root_id2 = $this->imp->createNode(array('identifier' => 'root2'));
    $node_id = $this->imp->createNode(array('identifier' => 'test1'), $root_id1);

    $node = array(
      'identifier' => 'test2',
      'parent_id' => $root_id2,
    );

    $this->assertTrue($this->imp->updateNode($node_id, $node));

    $updated_node = $this->imp->getNode($node_id);

    $this->assertEqual($updated_node['identifier'], 'test2');
    $this->assertEqual($updated_node['children'], 0);

    $root_node1 = $this->imp->getNode($root_id1);
    $this->assertEqual($root_node1['children'], 0);

    $root_node2 = $this->imp->getNode($root_id2);
    $this->assertEqual($root_node2['children'], 1);
  }

  /*function testGetSubBranchFailed()
  {
    $this->assertNull($this->imp->getBranch(1));
  }

  function testGetSubBranch()
  {
    $root_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id_1 = $this->imp->createSubNode($root_id, array('identifier' => 'test'));
    $sub_node_id_1_1 = $this->imp->createSubNode($sub_node_id_1, array('identifier' => 'test'));
    $sub_node_id_1_1_1 = $this->imp->createSubNode($sub_node_id_1_1, array('identifier' => 'test'));
    $sub_node_id_1_1_2 = $this->imp->createSubNode($sub_node_id_1_1, array('identifier' => 'test'));

    //getting all
    $rs = $this->imp->getSubBranch($sub_node_id_1);
    $branch = $rs->getArray('id');
    $this->assertEqual(3, sizeof($branch));
    $this->_checkResultNodesArray($branch);
    $this->_checkProperNesting($branch);

    $node = current($branch);
    $this->assertEqual($node['id'], $sub_node_id_1_1, '%s, invalid parameter: id');

    //getting at unlimited depth, including node
    $rs = $this->imp->getSubBranch($sub_node_id_1, -1, true);
    $branch = $rs->getArray('id');
    $this->assertEqual(4, sizeof($branch));
    $this->_checkResultNodesArray($branch);
    $this->_checkProperNesting($branch);

    //getting at depth = 1
    $rs = $this->imp->getSubBranch($sub_node_id_1, 1);
    $branch = $rs->getArray('id');
    $this->assertEqual(1, sizeof($branch));
    $this->_checkResultNodesArray($branch,  __LINE__);
    $this->_checkProperNesting($branch);

    //getting at depth = 1, including node
    $rs = $this->imp->getSubBranch($sub_node_id_1, 1, true);
    $branch = $rs->getArray('id');
    $this->assertEqual(2, sizeof($branch));
    $this->_checkResultNodesArray($branch,  __LINE__);
    $this->_checkProperNesting($branch);
  }*/

  function testGetNodeByPathFailed()
  {
    $root_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id_1 = $this->imp->createNode(array('identifier' => '1_test'), $root_id);
    $sub_node_id_1_1 = $this->imp->createNode(array('identifier' => '1_1_test'), $sub_node_id_1);
    $sub_node_id_1_1_1 = $this->imp->createNode(array('identifier' => '1_1_1_test'), $sub_node_id_1_1);
    $sub_node_id_1_1_2 = $this->imp->createNode(array('identifier' => '1_1_2_test'), $sub_node_id_1_1);

    $root_id2 = $this->imp->createNode(array('identifier' => 'root2'));
    $sub_node_id_2 = $this->imp->createNode(array('identifier' => '2_test'), $root_id2);

    $this->assertFalse($this->imp->getNodeByPath(''));
    $this->assertFalse($this->imp->getNodeByPath('/root///'));
    $this->assertFalse($this->imp->getNodeByPath('/root/wow/yo'));
    $this->assertFalse($this->imp->getNodeByPath('/root/2_test'));
  }

  function testGetNodeByPath()
  {
    $root_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id_1 = $this->imp->createNode(array('identifier' => 'test1'), $root_id);
    $sub_node_id_1_1 = $this->imp->createNode(array('identifier' => 'test1'), $sub_node_id_1);
    $sub_node_id_1_1_1 = $this->imp->createNode(array('identifier' => 'test1'), $sub_node_id_1_1);
    $sub_node_id_1_1_2 = $this->imp->createNode(array('identifier' => 'test2'), $sub_node_id_1_1);

    $node = $this->imp->getNodeByPath('/root/');
    $this->assertEqual($node['id'], $root_id);
    $this->_checkResultNodesArray($node);

    $node = $this->imp->getNodeByPath('/root/test1/test1/');
    $this->assertEqual($node['id'], $sub_node_id_1_1);
    $this->_checkResultNodesArray($node,  __LINE__);

    $node = $this->imp->getNodeByPath('/root/test1/test1/test2');
    $this->assertEqual($node['id'], $sub_node_id_1_1_2);
    $this->_checkResultNodesArray($node,  __LINE__);

    $node = $this->imp->getNodeByPath('/root/test1/test1/test1/');
    $this->assertEqual($node['id'], $sub_node_id_1_1_1);
    $this->_checkResultNodesArray($node,  __LINE__);
  }

  function testGetPathToNode()
  {
    $root_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id_1 = $this->imp->createNode(array('identifier' => 'test1'), $root_id);
    $sub_node_id_1_1 = $this->imp->createNode(array('identifier' => 'test1'), $sub_node_id_1);
    $sub_node_id_1_1_1 = $this->imp->createNode(array('identifier' => 'test1'), $sub_node_id_1_1);
    $sub_node_id_1_1_2 = $this->imp->createNode(array('identifier' => 'test2'), $sub_node_id_1_1);

    $path = $this->imp->getPathToNode($root_id, '|');
    $this->assertEqual($path, '|root');

    $path = $this->imp->getPathToNode(array('id' => $sub_node_id_1_1,
                                               'identifier' => 'test1'));
    $this->assertEqual($path, '/root/test1/test1');

    $path = $this->imp->getPathToNode($sub_node_id_1_1_2, '|');
    $this->assertEqual($path, '|root|test1|test1|test2');

    $path = $this->imp->getPathToNode(-1000000);
    $this->assertNull($path);
  }

  /*function testGetSubBranchByPathFailed()
  {
    $this->assertFalse($this->imp->getSubBranch(1));
  } */

  function testGetNodesByIds()
  {
    $root_id = $this->imp->createNode(array('identifier' => 'root'));
    $sub_node_id_1 = $this->imp->createNode(array('identifier' => 'test1'), $root_id);
    $sub_node_id_1_1 = $this->imp->createNode(array('identifier' => 'test1'), $sub_node_id_1);
    $sub_node_id_1_1_1 = $this->imp->createNode(array('identifier' => 'test1'), $sub_node_id_1_1);
    $sub_node_id_1_1_2 = $this->imp->createNode(array('identifier' => 'test2'), $sub_node_id_1_1);

    $rs = $this->imp->getNodesByIds(
      array(
        $root_id,
        $sub_node_id_1,
        $sub_node_id_1_1,
        $sub_node_id_1_1_2,
        $sub_node_id_1_1_1,
        -1
      )
    )->sort(array('id' => 'ASC'));

    $nodes = $rs->getArray('id');

    $this->assertEqual(sizeof($nodes), 5);
    $this->_checkResultNodesArray($nodes,  __LINE__);

    $rs = $this->imp->getNodesByIds(
      array(
        $sub_node_id_1,
        $sub_node_id_1_1,
        $sub_node_id_1_1_1,
        -1
      )
    )->sort(array('id' => 'ASC'));

    $nodes = $rs->getArray('id');

    $this->assertEqual(sizeof($nodes), 3);
    $this->_checkResultNodesArray($nodes,  __LINE__);

    $rs = $this->imp->getNodesByIds(array());

    $nodes = $rs->getArray();

    $this->assertEqual(sizeof($nodes), 0);
  }

  function testGetChildrenAllFailed()
  {
    $this->assertNull($this->imp->getChildrenAll(10000));
  }
  
  function testGetChildrenAll()
  {
    $this->imp->initTree(array('identifier' => 'root'));
    $id = $this->imp->createNode(array('identifier' => 'node_1'));
    $node_id = $this->imp->createNode(array('identifier' => 'node_1_1'), $id);
    $child_id = $this->imp->createNode(array('identifier' => 'node_1_1_1'), $node_id);
    $this->imp->createNode(array('identifier' => 'node_1_2'), $id);
    
    $children = $this->imp->getChildrenAll($id);
    
    $this->assertEqual(3, $children->count());      
  }
  
  function testGetChildrenDepth()
  {
    $root_id = $this->imp->initTree(array('identifier' => 'root'));
    $sub_node_id_1 = $this->imp->createNode(array('identifier' => 'test', $root_id));
    $sub_node_id_1_1 = $this->imp->createNode(array('identifier' => 'test'), $sub_node_id_1);
    $sub_node_id_1_1_1 = $this->imp->createNode(array('identifier' => 'test'), $sub_node_id_1_1);
    $sub_node_id_1_1_2 = $this->imp->createNode(array('identifier' => 'test'), $sub_node_id_1_1);

    //getting all
    $rs = $this->imp->getChildren($sub_node_id_1, -1);
    $branch = $rs->getArray('id');
    $this->assertEqual(3, sizeof($branch));


    $node = current($branch);
    $this->assertEqual($node['id'], $sub_node_id_1_1, '%s, invalid parameter: id');


    //getting at depth = 1
    $rs = $this->imp->getChildren($sub_node_id_1, 1);
    $branch = $rs->getArray('id');
    $this->assertEqual(1, sizeof($branch));
  }
  
  function testCountChildrenDepth()
  {
    $this->imp->initTree(array('identifier' => 'root'));
    $id = $this->imp->createNode(array('identifier' => 'node_1'));
    $node_id = $this->imp->createNode(array('identifier' => 'node_1_1'), $id);
    $child_id = $this->imp->createNode(array('identifier' => 'node_1_1_1'), $node_id);
    $this->imp->createNode(array('identifier' => 'node_1_2'), $id);
    
    $children = $this->imp->countChildren($id, 1);
    $this->assertEqual(2, $children);
    
    $children = $this->imp->countChildren($id, 2);
    $this->assertEqual(3, $children);
    
    $children = $this->imp->countChildren($id, -1);
    $this->assertEqual(3, $children);            
  }
  
  function testCountChildrenAllFailed()
  {
    $this->assertNull($this->imp->countChildrenAll(10000));
  }
  
  function testCountChildrenAll()
  {
    $this->imp->initTree(array('identifier' => 'root'));
    $id = $this->imp->createNode(array('identifier' => 'node_1'));
    $node_id = $this->imp->createNode(array('identifier' => 'node_1_1'), $id);
    $child_id = $this->imp->createNode(array('identifier' => 'node_1_1_1'), $node_id);
    $this->imp->createNode(array('identifier' => 'node_1_2'), $id);
    
    $children = $this->imp->countChildrenAll($id);
    
    $this->assertEqual(3, $children);      
  }
  
  function _checkResultNodesArray($nodes, $line='')
  {
    if(isset($nodes['id']))//check for array
      $this->assertEqual($this->imp->getNode($nodes['id'])->export(), $nodes->export());
    else
      foreach($nodes as $node)
        $this->assertEqual($this->imp->getNode($node['id'])->export(), $node->export());
  }

  function _checkProperNesting($nodes, $line='')
  {
    $this->assertEqual(lmbArrayHelper :: sortArray($nodes, array('path' => 'ASC')),
                       $nodes);

    $path = lmbArrayHelper :: getMinColumnValue('path', $nodes, $index);
    $parent_paths[] = $this->_getParentPath($path);

    $counter = 0;
    foreach($nodes as $id => $node)
    {
      $parent_path = $this->_getParentPath($node['path']);

      $this->assertTrue(in_array($parent_path, $parent_paths),
        'path is improperly nested: ' . $node['path'] . ' , expected parent not found: ' . $parent_path . ' at line: ' . $line);

      $parent_paths[] = $node['path'];
    }
  }

  function _getParentPath($path)
  {
    preg_match('~^(.*/)[^/]+/$~', $path, $matches);
    return $matches[1];
  }
}
?>
