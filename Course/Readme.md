## Course Calendar Scraper

This folder contains the initial code to parse the course details from the
undergraduate calendar. 

### Usage

```php
echo parse_courses('MATH');
```

### Output

```
Array
(
    [department] => MATH
    [number] => 128
    [credits] => 0.50
    [url] => http://www.ucalendar.uwaterloo.ca/1314/COURSE/course-MATH.html#MATH128
    [type] => Undergraduate
    [calendar_year] => 1314
    [instructions] => Array
        (
            [0] => LEC
            [1] => TUT
        )

    [id] => 006872
    [title] => Calculus 2 for the Sciences
    [description] => Transforming and evaluating integrals; application to volumes and arc length; improper integrals. Separable and linear first order differential equations and applications. Introduction to sequences. Convergence of series; Taylor polynomials, Taylor's Remainder Theorem, Taylor series and applications. Parametric/vector representation of curves; particle motion and arc length. Polar coordinates in the plane. [Offered: F,W,S]
    [prerequisites] => Prereq: One of MATH 117, 127, 137, 147.
    [antirequisites] => Antireq: MATH 118, 119, 138, 148
    [offered_online] => 1
    [terms_offered] => F,W,S
)
```

### How can I help?

If you can better represent the current information or find bugs
or missing information, let us know 

