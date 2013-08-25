## Schedule Parser

This is the initial code that parses the schedule from the registrar's website.
The code normally handles classes that don't have additional meta information, like reservation data,
multi dates/locations.

### Usage

```php
<?php
echo parse_schedule('1139', 'PHYS', '236')
?>
```

### Output

```
Array
(
    [0] => Array
        (
            [department] => PHYS
            [number] => 236
            [credits] => 0.5
            [title] => Computational Physics 1
            [course_id] => 6699
            [section] => LEC 001
            [campus] => UW    U
            [associated_classes] => 1
            [related_component_1] => 101
            [related_component_2] => &nbsp
            [enrollment_capacity] => 120
            [enrollment_total] => 84
            [waiting_capacity] => 0
            [waiting_total] => 0
            [dates] => 08:30-09:20MWF
            [location] => B1    370
            [instructor] => O'Donovan,Chris
        )

    [1] => Array
        (
            [department] => PHYS
            [number] => 236
            [credits] => 0.5
            [title] => Computational Physics 1
            [course_id] => 6700
            [section] => TUT 101
            [campus] => UW    U
            [associated_classes] => 1
            [related_component_1] => &nbsp
            [related_component_2] => &nbsp
            [enrollment_capacity] => 120
            [enrollment_total] => 84
            [waiting_capacity] => 0
            [waiting_total] => 0
            [dates] => 09:30-10:20F
            [location] => B1    370
            [instructor] => O'Donovan,Chris
        )

)
```

### Edge Cases

The code currently fails for the following courses, where not all information is parsed

- [PHYS 131L](http://www.adm.uwaterloo.ca/cgi-bin/cgiwrap/infocour/salook.pl?sess=1139&subject=PHYS&level=under&cournum=131L)
- [PHYS 454](http://www.adm.uwaterloo.ca/cgi-bin/cgiwrap/infocour/salook.pl?sess=1139&subject=PHYS&level=under&cournum=454)
- [PHYS 353L](http://www.adm.uwaterloo.ca/cgi-bin/cgiwrap/infocour/salook.pl?sess=1139&subject=PHYS&level=under&cournum=353L)
- [PHYS 121L](http://www.adm.uwaterloo.ca/cgi-bin/cgiwrap/infocour/salook.pl?sess=1139&subject=PHYS&level=under&cournum=121L)
- [CS 246](http://www.adm.uwaterloo.ca/cgi-bin/cgiwrap/infocour/salook.pl?sess=1139&subject=CS&level=under&cournum=246)


## How can I help?

Any improvements to the scrapers are appreciated:

- The improvements should be in PHP
- You are free to modify the array structure/hierarchy

