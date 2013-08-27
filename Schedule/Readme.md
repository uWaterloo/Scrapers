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
            [campus] => UW U
            [associated_classes] => 1
            [related_component_1] => 101
            [related_component_2] =>
            [enrollment_capacity] => 120
            [enrollment_total] => 84
            [waiting_capacity] => 0
            [waiting_total] => 0
            [reserves] => Array
                (
                )
            [classes] => Array
                (
                    [0] => Array
                        (
                            [dates] => Array
                                (
                                    [start_time] => 08:30
                                    [end_time] => 09:20
                                    [weekdays] => MWF
                                    [start_date] =>
                                    [end_date] =>
                                )
                            [location] => B1 370
                            [instructor] => O'Donovan,Chris
                        )
                )
        )

    [1] => Array
        (
            [department] => PHYS
            [number] => 236
            [credits] => 0.5
            [title] => Computational Physics 1
            [course_id] => 6700
            [section] => TUT 101
            [campus] => UW U
            [associated_classes] => 1
            [related_component_1] =>
            [related_component_2] =>
            [enrollment_capacity] => 120
            [enrollment_total] => 84
            [waiting_capacity] => 0
            [waiting_total] => 0
            [reserves] => Array
                (
                )
            [classes] => Array
                (
                    [0] => Array
                        (
                            [dates] => Array
                                (
                                    [start_time] => 09:30
                                    [end_time] => 10:20
                                    [weekdays] => F
                                    [start_date] =>
                                    [end_date] =>
                                )
                            [location] => B1 370
                            [instructor] => O'Donovan,Chris
                        )
                )
        )
)
```

### How can I help?

Any improvements to the scrapers are appreciated:

- The improvements should be in PHP
- You are free to modify the array structure/hierarchy

