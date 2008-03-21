<?php
echo form::open();
echo 'Name: '.form::input('name') .'<br />';
echo 'Email: '.form::input('email').'<br />';
echo 'Preference: '.form::checkbox('preference[]','pizza').' Pizza <br />';
echo 'Preference: '.form::checkbox('preference[]','spaghetti').' Spaghetti <br />';
echo 'Preference: '.form::checkbox('preference[]','frenchfries').' French fries <br />';
echo 'Preference: '.form::checkbox('preference[]','sauerkraut').' Sauerkraut<br />';
echo 'Comment: '.form::textarea('comment').'<br />';
echo form::submit('Submit','Submit');
echo form::close();
?>