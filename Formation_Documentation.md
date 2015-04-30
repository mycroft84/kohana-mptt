# Introduction #
The Formation module is a replacement of the current validation library as well as the Forge library. It was created due to some specific needs of mine and developed as such. It has reached a state in which it might be beneficial to other users as well.

I would greatly appreciate feedback, comments, bugfixes etc.

You can download the latest release from http://code.google.com/p/kohana-mptt/downloads/list or checkout the latest edition from the subversion repository.

If you're looking for documentation on ModelToForm , follow the link. ModelToForm is a way to have forms generated from your models. It's a wIP.
## Installation ##
Before installation you should know how to work with Kohana else this might become difficult.

You should extract the archive and put it into your modules folder. In your application/config/config.php you should enable this module.

## Documentation ##
Now, you'll find several files in the formation folder: Field.php, Formation.php and Validation.php as well as a folder elements and a folder rules. As is usual in Kohana the files map to classes.

The Field class stands for an input field in a form. Elements extend from this field, such as the Input element, Password element etc.. A field has rules for validation, these rules are found in the rules folder.

The Validation class does just validation, it doesn't generate your forms. Formation class extends Validation and does generate your forms.

**Note** I moved rules and elements in a subfolder for clarity. This means they cannot be autoloaded by Kohana unless you register a new autoloader. The Formation or Validation class usually do this but if you want to use elements or rules outside of these classes you should register the autoloader yourself. You can find it in Validation.php

### Your first form ###
First we use the Validation class without form generation. We have a form which has to fields 'username' and 'password'.
```
$_POST=new Validation($_POST);
$_POST['username']->add_rule('Rule_Required');
$_POST['password']->add_pre_filter('trim')->add_rule('Rule_Min_Length',8)->add_rule('Rule_Required');
```

You see, it's easy. Normally the $_POST is filled by php and you use it, now you set some rules on it's keys and let it validate. If the form is submitted the constructor takes the $_POST values. If not you still create the fields in your validation and add the rules. Would work for $_GET as well of course._

