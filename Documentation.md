# Documentation #

Overview of the functionality of Kohana MPTT.

# Introduction #
Nested sets or modified preorder tree traversal (mptt) is a way to store hierarchies in a database.

The easiest implementation of hierarchies in a database is the adjacency list model. This means a record has a field named something like 'parent\_id' which stores the id of the parent of the record. The parent can have another parent and so on and so forth. Problem with retrieving the lineage of a record is that when there are several levels in the hierarchy the queries can become quite expensive.

The mptt approach solves this, the place of a record in the hierarchy is set when the record is created or moved and selecting the record and its lineage is thus less expensive. Result is, however, that moving/ deleting or creating records in the tree is expensive query-wise. Also, when the hierarchy is incorrectly set in the record the hierachy tree might be lost.

Major advantage is easy retrieval of multiple levels and speed in doing so. Edits are more costly so use only when the selects outnumber the updates,deletes and inserts by far.

There are much better explanations of the mptt and adjacency list model on the internet and I would **strongly** advice you to read those. They have diagrams and clear examples which make understanding mptt much easier. Also, I do not feel it is my place to explain mptt.

Some reading material
  * http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
  * http://www.sitepoint.com/article/hierarchical-data-database
  * http://phpriot.com/articles/nested-trees-1

I think it is important to know how mptt works when using this class so you know what happens.

## MPTT library ##
The MPTT library for Kohana makes it easier to work with nested sets. It takes care of most of the logic for you. Also, it works with the ORM library of Kohana and extends it. This means you can use the normal ORM syntax while working with nested sets.

**Note:** at present this library does not lock tables. This means the integrity of the tree might be at stake with multiple edits happening at once.

## Some notes on the jargon ##
Below a list of words used in the library and documentation which may be unclear.
  * tree - the hierarchy of several nodes visualized as a tree
  * node - a record in the tree, can have one and only one parent
  * parent - parent of a node
  * children - the direct children of a node  (not grandchildren)
  * descendants - children of a node, and their children, and their children, etc. etc.
  * siblings - parent has children x,y x is sibling of y, y is sibling of x
  * leaf - last node in a tree, has no children
  * root - the sole parent in a tree, has no parents
  * scope - each node in the tree has an identifier to see which scope it has, this way you can have multiple trees in one table
  * path - ancestry of a node

## Configuration ##
The MPTT.php file can be retrieved from svn http://kohana-mptt.googlecode.com/svn/trunk/MPTT.php
The file should be placed in a libraries folder.

Models are stored in the /models directory. The model of an mptt table will look something like this
```
class Place_Model extends MPTT {

    protected $left_column     =    'lft'; //database column where left values are stored
	
    protected $right_column    =    'rgt';//database colunn where right values are stored
	
    protected $parent_column   =    'parent_id';//database column where parent ids are stored
	
    protected $scope_column    =    'scope';//database column where the scope is stored
    
    function __construct($id = FALSE)
    {
        parent::__construct($id);
    }
}
```
The name of the model uses the ORM conventions of Kohana. {singular\_of\_table}_Model extends MPTT/_

Make sure the 4 columns listed above are present in the table.

The values above are also the default values, if these are your values you do not need to supply them in the model.


## Other notes ##
A node of whom its children are retrieved stores these children in ->children
These children store their parent in ->parent

```
//Earth root node has id#1
$earth=new Place_Model(1);

$earth->get_children();
foreach($earth->children as $child)
{
echo $child->name;
echo $child->parent->name;
}
```

## Retrieving a node ##
You can retrieve a single node by supplying its id to the constructor
```
$earth=new Place_Model(1);
```
But also by other ORM methods. I have not tested the overloading of where\_key(), this thus might not work at all times.

# Documentation #
As an example I will work with a table called 'places'. The root node is 'Earth', its children something like 'North-America','Europe','Asia', children of Europe 'UK','Germany','France', children of France might be 'Paris','Bordeaux','Lyon' etc. This is what roughly will be used.

## Creating the tree ##
### make\_root() ###
To create a tree you have to create a root node.
```
$root=new Place_Model();
$root->name='Earth';
$root->make_root();
```
Scope defaults to 1, with only 1 tree there's no need set it manually.

### Inserting childs ###
```
   Earth
Asia Europe
```
Say you want to insert a child named North-America, its place can be left of Asia, right of Asia or right of Europe.

