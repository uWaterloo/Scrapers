## Course Calendar Scraper

This folder contains the initial code to parse the course details from the
undergraduate calendar. 

### Usage

```php
echo parse_courses('MATH');
```

### Output

```json
{
   "id":"006872",
   "department":"MATH",
   "number":"128",
   "title":"Calculus 2 for the Sciences",
   "credits":"0.50",
   "description":"Transforming and evaluating integrals; application to volumes and arc length; improper integrals. Separable and linear first order differential equations and applications. Introduction to sequences. Convergence of series; Taylor polynomials, Taylor's Remainder Theorem, Taylor series and applications. Parametric\/vector representation of curves; particle motion and arc length. Polar coordinates in the plane. [Offered: F,W,S]",
   "instructions":[
      "LEC",
      "TUT"
   ],
   "prerequisites":"One of MATH 117, 127, 137, 147.",
   "antirequisites":"MATH 118, 119, 138, 148",
   "corequisites":null,
   "crosslistings":null,
   "terms_offered":[
      "F",
      "W",
      "S"
   ],
   "offerings":{
      "online":true,
      "online_only":false,
      "st_jerome":false,
      "st_jerome_only":false,
      "renison":false,
      "renison_only":false,
      "conrad_grebel":false,
      "conrad_grebel_only":false
   },
   "needs_department_consent":false,
   "needs_instructor_consent":false,
   "extra":null,
   "notes":null,
   "calendar_year":"1314",
   "type":"undergraduate",
   "url":"http:\/\/www.ucalendar.uwaterloo.ca\/1314\/COURSE\/course-MATH.html#MATH128"
}
```

### How can I help?

If you can better represent the current information or find bugs
or missing information, let us know 

