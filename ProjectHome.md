# Update #
I won't have time in the near future to do bugfixes, new features etc. Feel free to fork any of the stuff in this repo with attribution.

# MPTT for Kohana #
**This project has been superseded by a behaviour for ORM which does the same**
An implementation of MPTT for Kohanaphp using Kohana's ORM functionality.

It is based on the implementation of MPTT for CI: http://www.codeigniter.com/wiki/Nested_Sets/ but some major modifications to support ORM, scopes and parent ids

This means it is in functionality and syntax more similar to http://trac.symfony-project.com/wiki/sfPropelActAsNestedSetBehaviorPlugin

See [Documentation](Documentation.md) for more information about getting file and installing and such.

Please submit bugs when you see them. Be clear about the exact circumstances of when a method breaks integrity of a table.

  * [Roadmap](Roadmap.md) for MPTT
  * [Changelog](Changelog.md) for MPTT

# Tree behaviour for Kohana ORM #
Behaviour for Kohana ORM to do adjacency list trees.

# SpamCheck library for Kohana #
[SpamCheck](http://code.google.com/p/kohana-mptt/source/browse/trunk/spamcheck/?r=92) module for Kohana. Download libraries into a /libraries folder and you're done.

Gives scores ranging between -100 (bad) and +100 (good). Or just a boolean
Currently a links and a Akismet plugin
**Links**
```
$spam=new SpamCheck;
$spam->add_check('links')->set_link_penalty(4)->set_max_links(2); 
$spam->add_field('content','some content');
$score=$spam->check();
if(!$spam->is_spam())
   echo 'no spam';
```
If there are more than 2 links a penalty is given -(number\_of\_links^4) else a bonus of 100 points.

```
$spam=new SpamCheck;
$spam->add_check('links',2)->set_link_penalty(4)->set_max_links(2); 
$spam->add_check('akismet')->setAPIKey('api_key')->setBlogURL('http://blog url');
$spam->set_weight('akismet',1);
$spam->add_field('content','some content');
$score=$spam->check();
if(!$spam->is_spam())
   echo 'no spam';

$spam->get_weighted_scores();
$spam->get_scores();

```
Uses akismet for spam check of comments. Bear in mind you should have more fields than the 'content' field for a better success rate. Will be documented later.

The score is now an average between the links and the akismet plugin. The links plugin has a second argument giving it's weight. So the links plugin has the weight of 2 so is more important.

Adding new plugins is not hard. Stuff like the Defensio API might be added, or a bad words list.


# Formation/Validation #

Form generation and validation for Kohana.

Latest release on the right

[Formation\_Documentation](Formation_Documentation.md)