#### insert\_as\_first\_child\_of() ####
```
//Earth root node has id#1
$earth=new Place_Model(1);

$na=new Place_Model();
$na->name='North-America';
$na->insert_as_first_child_of($earth);
```
Inserts the child before Asia

#### insert\_as\_last\_child\_of() ####
```
//Earth root node has id#1
$earth=new Place_Model(1);

$na=new Place_Model();
$na->name='North-America';
$na->insert_as_last_child_of($earth);
```
Inserts the child after Asia

#### insert\_as\_next\_sibling\_of() ####
```
//Asia node has id#2
$asia=new Place_Model(2);

$na=new Place_Model();
$na->name='North-America';
$na->insert_as_next_sibling_of($asia);
```
Inserts the child to the right of Asia.

#### insert\_as\_prev\_sibling\_of() ####
See insert\_next\_sibling\_of() but then it adds it to the right of Asia

## Retrieval methods ##
### get\_children($return) ###
If $return is true it will return an array of the children of the current node.
In either case the current node has a property ->children with the array of children.
Each child also has the parent property with the parent object stored in it.

Earth has children Asia, Europe, North-America
```
//Earth root node has id#1
$earth=new Place_Model(1);

$earth->get_children();
foreach($earth->children as $child)
{
echo $child->name;
echo $child->parent->name;
}

$children=$earth->get_children(true);
foreach($children as $child)
{
echo $child->name;

}

```

### get\_decendants() ###
Stores the children of the current node in `$node->children` all the way done. Grandchildren are stored in the `$child->children` property. With a recursion you can thus display the entire tree.

### get\_leaves() ###
Returns an array of the leaves of the current tree.

### get\_path() ###
Returns an array with the path towards the current node.

### get\_root() ###
Returns the root of the current tree as an object.
```

$earth=new Place_Model();
$earth=$earth->get_root();

$earth->get_descendants();
//$earth now has the entire tree sored in it
```

### get\_first\_child() ###
Returns first child of current node

### get\_last\_child() ###
Returns last child of current node

### get\_prev\_sibling() ###
Returns previous sibling of current node.

### get\_next\_sibling ###
Returns next sibling of current node.

### get\_parent ###
Returns parent of current node.

### get\_tree($parent\_node) ###
Returns object with tree. You can supply it with an id or a node object.
Tree can be supplied to mptt->debug\_tree();

## Deleting methods ##
### delete\_tree() ###
Deletes an tree of the current scope, use with caution

### delete\_node($children) ###
Deletes current node and any children when $children is true. Default = true
It will not delete the children when $children is false and will then move up the children in the hierarchy.

### delete\_descendants() ###
Deletes descendants of current node but not the node itself

### delete\_children() ###
Will just delete children of current node but not its descendants so descendants move up the tree.

## Scope ##
The scope of an object will be retrieved from the record when the object is created.
### get\_scope() ###
Returns scope of current object

### set\_scope ###
Set scope of current object, only works on new objects.
```
//Asia node has id#2
$asia=new Place_Model(2);

$na=new Place_Model();
$na->set_scope(2);
$na->name='North-America';
$na->insert_as_next_sibling_of($asia);
```

## Informational methods ##
### get\_number\_of\_descendants() ###
Returns number of descendants of current node

### get\_number\_of\_children() ###
Returns number of children of current node

### get\_level() ###
Returns level of current node, root is 0

## Helper methods ##
### is\_valid\_node() ###
Superficial way of checking the node is valid

### has\_parent() ###
### has\_next\_sibling() ###
### has\_prev\_sibling() ###
### has\_descendants() ###
### is\_root() ###
### is\_leaf() ###
### is\_descendant\_of() ###
### is\_child\_of() ###
### is\_equal\_to ###
### is\_descendant\_of\_or\_equal\_to() ###

## Moving methods ##
This should work but has not yet been thoroughly tested. It should be self explanatory.
Also moves children of the current node to the target
### move\_to\_next\_sibling\_of($target) ###
### move\_to\_prev\_sibling\_of($target) ###
### move\_to\_first\_child\_of($target) ###
### move\_to\_last\_child\_of($target) ###

## Utility functions ##
### debug\_tree($tree,$disp\_col) ###
Outputs the tree, ids, left and right values and parent ids, + the column you want to display.
### rebuild\_tree($parent\_id,$left) ###
Rebuild the mptt tree according to the parent\_ids. Supply with id of the root node and its left value: 1



