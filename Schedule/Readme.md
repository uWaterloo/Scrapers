## Schedule Parser

This is the initial code that parses the schedule from the registrar's website.
The code normally handles classes that don't have additional meta information, like reservation data,
multi dates/locations.

### Usage

```php
<?php
echo json_encode(parse_schedule('1139', 'PHYS', '236'));
?>
```

### Output

```json
[
   {
      "department":"PHYS",
      "number":"236",
      "credits":"0.5",
      "title":"Computational Physics 1",
      "course_id":"6699",
      "section":"LEC 001",
      "campus":"UW U",
      "associated_class":"1",
      "related_component_1":"101",
      "related_component_2":"",
      "enrollment_capacity":"120",
      "enrollment_total":"78",
      "waiting_capacity":"0",
      "waiting_total":"0",
      "topic":"",
      "reserves":[

      ],
      "classes":[
         {
            "dates":{
               "start_time":"08:30",
               "end_time":"09:20",
               "weekdays":"MWF",
               "start_date":"",
               "end_date":"",
               "is_tba":false,
               "is_cancelled":false,
               "is_closed":false
            },
            "location":{
               "building":"B1",
               "room":"370"
            },
            "instructors":[
               "O'Donovan,Chris"
            ]
         }
      ],
      "held_with":[

      ]
   },
   {
      "department":"PHYS",
      "number":"236",
      "credits":"0.5",
      "title":"Computational Physics 1",
      "course_id":"6700",
      "section":"TUT 101",
      "campus":"UW U",
      "associated_class":"1",
      "related_component_1":"",
      "related_component_2":"",
      "enrollment_capacity":"120",
      "enrollment_total":"78",
      "waiting_capacity":"0",
      "waiting_total":"0",
      "topic":"",
      "reserves":[

      ],
      "classes":[
         {
            "dates":{
               "start_time":"09:30",
               "end_time":"10:20",
               "weekdays":"F",
               "start_date":"",
               "end_date":"",
               "is_tba":false,
               "is_cancelled":false,
               "is_closed":false
            },
            "location":{
               "building":"B1",
               "room":"370"
            },
            "instructors":[
               "O'Donovan,Chris"
            ]
         }
      ],
      "held_with":[

      ]
   }
]
```

### How can I help?

Any improvements to the scrapers are appreciated:

- The improvements should be in PHP
- You are free to modify the array structure/hierarchy