Other methods you can carry out on a $_POST field.
  * add\_rules, remove\_rule, clear\_rules
  * add\_post\_filter, add\_post\_filters, remove\_post\_filter, clear\_post\_filters
  * add\_pre\_filters etc.
  * add\_callback etc.
  * get\_value(), set\_value(), get\_name(),
  * get/set\_screen\_name (it's the name of the field used in labels or errors)
  * error() returns array with one error with message
  * add\_error() mostly used internally but might be useful for callbacks
  * remove\_error()
  * error\_format() supply string with {message} as message placeholder
  * get\_error\_format()
  * set\_language\_file() set language file for all rules this field has (default: validation which maps to i18n/language/validation.php
  * validate() validates the field, returns boolean first pre-filters, rules, callbacks post-filters_


You can use also get and set the properties, only not name
```
$_POST['password']->screen_name='asdf';
```

You can at any time echo the object
```
echo $_POST['username'];
```

It's an array object, means you can sort it, count it, put it in a loop
```
$_POST->asort();
count($_POST);
```

Fun he

Ok, on to the Validation class

### Validation class ###
Some of the class methods
  * add\_rule, rules, clear and all that as well as for callbacks and pre/post filters so you can attach a rule to whole league of fields
  * error\_format() set error format for all fields
  * validate() validates all fields
  * errors() returns array of fields with an error for each if there is one
  * validate\_parial(array of fieldnames) validate form partially
  * validate\_partial\_json same as above just returns a json boolean
  * set\_language\_file() set language file for all rules in every field

```
$_POST=new Validation($_POST);
$_POST['username']->add_rule('Rule_Required');
$_POST['password']->add_pre_filter('trim')->add_rule('Rule_Min_Length',8)->add_rule('Rule_Required');
if($_POST->validate())
{
 echo 'have fun';
}
else
{
 var_dump($_POST->errors();
}
```

Again, fairly simple or not :)

## Formation class ##
Works pretty similar to the Validation class when it comes to adding rules, filters and callbacks. Every Element inherits from the Field class so all those methods are available.

### First form ###
```
$form=new Formation;
$form->set_method('POST'); //is default but still
$form->set_attr('id','form_css_id');

$form->add_element('input','username')->add_rule('Rule_Exact_Length',4);

if($form->validate())
{
  echo 'yah';
}
else
{
  echo $form; //will call $form->render()
}
```
You can see I create an instance of the form, set it's method and id add one element with a rule. Then I try to validate it, if it fails it renders the form.

The class renders a view with the form. This view or template can be found in formation/views/formation\_template.php You can change the template with
```
$form->set_template('your_template');
```
get\_template() retrieves the template.

You might have some variables in your template that you want to fill. By default there is an $legend variable. You can set variables in your template like this, just like views in Kohana.
```
$form->legend='your legend';
$form->some_var='some value';
```

Formation extends Validation so the Validation methods are also available in the Formation class.
#### Elements ####
Like seen in the example above you can add elements.
Related methods
  * add\_elements
  * remove\_element
  * clear\_elements

You can also add elements like this
```
$input=new Element_Input('email');
$input->class='some_class';
$input->set_attr('id','some_id')
      ->add_rule('Rule_Matches',$form['username']);

$form->add_element($input)->label()->set_text('email label ');
```

This means there are two syntaxes

```
$form->add_element(new Element_Input('name'));
$form->add_element('input','name');
```
Elements you can add
  * Element\_Checkbox
  * Element\_Checklist
  * Element\_Dropdown
  * Element\_Email -  same as input but comes with the email rule
  * Element\_Csrf - cross site request forgery protection
  * Element\_Group - put some elements between 

&lt;fieldset&gt;


  * Element\_Hidden
  * Element\_Input
  * Element\_Label (not really an element, is covered later)
  * Element\_Password
  * Element\_Submit
  * Element\_Textarea
  * Element\_Upload

##### Label #####
Labels are special. See example
```
$form->add_element('input','name')->label()->set_attr('id','someid')->set_text('some text');
```
label() returns an object label of which you can set the attributes and text.

##### Special elements #####
Some elements have special methods. Check the source.
$form->add\_element('upload','load\_file')->set\_directory('some\_dir');

#### Groups ####
Groups also work as elements but do not extend Element\_Input but Formation. The default template renders them within a 

&lt;fieldset&gt;

 element. You can nest them.

```
$form->add_element('input','email');
$form->add_group(array('email'),'group1');

$form['group1']->add_element('password','Password field')->add_rule('Rule_Min_Length',8);
$form['group1']->add_group(array(),'group2');
$form['group1']['group2']->add_element('input','sth');
```
The first argument of the add\_group method can be a string or array with the names of elements, or elements themselves. If they are names they will be retrieved from the group's parents and moved to the group. Else, they will simply be added. Only the parent's elements are searched for, grandparents are not.

Groups extend Formation so many Formation methods are valid here. The default template for groups is the same as for Formation.

### Rules, callbacks and filters ###
Callbacks and filters are all callbacks.

#### Rules ####
Rules are the following
  * Rule\_Alpha
  * Rule\_Alpha\_Numeric
  * Rule\_Array
  * Rule\_Csrf - no need to call manually, is included in Element\_Csrf
  * Rule\_Depends\_On
  * Rule\_Digit
  * Rule\_Email //is included in Element\_Email
  * Rule\_Exact\_Length
  * Rule\_Ip
  * Rule\_Length
  * Rule\_Matches
  * Rule\_Max\_Length
  * Rule\_Min\_Length
  * Rule\_Numeric
  * Rule\_Range
  * Rule\_Regex
  * Rule\_Required
  * Rule\_Upload\_Allowed
  * Rule\_Upload\_Required
  * Rule\_Upload\_Size
  * Rule\_Url

Some accept arguments, you can also set the error message, language file. In the language files that come with Formation you can see the syntax used.

## Storing forms ##
Using a form class you can store forms and use the throughout your application.
**Example: My\_Form.php**
```
class My_Form_Core extends Formation{
   public function __construct()
   {
      parent::__construct();
      $this->legend='Some legend';
      $this->add_element('input','email')->add_rule('Rule_Required');
      $this->add_element('input','username')->add_rule('Rule_Required');
      $this->add_element('submit','Submit');
   }
}
```
Now loading the object is as easy as `$form=new My_Form; $form->render();`
## Example of Formation ##
A complete example
```
$form=new Formation;
		
$form->add_element('input','username')->add_rule('Rule_Exact_Length',4);

$form->add_element('upload','load')->add_rule('Rule_Upload_Size',1050);
		$form->add_element('input','email')->add_rule('Rule_Email')->add_rule('Rule_Required');
$form->add_group(array('username'),'group1');
$form['group1']->add_element('password','Password field')->add_rule('Rule_Min_Length',8);

$form->add_element('submit','Submit');
$form['group1']->legend='User/pass here please';
$form->legend='Some legend';
if($form->validate())
{
	echo 'validates';
}
else
{
	echo $form;
}	
```