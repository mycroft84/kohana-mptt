# Introduction #

It's simple, you declare your model with all validation rules that come with it. Nothing new here, but now you declare a form which retrieves the model and you select which fields the form should have. You can now ask the form to display with all the values from the model.

Example of declaring a form.
```
class User_Form_Core extends Model_Formation{
	
//Exclude these fields, all other fields in the model are displayed
protected $exclude=array('id','modified','created','role_id');

//Include these fields, all fields not in here are not displayed
protected $form_fields=array();

//Which model this form takes after
protected $_model='User_Model';

protected $disabled=array('modified'); //shows the form field but disabled so it can't be edited.

}
```

Example of the model
```
class User_Model extends ORM{
	protected $validate=array(
	'name'=>array(	
		'rules'=>array(array('Rule_Max_Length',50)),
		'pre_filters'=>array('ucfirst')
		
		),
	'email'=>array(
		'type'=>'email',
		'rules'=>array('Rule_Required'),
		),
	'preference'=>array(
		'type'=>'checklist',
		),
	'comment'=>	array(
		'pre_filters'=>array('trim'))
	);
}
```
Now you can start generating your forms:
A random controller
```
public function create_user()
{
    $form=new User_Form();
    if($form->save())
    {
       echo 'User saved';
    }
    else
    {
       echo $form->render();
    }

}
public function edit_user($id)
{
    $form=new User_Form($id);//or $form=new User_Form(new User_Model($id));
    if($form->save())
    {
       echo 'User saved';
    }
    else
    {
       echo $form->render();
    }
}
```
That's it, the render() method will give you a form with rules, validation and all retrieved from the model. Easy for your backend and perhaps your frontend if you need some forms quickly.

Remember, the form is an instance of Formation so you can do this:
```

public function edit_user($id)
{
    $form=new User_Form($id);
    $form->add_element('password','password_match')->add_rule('Rule_Matches',$form['password']);
    if($form->save())
    {
       echo 'User saved';
    }
    else
    {
       echo $form->render();
    }
}
```
You can change the entire form after you retrieved it from the model.

But you can also change stuff from your User\_Form class, such as the template
```
class User_Form_Core extends Model_Formation{
	
//...
protected $template='your_template';

}
```